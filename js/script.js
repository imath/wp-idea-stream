/*!
 * WP Idea Stream script
 */

;
(function($) {

	// Only use raty if loaded
	if ( typeof wp_idea_stream_vars.raty_loaded != 'undefined' ) {

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
			click      : function( score, evt ) {
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

		var data = {
			action: 'wp_idea_stream_rate',
			rate: score,
			wpnonce: wp_idea_stream_vars.wpnonce,
			idea:$('#rate').data('idea')
		};

		$.post( wp_idea_stream_vars.ajaxurl, data, function( response ) {
			if( response && response > 0  ){
				$( '.rating-info' ).html( wp_idea_stream_vars.success_msg + ' ' + response ).fadeOut( 2000, function() {
					wpis_update_rate_num( 1 );
					$(this).show();
				} );
			} else {
				$( '.rating-info' ).html( wp_idea_stream_vars.error_msg );
				$.fn.raty( 'readOnly', false, '#rate' );
			}
		});
	}

	function wpis_update_rate_num( rate ) {
		var number = Number( wp_idea_stream_vars.rate_nb ) + rate,
			msg;

		if ( 1 == number ) {
			msg = wp_idea_stream_vars.one_rate;
		} else if( '0' == number ) {
			msg = wp_idea_stream_vars.not_rated;
		} else {
			msg = wp_idea_stream_vars.x_rate.replace( '%', number );
		}

		$( '.rating-info' ).html( '<a>' + msg + '</a>' );
	}

	if ( typeof wp_idea_stream_vars.tagging_loaded != 'undefined' ) {
		$( '#_wp_idea_stream_the_tags' ).tagging( {
			'tags-input-name'      : 'wp_idea_stream[_the_tags]',
			'edit-on-delete'       : false,
			'tag-char'             : '',
			'no-duplicate-text'    : wp_idea_stream_vars.duplicate_tag,
			'forbidden-chars-text' : wp_idea_stream_vars.forbidden_chars,
			'forbidden-words-text' : wp_idea_stream_vars.forbidden_words
		} );

		// Make sure the title gets the focus
		$( '#_wp_idea_stream_the_title' ).focus();

		// Add most used tags
		$( '#wp_idea_stream_most_used_tags .tag-items a' ).on( 'click', function( event ) {
			event.preventDefault();

			$( '#_wp_idea_stream_the_tags' ).tagging( "add", $( this ).html() );
		} );

		// Reset tags
		$( '#wp-idea-stream-form' ).on( 'reset', function( event ) {
			$( '#_wp_idea_stream_the_tags' ).tagging( 'reset' );
		} );
	}

	// Set the interval and the namespace event
	if ( typeof wp != 'undefined' && typeof wp.heartbeat != 'undefined' && typeof wp_idea_stream_vars.pulse != 'undefined' ) {
		wp.heartbeat.interval( wp_idea_stream_vars.pulse );

		$.fn.extend( {
			'heartbeat-send': function() {
				return this.bind( 'heartbeat-send.ideastream' );
	        }
	    } );
	}

	// Send the current idea ID being edited
	$( document ).on( 'heartbeat-send.ideastream', function( e, data ) {
		data['ideastream_heartbeat_current_idea'] = wp_idea_stream_vars.idea_id;
    } );

	// Inform the user if data has been returned
	$( document ).on( 'heartbeat-tick', function( e, data ) {

		// Only proceed if an admin took the lead
		if ( ! data['ideastream_heartbeat_response'] )
        	return;

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

	if ( typeof wp_idea_stream_vars.profile_editing != 'undefined' ) {
		$( '#wp_idea_stream_profile_description' ).show();
		$( 'textarea[name="wp_idea_stream_profile[description]"]').hide();

		$( '#wp_idea_stream_profile_form' ).on( 'submit', function(event) {
			$( 'textarea[name="wp_idea_stream_profile[description]"]').val( $( '#wp_idea_stream_profile_description' ).html() );
		} );
	}

	// Admin
	if ( typeof wp_idea_stream_vars.is_admin != 'undefined' ) {
		function group_selected( e, ui ) {
			$( '#group-selected' ).html( '<input type="checkbox" name="_ideastream_group_id" id="_ideastream_group_id" value="' + ui.item.value + '" checked><strong class="label"><a href="' + ui.item.link +'">' + ui.item.label + '</a></strong></input>' );
		}

		$( '#wp_idea_stream_buddypress_group' ).autocomplete( {
			source:    ajaxurl + '?action=ideastream_search_groups&user_id=' + wp_idea_stream_vars.author,
			delay:     500,
			minLength: 2,
			position:  ( 'undefined' !== typeof isRtl && isRtl ) ? { my: 'right top', at: 'right bottom', offset: '0, -1' } : { offset: '0, -1' },
			open:      function() { $( this ).addClass( 'open' ); },
			close:     function() { $( this ).removeClass( 'open' ); $( this ).val( '' ); },
			select:    function( event, ui ) { group_selected( event, ui ); }
		} );
	}

})(jQuery);
