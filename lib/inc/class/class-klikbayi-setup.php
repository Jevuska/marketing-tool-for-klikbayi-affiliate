<?php
/*
 * @package KLIKBAYI
 * @category Core
 * @author Jevuska
 * @version 1.0
 */

if ( !  defined( 'ABSPATH' ) || ! defined( 'KLIKBAYI_PLUGIN_FILE' ) )
	exit;

class KLIKBAYI_Setup
{
	public static function on_activation()
	{
		global $klikbayi_settings, $klikbayi_sanitize;
		
		if ( empty( $klikbayi_settings ) ):
			$klikbayi_settings = klikbayi_default_setting();
		else :
			$new_update = klikbayi_default_setting( 'update' );
			foreach ( $new_update as $key => $value ) {
				if ( ! isset( $klikbayi_settings[ $key ] ) ) {
					$klikbayi_settings[ $key ] = $value;
				}
			}
		endif;

		update_option( 'klikbayi_domain', $klikbayi_settings['domain'] );
		
		update_option( 'klikbayi_blog', $klikbayi_settings['blog'] );
		
		unset( $klikbayi_settings['domain'] );
		unset( $klikbayi_settings['blog'] );
		
		update_option( 'klikbayi_option', $klikbayi_settings );
		
		$current_version = get_option( 'klikbayi_version' );
		
		if ( '' != $current_version ) {
			update_option( 'klikbayi_upgraded_from', $current_version
			);
		} else {
			$klikbayi_data = get_plugin_data( KLIKBAYI_PLUGIN_PATH . '/klikbayi.php' );
			update_option( 'klikbayi_version', $klikbayi_data['Version'] );
		}
	}

	public static function on_deactivation()
	{
		delete_option( 'klikbayi_upgraded_from' );
		delete_option( 'klikbayi_version' );
	}
	
	public static function on_uninstall()
	{
		delete_option( 'klikbayi_option' );
		delete_option( 'klikbayi_domain' );
		delete_option( 'klikbayi_blog' );
		delete_option( 'klikbayi_upgraded_from' );
		delete_option( 'klikbayi_version' );
	}
}