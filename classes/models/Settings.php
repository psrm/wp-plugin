<?php

namespace psrm\models;


class Settings {
	// Plugin's settings key
	protected $settingsKey = 'psrm-settings';

	// Array of option groups keys. Each key represents settings on a single page
	protected $optionGroupsKeys = array();

	const DonationGroup = 'donations';
	const GooglePlacesGroup = 'google-places';
	const DevelopmentGroup = 'development';

	protected $optionGroups = array(
		self::DonationGroup     => 'Donations',
		self::GooglePlacesGroup => 'Google Places',
	);

	// All loaded settings
	protected $settings;

	private function __construct() {
		$this->optionGroupsKeys = array_keys($this->optionGroups);
	}

	private function loadOptions() {
		$this->settings = get_option($this->settingsKey);
	}

	public static function load() {
		static $class = null;

		if (is_null($class)) {
			$class = new self;
			$class->loadOptions();
		}

		return $class;
	}

	public function setSettingsKey($key) {
		$this->settingsKey = $key;
	}

	public function getSettingsKey() {
		return $this->settingsKey;
	}


	public function getOptionGroups() {
		return $this->optionGroups;
	}

	public function getOptions($optionGroupKey = false) {
		if ($optionGroupKey) {
			return (isset($this->settings[$optionGroupKey]))
				? $this->settings[$optionGroupKey] : false;
		}

		return !empty($this->settings) ? $this->settings : array();
	}


	public function getOption($optionName, $optionGroupKey) {
		if (!in_array($optionGroupKey, $this->optionGroupsKeys)) {
			trigger_error("Option Group Key $optionGroupKey doesn't exist.", E_USER_ERROR);
		}

		return (isset($this->settings[$optionGroupKey][$optionName]))
			? $this->settings[$optionGroupKey][$optionName] : null;
	}


	public function setOptions($newOptions, $optionGroupKey) {
		// if optionGroupKeys are set, then require a key
		// otherwise there is a chance of an error to overwrite all settings

		if ($this->optionGroupsKeys) {
			if (!$optionGroupKey) {
				trigger_error('$optionGroupKey is required.', E_USER_ERROR);

				return;
			} else if (!in_array($optionGroupKey, $this->optionGroupsKeys)) {
				trigger_error('Option Group Key "' . $optionGroupKey . '" doesn\'t exist in ' . get_class($this) . '::optionGroupsKeys.', E_USER_ERROR);

				return;
			}

			$this->settings[$optionGroupKey] = $newOptions;
		} else {
			$this->settings = $newOptions;
		}

		update_option($this->settingsKey, $this->settings);
	}

	public function getInputName($optionName, $optionGroupKey) {
		if ($this->optionGroupsKeys) {
			return $this->settingsKey . '[' . $optionGroupKey . ']' . '[' . $optionName . ']';
		}

		return $this->settingsKey . '[' . $optionName . ']';
	}
}
