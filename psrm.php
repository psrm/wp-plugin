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

    public static $classes;

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

        self::$classes       = self::$dir . '/classes';
		self::$controllers   = self::$dir . '/classes/controllers';
		self::$models        = self::$dir . '/classes/models';
        self::$interfaces    = self::$dir . '/classes/interfaces';
        self::$utils         = self::$dir . '/classes/utils';

		self::$scripts       = self::$url . '/resources/scripts';
		self::$styles        = self::$url . '/resources/style';
		self::$views         = self::$dir . '/resources/views';

		self::$settingsKey   = self::$slug . '-settings';
	}

	function initPlugin()
	{
		add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAdminScripts' ] );

		require_once('autoload.php');

		// Instantiate all controllers
        $dir = new \DirectoryIterator(self::$controllers);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $class_name = 'psrm\controllers\\' . str_replace('.php', '', $fileinfo->getFilename());
                new $class_name();
            }
        }

		// Instantiate Cron utility
		new utils\Cron();
	}

	function enqueueScripts()
	{
		wp_enqueue_style(self::$slug . '-plugin-styles', self::$styles . '/main.min.css', [], '1440911064');

		wp_register_script(self::$slug . '-plugin-scripts', self::$scripts . '/scripts.min.js', ['jquery'], '1440911064');
		wp_localize_script( self::$slug . '-plugin-scripts', 'psrm', [
			'ajaxurl' => admin_url('admin-ajax.php'),
		] );
		wp_enqueue_script(self::$slug . '-plugin-scripts');
	}

	public function enqueueAdminScripts() {
		wp_enqueue_style( self::$slug . '-plugin-admin-styles', self::$styles . '/admin.min.css', [ ], '1440911064' );
		wp_enqueue_script( self::$slug . '-plugin-admin-scripts', self::$scripts . '/admin.min.js', [ 'jquery' ], '1440911064' );
	}

}