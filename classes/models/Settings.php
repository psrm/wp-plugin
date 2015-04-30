<?php

namespace psrm\models;

use psrm\interfaces\AbstractSettingsModel;
use psrm\PSRM;

class Settings extends AbstractSettingsModel
{
	protected static $__CLASS__ = __CLASS__; // Provide this in each singleton class.

	protected $optionGroups = array(
		'donations' => 'Donations',
	);

	public function __construct()
	{
		parent::__construct(PSRM::$slug, PSRM::$settingsKey);

		$this->loadOptions();
	}
}