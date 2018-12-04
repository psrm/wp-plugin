<?php

namespace psrm\controllers;

use psrm\PSRM;
use psrm\utils\Views;
use psrm\models\Donation as DonationSettings;

class Donation {
	protected $settings;
	protected $view;

	public function __construct() {
		$this->settings = DonationSettings::load();
		$this->view     = new Views( PSRM::$views );
		add_shortcode( PSRM::$slug . '-donation-form', [ $this, 'display_donation_form' ] );
		add_action( 'wp_ajax_process_donation', [ $this, 'process_donation' ] );
		add_action( 'wp_ajax_nopriv_process_donation', [ $this, 'process_donation' ] );
	}

	public function display_donation_form() {
		return $this->view->render( 'donation-form', array(
			'donation_amounts'      => $this->settings->getOption(DonationSettings::DonationAmounts, DonationSettings::Group),
			'donation_funds'        => $this->settings->getOption(DonationSettings::DonationFunds, DonationSettings::Group),
			'allow_custom_amount'   => $this->settings->getOption(DonationSettings::AllowCustomAmountOptionName, DonationSettings::Group),
			'custom_donation_floor' => $this->settings->getOption(DonationSettings::CustomAmountFloorOptionName, DonationSettings::Group)
		) );
	}

	public function process_donation() {
		$data = $this->doValidation( $_POST, $this->settings->getOption(DonationSettings::CustomAmountFloorOptionName, DonationSettings::Group));

		$donation_amounts = $this->settings->getOption(DonationSettings::DonationAmounts, DonationSettings::Group);

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
				\Stripe\Stripe::setApiKey($this->settings->getOption(DonationSettings::StripeSecretKeyOptionName, DonationSettings::Group));

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
					$this->settings->getOption(DonationSettings::EmailSuccessfulDonationOptionName, DonationSettings::Group),
					"Successful donation for ${$donation_amount}",
					'Successful donation! View this transaction in Stripe: ' . $this->settings->getOption(DonationSettings::StripeDashboardUrlOptionName, DonationSettings::Group) . $response->id
				);

				echo $this->view->render('donation-successful-outcome', [
					'donationAmount' => $donation_amount,
					'transactionId'  => $response->id,
				]);
			} catch ( \Exception $e ) {
				echo $this->view->render('donation-failed-outcome', [
					'message'      => 'There was an error.',
					'responseText' => $e->getMessage()
				]);
			}

		} else {
			echo $this->view->render('donation-failed-outcome', [
				'message'      => 'Form validation failed. Correct the errors and resubmit.',
				'responseText' => $data[ 'result' ]
			]);
		}

		exit;
	}

	protected function doValidation($data, $donation_min ) {
		$gump = new \GUMP();

		$data = $gump->sanitize( $data );

		$gump->validation_rules( [
			'amount'       => 'required',
			'fund'         => 'required',
			'customAmount' => 'numeric|min_numeric,' . $donation_min,
			'email'        => 'required|valid_email',
			'stripeToken'  => 'required'
		] );

		try {
			$validated_data = $gump->run($data);
		} catch (\Exception $e) {
			return [
				'success' => false,
				'result'  => $e->getMessage(),
			];
		}

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
}
