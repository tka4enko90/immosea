<?php

class Coupon extends HttpError {
    private $params;
    private $order;
    private $orderID;
    private $coupon;
    private $error;
    private $apply_coupon;



    private $order_metas;
    private $order_products;
    private $payment;

    public function __construct(ErrorService $error, Payment $payment, Apply_Coupon $apply_coupon)
    {
        $this->error = $error;
        $this->payment = $payment;
        $this->apply_coupon = $apply_coupon;
    }

    /**
     * @param $request
     *
     * @return array
     */
    public function apply_coupon($request)
    {

        $this->setParams($request->get_params());

        if (empty($this->params['coupon'])){ $this->setStatusCode(404)->setMessage('coupon wasn\'t add to endpoint'); return $this->report();}

        $this->setOrder($this->payment->get_order());

        $this->setOrderID($this->getOrder()->get_id());
        $this->setCoupon($this->params['coupon']);

        $order_items = $this->getOrder()->get_items();

       if ($this->getOrder()->get_coupons()) {
           return $this->error->setStatusCode(404)->setMessage('Coupon already added to these products')->report();
       }
        if ($order_items) {
            foreach ($order_items as $order_item) {
                $product = wc_get_product($order_item->get_product_id());
                $response['products'][] = array(
                        'total' => $order_item->get_total(),
                        'product_id' => $order_item->get_product_id(),
                        'name' => $order_item->get_name(),
                        'quantity' => $order_item->get_quantity(),
                        'sku' => $product->get_sku(),
                        'price' => $product->get_price(),
                );
            }
        }
        if (!$this->is_referral_coupon_valid($this->getCoupon())) {
            return  $this->error->setStatusCode(404)->setMessage("Coupon `".$this->getCoupon()."` is invalid")->report();
        }
        $response_coupon = $this->apply_coupon->apply_coupon($this->getOrder(), $this->getCoupon());

        $response = array_merge($response, $response_coupon);


        $this->set_user_to_order();

        $response['payment_method'] = $this->payment->get_payments_data();
        return $response;
    }


    private function set_user_to_order() {
        if (is_user_logged_in()) {
            $this->getOrder()->set_customer_id(get_current_user_id());
            $this->getOrder()->save();
        }
    }

    private function is_referral_coupon_valid( $coupon_code ) {
        $coupon = new \WC_Coupon( $coupon_code );
        $discounts = new \WC_Discounts( WC()->cart );
        $valid_response = $discounts->is_coupon_valid( $coupon );
        if ( is_wp_error( $valid_response ) ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param mixed $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function getOrder() : WC_Order
    {
        return  $this->order;
    }

    /**
     * @param mixed $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return mixed
     */
    public function getOrderID()
    {
        return $this->orderID;
    }

    /**
     * @param mixed $orderID
     */
    public function setOrderID($orderID)
    {
        $this->orderID = $orderID;
    }

    /**
     * @return mixed
     */
    public function getOrderMetas()
    {
        return $this->order_metas;
    }

    /**
     * @param mixed $order_metas
     */
    public function setOrderMetas($order_metas)
    {
        $this->order_metas = $order_metas;
    }

    /**
     * @return mixed
     */
    public function getOrderProducts()
    {
        return $this->order_products;
    }

    /**
     * @param mixed $order_products
     */
    public function setOrderProducts($order_products)
    {
        $this->order_products = $order_products;
    }
    /**
     * @return mixed
     */
    public function getCoupon()
    {
        return $this->coupon;
    }

    /**
     * @param mixed $coupon
     */
    public function setCoupon($coupon)
    {
        $this->coupon = $coupon;
    }

}
