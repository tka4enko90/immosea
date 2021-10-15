<?php

/**
 * Register New Order Statuses
 */
function woo_pfa_register_preorder_for_admin_status() {
    register_post_status( 'wc-admin-preorder', array(
        'label'                     => 'Pre-Order for Admin',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Pre-Order for Admin (%s)', 'Pre-Order for Admin (%s)' )
    ) );
}
add_action( 'init', 'woo_pfa_register_preorder_for_admin_status' );

/**
 * Add to list of WC Order statuses
 * @param $order_statuses
 * @return array
 */
function woo_pfa_add_preorder_for_admin_to_order_statuses( $order_statuses ) {

    $new_order_statuses = array();

    // add new order status after processing
    foreach ( $order_statuses as $key => $status ) {

        $new_order_statuses[ $key ] = $status;

        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-admin-preorder'] = 'Pre-Order for Admin';
        }
    }

    return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'woo_pfa_add_preorder_for_admin_to_order_statuses' );


add_filter( 'woocommerce_email_actions', 'filter_woocommerce_email_actions' );
function filter_woocommerce_email_actions( $actions ){
    $actions[] = 'woocommerce_order_status_wc-admin-preorder';
    return $actions;
}