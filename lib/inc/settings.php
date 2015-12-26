<?php
/*
 * @package KLIKBAYI
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'KLIKBAYI_PLUGIN_FILE' ) )
	exit;

function klikbayi_settings()
{
	global $klikbayi_settings;
	
	if ( ! empty ( $klikbayi_settings ) )
		return $klikbayi_settings;
	
	$klikbayi_settings = get_option( 'klikbayi_option' );
	return $klikbayi_settings;
}

function klikbayi_default_setting( $option = '' )
{
	global $klikbayi_sanitize;
	
	$args  = $klikbayi_sanitize->sanitize();
	$keys  = array_keys( $args );
	$count = count( $keys );
	switch ( $option )
	{	
		case 'update' :
			return $args;
			break;
		
		case 'shortcode' :
			$exclude = array( 0, 1, 2, 5, 11 );
			for ( $i = 0; $i < $count; $i++ ):
				if ( in_array( $i, $exclude ) )
					unset( $args[ $keys[ $i ] ] );
			endfor;
			return $args;
			break;
		
		case 'reset' :
			$exclude = array( 0, 1 );
			for ( $i = 0; $i < $count; $i++ ):
				if ( in_array( $i, $exclude ) )
					unset( $args[ $keys[ $i ] ] );
			endfor;
			return $args;
			break;
		
		default :
			return $args;
			break;
	}
}