<?php
/**
 * Clicky for WordPress plugin file.
 *
 * @package Yoast/Clicky/View
 */

?><p>
	<?php
	printf(
		/* translators: 1: link open tag to clicky website tracking page; 2: link close tag. */
		esc_html__( 'Clicky can track Goals for you too, %1$syou can create them here%2$s. To be able to track a goal on this post, you need to specify the goal ID here. Optionally, you can also provide the goal revenue.', 'clicky' ),
		'<a target="_blank" href="' . esc_url( 'https://clicky.com/stats/goals-setup?site_id=' . $this->options['site_id'] ) . '">',
		'</a>'
	);
	?>
</p>
<table>
	<tr>
		<th><label for="clicky_goal_id"><?php esc_html_e( 'Goal ID:', 'clicky' ); ?></label></th>
		<td><input type="text" name="clicky_goal_id" id="clicky_goal_id" value="<?php echo ( isset( $clicky_goal['id'] ) ? esc_attr( $clicky_goal['id'] ) : '' ); ?>"/></td>
	</tr>
	<tr>
		<th><label for="clicky_goal_value"><?php esc_html_e( 'Goal Revenue:', 'clicky' ); ?></label></th>
		<td><input type="text" name="clicky_goal_value" id="clicky_goal_value" value="<?php echo ( isset( $clicky_goal['value'] ) ? esc_attr( $clicky_goal['value'] ) : '' ); ?>"/></td>
	</tr>
</table>
