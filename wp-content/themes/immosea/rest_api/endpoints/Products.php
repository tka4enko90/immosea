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
                    if (isset($association_of_product['product']) && $association_of_product['product']->post_status=== 'publish') {
						$product = wc_get_product($association_of_product['product']);
						$product_data = $product->get_data();
						if (!empty($product_data['id'])) :
							$product_title = get_field('product_title', $product_data['id']);
							$response[] = array(
								'product_title' => !empty($product_title) ? $product_title : '',
								'product_key' => $association_of_product['association'],
								'product_id' => $product_data['id'],
								'product_price' => $product_data['price'],
								'product_description' => $product_data['short_description']
							);
						endif;
					}
                }
        }
        return $response;
    }
}
