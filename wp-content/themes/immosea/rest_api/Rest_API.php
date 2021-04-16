<?php
include 'interfaces/Endpoint.php';
include 'services/ErrorService.php';
include 'services/HttpError.php';
include 'services/Cron_Theme.php';

include 'endpoints/Order.php';
include 'endpoints/Products.php';
include 'endpoints/Coupon.php';
include 'endpoints/Media.php';


class Rest_API {
    /**
     * Constructor for class
     *
     *
     * @since 0.0.1
     *
     * @param string $api_namespace
     * @param $version
     */

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
    }
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
                && strpos($meta_datum->key, '_wcpdf') !== 0)
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
                    'callback'        => array(new Order(new HttpError()), 'create_order' ),
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
                    'callback'        => array(new Coupon(), 'apply_coupon' ),
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
}

Rest_API::init('rest_api', 'v1');
