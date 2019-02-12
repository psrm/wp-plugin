<?php

namespace psrm\controllers;

use psrm\utils\View;
use psrm\models\Recaptcha as RecaptchaSettings;
use psrm\models\Settings;

class Recaptcha {
	protected $settings;

	public function __construct() {
		$this->settings = Settings::load();

		if (defined('WP_DEVELOPMENT') && !WP_DEVELOPMENT) {
			add_action('login_enqueue_scripts', [$this, 'enqueueRecaptchaScript']);
			add_filter('wp_authenticate_user', [$this, 'authenticateWithCaptcha'], 10, 2);
			add_action('login_form', [$this, 'displayCaptcha']);
		}
	}

	/**
	 * Enqueue the captcha script.
	 */
	public function enqueueRecaptchaScript() {
		wp_enqueue_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js' );
	}

	public function displayCaptcha() {
		$vars = [
			'site_key' => $this->settings->getOption(RecaptchaSettings::SiteKeyOptionName, RecaptchaSettings::Group),
		];

		echo new View('display-recaptcha', $vars);
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
			$rc       = new \ReCaptcha\ReCaptcha($this->settings->getOption(RecaptchaSettings::SecretKeyOptionName, RecaptchaSettings::Group));
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
