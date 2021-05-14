<?php
class Payment {
    function __construct()
    {


    }

    public function get_order() {

        if (!WC()->session->get('order_awaiting_payment')) {
            $order =  wc_create_order();
            WC()->session->set('order_awaiting_payment', $order->get_id());
        } else {
            $order = wc_get_order( WC()->session->get('order_awaiting_payment'));
        }

        return $order;
    }

    private function get_payments_methods() {
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        return $available_gateways;
    }
    public function get_payments_response($payment_method) {
        $available_gateways = $this->get_payments_methods();

        return $available_gateways['paypal']->process_payment($this->get_order());
    }

    public function get_payments_data() {
        $available_gateways = $this->get_payments_methods();
        foreach ($available_gateways as $key =>  $payment_method) {
            if (in_array($payment_method->id, $this->get_available_payments_method())) {
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
    private function get_available_payments_method () {
        $response = apply_filters('available_payment_methods', array(
            'paypal',
            'stripe_sofort',
            'german_market_purchase_on_account'
        ));
        return $response;
    }
}