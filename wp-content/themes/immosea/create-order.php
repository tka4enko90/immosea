<?php

/*
Template name: Create Order
*/

if (!empty($_POST['create_order'])) {
    $address = array(
        'first_name' => 'firstname',
        'last_name'  => 'lastname',
        'company'    => 'company',
        'email'      => 'user@example.com',
        'phone'      => '+380975724275',
        'address_1'  => 'test address',
        'address_2'  => '',
        'city'       => 'Kharkiv',
        'state'      => '',
        'postcode'   => '61007',
        'country'    => 'UA'
    );

    $order = wc_create_order();
    $order->add_product( wc_get_product(10  ), 1 );
    update_post_meta( $order->get_id(), '_year', '2021' );
    update_post_meta( $order->get_id(), '_year_title', 'Year Custom' );
    $order->set_address( $address, 'billing' );
    $order->set_address( $address, 'shipping' );

    $order->calculate_totals();

    WC()->session->order_awaiting_payment = $order->get_id();

    if (is_user_logged_in()) {
        $order->set_customer_id(get_current_user_id());
        $order->save();
    }

    wp_redirect(get_permalink(get_page_by_path('show-order')));
    exit();
}

?>

<div>
    <form method="post" action="">
        <button type="submit" name="create_order" value="1">Create Order</button>
    </form>
</div>
