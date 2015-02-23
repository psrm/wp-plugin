<?php

namespace psrm\controllers;

use psrm\PSRM;
use psrm\common\utils\Views;

class DonationForm
{
	private $view;

	function __construct()
	{
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

	}
}