<?php

namespace psrm\models;

class Recaptcha extends Settings {
	const Group = 'recaptcha';
	const SiteKeyOptionName = 'google_recaptcha_site_key';
	const SecretKeyOptionName = 'google_recaptcha_secret_key';
}
