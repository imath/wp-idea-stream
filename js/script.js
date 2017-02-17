/* global wp_idea_stream_vars */
/*!
 * WP Idea Stream script
 */

(function( $ ) {

	/**
	 * Ajax Class.
	 * @type {Object}
	 */
	window.isAjax = {

		request: function( endpoint, data, method ) {
			data = data || {};

			if ( ! endpoint || ! method ) {
				return false;
			}

			this.ajaxRequest = $.ajax( {
				url: wp_idea_stream_vars.root_url + endpoint,
				method: method,
				beforeSend: function( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', wp_idea_stream_vars.nonce );
				},
				data: data
			} );

			return this.ajaxRequest;
		},

		get: function( endpoint, data ) {
			return this.request( endpoint, data, 'GET' );
		},

		post: function( endpoint, data ) {
			return this.request( endpoint, data, 'POST' );
		}
	};

	// Cleanup the url
	if ( wp_idea_stream_vars.canonical && window.history.replaceState ) {
		window.history.replaceState( null, null, wp_idea_stream_vars.canonical + window.location.hash );
	}

	// Only use raty if loaded
	if ( typeof wp_idea_stream_vars.raty_loaded !== 'undefined' ) {

		wpis_update_rate_num( 0 );

		$( 'div#rate' ).raty( {
			cancel     : false,
			half       : false,
			halfShow   : true,
			starType   : 'i',
			readOnly   : wp_idea_stream_vars.readonly,
			score      : wp_idea_stream_vars.average_rate,
			targetKeep : false,
			noRatedMsg : wp_idea_stream_vars.not_rated,
			hints      : wp_idea_stream_vars.hints,
			number     : wp_idea_stream_vars.hints_nb,
			click      : function( score ) {
				if ( ! wp_idea_stream_vars.can_rate ) {
					return;
				}
				// Disable the rating stars
				$.fn.raty( 'readOnly', true, '#rate' );
				// Update the score
				wpis_post_rating( score );
			}
		} );
	}

	function wpis_post_rating( score ) {
		$( '.rating-info' ).html( wp_idea_stream_vars.wait_msg );

		var endpoint = 'ideas/' + $( '#rate' ).data( 'idea' ) + '/rate';

		window.isAjax.post( endpoint, { rating: score } ).done( function( response ) {
			if ( response.idea_average_rate ) {
				$( '.rating-info' ).html( wp_idea_stream_vars.success_msg + ' ' + response.idea_average_rate ).fadeOut( 2000, function() {
					wpis_update_rate_num( 1 );
					$( this ).show();
				} );
			} else {
				$( '.rating-info' ).html( wp_idea_stream_vars.error_msg );
				$.fn.raty( 'readOnly', false, '#rate' );
			}
		} );
	}

	function wpis_update_rate_num( rate ) {
		var number = Number( wp_idea_stream_vars.rate_nb ) + rate,
			msg;

		if ( 1 === number ) {
			msg = wp_idea_stream_vars.one_rate;
		} else if( '0' === number ) {
			msg = wp_idea_stream_vars.not_rated;
		} else {
			msg = wp_idea_stream_vars.x_rate.replace( '%', number );
		}

		$( '.rating-info' ).html( '<a>' + msg + '</a>' );
	}

	if ( typeof wp_idea_stream_vars.tagging_loaded !== 'undefined' ) {
		$( '#_wp_idea_stream_the_tags' ).tagging( {
			'tags-input-name'      : 'wp_idea_stream[_the_tags]',
			'edit-on-delete'       : false,
			'tag-char'             : '',
			'forbidden-chars'      : [ '.', '_', '?', '<', '>' ],
			'forbidden-words'      : ['script'],
			'no-duplicate-text'    : wp_idea_stream_vars.duplicate_tag,
			'forbidden-chars-text' : wp_idea_stream_vars.forbidden_chars,
			'forbidden-words-text' : wp_idea_stream_vars.forbidden_words
		} );

		// Make sure the title gets the focus
		$( '#_wp_idea_stream_the_title' ).focus();

		// Add most used tags
		$( '#wp_idea_stream_most_used_tags .tag-items a' ).on( 'click', function( event ) {
			event.preventDefault();

			$( '#_wp_idea_stream_the_tags' ).tagging( 'add', $( this ).html() );
		} );

		// Reset tags
		$( '#wp-idea-stream-form' ).on( 'reset', function() {
			$( '#_wp_idea_stream_the_tags' ).tagging( 'reset' );
		} );
	}

	// Set the interval and the namespace event
	if ( typeof wp !== 'undefined' && typeof wp.heartbeat !== 'undefined' && typeof wp_idea_stream_vars.pulse !== 'undefined' ) {
		wp.heartbeat.interval( wp_idea_stream_vars.pulse );

		$.fn.extend( {
			'heartbeat-send': function() {
				return this.bind( 'heartbeat-send.ideastream' );
			}
		} );
	}

	// Send the current idea ID being edited
	$( document ).on( 'heartbeat-send.ideastream', function( e, data ) {
		data.ideastream_heartbeat_current_idea = wp_idea_stream_vars.idea_id;
	} );

	// Inform the user if data has been returned
	$( document ).on( 'heartbeat-tick', function( e, data ) {

		// Only proceed if an admin took the lead
		if ( ! data.ideastream_heartbeat_response ) {
			return;
		}

		if ( ! $( '#wp-idea-stream .message' ).length ) {
			$( '#wp-idea-stream' ).prepend(
				'<div class="message info">' +
					'<p>' + wp_idea_stream_vars.warning + '</p>' +
				'</div>'
			);
		} else {
			$( '#wp-idea-stream .message' ).removeClass( 'error' ).addClass( 'info' );
			$( '#wp-idea-stream .message p' ).html( wp_idea_stream_vars.warning );
		}

		$( '#wp-idea-stream .submit input[name="wp_idea_stream[save]"]' ).remove();

	} );

	if ( typeof wp_idea_stream_vars.is_profile !== 'undefined' ) {

		// Specific to IdeaStream User profile
		if ( typeof wp_idea_stream_vars.profile_editing !== 'undefined' ) {
			$( '#wp_idea_stream_profile_description' ).show();
			$( 'textarea[name="wp_idea_stream_profile[description]"]').hide();

			$( '#wp_idea_stream_profile_form' ).on( 'submit', function() {
				$( 'textarea[name="wp_idea_stream_profile[description]"]').val( $( '#wp_idea_stream_profile_description' ).html() );
			} );

			$( '#wp_idea_stream_profile_form input[type="submit"]' ).before( $( '.wp-embed-share' ).html() );
			$( '.wp-embed-share' ).remove();
		}

		// Embed dialog box
		$( '.wp-embed-share-input' ).on( 'click', function ( e ) {
			e.target.select();
		} );

		$( '.wp-embed-share-dialog-open' ).on( 'click', function () {
			$( '.wp-embed-share-dialog' ).removeClass( 'hidden' );
			$( '.wp-embed-share-tab-button [aria-selected="true"]' ).focus();
		} );

		$( '.wp-embed-share-dialog-close' ).on( 'click', function () {
			$( '.wp-embed-share-dialog' ).addClass( 'hidden' );
			$( '.wp-embed-share-dialog-open' ).focus();
		} );

		$( '.wp-embed-share-tab-button button' ).on( 'click', function( e ) {
			var control = $( e.target ).attr( 'aria-controls' );

			$( '.wp-embed-share-tab' ).each( function( t, tab ) {
				if ( control === $( tab ).prop( 'id' ) ) {
					$( tab ).attr( 'aria-hidden', 'false' );
				} else {
					$( tab ).attr( 'aria-hidden', 'true' );
				}
			} );
		} );
	}

} )( jQuery );
