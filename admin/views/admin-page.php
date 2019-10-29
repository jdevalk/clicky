<?php
/**
 * Clicky for WordPress plugin file.
 *
 * @package Yoast/Clicky/View
 */

?><div class="wrap">
	<h2>
		<img id="plugin_icon" src="<?php echo esc_url( CLICKY_PLUGIN_DIR_URL . 'images/clicky-32x32.png' ); ?>" class="alignleft" /> Clicky <?php esc_html_e( 'Configuration', 'clicky' ); ?>
	</h2>

	<form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">
	<?php
		settings_fields( Clicky_Options_Admin::$option_group );
	?>
	<div id="yoast_wrapper">
		<h2 class="nav-tab-wrapper" id="yoast-tabs">
			<a class="nav-tab" id="basic-tab" href="#top#basic"><?php esc_html_e( 'Basic settings', 'clicky' ); ?></a>
			<a class="nav-tab" id="advanced-tab" href="#top#advanced"><?php esc_html_e( 'Advanced settings', 'clicky' ); ?></a>
		</h2>

		<div class="tabwrapper">
			<div id="basic" class="yoast_tab">
				<?php do_settings_sections( 'clicky' ); ?>
			</div>
			<div id="advanced" class="yoast_tab">
				<?php do_settings_sections( 'clicky-advanced' ); ?>
			</div>
		</div>
		<?php
			submit_button( __( 'Save Clicky settings', 'clicky' ) );

			/**
			 * Allow for adding content to the Yoast Clicky admin page footer.
			 *
			 * @deprecated 1.10.0. Use the {@see 'Yoast\WP\Clicky\admin_footer'} action instead.
			 */
			do_action_deprecated( 'clicky_admin_footer', [], 'Yoast Clicky 1.10.0', 'Yoast\WP\Clicky\admin_footer' );

			/**
			 * Allow for adding content to the Yoast Clicky admin page footer.
			 *
			 * @since 1.10.0
			 */
			do_action( 'Yoast\WP\Clicky\admin_footer' );
		?>
	</div>
	</form>
	<div id="yoast_sidebar">
		<?php
			$this->yoast_news();
			$this->clicky_news();
		?>
	</div>
</div>
