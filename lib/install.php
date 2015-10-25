<?php
/*
 * @package KLIKBAYI
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'KLIKBAYI_PLUGIN_FILE' ) )
	exit;

register_activation_hook( KLIKBAYI_PLUGIN_FILE, array(
	'KLIKBAYI_Setup',
	'on_activation' 
) );

register_deactivation_hook( KLIKBAYI_PLUGIN_FILE, array(
	'KLIKBAYI_Setup',
	'on_deactivation' 
) );

register_uninstall_hook( KLIKBAYI_PLUGIN_FILE, array(
	'KLIKBAYI_Setup',
	'on_uninstall' 
) );

add_action( 'plugins_loaded', array(
	'KLIKBAYI_Load',
	'init' 
) );
