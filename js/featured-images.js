/* global tinymce */
/*!
 * WP Idea Stream Featured images script
 * It lists images inserted into the WP Editor.
 */

( function( $ ) {
	if ( ! $( '#idea-images-list' ).length || 'undefined' === typeof tinymce ) {
		return;
	}

	// Now it's a radiocheck!
	$( '#idea-images-list' ).on( 'click', ':checkbox', function( event ) {
		$( '#idea-images-list :checked' ).each( function() {
			if ( $( this ).val() !== $( event.target ).val() ) {
				$( this ).prop( 'checked', false );
			}
		} );
	} );

	$( document ).on( 'tinymce-editor-init', function( event, editor ) {
		editor.on( 'setcontent', function( event ) {
			if ( ! event.content ) {
				return;
			}

			var c = $.parseHTML( event.content ), o, img, is_in = 0;

			if ( 'IMG' === $( c ).prop( 'nodeName' ) ) {
				img = $( c ).prop( 'src' );
			}

			if ( ! img ) {
				return;
			}

			o = $( '<li></li>' ).html(
				'<img src="' + img + '"/><div class="cb-container"><input type="checkbox" name="wp_idea_stream[_the_thumbnail][' + img + ']" value="' + img + '"/></div>'
			);

			// Display the container
			if ( $( '#idea-images-list' ).hasClass( 'hidden' ) ) {
				$( '#idea-images-list' ).removeClass( 'hidden' );
			} else {
				$( '#idea-images-list :checkbox' ).each( function( i, cb ) {
					/**
					 * The value of src can be different although image are the same.
					 * Once an image is saved as an attachment, we need to use the original
					 * image url as the index of the name attribute and compare it with
					 * the inserted image
					 */
					var match_src = $( cb ).prop( 'name' ).match( /wp_idea_stream\[_the_thumbnail\]\[(.*)\]/ );

					if ( match_src && img === match_src[1] ) {
						is_in += 1;
					}
				} );
			}

			// Insert the image in it...
			if ( ! $( '#idea-images-list ul' ).length ) {
				$( '#idea-images-list label' ).after( $( '<ul></ul>' ).html( o ) );

			// ... only if it's not already in!
			} else if ( 0 === is_in ) {
				$( '#idea-images-list ul' ).append( o );
			}

			is_in = 0;
		} );
	} );
} )( jQuery );
