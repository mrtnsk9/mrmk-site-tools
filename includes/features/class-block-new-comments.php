<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Block_New_Comments implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'block_new_comments',
			'name'            => 'Block New Comments',
			'description'     => 'Prevents new comments from being submitted site-wide. Existing comments remain visible and stored.',
			'category'        => 'General',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_filter( 'comments_open', '__return_false', 100, 2 );
	}
}
