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

		add_action( 'login_enqueue_scripts', [ $this, 'enqueueRecaptchaScript' ] );
		add_filter( 'wp_authenticate_user', [ $this, 'authenticateWithCaptcha' ], 10, 2 );
		add_action( 'login_form', [ $this, 'displayCaptcha' ] );
	}

	/**
	 * Enqueue the captcha script.
	 */
	public function enqueueRecaptchaScript() {
		wp_enqueue_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js' );
	}

	public function displayCaptcha() {
		echo $this->view->render( 'display-recaptcha', [ 'site_key' => GOOGLE_RECAPTCHA_SITE_KEY ] );
	}

	/**
	 * Will verify if a captcha was submitted and if so, if it's valid.
	 *
	 * @return bool|\WP_Error True on successful captcha, else WP_Error explaining the error.
	 */
	public function verifyCaptcha()
	{
		$error = '';

		try {
			$rc = new \ReCaptcha\ReCaptcha(GOOGLE_RECAPTCHA_SECRET_KEY);
			$response = $rc->verify($_POST['g-recaptcha-response']);

			if ($response->isSuccess()) {
				return true;
			}

			$error_codes = $response->getErrorCodes();
			foreach ($error_codes as $code) {
				switch ($code) {
					case 'missing-input-secret':
						$error .= 'The secret parameter is missing. ';
						break;
					case 'invalid-input-secret':
						$error .= 'The secret parameter is invalid or malformed. ';
						break;
					case 'missing-input-response':
						return new \WP_Error($code, __('You must submit the CAPTCHA.'));
						break;
					case 'invalid-input-response':
						$error .= 'The response parameter is invalid or malformed. ';
						break;
					default:
						$error .= 'Unknown error with Recaptcha ';
				}
			}
		} catch (\Exception $e) {
			$error .= $e->getMessage();
		}

		error_log('#recaptcha: ' . $error);
		return new \WP_Error('recaptcha-error', __('An error occurred. Please try to log in again. If it keeps happening please contact site support.'));
	}

	/**
	 * Uses the WordPress filter wp_authenticate_user to verify the captcha was submitted
	 * before allowing the user to login.
	 *
	 * @param $user \WP_User The user object after username and password have been verified.
	 *
	 * @return \WP_User|\WP_Error Returns the user object if successful,
	 * else WP_Error explaining the error.
	 */
	public function authenticateWithCaptcha( $user ) {
		$captcha = $this->verifyCaptcha();

		if ( $captcha === true ) {
			return $user;
		}

		return $captcha;
	}
}
