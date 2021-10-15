<?php
/**
 *  Add a custom email to the list of emails WooCommerce should load
 *
 * @since 0.1
 * @param array $email_classes available email classes
 * @return array filtered available email classes
 */
function woo_pfa_add_preorder_for_admin_woocommerce_email( $email_classes ) {

    // include our custom email class
    require_once( 'class-wc-preorder-for-admin-mail.php' );

    // add the email class to the list of email classes that WooCommerce loads
    $email_classes['WC_Preorder_For_Admin_Email'] = new WC_Preorder_For_Admin_Email();

    return $email_classes;

}
add_filter( 'woocommerce_email_classes', 'woo_pfa_add_preorder_for_admin_woocommerce_email' );