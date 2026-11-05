/**
 * Smartart Weekly Timetable – accessible day tabs for the mobile view.
 * Without JavaScript every day is simply listed one under the other.
 */
( function () {
	'use strict';

	function activate( tabs, panels, index, focus ) {
		tabs.forEach( function ( tab, i ) {
			var on = i === index;
			tab.setAttribute( 'aria-selected', on ? 'true' : 'false' );
			tab.setAttribute( 'tabindex', on ? '0' : '-1' );
			panels[ i ].hidden = ! on;
		} );
		if ( focus ) {
			tabs[ index ].focus();
		}
	}

	function init( root ) {
		var wrap = root.querySelector( '.sawt-tabs' );
		var tabs = Array.prototype.slice.call( root.querySelectorAll( '.sawt-tab' ) );
		var panels = Array.prototype.slice.call( root.querySelectorAll( '.sawt-panel' ) );

		if ( ! wrap || tabs.length === 0 || tabs.length !== panels.length ) {
			return;
		}

		wrap.hidden = false;
		wrap.setAttribute( 'role', 'tablist' );
		root.classList.add( 'sawt-js' );

		tabs.forEach( function ( tab, i ) {
			tab.setAttribute( 'role', 'tab' );
			tab.setAttribute( 'aria-controls', panels[ i ].id );
			panels[ i ].setAttribute( 'role', 'tabpanel' );
			panels[ i ].setAttribute( 'aria-labelledby', tab.id );

			tab.addEventListener( 'click', function () {
				activate( tabs, panels, i, false );
			} );

			tab.addEventListener( 'keydown', function ( event ) {
				var count = tabs.length;
				var next = null;

				if ( event.key === 'ArrowRight' ) {
					next = ( i + 1 ) % count;
				} else if ( event.key === 'ArrowLeft' ) {
					next = ( i - 1 + count ) % count;
				} else if ( event.key === 'Home' ) {
					next = 0;
				} else if ( event.key === 'End' ) {
					next = count - 1;
				}

				if ( next !== null ) {
					event.preventDefault();
					activate( tabs, panels, next, true );
				}
			} );
		} );

		// Start on today's tab (Monday = first tab; Sunday falls back to the first tab).
		var day = new Date().getDay();
		var start = day === 0 ? 0 : Math.min( day - 1, tabs.length - 1 );
		activate( tabs, panels, start, false );
	}

	document.querySelectorAll( '[data-sawt]' ).forEach( init );
}() );
