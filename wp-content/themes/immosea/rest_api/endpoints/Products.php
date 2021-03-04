<?php

class Products implements Endpoint
{
    /**
     * @param $request
     *
     * @return array
     */
    public function getFields($request)
    {
        $response = [];
        $association_of_products = get_field('association_of_products', 'options');
        if ($association_of_products && function_exists('wc_get_product')) {
                foreach ($association_of_products as $association_of_product) {
                    $product = wc_get_product($association_of_product['product']);
                    $product_data = $product->get_data();
                    if ($product_data['status'] === 'publish') {
                        $response[] = array(
                            'product_key' => $association_of_product['association'],
                            'product_id' => $product_data['id'],
                            'product_price' => $product_data['price'],

                        );
                    }

                }
        }
        return $response;
    }
}
