<?php
/*
Template name: Show Order
*/

$order = WC()->session->order_awaiting_payment ? wc_get_order(WC()->session->order_awaiting_payment) : false;

if (!$order) {
    echo 'You have no order. Please create order first.';
    return;
}

if (!empty($_POST['apply_coupon'])) {
    $order->apply_coupon($_POST['apply_coupon']);
    wp_redirect(get_permalink());
    exit();
}

if (!empty($_POST['proceed_payment'])) {
    $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

    update_post_meta( $order->get_id(), '_payment_method', 'paypal' );
    update_post_meta( $order->get_id(), '_payment_method_title', 'PayPal' );

    // Store Order ID in session so it can be re-used after payment failure
    WC()->session->order_awaiting_payment = $order->get_id();

    // Process Payment

    $result = $available_gateways[ 'paypal' ]->process_payment( $order->get_id() );

    // Redirect to success/confirmation/payment page
    if ( $result['result'] == 'success' ) {

        $result = apply_filters( 'woocommerce_payment_successful_result', $result, $order->id );

        wp_redirect( $result['redirect'] );
        exit;
    }
}

?>
<h1> Order # <?php echo $order->get_id(); ?></h1>
<table border="1" width="100%">
    <thead>
    <tr>
        <th>Item</th>
        <th>Quantity</th>
        <th>Price</th>
        <th>Subtotal</th>
        <th>Discount</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($order->get_items() as $item) : ?>
        <?php
        $item_price = wc_price( $order->get_item_subtotal( $item, false, true ), array( 'currency' => $order->get_currency() ) );
        $item_total = wc_price( $item->get_total(), array( 'currency' => $order->get_currency() ) );
        $item_discount = wc_price( wc_format_decimal( $item->get_subtotal() - $item->get_total(), '' ), array( 'currency' => $order->get_currency() ) );
        ?>
        <tr>
            <td><?php echo $item->get_name(); ?></td>
            <td><?php echo $item->get_quantity(); ?></td>
            <td><?php echo $item_price; ?></td>
            <td><?php echo $item_total; ?></td>
            <td><?php echo $item_discount; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<table class="wc-order-totals">
    <tr>
        <td class="label"><?php esc_html_e( 'Items Subtotal:', 'woocommerce' ); ?></td>
        <td width="1%"></td>
        <td class="total">
            <?php echo wc_price( $order->get_subtotal(), array( 'currency' => $order->get_currency() ) ); ?>
        </td>
    </tr>
    <?php if ( 0 < $order->get_total_discount() ) : ?>
        <tr>
            <td class="label"><?php esc_html_e( 'Coupon(s):', 'woocommerce' ); ?></td>
            <td width="1%"></td>
            <td class="total">-
                <?php echo wc_price( $order->get_total_discount(), array( 'currency' => $order->get_currency() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </td>
        </tr>
    <?php endif; ?>
    <?php if ( 0 < $order->get_total_fees() ) : ?>
        <tr>
            <td class="label"><?php esc_html_e( 'Fees:', 'woocommerce' ); ?></td>
            <td width="1%"></td>
            <td class="total">
                <?php echo wc_price( $order->get_total_fees(), array( 'currency' => $order->get_currency() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </td>
        </tr>
    <?php endif; ?>
</table>

<div>
    <form method="post" action="">
        <input name="apply_coupon" type="text">
        <button type="submit">Apply Coupon</button>
    </form>
</div>

<div>
    <form method="post" action="">
        <button type="submit" name="proceed_payment" value="1">Proceed Payment</button>
    </form>
</div>
