<?php

class Order extends HttpError {
    private $error;
    private $params;
    private $order;
    private $orderID;
    private $order_metas;
    private $order_products;
    private $cart;
    private $contactData;

    public function __construct(ErrorService $error)
    {
        $this->error = $error;
    }

    /**
     * @param $request
     *
     * @return array
     */
    public function create_order($request)
    {
        try {
            $session = new WC_Session_Handler();
            if (!$request->get_params()) {
                return $this->error->setStatusCode(400)->setMessage("Params wasn't set")->report();
            }
            $this->setParams($request->get_params());
            $order = wc_create_order();
            $this->setOrder($order);
            $this->setOrderID($this->getOrder()->ID);
            $this->setCart($this->getParams('cart'));
            $this->setContactData($this->getParams('contactData'));
            $this->setOrderMetas($this->prepare_order_fields($this->getParams('collectData')));
            $products = $this->get_association_of_products($this->cart);
            $this->setOrderProducts($products);
            if ($this->contactData) {
                $this->updated_order_contact_data($this->contactData);
            }
            $this->update_order_products($this->getOrderProducts());
            $this->update_order_post_meta($this->getOrderMetas(), $this->getOrderID());
            $this->bind_image_with_order($this->cart['image']);

            if ($this->cart['uploads_images']) {
                foreach ($this->cart['uploads_images'] as $upload_image) {
                    $this->bind_image_with_order($upload_image);
                }
            }

            if (isset( $session )) {
                $session->set('order_awaiting_payment', $this->getOrderID());
            }
            $order_items = $this->getOrder()->get_items();
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
                $response['total_price'] = $this->getOrder()->calculate_totals();
                $response['currency'] = $this->getOrder()->get_currency();
            }

            $this->set_user_to_order();
            $this->getOrder()->calculate_totals();
            $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
            $result = $available_gateways[ 'paypal' ]->process_payment($this->getOrderID());
            if ( $result['result'] == 'success' ) {
                $result = apply_filters( 'woocommerce_payment_successful_result', $result, $this->getOrderID() );
                $response['result'] = $result;
            }else {
                return $this->error->setStatusCode(404)->setMessage('Process payment wrong')->report();
            }
        } catch (Exception $e) {
            return $this->error->setStatusCode(404)->setMessage($e->getMessage())->report();
        }

