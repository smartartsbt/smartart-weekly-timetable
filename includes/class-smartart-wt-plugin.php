<?php
/**
 * Plugin bootstrap class: shortcode and assets.
 *
 * @package Smartart_Weekly_Timetable
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin bootstrap: registers the shortcode and its assets.
 */
final class Smartart_WT_Plugin {

	/**
	 * Number of timetables rendered on the current page (used for unique IDs).
	 *
	 * @var int
	 */
	private static int $instances = 0;

	/**
	 * Hook everything up.
	 */
	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_assets' ) );
		add_shortcode( 'smartart_timetable', array( __CLASS__, 'shortcode' ) );
	}

	/**
	 * Register (not enqueue) the front-end assets. They are only loaded when the shortcode runs.
	 */
	public static function register_assets(): void {
		wp_register_style(
			'smartart-wt',
			SMARTART_WT_URL . 'assets/css/timetable.css',
			array(),
			SMARTART_WT_VERSION
		);
		wp_register_script(
			'smartart-wt',
			SMARTART_WT_URL . 'assets/js/timetable.js',
			array(),
			SMARTART_WT_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}

	/**
	 * Shortcode: [smartart_timetable url="https://docs.google.com/spreadsheets/d/e/.../pub?output=csv" cache="5"]
	 *
	 * @param array<string,string>|string $atts Shortcode attributes.
	 * @return string
	 */
	public static function shortcode( $atts ): string {
		$atts = shortcode_atts(
			array(
				'url'   => '',
				'cache' => '5',
			),
			is_array( $atts ) ? $atts : array(),
			'smartart_timetable'
		);

		$minutes = min( 1440, max( 1, absint( $atts['cache'] ) ) );

		if ( '' === trim( (string) $atts['url'] ) ) {
			return self::notice( __( 'Smartart Weekly Timetable: please add the url attribute to the shortcode.', 'smartart-weekly-timetable' ) );
		}

		$url = Smartart_WT_Source::validate_url( (string) $atts['url'] );
		if ( null === $url ) {
			return self::notice( __( 'Smartart Weekly Timetable: the URL must be a Google Sheets link published to the web as CSV.', 'smartart-weekly-timetable' ) );
		}

		$data = Smartart_WT_Source::get( $url, $minutes );
		if ( is_wp_error( $data ) ) {
			return self::notice( $data->get_error_message() );
		}

		wp_enqueue_style( 'smartart-wt' );
		wp_enqueue_script( 'smartart-wt' );
		++self::$instances;

		return Smartart_WT_Renderer::render( $data, self::$instances );
	}

	/**
	 * Output a notice. Visitors only see a generic message, editors see the real reason.
	 *
	 * @param string $message Detailed message for editors.
	 * @return string
	 */
	private static function notice( string $message ): string {
		wp_enqueue_style( 'smartart-wt' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			$message = __( 'The timetable is currently unavailable.', 'smartart-weekly-timetable' );
		}
		return '<p class="sawt-notice">' . esc_html( $message ) . '</p>';
	}
}
