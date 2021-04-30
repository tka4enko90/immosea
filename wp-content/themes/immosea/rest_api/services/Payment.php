<?php
class Payment {
    function __construct()
    {
        WC()->initialize_session();
        if (isset(WC()->session)) {
            if (!WC()->session->has_session()) {
                WC()->session->set_customer_session_cookie(true);
            }
        }
    }

    public function get_order() {
        if (!WC()->session->get('order_awaiting_payment') || !wc_get_order(WC()->session->get('order_awaiting_payment'))) {
            $order =  wc_create_order();
            WC()->session->set('order_awaiting_payment', $order->get_id());
        } else {
            $order = wc_get_order( WC()->session->get('order_awaiting_payment'));
        }
        return $order;
    }

    public function get_payments_method_response($order_ID) {
        include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
        include_once WC_ABSPATH . 'includes/wc-notice-functions.php';
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

        foreach ($available_gateways as $key =>  $payment_method) {
            if (in_array($payment_method->id, $this->get_payments_method())) {
                $response[$payment_method->id] = $payment_method->process_payment($this->get_order());
                $paypal_image = WC()->plugin_url() . "/includes/gateways/paypal/assets/images/paypal.png";
                $icon = $payment_method->id === 'paypal' ? "<img src=\"$paypal_image\">" : $payment_method->get_icon();
                $response[$payment_method->id]['data']= array(
                    'title' => $payment_method->title,
                    'image' => $icon,
                );
            }
        }
        return $response;
    }
    private function get_payments_method () {
        $response = apply_filters('available_payment_methods', array(
            'paypal',
            'stripe_sofort'
        ));
        return $response;
    }
}