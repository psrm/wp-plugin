<?php

namespace psrm\controllers;

use psrm\models\Settings as SettingsModel;
use psrm\utils\View;
use psrm\PSRM;

class Settings
{
	private $model;

	function __construct()
	{
		$this->model = SettingsModel::load();

		add_action('admin_init', array($this, 'admin_init'));
		add_action('admin_menu', array($this, 'admin_menu'));
	}

	function admin_init()
	{
		$settingsKey = $this->model->getSettingsKey();
		register_setting($settingsKey, $settingsKey, array($this, 'validateSettings'));
	}

	function admin_menu()
	{
		add_menu_page(
			PSRM::NAME . ' | Settings',
			PSRM::NAME . ' Settings',
			'manage_options',
			PSRM::$slug,
			array($this, 'add_menu_page_callback'),
			'dashicons-admin-generic',
			'2.1'
		);
	}

	function add_menu_page_callback()
	{
		$vars = $this->getOptionTabsVars();
		$vars['pageSlug']      = PSRM::$slug;
		$vars['pageTitle']     = PSRM::NAME . ' | Settings';
		$vars['settingsModel'] = $this->model;
		$vars['view']          = new View('settings-' . $vars['current_tab'], $vars);

		echo new View('settings', $vars);
	}

	function getOptionTabsVars()
	{
		// Tabs use option group keys

		// Get all option groups - keys => captions

		$optionGroups = $this->model->getOptionGroups();

		// Define default tab (the first one on the list)

		$defaultTab = array_keys($optionGroups);
		$defaultTab = array_shift($defaultTab);

		// Set vars for the view

		$vars['tabs'] = $this->model->getOptionGroups();
		$vars['current_tab'] = isset($_GET['tab']) ? $_GET['tab'] : $defaultTab;

		return $vars;
	}

	function validateSettings($settings)
	{
		$existingSettings = $this->model->getOptions();

		// do validation here...
		// submitted array look like this:
		// array( 'section_key' => array(values) );
		// you can validate based on section key.

		$result = array_merge($existingSettings, $settings);

		return $result;
	}
}
