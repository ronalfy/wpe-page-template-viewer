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
			add_filter( 'parse_query', array( $this, 'filter_templates' ) );
			load_plugin_textdomain( 'wpe-page-template-viewer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			$this->page_templates = wp_get_theme()->get_page_templates();
		}
	}

	/**
	 * Modifies query to filter for template type.
	 *
	 * Modifies query to filter for template type.
	 *
	 * @since 1.0.0
	 *
	 * @param object $query WordPress' main query
	 * @return object $query WordPress' modified main query if template matches
	 */
	public function filter_templates( $query ) {
		if( ( ! is_admin() || ! $query->is_main_query() ) ) { 
			return $query;
		}
		if( 'page' !== $query->get('post_type' ) || ! isset( $_REQUEST['page-template'] ) ) {
			return $query;
		};
		if( 'none' === $_REQUEST['page-template'] ) {
			return $query;
		}

		$query->set( 'meta_key', '_wp_page_template' );
		$query->set( 'meta_value', sanitize_text_field( $_REQUEST['page-template'] ) );
		return $query;
	}

	/**
	 * Adds template filter to page.
	 *
	 * Adds template filter to page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_type   Post type to add filter for.
	 * @param string $which       Position of filter.
	 * @return void
	 */
	public function maybe_output_template_filter( $post_type, $which ) {
		if ( 'page' !== $post_type ) return;
		$option_selected = 'none';
		if ( isset( $_REQUEST['page-template'] ) ) {
			$option_selected = $_REQUEST['page-template'];
		}
		echo '<select id="page-template" name="page-template">';
		printf( '<option value="none" %s>%s</option>', selected( 'none', $option_selected, false ), esc_html__( 'Select a Template', 'wpe-page-template-viewer' ) );
		printf( '<option value="default" %s>%s</option>', selected( 'default', $option_selected, false ), esc_html__( 'Default', 'wpe-page-template-viewer' ) );
		foreach( $this->page_templates as $slug => $name ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $slug ), selected( $slug, $option_selected, false ), esc_html( $name ) );
		}
		echo '</select>';	
	}

	/**
	 * Adds a custom column to the page post type.
	 *
	 * Adds a new page template column to the page post type.
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns Associative array of page columns
	 * @return array $columns Associative array of page columns
	 */
	public function manage_page_columns( $columns ) {
		$columns['page_template'] = 'Template';
		return $columns;
	}

	/**
	 * Output template column value.
	 *
	 * Output template column value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $column_name Name of the column
	 * @param int    $page_id     ID of the page
	 * @return void
	 */
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