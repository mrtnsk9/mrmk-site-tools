<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Disable_Categories implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'disable_categories',
			'name'            => 'Disable Categories',
			'description'     => 'Removes the built-in post categories editing interface, hides categories and category links from public output, and returns 404 for category archives. Existing categories and post assignments remain stored.',
			'category'        => 'General',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_action( 'init', array( __CLASS__, 'disable_post_categories' ), 100 );
		add_filter( 'get_the_terms', array( __CLASS__, 'hide_post_categories' ), 10, 3 );
		add_filter( 'get_terms', array( __CLASS__, 'hide_post_category_terms' ), 10, 3 );
		add_filter( 'term_link', array( __CLASS__, 'hide_post_category_link' ), 10, 3 );
		add_filter( 'wp_get_nav_menu_items', array( __CLASS__, 'remove_post_category_menu_items' ), 10, 3 );
		add_action( 'template_redirect', array( __CLASS__, 'disable_post_category_archive' ), 1 );
	}

	public static function disable_post_categories() {
		$taxonomy = get_taxonomy( 'category' );
		if ( ! $taxonomy ) {
			return;
		}

		// Keep category rewrite/query vars active so archive requests can be recognized and returned as 404s.
		$taxonomy->show_ui               = false;
		$taxonomy->show_in_menu          = false;
		$taxonomy->show_in_nav_menus     = false;
		$taxonomy->show_tagcloud         = false;
		$taxonomy->show_in_quick_edit    = false;
		$taxonomy->show_admin_column     = false;
		$taxonomy->show_in_rest          = false;

		foreach ( (array) $taxonomy->object_type as $object_type ) {
			unregister_taxonomy_for_object_type( 'category', $object_type );
		}
	}

	public static function hide_post_categories( $terms, $post_id, $taxonomy ) {
		if ( self::is_public_request() && 'category' === $taxonomy ) {
			return false;
		}

		return $terms;
	}

	public static function hide_post_category_terms( $terms, $taxonomies, $args ) {
		if ( ! self::is_public_request() || ! is_array( $terms ) || is_wp_error( $terms ) ) {
			return $terms;
		}

		$taxonomies = (array) $taxonomies;
		if ( array( 'category' ) === array_values( $taxonomies ) ) {
			return array();
		}

		return array_values(
			array_filter(
				$terms,
				static function ( $term ) {
					return ! ( $term instanceof WP_Term && 'category' === $term->taxonomy );
				}
			)
		);
	}

	public static function hide_post_category_link( $termlink, $term, $taxonomy ) {
		if ( self::is_public_request() && 'category' === $taxonomy ) {
			return '';
		}

		return $termlink;
	}

	public static function remove_post_category_menu_items( $items, $args, $menu ) {
		if ( ! self::is_public_request() || ! is_array( $items ) ) {
			return $items;
		}

		$items = array_filter(
			$items,
			static function ( $item ) {
				return ! ( isset( $item->type, $item->object ) && 'taxonomy' === $item->type && 'category' === $item->object );
			}
		);

		return array_values( $items );
	}

	public static function disable_post_category_archive() {
		if ( ! self::is_public_request() || ! is_category() ) {
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

	private static function is_public_request() {
		return ! is_admin();
	}
}
