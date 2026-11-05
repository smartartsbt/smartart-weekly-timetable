<?php
/**
 * Clean up cached data when the plugin is deleted.
 *
 * @package Smartart_Weekly_Timetable
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once __DIR__ . '/includes/class-smartart-wt-source.php';

Smartart_WT_Source::purge();
