<?php

namespace psrm\controllers;

use psrm\PSRM;
use psrm\common\utils\Views;
use psrm\models\Donation as DonationSettings;

class Donation
{
	private $model;
	private $view;

	function __construct()
	{
		$this->model = new DonationSettings();
		$this->view = new Views(PSRM::$views);
		add_shortcode(PSRM::$slug . '-donation-form', [$this, 'display_donation_form']);
		add_action('wp_ajax_process_donation', [$this, 'process_donation']);
		add_action('wp_ajax_nopriv_process_donation', [$this, 'process_donation']);
	}

	function display_donation_form()
	{
		return $this->view->render('donation-form');
	}

	function process_donation()
	{
		$transaction = new \AuthorizeNetAIM;
		$transaction->setSandbox(AUTHORIZE_NET_SANDBOX);
		$transaction->setFields(
			array(
				'amount' => $_POST['amount'],
				'card_num' => $_POST['cc_num'],
				'exp_date' => $_POST['expire_date'],
				'email' => $_POST['email'],
				'card_code' => $_POST['cvc'],
				/*'first_name' => $_POST['x_first_name'],
				'last_name' => $_POST['x_last_name'],
				'address' => $_POST['x_address'],
				'city' => $_POST['x_city'],
				'state' => $_POST['x_state'],
				'country' => $_POST['x_country'],
				'zip' => $_POST['x_zip'],*/
			)
		);
		$response = $transaction->authorizeAndCapture();
		if ($response->approved) {

			$trans = [
				'id' => $response->transaction_id,
				'revenue' => $response->amount,
			];
			$items = [
				[
					'name' => 'Donation of $' . $response->amount,
					'sku' => 'DONATE' . $response->amount,
					'category' => 'Donation',
					'price' => $response->amount,
					'qty' => 1,
				]
			];

			$output = [
				'success' => true,
				'message' => 'Thank you for you donation!',
				'transactionId' => $response->transaction_id,
				'analytics' => $this->view->render('ecommerce-ga-donation', compact('trans', 'items')),
			];

		} else {
			$output = [
				'success' => false,
				'message' => 'Your donation could not be processed.',
				'responseReasonCode' => $response->response_reason_code,
				'responseCode' => $response->response_code,
				'responseText' => $response->response_reason_text,
			];
		}
		echo json_encode($output);
		die();
	}
}