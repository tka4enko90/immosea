<?php
/**
 * Plugin Name: WooCommerce Pre-Order Status and Email for Admin
 * Description: A new Email <strong>"Pre-Order For Admin"</strong> is added to <strong>Woocommerce->Settings->Emails</strong>. Also, a new status <strong>"Pre-Order for Admin"</strong> is added for Order. When creating a New Order with the "Pre-Order for Admin" status, email will be sent to the administrator by default.
 * Version: 1.0
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'admin_init', 'woo_pfa_check_woocommerce' );

/**
 * Check if WooCommerce activated and WooCommerce Class Exists
 * @return bool
 */
function woo_pfa_check_woocommerce() {
    $woo_is_active = false;

    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    if (is_plugin_active('woocommerce/woocommerce.php') && class_exists('WooCommerce')) {

        $woo_is_active = true;

    }
    return $woo_is_active;

}

add_action('admin_notices', 'woo_pfa_admin_notices');

/**
 * Todo: Don't include files if Woocommerce is not installed or activated
 */
require_once ('includes/woo-pfa-inc-email.php');
require_once ('includes/woo-pfa-order-status.php');

/**
 * Show Admin Notice if Woocommerce not installed or empty WooCommerce Class
 */
function woo_pfa_admin_notices() {
    $woo_is_active = woo_pfa_check_woocommerce();

    if (!$woo_is_active) {
        $html = '<div class="notice notice-error notice-alt>">';
        $html .= '<p>Plugin <strong>"WooCommerce Pre-Order Status and Email for Admin"</strong> requires you to install or activate the <strong>WooCommerce</strong> plugin. You can download this plugin from <a href="https://wordpress.org/plugins/woocommerce/">here</a>. Or if you already have WooCommerce installed, you need to activate it</p>';
        $html .= '</div>';
        echo $html;
    }
    return;
}


