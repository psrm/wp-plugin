<?php

namespace psrm\models;

class GooglePlaces extends Settings {
	function __construct()
	{
		parent::__construct();

		define('GOOGLE_PLACES_API_KEY', $this->getOption('google_places_api_key', 'google-places'));
		define('GOOGLE_PLACES_ID', $this->getOption('google_places_id', 'google-places'));
	}
}