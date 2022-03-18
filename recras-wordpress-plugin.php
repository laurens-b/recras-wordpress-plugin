<?php
/*
Plugin Name: Recras WordPress Plugin
Plugin URI: https://www.recras.nl/
Version: 4.7.9
Description: Easily integrate your Recras data into your own site
Requires at least: 5.7
Requires PHP: 5.6.0
Author: Recras
Author URI: https://www.recras.nl/
Text Domain: recras
Domain Path: /lang
*/

// Debugging
if (WP_DEBUG) {
    error_reporting(-1);
    ini_set('display_errors', 'On');
}

if (!function_exists('add_action')) {
    die('You cannot run this file directly.');
}

require_once(__DIR__ . '/vendor/autoload.php');
$recrasPlugin = new \Recras\Plugin();
