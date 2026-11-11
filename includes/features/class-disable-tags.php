<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Disable_Tags implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'disable_tags',
			'name'            => 'Disable Tags',
			'description'     => 'Disables the built-in post tags taxonomy. Existing tags and post assignments remain stored.',
			'category'        => 'General',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_action( 'init', array( __CLASS__, 'disable_post_tags' ), 100 );
		add_filter( 'get_the_terms', array( __CLASS__, 'hide_post_tags' ), 10, 3 );
		add_filter( 'get_terms', array( __CLASS__, 'hide_post_tag_terms' ), 10, 3 );
		add_filter( 'term_link', array( __CLASS__, 'hide_post_tag_link' ), 10, 3 );
		add_filter( 'wp_get_nav_menu_items', array( __CLASS__, 'remove_post_tag_menu_items' ), 10, 3 );
		add_action( 'template_redirect', array( __CLASS__, 'disable_post_tag_archive' ), 1 );
	}

	public static function disable_post_tags() {
		$taxonomy = get_taxonomy( 'post_tag' );
		if ( ! $taxonomy ) {
			return;
		}

		$taxonomy->public                = false;
		$taxonomy->publicly_queryable    = false;
		$taxonomy->show_ui               = false;
		$taxonomy->show_in_menu          = false;
		$taxonomy->show_in_nav_menus     = false;
		$taxonomy->show_tagcloud         = false;
		$taxonomy->show_in_quick_edit    = false;
		$taxonomy->show_admin_column     = false;
		$taxonomy->show_in_rest          = false;
		$taxonomy->query_var             = false;
		$taxonomy->rewrite               = false;

		foreach ( (array) $taxonomy->object_type as $object_type ) {
			unregister_taxonomy_for_object_type( 'post_tag', $object_type );
		}
	}

	public static function hide_post_tags( $terms, $post_id, $taxonomy ) {
		if ( self::is_public_request() && 'post_tag' === $taxonomy ) {
			return false;
		}

		return $terms;
	}

	public static function hide_post_tag_terms( $terms, $taxonomies, $args ) {
		if ( ! self::is_public_request() || ! is_array( $terms ) || is_wp_error( $terms ) ) {
			return $terms;
		}

		$taxonomies = (array) $taxonomies;
		if ( array( 'post_tag' ) === array_values( $taxonomies ) ) {
			return array();
		}

		return array_values(
			array_filter(
				$terms,
				static function ( $term ) {
					return ! ( $term instanceof WP_Term && 'post_tag' === $term->taxonomy );
				}
			)
		);
	}

	public static function hide_post_tag_link( $termlink, $term, $taxonomy ) {
		if ( self::is_public_request() && 'post_tag' === $taxonomy ) {
			return '';
		}

		return $termlink;
	}

	public static function remove_post_tag_menu_items( $items, $args, $menu ) {
		if ( ! self::is_public_request() || ! is_array( $items ) ) {
			return $items;
		}

		$items = array_filter(
			$items,
			static function ( $item ) {
				return ! ( isset( $item->type, $item->object ) && 'taxonomy' === $item->type && 'post_tag' === $item->object );
			}
		);

		return array_values( $items );
	}

	public static function disable_post_tag_archive() {
		if ( ! self::is_public_request() || ! is_tag() ) {
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
