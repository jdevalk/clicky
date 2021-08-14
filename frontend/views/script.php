<?php
/**
 * Clicky for WordPress plugin file.
 *
 * @package Yoast/Clicky/View
 */

/**
 * Global for CSP nonce.
 *
 * @var string $clicky_extra_nonce
 */

if ( ! empty( $clicky_extra ) ) {
	?>
	<script nonce="<?php echo esc_attr( $clicky_extra_nonce ); ?>">
		<?php
		echo 'var clicky_custom = clicky_custom || {}; ';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $clicky_extra;
		?>
	</script>
	<?php
}

// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
echo '<script async src="//static.getclicky.com/' . esc_attr( $this->options['site_id'] ) . 'js"></script>' . "\n";
