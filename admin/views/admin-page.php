<?php
/**
 * Clicky for WordPress plugin file.
 *
 * @package Clicky/View
 */

?><div class="wrap">
	<h2>
		<img id="plugin_icon" src="<?php echo esc_url( CLICKY_PLUGIN_DIR_URL . 'images/clicky-32x32.png' ); ?>" class="alignleft" /> Clicky <?php esc_html_e( 'Configuration', 'clicky' ); ?>
	</h2>

	<form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">
	<?php
		settings_fields( Clicky_Options_Admin::$option_group );
	?>
	<div id="clicky_wrapper">
		<h2 class="nav-tab-wrapper" id="clicky-tabs">
			<a class="nav-tab" id="basic-tab" href="#top#basic"><?php esc_html_e( 'Basic settings', 'clicky' ); ?></a>
			<a class="nav-tab" id="advanced-tab" href="#top#advanced"><?php esc_html_e( 'Advanced settings', 'clicky' ); ?></a>
		</h2>

		<div class="tabwrapper">
			<div id="basic" class="clicky_tab">
				<?php do_settings_sections( 'clicky' ); ?>
			</div>
			<div id="advanced" class="clicky_tab">
				<?php do_settings_sections( 'clicky-advanced' ); ?>
			</div>
		</div>
		<?php
			submit_button( __( 'Save Clicky settings', 'clicky' ) );

			/**
			 * Allow for adding content to the Clicky admin page footer.
			 *
			 * @deprecated 1.10.0. Use the {@see 'clicky/admin_footer'} action instead.
			 */
			do_action_deprecated( 'clicky_admin_footer', [], 'Clicky 1.10.0', 'clicky/admin_footer' );

			/**
			 * Allow for adding content to the Clicky admin page footer.
			 *
			 * @since 1.10.0
			 * @deprecated 2.1.0. Use the {@see 'clicky/admin_footer'} action instead.
			 */
			do_action_deprecated( 'Yoast\WP\Clicky\admin_footer', [], 'Clicky 2.1.0', 'clicky/admin_footer' );

			/**
			 * Allow for adding content to the Clicky admin page footer.
			 *
			 * @since 2.1.0
			 */
			do_action( 'clicky/admin_footer' );
		?>
	</div>
	</form>
	<div id="clicky_sidebar">
		<?php
			$this->clicky_news();
		?>
	</div>
</div>
