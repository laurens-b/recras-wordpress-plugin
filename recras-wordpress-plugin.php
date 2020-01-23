<?php
/*
Plugin Name: Recras WordPress Plugin
Plugin URI: https://www.recras.nl/
Description: Easily integrate your Recras data into your own site
Author: Recras
Text Domain: recras
Domain Path: /lang
Version: 3.3.0

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

if (version_compare(phpversion(), '5.6', '<')) {
    function phpVersionTooOld() {
        echo '<div class="error">';
        echo '<h2>Recras WordPress plugin</h2>';
        echo '<p>';
        echo 'The Recras WordPress plugin requires PHP 5.6 or higher but your server is running PHP version ' . phpversion() . '. The plugin will not be activated.<br>';
        echo 'All PHP versions before 7.2.0 have reached "End of Life" and no longer receive bugfixes or security updates. ';
        echo 'The official information on how to update and why can be found on <a href="https://www.php.net/supported-versions.php" target="_blank"><strong>the PHP website</strong></a>.';
        echo '</p></div>';
    }
    add_action('admin_notices', 'phpVersionTooOld');
    return;
}

require_once('vendor/autoload.php');
$recrasPlugin = new \Recras\Plugin;
