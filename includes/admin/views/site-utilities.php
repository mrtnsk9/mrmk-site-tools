<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section id="mrmk-st-utilities" class="mrmk-st-utilities" aria-labelledby="mrmk-st-utilities-title">
	<h2 id="mrmk-st-utilities-title"><?php echo esc_html__( 'Site Utilities', 'mrmk-st' ); ?></h2>
	<?php if ( '' !== $completed_message ) : ?>
		<div class="notice notice-success inline">
			<p><?php echo esc_html( $completed_message ); ?></p>
		</div>
	<?php endif; ?>
	<?php if ( '' !== $failed_message ) : ?>
		<div class="notice notice-error inline">
			<p><?php echo esc_html( $failed_message ); ?></p>
		</div>
	<?php endif; ?>
	<?php foreach ( $utilities as $utility ) : ?>
		<div class="mrmk-st-utility">
			<div>
				<h3><?php echo esc_html( $utility['name'] ); ?></h3>
				<p><?php echo esc_html( $utility['description'] ); ?></p>
			</div>
			<form
				action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
				method="post"
				onsubmit="return confirm('<?php echo esc_js( $utility['confirmation'] ); ?>');"
			>
				<input type="hidden" name="action" value="mrmk_st_run_utility" />
				<input type="hidden" name="utility" value="<?php echo esc_attr( $utility['slug'] ); ?>" />
				<?php wp_nonce_field( 'mrmk_st_utility_' . $utility['slug'] ); ?>
				<?php submit_button( $utility['button_label'], 'secondary', 'submit', false ); ?>
			</form>
		</div>
	<?php endforeach; ?>
</section>
