<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Disable_Author_Pages implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'disable_author_pages',
			'name'            => 'Disable Author Pages',
			'description'     => 'Returns a 404 for author archives and removes standard author archive links from the front end.',
			'category'        => 'General',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_action( 'template_redirect', array( __CLASS__, 'disable_author_pages' ), 1 );
		add_filter( 'the_author_posts_link', array( __CLASS__, 'remove_author_posts_link' ), 10, 3 );
		add_filter( 'render_block_data', array( __CLASS__, 'remove_author_block_links' ), 10, 3 );
	}

	public static function disable_author_pages() {
		if ( ! is_author() ) {
			return;
		}

		global $wp_query;

		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}

	public static function remove_author_posts_link( $link, $author, $title ) {
		return is_admin() ? $link : wp_strip_all_tags( $link );
	}

	public static function remove_author_block_links( $parsed_block, $source_block, $parent_block ) {
		if ( is_admin() || empty( $parsed_block['blockName'] ) ) {
			return $parsed_block;
		}

		$block_name = $parsed_block['blockName'];
		$is_author_block = in_array( $block_name, array( 'core/post-author', 'core/post-author-name' ), true );

		if ( 'core/avatar' === $block_name ) {
			$has_comment_context = is_object( $parent_block ) && isset( $parent_block->context['commentId'] );
			$has_author_context  = ! empty( $parsed_block['attrs']['userId'] ) ||
				( is_object( $parent_block ) && isset( $parent_block->context['postId'] ) && ! $has_comment_context );
			$is_author_block = $has_author_context && ! $has_comment_context;
		}

		if ( $is_author_block ) {
			$parsed_block['attrs']['isLink'] = false;
		}

		return $parsed_block;
	}
}
