<?php
/**
 * @package Yoast/Clicky/View
 */

?><script type="text/javascript">
	<?php
	if ( ! empty( $clicky_extra ) ) {
		echo 'var clicky_custom = clicky_custom || {}; ';
		echo $clicky_extra;
	} ?>
	var clicky = { log : function () { return true;	}, goal: function () { return true;	} };
	var clicky_site_id = <?php echo $this->options['site_id']; ?>;
	(function () {
		var s = document.createElement('script');s.type = 'text/javascript';s.async = true;s.src = '//static.getclicky.com/js';
		( document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0] ).appendChild(s);
	})();
</script>
<noscript><p><img alt="Clicky" width="1" height="1" src="//in.getclicky.com/<?php echo $this->options['site_id']; ?>ns.gif" /></p></noscript>
