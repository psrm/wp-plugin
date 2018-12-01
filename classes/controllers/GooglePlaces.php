<?php

namespace psrm\controllers;

use psrm\models\GooglePlaces as PlacesSettings;
use psrm\models\Settings;
use \Curl\Curl;
use psrm\widgets\DailyHours;

class GooglePlaces {
	private $settings;
	static $dowMap;

	function __construct() {
		$this->settings = Settings::load();
		self::$dowMap   = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
		add_action('psrm-hourly-cron', [$this, 'retrieve_latest_hours']);
		new DailyHours();
	}

	function retrieve_latest_hours() {
		$curl = new Curl();
		$curl->get( 'https://maps.googleapis.com/maps/api/place/details/json', array(
			'placeid' => $this->settings->getOption(PlacesSettings::ApiKeyOptionName, PlacesSettings::Group),
			'key'     => $this->settings->getOption(PlacesSettings::IdOptionName, PlacesSettings::Group)
		) );

		if($curl->response->status == 'OK') {
			if ( get_transient( 'psrm-details' ) ) {
				delete_transient( 'psrm-details' );
			}

			set_transient( 'psrm-details', $curl->response->result, 4 * WEEK_IN_SECONDS );
		}
	}

}
