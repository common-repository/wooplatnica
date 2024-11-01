<?php defined('ABSPATH') or die('No script kiddies please!');

/*
Plugin Name: Wooplatnica
Plugin URI: https://wordpress.org/plugins/wooplatnica/
Description: Generisanje opšte uplatnice i NBS IPS QR kôda za uplate iz Srbije, u PDF formatu.
Author: Nemanja Avramović
Version: 1.0
Author URI: https://avramovic.info
*/

defined('WOOPLATNICA_FILE') or define('WOOPLATNICA_FILE', __FILE__);

require dirname(__FILE__)."/vendor/autoload.php";

add_filter('kses_allowed_protocols', function ($protocols) {

    if (!in_array('data', $protocols)) {
        $protocols[] = 'data';
    }

    return $protocols;
});

register_activation_hook(WOOPLATNICA_FILE, function () {
    if (!class_exists('WC_Payment_Gateway')) {
        die('WooCommerce nije aktiviran ili Wooplatnica nije kompatibilna sa verzijom WooCommerce-a koju imate.');
    }
});

add_action('wp_loaded', function () {
    $wooplatnicaClasses = [
        'Wooplatnica'            => true,
        'Nalog'                  => false,
        'Uplatnica'              => false,
        'IpsQr'                  => false,
        'IpsQrLocal'             => false,
        'IpsQrGoogle'            => false,
        'UplatnicaPDF'           => false,
        'WC_Gateway_Wooplatnica' => false,
    ];

    if (!class_exists('WC_Payment_Gateway')) {
        require_once(ABSPATH.'wp-admin/includes/plugin.php');
        deactivate_plugins(WOOPLATNICA_FILE);
        wp_redirect('plugins.php?deactivate=true');
        die();
    }

    foreach ($wooplatnicaClasses as $wooplatnicaClass => $init) {
        require_once(__DIR__.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.$wooplatnicaClass.'.php');
        if ($init) {
            $$wooplatnicaClass = new $wooplatnicaClass;
        }
    }
});
