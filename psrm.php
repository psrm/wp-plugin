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

	public static $third_party;
	public static $images;
	public static $scripts;
	public static $styles;


	function __construct()
	{
		self::$slug = basename( dirname( __FILE__ ) );
		self::$dir  = WPMU_PLUGIN_DIR . '/' . self::$slug;
		self::$url  = WPMU_PLUGIN_URL . '/' . self::$slug;

		self::$controllers   = self::$dir . '/classes/controllers';
		self::$models        = self::$dir . '/classes/models';
		self::$views         = self::$dir . '/classes/views';

		self::$third_party  = self::$dir . '/third-party';
		self::$images       = self::$url . '/resources/images';
		self::$scripts      = self::$url . '/resources/scripts/build';
		self::$styles       = self::$url . '/resources/styles';

		$this->initPlugin();
	}

	function initPlugin()
	{
		require_once('vendor/autoload.php');
		require_once(self::$controllers . '/Equipment.php');
		require_once(self::$controllers . '/GravityFormFilters.php');
		require_once(self::$controllers . '/GoogleAnalytics.php');

		new controllers\Equipment();
		new controllers\GravityFormFilters();
		new controllers\GoogleAnalytics();
	}

}