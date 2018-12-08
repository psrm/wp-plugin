<?php

namespace psrm\controllers;

use psrm\utils\View;

class GoogleAnalytics
{
	public $cookie_name;
	function __construct()
	{
		$this->cookie_name = 'psrm_ga_exclude';
		add_action('admin_init', array($this, 'tracking_exclusion_cookie'));
		if(!isset($_COOKIE[$this->cookie_name])) {
			add_action( 'wp_head', array( $this, 'tracking_code' ) );
		}
	}

	function tracking_exclusion_cookie()
	{
		if ( !isset($_COOKIE[$this->cookie_name])) {
			setcookie( $this->cookie_name, 1, time() + 3600 * 24 * 365, COOKIEPATH, COOKIE_DOMAIN, false );
		}
	}

	function tracking_code()
	{
		echo new View('google-analytics');
	}
}