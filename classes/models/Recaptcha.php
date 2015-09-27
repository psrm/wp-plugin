<?php

namespace psrm\models;

class Recaptcha extends Settings {
	public function __construct() {
		parent::__construct();

		define( 'GOOGLE_RECAPTCHA_SITE_KEY', $this->getOption( 'google_recaptcha_site_key', 'recaptcha' ) );
		define( 'GOOGLE_RECAPTCHA_SECRET_KEY', $this->getOption( 'google_recaptcha_secret_key', 'recaptcha' ) );
	}
}