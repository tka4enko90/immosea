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
//
//add_action('wpo_wcpdf_after_order_details', (function($item, $order){
//
//        $items = $order->get_meta_data();
//        $templates = '<table class="order-details">';
//        if( sizeof( $items ) > 0 ) :
//        foreach( $items as $item_id => $item ) :
//            $meta = $item->get_data();
//           $templates .= '
//            <tr>
//                <td class="product">
//                    <dl class="meta">
//                        <dt class="sku"><dd class="sku">'.$meta['key'].'</dd>
//                    </dl>
//                </td>
//                <td class="product">
//                    <dl class="meta">
//                        <dt class="sku"><dd class="sku">'.$meta['value'].'</dd>
//                    </dl>
//                </td>
//            </tr>
//        ';
//		 endforeach;
//		endif;
//		$templates .= '</table>';
//    print_r($templates);
//}), 10, 2);
