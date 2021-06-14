<?php
/*
Plugin Name: Recras WordPress Plugin
Plugin URI: https://www.recras.nl/
Description: Easily integrate your Recras data into your own site
Author: Recras
Requires at least: 5.3
Requires PHP: 5.6.0
Text Domain: recras
Domain Path: /lang
Version: 4.3.0

Author URI: https://www.recras.nl/
*/

// Debugging
if (WP_DEBUG) {
    error_reporting(-1);
    ini_set('display_errors', 'On');
}

if (!function_exists('add_action')) {
    die('You cannot run this file directly.');
}

require_once('vendor/autoload.php');
$recrasPlugin = new \Recras\Plugin;
