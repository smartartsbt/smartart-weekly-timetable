<?php
/**
 * Fetching, validating, parsing and caching the Google Sheets CSV.
 *
 * @package Smartart_Weekly_Timetable
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data source. Nothing here touches the database directly: caching uses the Transients API only.
 */
final class Smartart_WT_Source {

	private const CACHE_PREFIX = 'smartart_wt_';
	private const KEYS_OPTION  = 'smartart_wt_cache_keys';
	private const MAX_BYTES    = 262144; // 256 KB.
	private const MAX_SLOTS    = 96;
	private const MAX_ROWS     = 200;
	private const MAX_CELL_LEN = 80;

	/**
	 * Number of day columns in the sheet (Monday to Saturday).
	 */
	public const DAYS = 6;

	/**
	 * Validate and normalise the sheet URL. Only https://docs.google.com/spreadsheets/... CSV links pass.
	 *
	 * @param string $url Raw URL from the shortcode.
	 * @return string|null Safe URL or null when invalid.
	 */
	public static function validate_url( string $url ): ?string {
		// The editor may have turned "&" into an HTML entity.
		$url = trim( html_entity_decode( $url, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );

		if ( '' === $url || strlen( $url ) > 500 ) {
			return null;
		}

		$parts = wp_parse_url( $url );
		if ( ! is_array( $parts ) ) {
			return null;
		}

		if ( ( $parts['scheme'] ?? '' ) !== 'https' ) {
			return null;
		}
		if ( strtolower( (string) ( $parts['host'] ?? '' ) ) !== 'docs.google.com' ) {
			return null;
		}
		if ( isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['port'] ) || isset( $parts['fragment'] ) ) {
			return null;
		}

		$path = (string) ( $parts['path'] ?? '' );
		if ( 1 !== preg_match( '#^/spreadsheets/d/(e/[A-Za-z0-9_-]+/pub|[A-Za-z0-9_-]+/export)$#', $path ) ) {
			return null;
		}

		$query = array();
		parse_str( (string) ( $parts['query'] ?? '' ), $query );
		$is_csv = ( 'csv' === ( $query['output'] ?? null ) ) || ( 'csv' === ( $query['format'] ?? null ) );
		if ( ! $is_csv ) {
			return null;
		}

		$safe = esc_url_raw( $url, array( 'https' ) );
		return '' === $safe ? null : $safe;
	}

	/**
	 * Get the parsed timetable (from cache when possible).
	 *
	 * @param string $url     Validated URL.
	 * @param int    $minutes Cache lifetime in minutes.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function get( string $url, int $minutes ) {
		$key   = self::CACHE_PREFIX . md5( $url );
		$fresh = get_transient( $key );
		if ( is_array( $fresh ) ) {
			return $fresh;
		}

		$result = self::fetch( $url );
		if ( is_wp_error( $result ) ) {
			// Serve the last good copy if the source is temporarily unavailable.
			$stale = get_transient( $key . '_stale' );
			return is_array( $stale ) ? $stale : $result;
		}

		set_transient( $key, $result, $minutes * MINUTE_IN_SECONDS );
		set_transient( $key . '_stale', $result, WEEK_IN_SECONDS );
		self::remember_key( $key );

		return $result;
	}

	/**
	 * Remove every cached copy (used on uninstall).
	 */
	public static function purge(): void {
		$keys = get_option( self::KEYS_OPTION, array() );
		if ( is_array( $keys ) ) {
			foreach ( $keys as $key ) {
				if ( is_string( $key ) ) {
					delete_transient( $key );
					delete_transient( $key . '_stale' );
				}
			}
		}
		delete_option( self::KEYS_OPTION );
	}

	/**
	 * Download the CSV with the "safe" HTTP API (blocks private/local addresses).
	 *
	 * @param string $url Validated URL.
	 * @return array<string,mixed>|WP_Error
	 */
	private static function fetch( string $url ) {
		$response = wp_safe_remote_get(
			$url,
			array(
				'timeout'             => 10,
				'redirection'         => 3,
				'limit_response_size' => self::MAX_BYTES,
				'headers'             => array( 'Accept' => 'text/csv' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'smartart_wt_http', __( 'The timetable source could not be reached.', 'smartart-weekly-timetable' ) );
		}

		if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error( 'smartart_wt_status', __( 'The timetable source returned an error. Check that the sheet is published to the web as CSV.', 'smartart-weekly-timetable' ) );
		}

		$body = (string) wp_remote_retrieve_body( $response );

		if ( strlen( $body ) >= self::MAX_BYTES ) {
			return new WP_Error( 'smartart_wt_size', __( 'The timetable source is too large.', 'smartart-weekly-timetable' ) );
		}

		// Google answers with an HTML page when the link is not a real CSV publication.
		if ( '' === trim( $body ) || '<' === substr( ltrim( $body ), 0, 1 ) ) {
			return new WP_Error( 'smartart_wt_html', __( 'The published sheet did not return CSV data. Publish the sheet again with the CSV format.', 'smartart-weekly-timetable' ) );
		}

		return self::parse( $body );
	}

