<?php

namespace psrm\models;

class Donation extends Settings {
	function __construct() {
		parent::__construct();

		define( 'STRIPE_SECRET_KEY', $this->getOption( 'stripe_secret_key', 'donations' ) );
		define( 'STRIPE_PUBLIC_KEY', $this->getOption( 'stripe_public_key', 'donations' ) );
		define( 'STRIPE_CHECKOUT_IMAGE_URL', $this->getOption( 'checkout_image_url', 'donations' ) );
		define( 'STRIPE_ALLOW_CUSTOM_AMOUNT', $this->getOption( 'allow_custom_amount', 'donations' ) );
		define( 'STRIPE_CUSTOM_DONATION_FLOOR', $this->getOption( 'custom_donation_floor', 'donations' ) );
	}
}