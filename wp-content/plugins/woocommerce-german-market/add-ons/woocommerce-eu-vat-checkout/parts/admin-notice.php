<?php
/**
 * Admin notice template with dismiss link
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="updated wcevc-admin-note">
	<?php if ( $show_dismiss ) : ?>
		<?php
		$dismiss_args = array(
			'wcevc_dismiss_notice' => $type,
			'_wcevc_nonce'         => wp_create_nonce( 'wcevc' )
		);
		$dismiss_link = add_query_arg( $dismiss_args );
		?>
		<a class="wcevc-admin-note-close dashicons-before dashicons-dismiss" href="<?php echo $dismiss_link; ?>">
			<?php _ex( 'Dismiss', 'Close link for admin notice. Use translation for identical WordPress core string.', 'wcevc'); ?>
		</a>
	<?php endif; ?>
	<p><?php echo $message; ?></p>
</div>