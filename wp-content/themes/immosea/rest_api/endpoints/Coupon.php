<?php

class Coupon extends HttpError {
    private $params;
    private $order;
    private $orderID;
    private $coupon;



    private $order_metas;
    private $order_products;


    /**
     * @param $request
     *
     * @return array
     */
    public function apply_coupon($request)
    {

        $this->setParams($request->get_params());
        WC()->initialize_session();

        if (empty(WC()->session->get('order_awaiting_payment'))) { $this->setStatusCode(404)->setMessage('order_id wasn\'t add to endpoint'); return $this->report();}
        if (empty($this->params['coupon'])){ $this->setStatusCode(404)->setMessage('coupon wasn\'t add to endpoint'); return $this->report();}

        $order = wc_get_order(WC()->session->get('order_awaiting_payment'));

        if (!$order) {
            $this->setStatusCode(404)->setMessage('order dosen\'t exist'); return $this->report();
        }

        $this->setOrder($order);
        $this->setCoupon($this->params['coupon']);

        $this->setOrderID($this->getOrder()->ID);

        $order_items = $this->getOrder()->get_items();

        $this->getOrder()->apply_coupon($this->getCoupon());

        $response['order_id'] = $this->getOrderID();
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
            $coupon = new WC_Coupon($this->getCoupon());
            $response['sub_total'] = $this->getOrder()->get_subtotal();
            $response['total_price'] = $this->getOrder()->get_total();
            $response['amount'] = $coupon->get_amount();
            $response['amount_type'] = $coupon->get_discount_type();
        }

        $this->set_user_to_order();
        $this->getOrder()->calculate_totals();
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        $result = $available_gateways[ 'paypal' ]->process_payment($this->getOrderID());
        if ( $result['result'] == 'success' ) {
            $result = apply_filters( 'woocommerce_payment_successful_result', $result, $this->getOrderID() );
            $response['result'] = $result;
        }else {
            $this->setStatusCode(404)->setMessage('Process payment worn');
            return  $this->report();
        }
        return $response;
    }



    private function set_user_to_order() {
        if (is_user_logged_in()) {
            $this->getOrder()->set_customer_id(get_current_user_id());
            $this->getOrder()->save();
        }
    }
    private function update_order_products($products) {
        foreach ($products as $item) {
            if (isset($item['product_id']) && $item['product_id']) {
                $this->getOrder()->add_product( wc_get_product($item['product_id']  ), isset($item['qty']) ? $item['qty'] : 1 );
            }
        }
    }

    private function update_order_post_meta($order_metas, $order_ID) {

        foreach ($order_metas as $key => $value) {
            update_post_meta( $order_ID, $key, $value);
        }
    }
//    private function render_order_custom_fields() {
//        $order = $this->getOrder();
//
//        $template = "
//            <tbody>";
//        foreach ($order->get_meta_data() as $meta_datum) {
//            $template .= '
//                <tr>
//                    <td class="total"><span class="amount">'.$meta_datum->key.'</span></td>
//                    <td class="%1"></td>
//                    <td>'.$meta_datum->value.'</td>
//
//                </tr>';
//
//        }
//        $template .= '</tbody>';
//        echo $template;
//    }

    private function prepare_order_fields($fields) {
        $fields = array(
            '_year' => 1990
        );

        $response = [];
        if ($fields) {
            foreach ($fields as $key => $field) {
                if ($key === '_year') {
                    $response['Year'] = $field;
                }
            }
            return $response;
        }
        return false;
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
