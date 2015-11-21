<?php

namespace psrm\controllers;

use psrm\PSRM;
use psrm\utils\Views;
use psrm\models\Donation as DonationSettings;

class Donation {
	protected $model;
	protected $view;

	public function __construct() {
		$this->model = new DonationSettings();
		$this->view  = new Views( PSRM::$views );
		add_shortcode( PSRM::$slug . '-donation-form', [ $this, 'display_donation_form' ] );
		add_action( 'wp_ajax_process_donation', [ $this, 'process_donation' ] );
		add_action( 'wp_ajax_nopriv_process_donation', [ $this, 'process_donation' ] );
	}

	public function display_donation_form() {
		return $this->view->render( 'donation-form', array(
			'donation_amounts'      => $this->model->getOption( 'donation_amounts', 'donations' ),
			'donation_funds'        => $this->model->getOption( 'donation_funds', 'donations' ),
			'allow_custom_amount'   => STRIPE_ALLOW_CUSTOM_AMOUNT,
			'custom_donation_floor' => STRIPE_CUSTOM_DONATION_FLOOR
		) );
	}

	public function process_donation() {
		$data = $this->doValidation( $_POST, STRIPE_CUSTOM_DONATION_FLOOR );

		$donation_amounts = $this->model->getOption( 'donation_amounts', 'donations' );

		if ( $data[ 'success' ] &&
		     (
			     $data[ 'result' ][ 'amount' ] == 'custom' ||
			     isset( $donation_amounts[ $data[ 'result' ][ 'amount' ] ] )
		     )
		) {
			if ( $data[ 'result' ][ 'amount' ] == 'custom' ) {
				$donation_amount = $data[ 'result' ][ 'customAmount' ];
			} else {
				$donation_amount = $donation_amounts[ $data[ 'result' ][ 'amount' ] ];
			}

			try {
				\Stripe\Stripe::setApiKey( STRIPE_SECRET_KEY );

				$response = \Stripe\Charge::create( [
					'amount'        => $donation_amount * 100,
					'currency'      => 'usd',
					'source'        => $data[ 'result' ][ 'stripeToken' ],
					'receipt_email' => $data[ 'result' ][ 'email' ],
					'metadata'      => [
						'Fund' => $data[ 'result' ][ 'fund' ]
					]
				] );

				wp_mail(
					$this->model->getOption( 'email_successful_donation', 'donations' ),
					'Successful donation for $' . $response->amount / 100,
					'Successful donation! View this transaction in Stripe: ' . $this->model->getOption( 'stripe_dashboard_url', 'donations' ) . $response->id
				);

				$trans = [
					'id'      => $response->id,
					'revenue' => $donation_amount,
				];
				$items = [
					[
						'name'     => 'Donation of $' . $donation_amount,
						'sku'      => 'DONATE' . $donation_amount,
						'category' => 'Donation',
						'price'    => $donation_amount,
						'qty'      => 1,
					]
				];

				$output = [
					'success'       => true,
					'message'       => 'Thank you for your donation!',
					'transactionId' => $response->id,
					'analytics'     => $this->view->render( 'ecommerce-ga-donation', compact( 'trans', 'items' ) ),
				];
			} catch ( \Stripe\Error\Card $e ) {
				$output = [
					'success'      => false,
					'message'      => 'Payment processing failed.',
					'responseText' => $e->getMessage()
				];
			} catch ( \Exception $e ) {
				$output = [
					'success'      => false,
					'message'      => 'There was an error.',
					'responseText' => $e->getMessage()
				];
			}

		} else {
			$output = [
				'success'            => false,
				'message'            => 'Form validation failed. The error(s) are listed below. Correct the errors and resubmit.',
				'responseReasonCode' => 0,
				'responseCode'       => 0,
				'responseText'       => $data[ 'result' ]
			];
		}

		echo json_encode( $output );

		exit;
	}

	protected function doValidation( $data, $donation_min ) {
		$gump = new \GUMP();

		$data = $gump->sanitize( $data );

		$gump->validation_rules( [
			'amount'       => 'required',
			'fund'         => 'required',
			'customAmount' => 'numeric|min_numeric,' . $donation_min,
			'email'        => 'required|valid_email',
			'stripeToken'  => 'required'
		] );

		$validated_data = $gump->run( $data );

		if ( $validated_data === false ) {
			return [
				'success' => false,
				'result'  => $gump->get_readable_errors( true )
			];
		} else {
			return [
				'success' => true,
				'result'  => $validated_data
			];
		}
	}

//	public function
}