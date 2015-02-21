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

	public static $interfaces;
	public static $utils;
	public static $settingsKey;


	function __construct()
	{
		self::$slug = basename( dirname( __FILE__ ) );
		self::$dir  = WPMU_PLUGIN_DIR . '/' . self::$slug;
		self::$url  = WPMU_PLUGIN_URL . '/' . self::$slug;

		self::$controllers   = self::$dir . '/classes/controllers';
		self::$models        = self::$dir . '/classes/models';
		self::$views         = self::$dir . '/classes/views';

		self::$interfaces    = self::$dir . '/common/interfaces';
		self::$utils         = self::$dir . '/common/utils';
		self::$settingsKey   = self::$slug . '-settings';

		$this->initPlugin();
	}

	function initPlugin()
	{
		require_once('vendor/autoload.php');
		require_once(self::$interfaces . '/AbstractSettingsModel.php');
		require_once(self::$utils . '/Views.php');
		require_once(self::$controllers . '/Settings.php');
		require_once(self::$controllers . '/Equipment.php');
		require_once(self::$controllers . '/GravityFormFilters.php');
		require_once(self::$controllers . '/GoogleAnalytics.php');

		new controllers\Settings();
		new controllers\Equipment();
		new controllers\GravityFormFilters();
		new controllers\GoogleAnalytics();
	}

}