<?php
add_theme_support( 'post-thumbnails' );
add_theme_support( 'title-tag' );
add_filter('wpcf7_autop_or_not', '__return_false');


function inclusion_enqueue() {
    $ver_num = mt_rand();

    wp_enqueue_style('style', get_template_directory_uri().'/dist/style.css', array(), $ver_num, 'all' );
    wp_enqueue_script('scripts', get_template_directory_uri().'/dist/app.js', array('jquery'), $ver_num, true);
}
add_action('wp_enqueue_scripts','inclusion_enqueue');

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

function my_load_scripts($hook) {
    //TODO add hash to url for uncached

    wp_enqueue_script( 'vue', 'https://cdn.jsdelivr.net/npm/vue@2.6.12', array());
    wp_enqueue_script( 'my-app', get_template_directory_uri().'/app/dist/my-app.js');

}
add_action('wp_enqueue_scripts', 'my_load_scripts');


// Add Page Options
if( function_exists('acf_add_options_page') ) {
    acf_add_options_page(array(
        'page_title' 	=> 'Options',
        'menu_title'	=> 'Options',
        'menu_slug' 	=> 'options',
        'capability'	=> 'edit_posts',
        'redirect'		=> false
    ));
}

// Register menu
register_nav_menus( array(
    'menu' => 'Menu',
    'footer' => 'Footer',
    'services' => 'Services'
) );
