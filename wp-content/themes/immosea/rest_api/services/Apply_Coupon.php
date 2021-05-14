<?php
class Apply_Coupon {
    private $error;
    function __construct(ErrorService $error)
    {
        $this->error = $error;
    }
    public function apply_coupon($order, $coupon) {
        if ($coupon && $order) {
            $price_before_coupon = $order->get_total();
            $applied_coupon = $order->apply_coupon($coupon);
            if (is_wp_error($applied_coupon)) {
                return $this->error->setStatusCode(404)->setMessage($applied_coupon->get_error_message())->report();
            }
            $coupon = new WC_Coupon($coupon);
            $response['sub_total'] = $price_before_coupon;
            $response['total_price'] = $order->get_total();
            $response['amount'] = $coupon->get_amount();
            $response['amount_type'] = $coupon->get_discount_type();
            $response['coupon'] = $coupon->code;
            return $response;
        }
    }
}