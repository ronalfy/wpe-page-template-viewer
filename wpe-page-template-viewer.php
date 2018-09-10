<?php
/**
 * Plugin Name:       WPE Page Template Viewer
 * Plugin URI:        https://github.com/ronalfy/wpe-page-template-viewer
 * Description:       View which page templates are used.
 * Version:           1.0.0
 * Author:            Ronald Huereca
 * Author URI:        https://mediaron.com
 * Text Domain:       wpe-page-template-viewer
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

class WPE_Page_Template_Viewer {

	private $page_templates = null;

	public function __construct() {
		global $pagenow;
		if( 'edit.php' === $pagenow && isset( $_GET['post_type'] ) && 'page' === $_GET['post_type'] ) {
			add_filter( 'manage_pages_columns', array( $this, 'manage_page_columns' ) );
			add_action( 'manage_pages_custom_column', array( $this, 'manage_pages_custom_column' ) );
			add_action( 'restrict_manage_posts' , array( $this, 'maybe_output_template_filter' ), 10, 2 );
			$this->page_templates =  wp_get_theme()->get_page_templates();
		}
	}

	public function maybe_output_template_filter( $post_type, $which ) {
		if ( 'page' !== $post_type ) return;
		echo '<select id="page-template" name="page-template">';
		printf( '<option value="default">%s</option>', esc_html__( 'Default', 'wpe-page-template-viewer' ) );
		foreach( $this->page_templates as $slug => $name ) {
			printf( '<option value="%s">%s</option>', esc_attr( $slug ), esc_html( $name ) );
		}
		echo '</select>';	
	}

	public function manage_page_columns( $columns ) {
		$columns['page_template'] = 'Template';
		return $columns;
	}

	public function manage_pages_custom_column( $column_name, $page_id ) {
		if( 'page_template' === $column_name ) {
			$template = get_page_template_slug( $page_id );
			$template_name = false;
			foreach( $this->page_templates as $slug => $name ) {
				if( $template === $slug ) {
					$template_name = $name;
					break;
				}
			}
			if( empty( $template_name ) ) {
				$template_name = __( 'Default', 'wpe-page-template-viewer' );
			}
			echo esc_html( $template_name );
		}
	}

}
add_action( 'plugins_loaded', function() {
	new WPE_Page_Template_Viewer();
} );