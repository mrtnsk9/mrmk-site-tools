<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Hide_Default_Category implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'hide_default_category',
			'name'            => 'Hide Default Category',
			'description'     => 'Hides the configured default category from public lists, links and returns a 404 for its archive. Posts remain assigned to it.',
			'category'        => 'General',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_filter( 'get_the_terms', array( __CLASS__, 'hide_post_category_terms' ), 10, 3 );
		add_filter( 'terms_clauses', array( __CLASS__, 'exclude_default_category_from_term_queries' ), 10, 3 );
		add_filter( 'term_link', array( __CLASS__, 'hide_default_category_term_link' ), 10, 3 );
		add_action( 'template_redirect', array( __CLASS__, 'disable_default_category_archive' ), 1 );
	}

	public static function hide_post_category_terms( $terms, $post_id, $taxonomy ) {
		if ( ! self::should_hide_category() || 'category' !== $taxonomy || ! is_array( $terms ) ) {
			return $terms;
		}

		$default_category_id = self::get_default_category_id();
		if ( ! $default_category_id ) {
			return $terms;
		}

		$terms = array_filter(
			$terms,
			static function ( $term ) use ( $default_category_id ) {
				return ! ( $term instanceof WP_Term && (int) $term->term_id === $default_category_id );
			}
		);

		return array_values( $terms );
	}

	public static function exclude_default_category_from_term_queries( $clauses, $taxonomies, $args ) {
		if ( ! self::should_hide_category() || ! in_array( 'category', (array) $taxonomies, true ) ) {
			return $clauses;
		}

		$default_category_id = self::get_default_category_id();
		if ( ! $default_category_id ) {
			return $clauses;
		}

		global $wpdb;

		$clauses['where'] .= $wpdb->prepare( ' AND t.term_id != %d', $default_category_id );

		return $clauses;
	}

	public static function hide_default_category_term_link( $termlink, $term, $taxonomy ) {
		if (
			self::should_hide_category() &&
			'category' === $taxonomy &&
			$term instanceof WP_Term &&
			(int) $term->term_id === self::get_default_category_id()
		) {
			return '';
		}

		return $termlink;
	}

	public static function remove_default_category_menu_items( $items, $args ) {
		if ( ! self::should_hide_category() ) {
			return $items;
		}

		$default_category_id = self::get_default_category_id();
		if ( ! $default_category_id ) {
			return $items;
		}

		foreach ( $items as $key => $item ) {
			if (
				isset( $item->type, $item->object, $item->object_id ) &&
				'taxonomy' === $item->type &&
				'category' === $item->object &&
				(int) $item->object_id === $default_category_id
			) {
				unset( $items[ $key ] );
			}
		}

		return array_values( $items );
	}

	public static function disable_default_category_archive() {
		$default_category_id = self::get_default_category_id();

		if ( ! self::should_hide_category() || ! $default_category_id || ! is_category( $default_category_id ) ) {
			return;
		}

		global $wp_query;

		$is_feed = is_feed();
		$wp_query->set_404();
		if ( $is_feed ) {
			$wp_query->is_feed = false;
		}
		status_header( 404 );
		nocache_headers();
	}

	private static function should_hide_category() {
		return ! is_admin() && ! current_user_can( 'manage_categories' );
	}

	private static function get_default_category_id() {
		return absint( get_option( 'default_category' ) );
	}
}
