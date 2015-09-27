<?php

namespace psrm\controllers;

use psrm\PSRM;
use psrm\utils\Views;
use psrm\models\Recaptcha as RecaptchaSettings;

class Recaptcha {
	protected $model;
	protected $view;

	public function __construct() {
		$this->model = new RecaptchaSettings();
		$this->view  = new Views( PSRM::$views );
	}

	public function display_captcha() {
		echo $this->view->render( 'display-recaptcha', [ 'site_key' => GOOGLE_RECAPTCHA_SITE_KEY ] );
	}

	public function verify_captcha( $user, $password ) {
		if ( isset( $_POST[ 'g-recaptcha-response' ] ) ) {
			$rc       = new \ReCaptcha\ReCaptcha( GOOGLE_RECAPTCHA_SECRET_KEY );
			$response = $rc->verify( $_POST[ 'g-recaptcha-response' ] );

			if ( $response->isSuccess() ) {
				return $user;
			}

			$error_codes = $response->getErrorCodes();
			$error = '';
			foreach($error_codes as $code){
				switch($code){
					case 'missing-input-secret':
						$error .= 'The secret parameter is missing. ';
						break;
					case 'invalid-input-secret':
						$error .= 'The secret parameter is invalid or malformed. ';
						break;
					case 'missing-input-response':
						$error .= 'The response parameter is missing. ';
						break;
					case 'invalid-input-response':
						$error .= 'The response parameter is invalid or malformed. ';
						break;
					default:
						$error .= 'There was a general error. Please contact support. ';
				}
			}

			return new \WP_Error( 'Captcha Invalid', __( '<strong>ERROR</strong>: ' . $error ) );
		}

		return new \WP_Error( 'Captcha Invalid', __( '<strong>ERROR</strong>: You must use the reCAPTCHA system and submit a CAPTCHA response to log in.' ) );
	}
}
