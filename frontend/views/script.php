<?php
/**
 * Clicky for WordPress plugin file.
 *
 * @package Yoast/Clicky/View
 */

?>
<script>
	<?php
	if ( ! empty( $clicky_extra ) ) {
		echo 'var clicky_custom = clicky_custom || {}; ';
		echo $clicky_extra;
	} ?>

    var clicky_site_ids = clicky_site_ids || [];
    clicky_site_ids.push(<?php echo $this->options['site_id']; ?>);
</script>
<script async src="//static.getclicky.com/js"></script>
