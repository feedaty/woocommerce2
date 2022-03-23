<div class="<?php echo esc_attr( $this->plugin_name ); ?>__content">
    <div class="header-logo"><img src="<?php printf('%s/assets/feedaty_logo.svg', plugins_url($this->plugin_name)); ?>"></div>
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
	<?php if(!class_exists('WooCommerce')):?>
        <h2><?php _e('You must download and activate WooCommerce in order to use this plugin.', $this->plugin_name);?></h2>
	<?php else: ?>
		<?php do_action( 'feedaty_fwr__before_global_settings' ); ?>

        <div class="<?php echo esc_attr( $this->plugin_name ); ?>-settings-form container clearfix">
            <form method="post" name="<?php echo $this->plugin_name; ?>_options" action="options.php">
				<?php
					settings_fields( $this->plugin_name );
					do_settings_sections( $this->plugin_name );
					do_action( 'feedaty_fwr__global_settings' );
				?>
				<?php submit_button( __('Save all changes', $this->plugin_name), 'primary', 'submit', true ); ?>
            </form>
        </div>
		<?php do_action( 'feedaty_fwr__after_global_settings' ); ?>
	<?php endif; ?>
	<?php do_action( 'feedaty_fwr__copyrights' ); ?>
</div>