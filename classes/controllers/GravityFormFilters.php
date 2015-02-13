<?php

namespace psrm\controllers;

class GravityFormFilters
{
	function __construct()
	{
		add_filter( 'gform_submit_button', array($this, 'add_ga_conversion_tracking_code'), 10, 2);
	}

	function add_ga_conversion_tracking_code($button, $form) {
		$dom = new \DOMDocument();
		$dom->loadHTML($button);
		$input = $dom->getElementsByTagName('input')->item(0);
		if ($input->hasAttribute('onclick')) {
			$input->setAttribute("onclick","ga('send', 'event', '{$form['title']}', 'Submission');" . $input->getAttribute("onclick"));
		} else {
			$input->setAttribute("onclick","ga('send', 'event', '{$form['title']}', 'Submission');");
		}
		return $dom->saveHtml();
	}
}