<?php

namespace psrm\utils;

use psrm\PSRM;

class View {
	private $path;
	private $vars;

	public function __construct($filename = '', array $vars = []) {
		$this->path = PSRM::$views . '/' . $filename . '.phtml';
		$this->vars = $vars;
	}

	public function __toString() {
		$response = '';

		if (file_exists($this->path)) {
			ob_start();
			extract($this->vars);
			require($this->path);
			$response = ob_get_contents();
			ob_end_clean();
		}

		return $response;
	}
}
