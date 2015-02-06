<?php
/*
Plugin Name:    PSRM Mandatory
Description:    Mandatory plugin for features essential to http://psrm.org.
Version:        1.1.0
Author:         PSRMA
Text Domain:    psrm
License:        GPL2
*/

namespace psrm;

new PSRM();

class PSRM
{
	const NAME = 'PSRM';

	public static $slug;
	public static $dir;
	public static $url;

	public static $components;
	public static $CPT;
	public static $gravity_forms;

	public static $third_party;
	public static $images;
	public static $scripts;
	public static $styles;


	function __construct()
	{
		self::$slug = basename( dirname( __FILE__ ) );
		self::$dir  = WPMU_PLUGIN_DIR . '/' . self::$slug;
		self::$url  = WPMU_PLUGIN_URL . '/' . self::$slug;

		self::$components   = self::$dir . '/components';
		self::$CPT          = self::$components . '/CPT';
		self::$gravity_forms = self::$components . '/gravity_forms';

		self::$third_party  = self::$dir . '/third-party';
		self::$images       = self::$url . '/resources/images';
		self::$scripts      = self::$url . '/resources/scripts/build';
		self::$styles       = self::$url . '/resources/styles';

		$this->initPlugin();
	}

	function initPlugin()
	{
		require_once(self::$CPT . '/equipment.php');
		require_once(self::$gravity_forms . '/filters.php');

		new CPT\equipment\CPT();
		new gravity_forms\filters();
	}

}