<?php
/**
 * Clicky for WordPress plugin file.
 *
 * @package Yoast/Clicky/View
 */

/**
 * Global for CSP nonce.
 *
 * @var string $names_nonce
 */

?>
<script type='text/javascript' nonce='<?php echo esc_attr( $names_nonce ); ?>'>
	function clicky_gc(name) {
		var ca = document.cookie.split(';');
		for (var i in ca) {
			if (ca[i].indexOf(name + '=') != -1) {
				return decodeURIComponent(ca[i].split('=')[1]);
			}
		}
		return '';
	}
	var username_check = clicky_gc('<?php echo wp_json_encode( 'comment_author_' . md5( get_option( 'siteurl' ) ) ); ?>');
	if (username_check) var clicky_custom_session = {username: username_check};
</script>
