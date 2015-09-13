<?php

namespace psrm\controllers;

use psrm\PSRM;
use psrm\utils\Views;
use psrm\models\Donation as DonationSettings;

class Donation
{
	protected $model;
	protected $view;

	public function __construct()
	{
		$this->model = new DonationSettings();
		$this->view = new Views(PSRM::$views);
		add_shortcode(PSRM::$slug . '-donation-form', [$this, 'display_donation_form']);
		add_action('wp_ajax_process_donation', [$this, 'process_donation']);
		add_action('wp_ajax_nopriv_process_donation', [$this, 'process_donation']);
	}

	public function display_donation_form()
	{
		return $this->view->render( 'donation-form', array(
			'donation_amounts'      => $this->model->getOption( 'donation_amounts', 'donations' ),
			'allow_custom_amount'   => $this->model->getOption( 'allow_custom_amount', 'donations' ),
			'custom_donation_floor' => $this->model->getOption( 'custom_donation_floor', 'donations' )
		) );
	}

	public function process_donation()
	{
		$data = $this->doValidation( $_POST, $this->model->getOption( 'custom_donation_floor', 'donations' ) );

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

			$transaction = new \AuthorizeNetAIM;
			$transaction->setSandbox( AUTHORIZE_NET_SANDBOX );
			$transaction->setFields(
				array(
					'amount'     => $donation_amount,
					'card_num'   => $data[ 'result' ][ 'cc_num' ],
					'exp_date'   => $data[ 'result' ][ 'expire_date' ],
					'email'      => $data[ 'result' ][ 'email' ],
					'card_code'  => $data[ 'result' ][ 'cvc' ],
					'first_name' => $data[ 'result' ][ 'x_first_name' ],
					'last_name'  => $data[ 'result' ][ 'x_last_name' ],
					'address'    => $data[ 'result' ][ 'x_address' ],
					'city'       => $data[ 'result' ][ 'x_city' ],
					'state'      => $data[ 'result' ][ 'x_state' ],
					'zip'        => $data[ 'result' ][ 'x_zip' ]
				)
			);
			$response = $transaction->authorizeAndCapture();
			if ( $response->approved ) {

				$trans = [
					'id'      => $response->transaction_id,
					'revenue' => $response->amount,
				];
				$items = [
					[
						'name'     => 'Donation of $' . $response->amount,
						'sku'      => 'DONATE' . $response->amount,
						'category' => 'Donation',
						'price'    => $response->amount,
						'qty'      => 1,
					]
				];

				$output = [
					'success'       => true,
					'message'       => 'Thank you for you donation!',
					'transactionId' => $response->transaction_id,
					'analytics'     => $this->view->render( 'ecommerce-ga-donation', compact( 'trans', 'items' ) ),
				];

			} else {
				$output = [
					'success'            => false,
					'message'            => 'Your donation could not be processed.',
					'responseReasonCode' => $response->response_reason_code,
					'responseCode'       => $response->response_code,
					'responseText'       => $response->response_reason_text,
				];
			}
		} else {
			$output = [
				'success' => false,
			    'message' => 'Form validation failed.',
			    'responseReasonCode' => 0,
			    'responseCode' => 0,
			    'responseText' => $data['result']
			];
		}

		echo json_encode($output);

		exit;
	}

	protected function doValidation( $data, $donation_min ) {
		$gump = new \GUMP();

		$data = $gump->sanitize( $data );

		$gump->validation_rules( [
			'amount'       => 'required',
			'customAmount' => 'numeric|min_numeric,' . $donation_min,
			'email'        => 'required|valid_email',
			'cc_num'       => 'required|valid_cc',
			'expire_date'  => 'required',
			'cvc'          => 'required|min_len,3|max_len,4',
			'x_first_name' => 'required',
			'x_last_name'  => 'required',
			'x_address'    => 'required|street_address',
			'x_city'       => 'required',
			'x_state'      => 'required|exact_len,2',
			'x_zip'        => 'required|exact_len,5'
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