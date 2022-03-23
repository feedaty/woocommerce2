<div class="<?php echo esc_attr( $this->plugin_name ); ?>__content">
    <div class="header-logo"><img src="<?php printf('%s/assets/feedaty_logo.svg', plugins_url($this->plugin_name)); ?>"></div>
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
	<?php if(!class_exists('WooCommerce')):?>
        <h2><?php _e('You must download and activate WooCommerce in order to use this plugin.', $this->plugin_name);?></h2>
	<?php else: ?>
		<?php do_action( 'feedaty_fwr__before_form_export' ); ?>

        <div class="<?php echo esc_attr( $this->plugin_name ); ?>-settings-form container clearfix">
            <form method="post" name="<?php echo esc_attr( $this->plugin_name ); ?>_options" action="#">
				<?php
					do_action( 'feedaty_fwr__form_export' );
				?>
				<?php submit_button( __('Export Orders', $this->plugin_name), 'primary', 'submit', true ); ?>
            </form>
        </div>
		<?php do_action( 'feedaty_fwr__after_form_export' ); ?>
	<?php endif; ?>
	
	<?php do_action( 'feedaty_fwr__copyrights' ); ?>

</div>