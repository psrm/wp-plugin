<?php

namespace psrm\controllers;

use psrm\utils\View;

class GoogleAnalytics
{
	public $cookie_name;

	public function __construct()
	{
		$this->cookie_name = 'psrm_ga_exclude';
		add_action('admin_init', array($this, 'tracking_exclusion_cookie'));
		if(!isset($_COOKIE[$this->cookie_name])) {
			add_action('wp_head', array($this, 'displayTrackingCode'), PHP_INT_MIN);
		}
	}

	/**
	 * Set a cookie to remove the tracking code.
	 */
	public function tracking_exclusion_cookie()
	{
		if ( !isset($_COOKIE[$this->cookie_name])) {
			setcookie( $this->cookie_name, 1, time() + 3600 * 24 * 365, COOKIEPATH, COOKIE_DOMAIN, false );
		}
	}

	/**
	 * Display the Google Analytics tracking code.
	 */
	public function displayTrackingCode()
	{
		echo new View('google-analytics');
	}
}