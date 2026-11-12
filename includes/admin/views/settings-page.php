<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty($features) ) exit;
if ( empty($plugin) ) exit;

/** @var MRMK_ST_Plugin $plugin */
$saved_options = get_option( MRMK_ST_Plugin::OPTION_NAME, array() );
$saved_options = is_array( $saved_options ) ? $saved_options : array();
$categories = array();
foreach ( $features as $feature ) {
	if ( ! empty( $feature['category'] ) ) {
		$categories[ $feature['category'] ] = $feature['category'];
	}
}
natcasesort( $categories );
?>
<div class="wrap mrmk-st-page">
	<header class="mrmk-st-page__header">
		<div>
			<p class="mrmk-st-page__eyebrow"><?php echo esc_html__( 'Site configuration', 'mrmk-st' ); ?></p>
			<h1><?php echo esc_html__( 'mrmk Site Tools', 'mrmk-st' ); ?></h1>
			<p class="mrmk-st-page__intro"><?php echo esc_html__( 'Enable the tools you want this site to use. Changes take effect on the next request.', 'mrmk-st' ); ?></p>
		</div>
	</header>

	<form action="options.php" method="post" class="mrmk-st-form">
		<?php settings_fields( 'mrmk_st_settings' ); ?>
		<input
			type="hidden"
			name="<?php echo esc_attr( MRMK_ST_Plugin::OPTION_NAME ); ?>[_settings_submitted]"
			value="1"
		/>

		<div class="mrmk-st-toolbar">
			<label class="screen-reader-text" for="mrmk-st-feature-search">
				<?php echo esc_html__( 'Search features', 'mrmk-st' ); ?>
			</label>
			<input
				id="mrmk-st-feature-search"
				type="search"
				class="mrmk-st-toolbar__search"
				placeholder="<?php echo esc_attr__( 'Search features…', 'mrmk-st' ); ?>"
				autocomplete="off"
			/>
			<label class="mrmk-st-toolbar__filter" for="mrmk-st-feature-status">
				<span><?php echo esc_html__( 'Status', 'mrmk-st' ); ?></span>
				<select id="mrmk-st-feature-status" class="mrmk-st-toolbar__select">
					<option value="all"><?php echo esc_html__( 'All statuses', 'mrmk-st' ); ?></option>
					<option value="enabled"><?php echo esc_html__( 'Enabled only', 'mrmk-st' ); ?></option>
					<option value="disabled"><?php echo esc_html__( 'Disabled only', 'mrmk-st' ); ?></option>
				</select>
			</label>
			<label class="mrmk-st-toolbar__filter" for="mrmk-st-feature-category">
				<span><?php echo esc_html__( 'Category', 'mrmk-st' ); ?></span>
				<select id="mrmk-st-feature-category" class="mrmk-st-toolbar__select">
					<option value=""><?php echo esc_html__( 'All categories', 'mrmk-st' ); ?></option>
					<?php foreach ( $categories as $category ) : ?>
						<option value="<?php echo esc_attr( $category ); ?>"><?php echo esc_html( $category ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="mrmk-st-toolbar__filter" for="mrmk-st-feature-sort">
				<span><?php echo esc_html__( 'Sort', 'mrmk-st' ); ?></span>
				<select id="mrmk-st-feature-sort" class="mrmk-st-toolbar__select">
					<option value="name"><?php echo esc_html__( 'Name A–Z', 'mrmk-st' ); ?></option>
					<option value="enabled"><?php echo esc_html__( 'Enabled first', 'mrmk-st' ); ?></option>
				</select>
			</label>
			<span
				class="mrmk-st-toolbar__hint"
				data-search-results
				data-results-template="<?php echo esc_attr__( '%1$d of %2$d features shown (%3$d enabled)', 'mrmk-st' ); ?>"
				aria-live="polite"
			>
				<?php echo esc_html__( 'All features shown', 'mrmk-st' ); ?>
			</span>
		</div>

		<?php if ( empty( $features ) ) : ?>
			<div class="notice notice-info inline">
				<p><?php echo esc_html__( 'No features are currently available.', 'mrmk-st' ); ?></p>
			</div>
		<?php else : ?>
			<div class="mrmk-st-grid">
				<?php foreach ( $features as $slug => $feature ) : ?>
					<?php $enabled = $plugin->is_feature_enabled( $slug, $feature ); ?>
					<article
						class="mrmk-st-card<?php echo $enabled ? ' is-enabled' : ''; ?>"
						data-feature-card
						data-name="<?php echo esc_attr( $feature['name'] ); ?>"
						data-category="<?php echo esc_attr( $feature['category'] ); ?>"
						data-search="<?php echo esc_attr( strtolower( $feature['name'] . ' ' . $feature['description'] . ' ' . $feature['category'] ) ); ?>"
					>
						<div class="mrmk-st-card__body">
							<div class="mrmk-st-card__meta">
								<span class="mrmk-st-card__category"><?php echo esc_html( $feature['category'] ); ?></span>
								<span
									class="mrmk-st-card__state"
									data-feature-state
									data-enabled-text="<?php echo esc_attr__( 'Enabled', 'mrmk-st' ); ?>"
									data-disabled-text="<?php echo esc_attr__( 'Disabled', 'mrmk-st' ); ?>"
								>
									<?php echo $enabled ? esc_html__( 'Enabled', 'mrmk-st' ) : esc_html__( 'Disabled', 'mrmk-st' ); ?>
								</span>
							</div>
							<h2><?php echo esc_html( $feature['name'] ); ?></h2>
							<p><?php echo esc_html( $feature['description'] ); ?></p>
						</div>
						<div class="mrmk-st-card__footer">
							<label class="mrmk-st-toggle" for="mrmk-st-feature-<?php echo esc_attr( $slug ); ?>">
								<span><?php echo esc_html__( 'Enable feature', 'mrmk-st' ); ?></span>
								<input
									id="mrmk-st-feature-<?php echo esc_attr( $slug ); ?>"
									class="mrmk-st-toggle__input"
									name="<?php echo esc_attr( MRMK_ST_Plugin::OPTION_NAME ); ?>[<?php echo esc_attr( $slug ); ?>]"
									type="checkbox"
									value="1"
									<?php checked( $enabled ); ?>
								/>
								<span class="mrmk-st-toggle__track" aria-hidden="true"></span>
							</label>
							<?php if ( ! empty( $feature['settings'] ) && is_array( $feature['settings'] ) ) : ?>
								<div class="mrmk-st-card__settings">
									<?php foreach ( $feature['settings'] as $setting_name => $setting ) : ?>
										<?php
										if ( ! is_array( $setting ) || ! isset( $setting['type'] ) || 'number' !== $setting['type'] ) {
											continue;
										}
										$setting_id    = 'mrmk-st-setting-' . $slug . '-' . $setting_name;
										$setting_value = isset( $saved_options[ $setting_name ] )
											? $saved_options[ $setting_name ]
											: $setting['default'];
										?>
										<label for="<?php echo esc_attr( $setting_id ); ?>">
											<?php echo esc_html( $setting['label'] ); ?>
										</label>
										<input
											id="<?php echo esc_attr( $setting_id ); ?>"
											name="<?php echo esc_attr( MRMK_ST_Plugin::OPTION_NAME ); ?>[<?php echo esc_attr( $setting_name ); ?>]"
											type="number"
											min="<?php echo esc_attr( $setting['min'] ); ?>"
											max="<?php echo esc_attr( $setting['max'] ); ?>"
											step="1"
											value="<?php echo esc_attr( $setting_value ); ?>"
										/>
										<?php if ( ! empty( $setting['description'] ) ) : ?>
											<p><?php echo esc_html( $setting['description'] ); ?></p>
										<?php endif; ?>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
			<p class="mrmk-st-no-results" data-no-results hidden>
				<?php echo esc_html__( 'No features match your search.', 'mrmk-st' ); ?>
			</p>
		<?php endif; ?>

		<div class="mrmk-st-save-bar">
			<p><?php echo esc_html__( 'Save your changes to apply the selected features.', 'mrmk-st' ); ?></p>
			<?php submit_button( __( 'Save changes', 'mrmk-st' ), 'primary', 'submit', false ); ?>
		</div>
	</form>
	<?php $site_utilities->render(); ?>
</div>
