<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Normalize_Head_Void_Elements implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'normalize_head_void_elements',
			'name'            => 'Remove trailing slashes from void elements in head',
			'description'     => 'Removes XHTML-style trailing slashes from void elements in the document head. The rest of the page markup is unchanged.',
			'category'        => 'General',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_action( 'template_redirect', array( __CLASS__, 'start_output_buffer' ), 0 );
	}

	public static function start_output_buffer() {
		ob_start( array( __CLASS__, 'normalize_head_void_elements' ) );
	}

	public static function normalize_head_void_elements( $html ) {
		if ( ! is_string( $html ) || '' === $html || ! preg_match( '/<head\b[^>]*>/i', $html, $head_tag, PREG_OFFSET_CAPTURE ) ) {
			return $html;
		}

		$head_content_start = $head_tag[0][1] + strlen( $head_tag[0][0] );
		$head_close_start = stripos( $html, '</head', $head_content_start );
		if ( false === $head_close_start ) {
			return $html;
		}

		$head_close_end = strpos( $html, '>', $head_close_start );
		if ( false === $head_close_end ) {
			return $html;
		}

		$head_content = substr( $html, $head_content_start, $head_close_start - $head_content_start );
		$normalized_head = preg_replace_callback(
			'~<!--.*?-->|<(script|style)\b[^>]*>.*?</\1\s*>|<(area|base|br|col|embed|hr|img|input|link|meta|param|source|track|wbr)(?=[\s/>])((?:"[^"]*"|\'[^\']*\'|[^\'">])*)>~is',
			static function ( $matches ) {
				if ( empty( $matches[2] ) ) {
					return $matches[0];
				}

				$tag = '<' . $matches[2] . $matches[3] . '>';
				if ( preg_match( '/\s+\/>$/', $tag ) ) {
					return preg_replace( '/\s+\/>$/', '>', $tag );
				}

				if ( in_array( trim( $matches[3] ), array( '', '/' ), true ) || preg_match( '/["\']\/>$/', $tag ) ) {
					return substr( $tag, 0, -2 ) . '>';
				}

				return $tag;
			},
			$head_content
		);

		if ( null === $normalized_head ) {
			trigger_error( 'Unable to normalize void elements in the document head.', E_USER_WARNING );
			return $html;
		}

		return substr_replace( $html, $normalized_head, $head_content_start, $head_close_start - $head_content_start );
	}
}
