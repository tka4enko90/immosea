<?php
include 'interfaces/Endpoint.php';
include 'services/ErrorService.php';
include 'services/HttpError.php';
include 'services/Cron_Theme.php';
include 'services/Payment.php';
include 'services/Apply_Coupon.php';

include 'endpoints/Order.php';
include 'endpoints/Products.php';
include 'endpoints/Coupon.php';
include 'endpoints/Media.php';


class Rest_API {
    private $payment;
    private $apply_coupon;
    private static
        $instance = null;
    public static function init($api_namespace, $version) {
        if ( is_null( self::$instance ) )
        {
            self::$instance = new self($api_namespace, $version);

        }
        return self::$instance;
    }
    /**
     * Constructor for class
     *
     *
     * @since 0.0.1
     *
     * @param string $api_namespace
     * @param $version
     */
    private function __construct($api_namespace, $version)
    {
        $this->api_namespace = $api_namespace;
        $this->version = $version;
        add_action( 'rest_api_init', array($this, 'register_routes'));
        add_action( 'woocommerce_admin_order_totals_after_discount', array($this,'render_order_custom_fields' ), 10 );
        Cron_Remove_Images::init();

        add_action( 'woocommerce_thankyou', array($this, 'remove_cookie'));
        add_filter('woocommerce_return_to_shop_redirect', function ($url){
            return get_home_url();
        });
        add_filter('woocommerce_return_to_shop_text', function(){
           return __('Zurück zur Seite', 'immosea');
        });
        $this->payment = new Payment();
        $this->apply_coupon = new Apply_Coupon(new HttpError());
        add_action('wp_loaded', function(){
            if ( defined( 'WC_ABSPATH' ) ) {
                // WC 3.6+ - Cart and other frontend functions are not included for REST requests.
                include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
                include_once WC_ABSPATH . 'includes/wc-notice-functions.php';
                include_once WC_ABSPATH . 'includes/wc-template-hooks.php';
            }
            WC()->initialize_session();
            if (isset(WC()->session)) {

                if (!WC()->session->has_session()) {
                    WC()->session->set_customer_session_cookie(true);
                }
            }
            if ( null === WC()->session ) {
                $session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );

                WC()->session = new $session_class();
                WC()->session->init();
            }
            if ( null === WC()->cart ) {
                WC()->cart = new WC_Cart();

                // We need to force a refresh of the cart contents from session here (cart contents are normally refreshed on wp_loaded, which has already happened by this point).
                WC()->cart->get_cart();
            }
        });
    }
    /**
     * We have to tell WC that this should not be handled as a REST request.
     * Otherwise we can't use the product loop template contents properly.
     * Since WooCommerce 3.6
     *
     * @param bool $is_rest_api_request
     * @return bool
     */
    public function simulate_as_not_rest( $is_rest_api_request ) {
        if ( empty( $_SERVER['REQUEST_URI'] ) ) {
            return $is_rest_api_request;
        }

        // Bail early if this is not our request.
        if ( false === strpos( $_SERVER['REQUEST_URI'], $this->api_namespace ) ) {
            return $is_rest_api_request;
        }

        return false;
    }
    /**
     * @param $order
     */
    public function render_order_custom_fields($order) {
        $order = wc_get_order($order);
        $template = "
            <tbody>";
        foreach ($order->get_meta_data() as $meta_datum) {
            if($meta_datum->key != '_shipping_email'
                && $meta_datum->key != '_shipping_phone'
                && $meta_datum->key != '_new_order_email_sent'
                && $meta_datum->key != '_paypal_status'
                && $meta_datum->key != 'Payment type'
                && strpos($meta_datum->key, '_wcpdf') !== 0
                && strpos($meta_datum->key, '_stripe') !== 0 )
            $template .= '
                <tr>
                    <td class="sub-label" style="width:100%">'.$meta_datum->key.'</td>
                    <td class="%1"></td>
                    <td><span class="amount">'.$meta_datum->value.'</span></td>
                </tr>';

        }
        $template .= '</tbody>';
        echo $template;
    }
    /**
     * Register our endpoints
     *
     * @since 0.0.1
     */
    public function register_routes() {
        $root = $this->api_namespace;
        $version = $this->version;
        $this->site_create_endpoints($root, $version);
    }


    protected function site_create_endpoints($root, $version){
        /**
         * Get products endpoints
         */
        register_rest_route("{$root}/{$version}", '/get_products/', array(
                array(
                    'methods'         => \WP_REST_Server::READABLE,
                    'callback'        => array(new Products(), 'getFields' ),
                    'permission_callback' => array($this, 'permissions_check' )
                ),
            )
        );
        /**
         * Create order endpoints
         */
        register_rest_route("{$root}/{$version}", '/create_order/', array(
                array(
                    'methods'         => \WP_REST_Server::CREATABLE,
                    'callback'        => array(new Order(new HttpError(), $this->payment, $this->apply_coupon), 'create_order' ),
                    'permission_callback' => array($this, 'permissions_check' )
                ),
            )
        );
        /**
         * Coupon order endpoints
         */
        register_rest_route("{$root}/{$version}", '/apply_coupon/', array(
                array(
                    'methods'         => \WP_REST_Server::CREATABLE,
                    'callback'        => array(new Coupon(new HttpError(), $this->payment, $this->apply_coupon), 'apply_coupon' ),
                    'permission_callback' => array($this, 'permissions_check' )
                ),
            )
        );
        /**
         * Upload media endpoints
         */
        register_rest_route("{$root}/{$version}", '/media/', array(
                array(
                    'methods'         => \WP_REST_Server::CREATABLE,
                    'callback'        => array(new Media(new HttpError()), 'create_media' ),
                    'permission_callback' => array($this, 'permissions_check' )
                ),
            )
        );
    }

    public function paramIsPresent($paramName, $request) {
        $params = $request->get_params();
        return array_key_exists($paramName, $params);
    }
    /**
     * For now, all methods are public.
     *
     * @since 0.0.1
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return bool Always returns true.
     */
    public function permissions_check() {
        return true;
    }

    /**
     * @param $order_id
     * Remove cookie after success purchase
     */
    public  function remove_cookie( $order_id ) {
        echo '<script type="text/javascript">
        (function () {
           function setCookie(name, value, days) {
                var d = new Date;
                d.setTime(d.getTime() + 24*60*60*1000*days);
                document.cookie = name + "=" + value + ";path=/;expires=" + d.toGMTString();
            }
            function deleteCookie(name) { setCookie(name, "", -1); }
            deleteCookie("cart");
            deleteCookie("collectData");
            deleteCookie("contactData");
        })();
        </script>';
    }
}

Rest_API::init('rest_api', 'v1');

add_action('wp_loaded', function() {


},1, 10);