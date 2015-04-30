<?php
/**
 * This class first looks in theme's folder
 * then in the views, therefore allowing overwriting
 * of templates.
 */

namespace psrm\utils;


class Views
{
	protected $vars = false;
	protected $viewsDir = false;
	protected $fileName = null;

	function __construct($viewsDir)
	{
		$this->viewsDir = $viewsDir;
	}


	public function setFileName($fileName)
	{
		$this->fileName = $fileName;
	}


	public function set($name, $value)
	{
		$this->vars[$name] = $value;
	}


	public function get($name)
	{
		return (isset($this->vars[$name])) ? $this->vars[$name] : null;
	}


	public function render($fileName = false, $vars = false)
	{
		if (!$fileName)
		{
			if (!$this->fileName)
			{
				trigger_error("Template file name is required.", E_USER_ERROR);

				return;
			}
			else
			{
				$fileName = $this->fileName;
			}
		}

		// check if template exists in theme's folder

		$viewPath = locate_template($fileName . '.phtml');

		if ($viewPath == '')
		{
			$viewPath = $this->viewsDir . '/' . $fileName . '.phtml';
		}

		$vars = ($vars) ? $vars : $this->vars;

		return $this->renderView($viewPath, $vars);
	}

	public function renderView($fileName, $vars = false)
	{
		$response = false;

		if (file_exists($fileName))
		{
			ob_start();

			if (!empty($vars))
			{
				extract($vars);
			}

			require($fileName);

			$response = ob_get_contents();

			ob_end_clean();
		}

		return $response;
	}
}