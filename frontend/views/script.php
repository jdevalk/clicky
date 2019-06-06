<?php
/**
 * Clicky plugin file.
 *
 * @package Yoast/Clicky/View
 */

?>
<script>
	<?php
	if ( ! empty( $clicky_extra ) ) {
		echo 'var clicky_custom = clicky_custom || {}; ';
		// @codingStandardsIgnoreLine WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $clicky_extra;
	}
	?>

	var clicky_site_ids = clicky_site_ids || [];
	clicky_site_ids.push(<?php echo esc_attr( $this->options['site_id'] ); ?>);
</script>
<?php
// @codingStandardsIgnoreLine WordPress.WP.EnqueuedResources.NonEnqueuedScript
echo '<script async src="//static.getclicky.com/js"></script>';
