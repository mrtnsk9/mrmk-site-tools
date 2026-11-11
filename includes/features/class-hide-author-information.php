<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Hide_Author_Information implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'hide_author_information',
			'name'            => 'Hide Author Information',
			'description'     => 'Hides standard author details on public pages and returns a 404 for author archives. Feeds, REST responses, and admin output are unchanged.',
			'category'        => 'Privacy',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_action( 'template_redirect', array( __CLASS__, 'hide_author_archives' ), 1 );
		add_filter( 'the_author', array( __CLASS__, 'hide_author_value' ), 100 );
		add_filter( 'the_modified_author', array( __CLASS__, 'hide_author_value' ), 100 );
		add_filter( 'the_author_link', array( __CLASS__, 'hide_author_value' ), 100 );
		add_filter( 'the_author_posts_link', array( __CLASS__, 'hide_author_value' ), 100 );
		add_filter( 'author_link', array( __CLASS__, 'hide_author_value' ), 100 );
		add_filter( 'get_avatar', array( __CLASS__, 'hide_author_avatar' ), 100, 6 );
		add_filter( 'get_avatar_url', array( __CLASS__, 'hide_author_avatar_url' ), 100, 3 );
		add_filter( 'render_block', array( __CLASS__, 'hide_author_blocks' ), 100, 3 );
		add_filter( 'rank_math/json_ld', array( __CLASS__, 'hide_author_schema' ), 100, 2 );

		$author_fields = array(
			'description',
			'display_name',
			'first_name',
			'last_name',
			'user_description',
			'user_firstname',
			'user_lastname',
			'user_url',
		);

		foreach ( $author_fields as $field ) {
			add_filter( 'get_the_author_' . $field, array( __CLASS__, 'hide_author_value' ), 100 );
		}
	}

	public static function hide_author_archives() {
		if ( ! self::is_public_html_request() || ! is_author() ) {
			return;
		}

		global $wp_query;

		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}

	public static function hide_author_value( $value ) {
		return self::is_public_html_request() ? '' : $value;
	}

	public static function hide_author_avatar( $avatar, $id_or_email, $size, $default, $alt, $args ) {
		return self::is_public_html_request() && self::is_current_post_author( $id_or_email ) ? '' : $avatar;
	}

	public static function hide_author_avatar_url( $url, $id_or_email, $args ) {
		return self::is_public_html_request() && self::is_current_post_author( $id_or_email ) ? '' : $url;
	}

	public static function hide_author_blocks( $block_content, $block, $instance ) {
		if ( ! self::is_public_html_request() || empty( $block['blockName'] ) ) {
			return $block_content;
		}

		if ( in_array( $block['blockName'], array( 'core/post-author', 'core/post-author-name', 'core/post-author-biography' ), true ) ) {
			return '';
		}

		if ( 'core/avatar' === $block['blockName'] ) {
			$context = is_object( $instance ) && isset( $instance->context ) ? $instance->context : array();
			$attrs   = isset( $block['attrs'] ) ? $block['attrs'] : array();

			if ( ! isset( $context['commentId'] ) && ( ! empty( $attrs['userId'] ) || isset( $context['postId'] ) ) ) {
				return '';
			}
		}

		return $block_content;
	}

	public static function hide_author_schema( $data, $jsonld ) {
		if ( ! self::is_public_html_request() || ! is_array( $data ) ) {
			return $data;
		}

		unset( $data['ProfilePage'] );

		foreach ( $data as $key => $schema ) {
			$data[ $key ] = self::remove_author_properties( $schema );
		}

		return $data;
	}

	private static function remove_author_properties( $schema ) {
		if ( ! is_array( $schema ) ) {
			return $schema;
		}

		foreach ( $schema as $key => $value ) {
			if ( 'author' === $key ) {
				unset( $schema[ $key ] );
			} elseif ( is_array( $value ) ) {
				$schema[ $key ] = self::remove_author_properties( $value );
			}
		}

		return $schema;
	}

	private static function is_current_post_author( $id_or_email ) {
		if ( is_object( $id_or_email ) ) {
			$user_id = isset( $id_or_email->post_author ) ? (int) $id_or_email->post_author : (int) ( $id_or_email->user_id ?? $id_or_email->ID ?? 0 );
		} else {
			$user_id = is_numeric( $id_or_email ) ? (int) $id_or_email : 0;
		}

		if ( 0 === $user_id ) {
			return false;
		}

		global $post;

		if ( $post instanceof WP_Post && (int) $post->post_author === $user_id ) {
			return true;
		}

		return is_author() && (int) get_queried_object_id() === $user_id;
	}

	private static function is_public_html_request() {
		return ! is_admin() &&
			! is_feed() &&
			( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST );
	}
}
