<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Utility_Flush_Rank_Math_Sitemap_Cache implements MRMK_ST_Utility_Interface {
	public static function is_available(): bool {
		return defined( 'RANK_MATH_VERSION' );
	}

	public static function get_metadata() {
		return array(
			'slug'            => 'flush_rank_math_sitemap_cache',
			'name'            => __( 'Flush Rank Math sitemap cache', 'mrmk-st' ),
			'description'     => __( 'Invalidates Rank Math XML sitemap cache storage when Rank Math and its Sitemap module are available.', 'mrmk-st' ),
			'button_label'    => __( 'Flush sitemap cache', 'mrmk-st' ),
			'confirmation'    => __( 'Flush the Rank Math sitemap cache now?', 'mrmk-st' ),
			'success_message' => __( 'Rank Math sitemap cache was flushed.', 'mrmk-st' ),
			'failure_message' => __( 'Rank Math sitemap cache was not flushed. Check that Rank Math and its Sitemap module are active and that the cache API is available.', 'mrmk-st' ),
		);
	}

	public static function run(): bool {
		if ( ! defined( 'RANK_MATH_VERSION' ) ) {
			return false;
		}

		$rank_math_modules = get_option( 'rank_math_modules', array() );
		if ( ! is_array( $rank_math_modules ) || ! in_array( 'sitemap', $rank_math_modules, true ) ) {
			return false;
		}

		$cache_class = '\RankMath\Sitemap\Cache';
		$cache_method = array( $cache_class, 'invalidate_storage' );
		if ( ! class_exists( $cache_class ) || ! is_callable( $cache_method ) ) {
			return false;
		}

		call_user_func( $cache_method );

		return true;
	}
}
