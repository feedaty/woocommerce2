<?php
	if ( ! defined( 'ABSPATH' ) ) {
		echo "permission denied";
		exit;
	}
?>
<?php do_action( "{$this->plugin_name}-before-main-container"); ?>
<div class="<?php printf( '%s-reviews-main-container', esc_attr( $this->plugin_name ) ); ?>">
    <div class="reviews-logo-container" style="background:url('<?php printf('%s/assets/feedaty_logo.svg', plugins_url($this->plugin_name)); ?>');">
    </div>
	<?php if ( is_object( $reviews ) && ( is_array( $reviews->Feedbacks ) && count( $reviews->Feedbacks ) ) ):?>
	<?php do_action( "{$this->plugin_name}-before-reviews-loop"); ?>
    <div class="reviews-ratings-container">
		<?php do_action( "{$this->plugin_name}-reviews-loop-start"); ?>
		<?php foreach ($reviews->Feedbacks as $index => $review): ?>
		<?php //do_action( '{$this->plugin_name}-reviews', $review ); ?>
        <div class="single-review-wrap <?php printf( 'single-review-%s', esc_attr( $index ) ); ?>"
		<?php do_action( "{$this->plugin_name}-single-review", $review ); ?>
    </div>
<?php endforeach; ?>
	<?php do_action( "{$this->plugin_name}-reviews-loop-end"); ?>
</div>
<?php do_action( "{$this->plugin_name}-after-reviews-loop", $reviews); ?>
<?php else: ?>
	<?php do_action("{$this->plugin_name}-no_results"); ?>
<?php endif; ?>
<?php do_action( "{$this->plugin_name}-main-container-end"); ?>
</div>
<?php do_action( "{$this->plugin_name}-after-main-container"); ?>

