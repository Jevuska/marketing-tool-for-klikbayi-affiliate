<?php
/*
 * @package KLIKBAYI
 * @category Core
 * @author Jevuska
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'KLIKBAYI_PLUGIN_FILE' ) )
	exit;

	$klikbayi_settings = klikbayi_settings();
	$new_fields_defaults = array();
	foreach( $new_fields_defaults as $key => $value ) {
		if ( ! isset( $klikbayi_settings[ $key ] ) ) {
			$klikbayi_settings[$key] = $value;
		}
	}
	update_option( 'klikbayi_option', $klikbayi_settings );