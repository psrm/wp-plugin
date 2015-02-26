<?php
/*
Plugin Name:    PSRM Plugin
*/

namespace psrm;

new PSRM();

class PSRM
{
	const NAME = 'PSRM';

	public static $slug;
	public static $dir;
	public static $url;

	public static $controllers;
	public static $models;
	public static $views;

	public static $scripts;
	public static $styles;

	public static $interfaces;
	public static $utils;
	public static $settingsKey;


	function __construct()
	{
		$this->setConstants();
		$this->setVariables();
		$this->initPlugin();
	}

	function setConstants()
	{
		if(strpos($_SERVER['HTTP_HOST'], 'dev') !== false || $_SERVER['SERVER_NAME'] == 'staging.psrm.org') {
			define( 'AUTHORIZE_NET_SANDBOX', true);
		} else {
			define('AUTHORIZE_NET_SANDBOX', false);
		}
	}

	function setVariables()
	{
		self::$slug = basename( dirname( __FILE__ ) );
		self::$dir  = WPMU_PLUGIN_DIR . '/' . self::$slug;
		self::$url  = WPMU_PLUGIN_URL . '/' . self::$slug;

		self::$controllers   = self::$dir . '/classes/controllers';
		self::$models        = self::$dir . '/classes/models';
		self::$views         = self::$dir . '/classes/views';

		self::$scripts       = self::$url . '/resources/scripts';
		self::$styles        = self::$url . '/resources/style';

		self::$interfaces    = self::$dir . '/common/interfaces';
		self::$utils         = self::$dir . '/common/utils';
		self::$settingsKey   = self::$slug . '-settings';
	}

	function initPlugin()
	{
		add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);

		require_once('vendor/autoload.php');
		require_once(self::$interfaces . '/AbstractSettingsModel.php');
		require_once(self::$utils . '/Views.php');
		require_once(self::$models . '/load.php');
		require_once(self::$controllers . '/Settings.php');
		require_once(self::$controllers . '/Equipment.php');
		require_once(self::$controllers . '/GravityFormFilters.php');
		require_once(self::$controllers . '/GoogleAnalytics.php');
		require_once(self::$controllers . '/Donation.php');

		new controllers\Settings();
		new controllers\Equipment();
		new controllers\GravityFormFilters();
		new controllers\GoogleAnalytics();
		new controllers\Donation();
	}

	function enqueueScripts()
	{
		wp_enqueue_style(self::$slug . '-plugin-styles', self::$styles . '/main.min.css', [], '1424536731');

		wp_register_script(self::$slug . '-plugin-scripts', self::$scripts . '/scripts.min.js', ['jquery'], '1424536731');
		wp_localize_script( self::$slug . '-plugin-scripts', 'psrm', [
			'ajaxurl' => admin_url('admin-ajax.php'),
		] );
		wp_enqueue_script(self::$slug . '-plugin-scripts');
	}

}