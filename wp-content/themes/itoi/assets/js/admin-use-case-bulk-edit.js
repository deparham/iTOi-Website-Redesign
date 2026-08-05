/**
 * "Edit All Use Cases" admin screen (inc/use-case-bulk-edit.php) — wires
 * the WP media library picker to each row's hidden photo/video ID input,
 * via event delegation on the whole grid rather than binding a listener
 * per row (up to 42 rows x 2 buttons, no need for 84 separate listeners).
 */
( function () {
	'use strict';

	document.addEventListener( 'click', function ( e ) {
		var selectBtn = e.target.closest( '.itoi-media-select' );
		if ( selectBtn ) {
			e.preventDefault();
			openPicker( selectBtn );
			return;
		}

		var removeBtn = e.target.closest( '.itoi-media-remove' );
		if ( removeBtn ) {
			e.preventDefault();
			clearMedia( removeBtn.dataset.target );
		}
	} );

	function openPicker( button ) {
		var type = button.dataset.mediaType;
		var target = button.dataset.target;

		var frame = wp.media( {
			title: 'video' === type ? 'Select video' : 'Select photo',
			library: { type: type },
			multiple: false,
		} );

		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			setMedia( target, type, attachment );
		} );

		frame.open();
	}

	function setMedia( target, type, attachment ) {
		var input = document.getElementById( 'input-' + target );
		var preview = document.getElementById( 'preview-' + target );
		if ( ! input || ! preview ) {
			return;
		}

		input.value = attachment.id;

		if ( 'video' === type ) {
			preview.textContent = attachment.filename || attachment.title || '';
		} else {
			var thumbUrl = ( attachment.sizes && attachment.sizes.thumbnail ) ? attachment.sizes.thumbnail.url : attachment.url;
			preview.innerHTML = '';
			var img = document.createElement( 'img' );
			img.src = thumbUrl;
			img.alt = '';
			preview.appendChild( img );
		}
	}

	function clearMedia( target ) {
		var input = document.getElementById( 'input-' + target );
		var preview = document.getElementById( 'preview-' + target );
		if ( input ) {
			input.value = '0';
		}
		if ( preview ) {
			preview.innerHTML = '';
		}
	}
} )();
