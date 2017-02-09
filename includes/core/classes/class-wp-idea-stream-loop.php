<?php
/**
 * WP Idea Stream Loop Class.
 *
 * @package WP Idea Stream\core\classes
 *
 * @since 2.4.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * A loop class to extend for any object.
 *
 * As we use custom loops for ideas and comments,
 * it's a bit annoying to copy paste all loop code
 * for each object.
 *
 * @see  WP_Idea_Stream_Loop_Ideas for an example of use.
 *
 * @since 2.0.0
 */
class WP_Idea_Stream_Loop {

	/**
	 * Array of vars to customize loop vars
	 *
	 * @access  public
	 * @var     array
	 */
	public $loop_vars;

	/**
	 * A flag for whether the loop is currently being iterated.
	 *
	 * @access public
	 * @var bool
	 */
	public $in_the_loop;

	/**
	 * The page number being requested.
	 *
	 * @access public
	 * @var int
	 */
	public $page;

	/**
	 * The number of items to display per page of results.
	 *
	 * @access public
	 * @var int
	 */
	public $per_page;

	/**
	 * An HTML string containing pagination links.
	 *
	 * @access public
	 * @var string
	 */
	public $pag_links;

	/**
	 * the plugin prefix eg: wp_idea_stream.
	 *
	 * @access public
	 * @var string
	 */
	public $plugin_prefix;

	/**
	 * the item name to loop through eg: ideas
	 *
	 * @access public
	 * @var string
	 */
	public $item_name;

	/**
	 * Start method to build the loop
	 *
	 * @since 2.0.0
	 *
	 * @param  array $params must be an associative array where :
	 *         'plugin_prefix'    is the plugin prefix (only _ no - )
	 *         'item_name'        is the name of the idea (only _ no - )
	 *         'item_name_plural' is the plural for the item eg: ideas (only _ no - )
	 *         'items'            is an array of objects to loop through
	 *         'total_item_count' is max total item count
	 *         'page'             is the current page
	 *         'per_page'         is the items to show on a page.
	 * @param  array $paginate_args custom arguments to pass to paginate_links()
	 */
	public function start( $params = array(), $paginate_args = array() ) {
		// Make sure we have item name and item name plural
		$this->loop_vars = array(
			'item_name'        => 'item',
			'item_name_plural' => 'items',
		);

		$custom_vars = array_intersect_key( (array) $params, $this->loop_vars );

		if ( ! empty( $custom_vars ) && 2 == count( $custom_vars ) ) {
			$this->loop_vars = $custom_vars;
		}

		$this->loop_vars = array_merge( $this->loop_vars, array(
			'total_item_count' => 'total_' . $this->loop_vars['item_name'] . '_count',
			'item_count'       => $this->loop_vars['item_name'] .'_count',
			'current_item'     => 'current_' . $this->loop_vars['item_name'],
		) );

		$this->{$this->loop_vars['current_item']} = -1;

		// Parsing other params
		if ( ! empty( $params ) ) {
			foreach ( (array) $params as $key => $value ) {
				// This will be set in $this->{$this->loop_vars['item_name_plural']}
				if ( 'items' == $key ) {
					continue;
				}

				$this->{$key} = $value;
			}
		} else {
			return false;
		}

		// Setup the Items to loop through
		$this->{$this->loop_vars['item_name_plural']} = $params['items'];
		$this->{$this->loop_vars['total_item_count']} = $params['total_item_count'];

		if ( empty( $this->{$this->loop_vars['item_name_plural']} ) ) {
			$this->{$this->loop_vars['item_count']}       = 0;
			$this->{$this->loop_vars['total_item_count']} = 0;
		} else {
			$this->{$this->loop_vars['item_count']} = count( $this->{$this->loop_vars['item_name_plural']} );
		}

		if ( (int) $this->{$this->loop_vars['total_item_count']} && ! empty( $this->per_page ) ) {
			$default_paginate_args = array(
				'total'     => ceil( (int) $this->{$this->loop_vars['total_item_count']} / (int) $this->per_page ),
				'current'   => (int) $this->page,
			);

			$custom_paginate_args = wp_parse_args( $paginate_args, array(
				'base'      => '',
				'format'    => '',
				'prev_text' => _x( '&larr;', 'pagination previous text', 'wp-idea-stream' ),
				'next_text' => _x( '&rarr;', 'pagination next text',     'wp-idea-stream' ),
				'mid_size'  => 1,
			) );

			$this->pag_links = paginate_links( array_merge( $default_paginate_args, $custom_paginate_args ) );

			// Remove first page from pagination
			$this->pag_links = str_replace( '?paged=1', '', $this->pag_links );
			$this->pag_links = str_replace( '&#038;paged=1', '', $this->pag_links );
		}
	}

	/**
	 * Whether there are Items available in the loop.
	 *
	 * @since 2.0.0
	 *
	 * @return bool True if there are items in the loop, otherwise false.
	 */
	public function has_items() {
		if ( $this->{$this->loop_vars['item_count']} ) {
			return true;
		}

		return false;
	}

	/**
	 * Set up the next item and iterate index.
	 *
	 * @since 2.0.0
	 *
	 * @return object The next item to iterate over.
	 */
	public function next_item() {

		$this->{$this->loop_vars['current_item']}++;

		$this->{$this->loop_vars['item_name']} = $this->{$this->loop_vars['item_name_plural']}[ $this->{$this->loop_vars['current_item']} ];

		return $this->{$this->loop_vars['item_name']};
	}

	/**
	 * Rewind the items and reset items index.
	 *
	 * @since 2.0.0
	 */
	public function rewind_items() {

		$this->{$this->loop_vars['current_item']} = -1;

		if ( $this->{$this->loop_vars['item_count']} > 0 ) {
			$this->{$this->loop_vars['item_name']} = $this->{$this->loop_vars['item_name_plural']}[0];
		}
	}

	/**
	 * Whether there are items left in the loop to iterate over.
	 *
	 * @since 2.0.0
	 *
	 * @return bool True if there are more items to show,
	 *         otherwise false.
	 */
	public function items() {

		if ( $this->{$this->loop_vars['current_item']} + 1 < $this->{$this->loop_vars['item_count']} ) {
			return true;

		} elseif ( $this->{$this->loop_vars['current_item']} + 1 == $this->{$this->loop_vars['item_count']} ) {
			do_action( "{$this->plugin_prefix}_{$this->item_name}_loop_end" );

			$this->rewind_items();
		}

		$this->in_the_loop = false;
		return false;
	}

	/**
	 * Set up the current item inside the loop.
	 *
	 * @since 2.0.0
	 */
	public function the_item() {
		$this->in_the_loop  = true;
		$this->{$this->loop_vars['item_name']} = $this->next_item();

		// loop has just started
		if ( 0 === $this->{$this->loop_vars['current_item']} ) {
			do_action( "{$this->plugin_prefix}_{$this->item_name}_start" );
		}
	}
}