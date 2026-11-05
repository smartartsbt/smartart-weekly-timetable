<?php
/**
 * HTML output. Every dynamic value is escaped at the point of output.
 *
 * @package Smartart_Weekly_Timetable
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the desktop table and the mobile day list.
 */
final class Smartart_WT_Renderer {

	/**
	 * Render the timetable.
	 *
	 * @param array<string,mixed> $data     Parsed data from Smartart_WT_Source.
	 * @param int                 $instance Instance number on this page.
	 * @return string
	 */
	public static function render( array $data, int $instance ): string {
		$week   = (string) ( $data['week'] ?? '' );
		$legend = is_array( $data['legend'] ?? null ) ? array_values( $data['legend'] ) : array();
		$days   = is_array( $data['days'] ?? null ) ? array_values( $data['days'] ) : array();
		$dates  = is_array( $data['dates'] ?? null ) ? array_values( $data['dates'] ) : array();
		$slots  = is_array( $data['slots'] ?? null ) ? array_values( $data['slots'] ) : array();

		// Only columns that have a day name are shown (clear a header to hide a day).
		$cols = array();
		foreach ( $days as $d => $name ) {
			if ( is_string( $name ) && '' !== $name ) {
				$cols[] = (int) $d;
			}
		}

		$sessions = self::sessions( $slots, $legend, $cols );
		$id       = 'sawt-' . $instance;

		$html = '<div class="sawt" id="' . esc_attr( $id ) . '" data-sawt>';

		if ( '' !== $week ) {
			$html .= '<p class="sawt-week">' . esc_html( $week ) . '</p>';
		}

		$html .= self::legend( $legend );
		$html .= self::table( $slots, $days, $dates, $cols, $sessions );
		$html .= self::mobile( $days, $dates, $cols, $sessions, $id );
		$html .= '</div>';

		return $html;
	}

	/**
	 * Merge consecutive identical half-hour cells into sessions.
	 *
	 * @param array<int,array<string,mixed>> $slots  Slots.
	 * @param array<int,mixed>               $legend Person names (their position decides the color).
	 * @param int[]                          $cols   Visible day columns.
	 * @return array<int,array<int,array<string,mixed>>> Sessions per day column.
	 */
	private static function sessions( array $slots, array $legend, array $cols ): array {
		$out = array();
		$n   = count( $slots );

		foreach ( $cols as $d ) {
			$out[ $d ] = array();
			$i         = 0;
			while ( $i < $n ) {
				$name = (string) ( $slots[ $i ]['cells'][ $d ] ?? '' );
				if ( '' === $name ) {
					++$i;
					continue;
				}
				$j = $i;
				while ( $j + 1 < $n && (string) ( $slots[ $j + 1 ]['cells'][ $d ] ?? '' ) === $name ) {
					++$j;
				}
				$idx   = array_search( $name, $legend, true );
				$color = ( is_int( $idx ) && $idx < Smartart_WT_Source::DAYS ) ? $idx : Smartart_WT_Source::DAYS;

				$out[ $d ][] = array(
					'name'  => $name,
					'start' => (string) ( $slots[ $i ]['start'] ?? '' ),
					'end'   => (string) ( $slots[ $j ]['end'] ?? '' ),
					'row'   => $i,
					'span'  => $j - $i + 1,
					'color' => $color,
				);
				$i           = $j + 1;
			}
		}

		return $out;
	}

	/**
	 * Time range text.
	 *
	 * @param array<string,mixed> $session Session.
	 * @return string
	 */
	private static function range( array $session ): string {
		return $session['start'] . ' – ' . $session['end'];
	}

	/**
	 * Color legend.
	 *
	 * @param array<int,mixed> $legend Names.
	 * @return string
	 */
	private static function legend( array $legend ): string {
		$html = '';
		foreach ( $legend as $i => $name ) {
			if ( is_string( $name ) && '' !== $name ) {
				$html .= '<li class="sawt-chip sawt-c' . absint( $i ) . '">' . esc_html( $name ) . '</li>';
			}
		}
		return '' === $html ? '' : '<ul class="sawt-legend">' . $html . '</ul>';
	}

