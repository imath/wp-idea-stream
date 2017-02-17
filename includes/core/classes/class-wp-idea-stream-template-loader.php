<?php
/**
 * WP Idea Stream Template Loader Class.
 *
 * @package WP Idea Stream\core\classes
 *
 * @since 2.4.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Main Template loader class.
 *
 * Credits http://github.com/GaryJones/Gamajo-Template-Loader.
 *
 * @since 2.0.0
 */
class WP_Idea_Stream_Template_Loader {

	/**
	 * Prefix for filter names.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $filter_prefix = 'wp_idea_stream';

	/**
	 * Directory name where custom templates for this plugin should be found in the theme.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $theme_template_directory = 'wp-idea-stream';

	/**
	 * Retrieve a template part.
	 *
	 * @since 2.0.0
	 *
	 * @param string  $slug
	 * @param string  $name Optional. Default null.
	 * @param bool    $load Optional. Default true.
	 * @return string
	 */
	public function get_template_part( $slug, $name = null, $load = true, $require_once = true ) {
		// Execute code for this part
		do_action( 'get_template_part_' . $slug, $slug, $name );

		// Get files names of templates, for given slug and name.
		$templates = $this->get_template_file_names( $slug, $name );

		// Return the part that is found
		return $this->locate_template( $templates, $load, $require_once );
	}

	/**
	 * Given a slug and optional name, create the file names of templates.
	 *
	 * @since 2.0.0
	 *
	 * @param string  $slug
	 * @param string  $name
	 * @return array
	 */
	protected function get_template_file_names( $slug, $name, $ext = 'php' ) {
		$templates = array();
		if ( isset( $name ) ) {
			$templates[] = $slug . '-' . $name . '.' . $ext;
		}
		$templates[] = $slug . '.' . $ext;

		/**
		 * Allow template choices to be filtered.
		 */
		return apply_filters( $this->filter_prefix . '_get_template_part', $templates, $slug, $name );
	}

	/**
	 * Retrieve the name of the highest priority template file that exists.
	 *
	 * @since 2.0.0
	 *
	 * @param string|array $template_names Template file(s) to search for, in order.
	 * @param bool         $load           If true the template file will be loaded if it is found.
	 * @param bool         $require_once   Whether to require_once or require. Default true.
	 *   Has no effect if $load is false.
	 * @return string The template filename if one is located.
	 */
	public function locate_template( $template_names, $load = false, $require_once = true ) {
		// No file found yet
		$located = false;

		// Remove empty entries
		$template_names = array_filter( (array) $template_names );
		$template_paths = $this->get_template_paths();

		// Try to find a template file
		foreach ( $template_names as $template_name ) {
			// Trim off any slashes from the template name
			$template_name = ltrim( $template_name, '/' );

			// Try locating this template file by looping through the template paths
			foreach ( $template_paths as $template_path ) {
				if ( file_exists( $template_path . $template_name ) ) {
					$located = $template_path . $template_name;
					break 2;
				}
			}
		}

		if ( $load && $located ) {
			load_template( $located, $require_once );
		}

		return $located;
	}

	/**
	 * Return a list of paths to check for template locations.
	 *
	 * @since 2.0.0
	 *
	 * @return mixed|void
	 */
	protected function get_template_paths() {
		$theme_directory = trailingslashit( $this->theme_template_directory );

		$file_paths = array(
			10  => trailingslashit( get_template_directory() ) . $theme_directory,
			100 => $this->get_templates_dir(),
		);

		// Only add this conditionally, so non-child themes don't redundantly check active theme twice.
		if ( is_child_theme() ) {
			$file_paths[1] = trailingslashit( get_stylesheet_directory() ) . $theme_directory;
		}

		/**
		 * Allow ordered list of template paths to be amended.
		 */
		$file_paths = apply_filters( $this->filter_prefix . '_template_paths', $file_paths );

		// sort the file paths based on priority
		ksort( $file_paths, SORT_NUMERIC );

		return array_map( 'trailingslashit', $file_paths );
	}

	/**
	 * Return the path to the templates directory in this plugin.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_templates_dir() {
		return untrailingslashit( wp_idea_stream_get_templates_dir() );
	}

	/**
	 * Return the url to the plugin's stylesheet.
	 *
	 * That's my little "extend" of the Original GamaJo Class
	 * The goal is to also benefit of the template location feature
	 * for the css file. This way, a theme can override the plugin's
	 * stylesheet from the wp-idea-stream theme's folder as soon as
	 * the custom css file is named style.css
	 *
	 * @since 2.0.0
	 * @since 2.3.0 Added the $css parameter to eventually get any stylesheet and
	 *              not only the style.css one.
	 * @return string
	 */
	public function get_stylesheet( $css = 'style' ) {
		$styles = $this->get_template_file_names( $css, null, 'css' );

		$located = $this->locate_template( $styles );

		// Microsoft is annoying...
		$slashed_located     = str_replace( '\\', '/', $located );
		$slashed_content_dir = str_replace( '\\', '/', WP_CONTENT_DIR );
		$slashed_plugin_dir  = str_replace( '\\', '/', wp_idea_stream_get_plugin_dir() );

		// Should allways be the case for regular configs
		if ( false !== strpos( $slashed_located, $slashed_content_dir ) ) {
			$located = str_replace( $slashed_content_dir, content_url(), $slashed_located );

		// If not, WP Idea Stream might be symlinked, so let's try this
		} else {
			$located = str_replace( $slashed_plugin_dir, wp_idea_stream_get_plugin_url(), $slashed_located );
		}

		return $located;
	}
}
