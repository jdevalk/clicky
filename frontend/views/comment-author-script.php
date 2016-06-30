<?php
/**
 * @package Yoast/Clicky/View
 */

?><script type='text/javascript'>
	function clicky_gc(name) {
		var ca = document.cookie.split(';');
		for (var i in ca) {
			if (ca[i].indexOf(name + '=') != -1) {
				return decodeURIComponent(ca[i].split('=')[1]);
			}
		}
		return '';
	}
	var username_check = clicky_gc('comment_author_<?php echo md5( get_option( 'siteurl' ) ); ?>');
	if (username_check) var clicky_custom_session = {username: username_check};
</script>
