<?php
class Order {
    private $params;
    private $order;
    private $orderID;
    private $order_metas;
    private $order_products;


    /**
     * @param $request
     *
     * @return array
     */
    public function create_order($request)
    {
        $this->setParams($request -> get_params());
        $this->setOrder(wc_create_order());
        $this->setOrderID($this->getOrder()->ID);


        if (!$this->getParams()) {
            $this->setOrderMetas($this->prepare_order_fields($this->getParams()));
            $this->setOrderProducts(
                array(
                    array(
                        'product_id' => 48,
                        'qty' => 1,
                    ),
                    array(
                        'product_id' => 47,
                        'qty' => 2,
                    ),
                    array(
                        'product_id' => 43,
                        'qty' => 1,
                    )
                )
            );
            $this->update_order_products($this->getOrderProducts());
            $this->update_order_post_meta($this->getOrderMetas(), $this->getOrderID());
        }
        add_action( 'woocommerce_admin_order_totals_after_discount', array($this, 'render_order_custom_fields'), 10 );
        if (is_user_logged_in()) {
            $this->getOrder()->set_customer_id(get_current_user_id());
            $this->getOrder()->save();
        }

        $this->getOrder()->calculate_totals();
        WC()->session->order_awaiting_payment = $this->getOrderID();
        wp_redirect(get_permalink(get_page_by_path('show-order')));
//        add_filter( 'woocommerce_paypal_express_checkout_disable_smart_payment_buttons', '__return_true' );
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
    private function render_order_custom_fields() {
        $order = $this->getOrder();

        $template = "
            <tbody>";
        foreach ($order->get_meta_data() as $meta_datum) {
            $template .= '
                <tr>
                    <td class="total"><span class="amount">'.$meta_datum->key.'</span></td>
                    <td class="%1"></td>
                    <td>'.$meta_datum->value.'</td>
                    
                </tr>';

        }
        $template .= '</tbody>';
        echo $template;
    }

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
    public function getOrder()
    {
        return $this->order;
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
}
