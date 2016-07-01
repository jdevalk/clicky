<?php
/**
 * @package Yoast/Clicky/View
 */

?><p>
    <?php printf( __( 'Clicky can track Goals for you too, %1$syou can create them here%2$s. To be able to track a goal on this post, you need to specify the goal ID here.
    Optionally, you can also provide the goal revenue.', 'clicky' ), '<a target="_blank" href="https://clicky.com/stats/goals-setup?site_id=' . $this->options['site_id'] . '">', '</a>' ); ?>
</p>
<table>
    <tr>
        <th><label for="clicky_goal_id"><?php _e( 'Goal ID:', 'clicky' ); ?></label></th>
        <td><input type="text" name="clicky_goal_id" id="clicky_goal_id" value="<?php echo ( isset( $clicky_goal['id'] ) ? esc_attr( $clicky_goal['id'] ) : '' ); ?>"/></td>
    </tr>
    <tr>
        <th><label for="clicky_goal_value"><?php _e( 'Goal Revenue:', 'clicky' ); ?></label></th>
        <td><input type="text" name="clicky_goal_value" id="clicky_goal_value" value="<?php echo ( isset( $clicky_goal['value'] ) ? esc_attr( $clicky_goal['value'] ) : '' ); ?>"/></td>
    </tr>
</table>
