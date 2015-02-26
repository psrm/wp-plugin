<?php

namespace psrm\models;

class Donation extends Settings {
	function __construct()
	{
		parent::__construct();

		define('AUTHORIZENET_API_LOGIN_ID', $this->getOption('authorize_net_api_id', 'donations'));
		define('AUTHORIZENET_TRANSACTION_KEY', $this->getOption('authorize_net_transaction_key', 'donations'));
	}
}