	/**
	 * Desktop table (rowspan merges the half-hour rows of one session).
	 *
	 * @param array<int,array<string,mixed>> $slots    Slots.
	 * @param array<int,mixed>               $days     Day names.
	 * @param array<int,mixed>               $dates    Dates.
	 * @param int[]                          $cols     Visible columns.
	 * @param array<int,array<int,array>>    $sessions Sessions.
	 * @return string
	 */
	private static function table( array $slots, array $days, array $dates, array $cols, array $sessions ): string {
		$starts  = array();
		$covered = array();
		foreach ( $sessions as $d => $list ) {
			foreach ( $list as $s ) {
				$starts[ $d ][ $s['row'] ] = $s;
				for ( $k = 1; $k < $s['span']; $k++ ) {
					$covered[ $d ][ $s['row'] + $k ] = true;
				}
			}
		}

		$html  = '<div class="sawt-desktop"><table class="sawt-table">';
		$html .= '<caption class="screen-reader-text">' . esc_html__( 'Weekly timetable', 'smartart-weekly-timetable' ) . '</caption>';
		$html .= '<thead><tr><th scope="col" class="sawt-corner"><span class="screen-reader-text">' . esc_html__( 'Time', 'smartart-weekly-timetable' ) . '</span></th>';
		foreach ( $cols as $d ) {
			$html .= '<th scope="col">' . esc_html( (string) $days[ $d ] );
			if ( '' !== (string) ( $dates[ $d ] ?? '' ) ) {
				$html .= '<small>' . esc_html( (string) $dates[ $d ] ) . '</small>';
			}
			$html .= '</th>';
		}
		$html .= '</tr></thead><tbody>';

		foreach ( $slots as $i => $slot ) {
			$start = (string) ( $slot['start'] ?? '' );
			$hour  = 1 === preg_match( '/:00$/', $start );
			$html .= '<tr' . ( $hour ? ' class="sawt-hour"' : '' ) . '><th scope="row">' . esc_html( $start ) . '</th>';
			foreach ( $cols as $d ) {
				if ( isset( $starts[ $d ][ $i ] ) ) {
					$s     = $starts[ $d ][ $i ];
					$html .= '<td rowspan="' . absint( $s['span'] ) . '" class="sawt-s sawt-c' . absint( $s['color'] ) . '">'
						. '<strong>' . esc_html( $s['name'] ) . '</strong>'
						. '<span>' . esc_html( self::range( $s ) ) . '</span></td>';
				} elseif ( ! isset( $covered[ $d ][ $i ] ) ) {
					$html .= '<td></td>';
				}
			}
			$html .= '</tr>';
		}

		return $html . '</tbody></table></div>';
	}

	/**
	 * Mobile view: day tabs (JS) and one list per day (all visible without JS).
	 *
	 * @param array<int,mixed>            $days     Day names.
	 * @param array<int,mixed>            $dates    Dates.
	 * @param int[]                       $cols     Visible columns.
	 * @param array<int,array<int,array>> $sessions Sessions.
	 * @param string                      $id       Instance ID.
	 * @return string
	 */
	private static function mobile( array $days, array $dates, array $cols, array $sessions, string $id ): string {
		$tabs   = '';
		$panels = '';

		foreach ( $cols as $k => $d ) {
			$day  = (string) $days[ $d ];
			$date = (string) ( $dates[ $d ] ?? '' );

			$tabs .= '<button type="button" class="sawt-tab" id="' . esc_attr( $id . '-tab-' . $k ) . '"><b>'
				. esc_html( mb_substr( $day, 0, 3 ) ) . '</b><small>' . esc_html( $date ) . '</small></button>';

			$panels .= '<div class="sawt-panel" id="' . esc_attr( $id . '-panel-' . $k ) . '">'
				. '<h3 class="sawt-panel-title">' . esc_html( trim( $day . ' ' . $date ) ) . '</h3>';

			if ( empty( $sessions[ $d ] ) ) {
				$panels .= '<p class="sawt-none">' . esc_html__( 'No classes on this day.', 'smartart-weekly-timetable' ) . '</p>';
			} else {
				foreach ( $sessions[ $d ] as $s ) {
					$panels .= '<div class="sawt-item sawt-c' . absint( $s['color'] ) . '">'
						. '<span class="sawt-item-time">' . esc_html( self::range( $s ) ) . '</span>'
						. '<span class="sawt-item-name">' . esc_html( $s['name'] ) . '</span></div>';
				}
			}
			$panels .= '</div>';
		}

		return '<div class="sawt-mobile"><div class="sawt-tabs" hidden>' . $tabs . '</div>' . $panels . '</div>';
	}
}
