<?php
add_theme_support( 'post-thumbnails' );
add_theme_support( 'title-tag' );
add_filter('wpcf7_autop_or_not', '__return_false');

$include_folders =  array(
    'rest_api/',
    'functions/'
);
foreach ($include_folders as $inc_folder) {
    $include_folder = get_stylesheet_directory() . '/' . $inc_folder;
    foreach( glob( $include_folder.'*.php' ) as $file ) {
        require_once $file;
    }
}

function your_custom_field_function_name($order){
    $value = get_post_meta( $order->get_id(), '_year', true );
    echo "<p><strong>Name of pickup person:</strong> " .  $value."</p>";
}

//add_action( 'woocommerce_admin_order_data_after_billing_address', 'your_custom_field_function_name', 10, 1 );

function your_custom_field($item){
    $order = wc_get_order($item);

    $template = "
            <tbody>";
    foreach ($order->get_meta_data() as $meta_datum) {
        $template .= '
                <tr>
                    <td>'.$meta_datum->value.'</td>
                    <td class="%1"></td>
                    <td class="total"><span class="amount">'.$meta_datum->key.'</span></td>
                </tr>';

    }
    $template .= '</tbody>
    ';
    echo $template;
//   ?>
<!--   <pre>-->
<!--   --><?php //print_r( $order->get_meta_data())?>
<!--   </pre>-->
<!--   --><?php
//   ?>
<!--   <pre>-->
<!--   --><?php //print_r($class)?>
<!--   --><?php //print_r($item)?>
<!--   --><?php //print_r($order->get_meta)?>
   </pre>
   <?php
}

//add_action( 'woocommerce_admin_order_totals_after_discount', 'your_custom_field', 10 );


//add_filter('wpi_invoice_information_meta', (function(){
//    $response = array();
//}));

//add_filter( 'woocommerce_hidden_order_itemmeta','args_hidden_order_itemmeta', 10);
//function wpi_get_invoice_total_rows($total_rows ) {
//
//    return $total_rows;
//}
//add_filter( 'wpi_get_invoice_total_rows', 'wpi_get_invoice_total_rows');
