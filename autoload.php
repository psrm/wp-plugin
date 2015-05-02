<?php

require_once('vendor/autoload.php');

function psrm_wp_autoload($ClassName)
{
    $class_path = str_replace('psrm\\', '', $ClassName);
    $include_file = \psrm\PSRM::$classes . "/" . str_replace('\\', '/', $class_path) . '.php';
    if (file_exists($include_file)) {
        return require_once($include_file);
    }
    return false;
}

spl_autoload_register("psrm_wp_autoload");