	/**
	 * Turn the CSV into a sanitised array. Layout (0-based rows, 0-based columns):
	 * row 1 col 0 = week label, row 3 cols 1-6 = people, row 5 = day names, row 6 = dates,
	 * rows 7+ = slots (col 0 = "8:00 – 8:30", cols 1-6 = person name).
	 *
	 * @param string $csv Raw CSV.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function parse( string $csv ) {
		if ( str_starts_with( $csv, "\xEF\xBB\xBF" ) ) {
			$csv = substr( $csv, 3 );
		}

		$lines = preg_split( '/\R/', $csv );
		if ( ! is_array( $lines ) ) {
			return self::format_error();
		}

		$rows = array();
		foreach ( $lines as $line ) {
			if ( count( $rows ) >= self::MAX_ROWS ) {
				break;
			}
			$rows[] = str_getcsv( $line, ',', '"', '' );
		}

		$legend = self::row_cells( $rows, 3 );
		$days   = self::row_cells( $rows, 5 );
		$dates  = self::row_cells( $rows, 6 );

		if ( '' === implode( '', $days ) ) {
			return self::format_error();
		}

		$slots     = array();
		$row_total = count( $rows );
		$slot_cnt  = 0;
		for ( $r = 7; $r < $row_total && $slot_cnt < self::MAX_SLOTS; $r++ ) {
			$label = self::cell( $rows, $r, 0 );
			if ( '' === $label ) {
				break;
			}
			$parts = preg_split( '/\s*[\x{2013}\x{2014}-]\s*/u', $label, 2 );
			if ( ! is_array( $parts ) ) {
				continue;
			}
			++$slot_cnt;
			$slots[] = array(
				'start' => trim( $parts[0] ),
				'end'   => trim( $parts[1] ?? '' ),
				'cells' => self::row_cells( $rows, $r ),
			);
		}

		if ( array() === $slots ) {
			return self::format_error();
		}

		return array(
			'week'   => self::cell( $rows, 1, 0 ),
			'legend' => $legend,
			'days'   => $days,
			'dates'  => $dates,
			'slots'  => $slots,
		);
	}

	/**
	 * Standard "bad layout" error.
	 *
	 * @return WP_Error
	 */
	private static function format_error(): WP_Error {
		return new WP_Error( 'smartart_wt_format', __( 'The sheet does not match the expected timetable layout.', 'smartart-weekly-timetable' ) );
	}

	/**
	 * Cells 1..6 of a row, sanitised.
	 *
	 * @param array<int,array<int,string|null>> $rows Parsed rows.
	 * @param int                               $r    Row index.
	 * @return string[]
	 */
	private static function row_cells( array $rows, int $r ): array {
		$out = array();
		for ( $c = 1; $c <= self::DAYS; $c++ ) {
			$out[] = self::cell( $rows, $r, $c );
		}
		return $out;
	}

	/**
	 * One sanitised cell.
	 *
	 * @param array<int,array<int,string|null>> $rows Parsed rows.
	 * @param int                               $r    Row index.
	 * @param int                               $c    Column index.
	 * @return string
	 */
	private static function cell( array $rows, int $r, int $c ): string {
		$value = $rows[ $r ][ $c ] ?? '';
		if ( ! is_string( $value ) ) {
			return '';
		}
		return mb_substr( sanitize_text_field( $value ), 0, self::MAX_CELL_LEN );
	}

	/**
	 * Remember cache keys so they can be removed on uninstall (keeps the list short).
	 *
	 * @param string $key Transient key.
	 */
	private static function remember_key( string $key ): void {
		$keys = get_option( self::KEYS_OPTION, array() );
		if ( ! is_array( $keys ) ) {
			$keys = array();
		}
		if ( in_array( $key, $keys, true ) ) {
			return;
		}
		$keys[] = $key;
		if ( count( $keys ) > 20 ) {
			foreach ( array_slice( $keys, 0, -20 ) as $old ) {
				if ( is_string( $old ) ) {
					delete_transient( $old );
					delete_transient( $old . '_stale' );
				}
			}
			$keys = array_slice( $keys, -20 );
		}
		update_option( self::KEYS_OPTION, $keys, false );
	}
}
