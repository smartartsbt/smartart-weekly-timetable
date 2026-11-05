<?php
/**
 * Plugin Name:       Smartart Weekly Timetable
 * Plugin URI:        https://smartart.hu
 * Description:       Show a responsive, color-coded weekly timetable (classes, shifts, appointments) from a published Google Sheet with a simple shortcode.
 * Version:           1.0.0
 * Requires at least: 6.3
 * Requires PHP:      8.0
 * Author:            Smartart
 * Author URI:        https://smartart.hu
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       smartart-weekly-timetable
 * Domain Path:       /languages
 *
 * @package Smartart_Weekly_Timetable
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SMARTART_WT_VERSION', '1.0.0' );
define( 'SMARTART_WT_URL', plugin_dir_url( __FILE__ ) );

require_once __DIR__ . '/includes/class-smartart-wt-source.php';
require_once __DIR__ . '/includes/class-smartart-wt-renderer.php';
require_once __DIR__ . '/includes/class-smartart-wt-plugin.php';

Smartart_WT_Plugin::init();
