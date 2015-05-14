<?php

namespace psrm\controllers;

use psrm\models\GooglePlaces as PlacesSettings;
use \Curl\Curl;
use psrm\widgets\DailyHours;

class GooglePlaces {
	private $model;
	static $dowMap;

	function __construct() {
		$this->model = new PlacesSettings();
		self::$dowMap = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
		add_action('psrm-daily-cron', [$this, 'service_alert_auto_delete']);
		add_action('psrm-hourly-cron', [$this, 'retrieve_latest_hours']);
		new DailyHours();$this->retrieve_latest_hours();
	}

	function retrieve_latest_hours() {
		$curl = new Curl();
		$curl->get( 'https://maps.googleapis.com/maps/api/place/details/json', array(
			'placeid' => GOOGLE_PLACES_ID,
			'key'     => GOOGLE_PLACES_API_KEY
		) );

		if($curl->response->status == 'OK') {
			if ( get_transient( 'psrm-details' ) ) {
				delete_transient( 'psrm-details' );
			}

			set_transient( 'psrm-details', $curl->response->result, 4 * WEEK_IN_SECONDS );
		}
	}

}