        return $response;
    }

    private function bind_image_with_order($image) {
        $image_url = wp_get_attachment_image_url($image);
        if ($image_url && $image) {
            wp_update_post( array(
                'ID' => $image,
                'post_parent' => $this->getOrderID()
            ));
            update_post_meta($this->cart['image'], 'order_image', true);
        }
    }
    private function updated_order_contact_data($contactData) {
        if (isset($contactData['name'])) {
            $address['first_name'] = $contactData['name'];
        }
        if (isset($contactData['lastName'])) {
            $address['last_name'] = $contactData['lastName'];
        }
        if (isset($contactData['email'])) {
            $address['email'] = $contactData['email'];
        }
        if (isset($contactData['zip'])) {
            $address['postcode'] = $contactData['zip'];
        }
        if (isset($contactData['phone'])) {
            $address['phone'] = $contactData['phone'];
        }
        if (isset($contactData['address'])) {
            $address['address_1'] = $contactData['address'];
        }

        $this->order->set_address( $address, 'billing' );
        $this->order->set_address( $address, 'shipping' );
    }

    private function get_association_of_products($params) {
        $association_of_products = get_field('association_of_products', 'options');
        if (empty($association_of_products)) {
            return $this->error->setStatusCode(400)->setMessage("Association of products wasn't filled")->report();
        }

        foreach ($params as $key => $param) {
            if ($params[$key]) {
                foreach ($association_of_products as $i => $association_of_product) {

                    if ($key === 'photography' &&  $params['type'] === 'flat') {
                        $key = 'photography_flat';
                    }elseif($key === 'photography' &&  $params['type'] === 'property') {
                        $key = 'photography_property';
                    }elseif( $key === 'photography' && $params['type'] === 'house') {
                        if ($params['year'] && strtotime($params['year'].'-01-01') < strtotime('1979-01-01')) {
                            $key = 'energy_certificate_bg_house';
                        }else {
                            $key = 'photography_house';
                        }
                    }
                    if ($association_of_product['association'] === $key) {
                        $response[] = array(
                            'product_id' => $association_of_product['product']->ID,
                            'qty' => 1,
                        );
                    }
                }
            }
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


    private function prepare_order_fields($fields) {
        if ($fields) {
            $response = [];

            if ($fields) {
                foreach ($fields as $key => $field) {
                    if (!$fields[$key]) continue;
                    $field =  strip_tags($field);
                    if ($key === 'name_house') {
                        $response[$field] = $field;
                    }elseif ($key === 'sell_rent') {
                        $response["Sell/Rent"] = $field;
                    }elseif ($key === 'year') {
                        $response['Baujahr'] = $field;
                    }elseif ($key === 'floors') {
                        $response['Etagen'] = $field;
                    }elseif ($key === 'object') {
                        $response['Objekt in Etage'] = $field;
                    }elseif ($key === 'coownership') {
                        $response['Miteigentumsanteil'] = $field;
                    }elseif ($key === 'year_upgrade') {
                        $response['Letzte Modernisierung'] = $field;
                    }elseif ($key === 'lift') {
                        $response['Lift'] = $field;
                    }elseif ($key === 'status') {
                        $response['Zustand'] = $field;
                    }elseif ($key === 'available_from') {
                        $date = new DateTime($field);
                        $response['Verfügbar ab'] = $date->format('Y-m-d');
                    }elseif ($key === 'living_space') {
                        $response['Wohnfläche'] = $field;
                    }elseif ($key === 'usable_area') {
                        $response['Nutzfläche'] = $field;
                    }elseif ($key === 'property') {
                        $response['Grundstück'] = $field;
                    }elseif ($key === 'rooms') {
                        $response['Zimmer gesamt'] = $field;
                    }elseif ($key === 'bedroom') {
                        $response['Schlafzimmer'] = $field;
                    }elseif ($key === 'living_bedroom') {
                        $response['Wohn-Schlafzimmer'] = $field;
                    }elseif ($key === 'bathroom') {
                        $response['Badezimmer'] = $field;
                    }elseif ($key === 'toilets') {
                        $response['Separate WCs'] = $field;
                    }elseif ($key === 'balconies') {
                        $response['Anzahl Balkon'] = $field;
                    }elseif ($key === 'terrace') {
                        $response['Anzahl Terrasse'] = $field;
                    }elseif ($key === 'window_type') {
                        $response['Fensterart'] = implode(' | ', $fields[$key]);
                    }elseif ($key === 'glazing') {
                        $response['Verglasung'] = implode(' | ', $fields[$key]);
                    }elseif ($key === 'bjwindow') {
                        $date = new DateTime($field);
                        $response['BJ Fenster (falls abweichend)'] = $date->format('Y-m-d');
                    }elseif ($key === 'keller') {
                        $response['Keller'] = $field;
                    }elseif ($key === 'garden') {
                        $response['Garten'] = $field;
                    }elseif ($key === 'parking') {
                        $response['Stellplätze'] = implode(' | ', $fields[$key]);
                    }elseif ($key === 'number_parking') {
                        $response['Anzahl Stellplätze'] = $field;
                    }elseif ($key === 'number_units') {
                        $response['Anzahl Einheiten'] = $field;
                    }elseif ($key === 'residential_units') {
                        $response['Davon Wohneinheiten'] = $field;
                    }elseif ($key === 'which_commercial') {
                        $response['Davon Gewerbe'] = $field;
                    }elseif ($key === 'monthly_allowance') {
                        $response['Monatliches Hausgeld'] = $field;
                    }elseif ($key === 'purchase_price') {
                        $response['Kaufpreis'] = $field;
                    }elseif ($key === 'pitch_price') {
                        $response['Stellplatzpreis'] = $field;
                    }elseif ($key === 'rent') {
                        $response['Kaltmiete'] = $field;
                    }elseif ($key === 'additional_costs') {
                        $response['Nebenkosten'] = $field;
                    }elseif ($key === 'rent_parking') {
                        $response['Miete Stellplatz'] = $field;
                    }elseif ($key === 'fully_developed') {
                        $response['Voll Erschlossen'] = $field;
                    }elseif ($key === 'monument_protection') {
                        $response['Denkmalschutz'] = $field;
                    }elseif ($key === 'ensemble_protection') {
                        $response['Ensembleschutz'] = $field;
                    }elseif ($key === 'demolition_object') {
                        $response['Abrissobjekt'] = $field;
                    }elseif ($key === 'particularities') {
                        $response['Besonderheiten'] = $field;
                    }elseif ($key === 'heater') {
                        $response['Heizung'] = $field;
                    }elseif ($key === 'energy_certificate') {
                        $response['Energieausweis'] = $field;
                    }elseif ($key === 'consumption_value') {
                        $response['Verbrauchskennwert (kWh/(m²*a))'] = $field;
                    }elseif ($key === 'valid_energy_certificate') {
                        $response['Energieausweis gültig bis'] = $field;
                    }elseif ($key === 'included_hotwater') {
                        $response['Warmwasser enthalten'] = $field;
                    }elseif ($key === 'title') {
                        $response['Objekttitel'] = $field;
                    }elseif ($key === 'description') {
                        $response['Objektbeschreibung'] = $field;
                    }elseif ($key === 'description_location') {
                        $response['Lagebeschreibung'] = $field;
                    }elseif ($key === 'leisure') {
                        $response['Freizeit'] = $field;
                    }elseif ($key === 'others') {
                        $response['Sonstiges'] = $field;
                    }elseif ($key === 'rehabilitation') {
                        $response['Vorgenommene Sanierungsmaßnahmen'] = $field;
                    }elseif ($key === 'furnishing') {
                        $response['Ausstattung'] = implode(' | ', $fields[$key]);
                    }elseif ($key === 'further_equipment') {
                        $response['Weitere Ausstattung'] = $field;
                    }elseif ($key === 'floor_coverings') {
                        $response['Bodenbeläge'] = implode(' | ', $fields[$key]);
                    }elseif ($key === 'key_points') {
                        $response['Beschreibung (Stichpunkte)'] = $field;
                    }elseif ($key === 'address') {
                        $response['Adresse'] = $field;
                    }elseif ($key === 'postcode') {
                        $response['Im Exposé bitte nur Postleitzahl und Ort angeben.'] = $field;
                    }elseif ($key === 'uploads_docs') {
                        $images = '';
                        foreach ($fields[$key] as $item) {
                            $images .='<div><a href="'.wp_get_attachment_url($item['attachment_id']).'" target="_blank">'.wp_get_attachment_image($item['attachment_id'], 'thumbnail').'</a></div>';
                        }
                        $response['Documents'] = $images;
                    }elseif ($key === 'uploads') {
                        $images = '';
                        foreach ($fields[$key] as $item) {
                            $images .='<div><a href="'.wp_get_attachment_url($item['attachment_id']).'" target="_blank">'.wp_get_attachment_image($item['attachment_id'], 'thumbnail').'</a></div>';
                        }
                        $response['Images'] = $images;
                    }
                }
                return $response;
            }
        }


        return false;
    }
    /**
     * @return mixed
     */
    public function getParams($param = false)
    {

        if ($param) {
            if (!$this->params[$param]) {
                return $this->error->setStatusCode(400)->setMessage("Param `$param` wasn't set")->report();
            }
            return isset($this->params[$param]) ? $this->params[$param] : false;
        }
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
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param mixed $cart
     */
    public function setCart($cart)
    {
        $this->cart = $cart;
    }

    /**
     * @return mixed
     */
    public function getContactData()
    {
        return $this->contactData;
    }

    /**
     * @param mixed $contactData
     */
    public function setContactData($contactData)
    {
        $this->contactData = $contactData;
    }
}
