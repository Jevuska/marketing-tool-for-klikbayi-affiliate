<?php
/*
 * @package KLIKBAYI
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'KLIKBAYI_PLUGIN_FILE' ) )
	exit;

class KLIKBAYI_Load
{
	protected static $instance;
	
	public static function init()
	{
		is_null( self::$instance ) AND self::$instance = new self;
		return self::$instance;
	}
	
	public function __construct()
	{
		global $klikbayi_settings;
		
		if ( ! empty( $klikbayi_settings['aff'] ) )
		{
			
		
			add_action( 'init', 'klikbayi_register_shortcodes' );
			add_action( 'wp_enqueue_scripts', array(
				$this,
				'kb_global_enqueu_scripts' 
			) );
			
			add_action( 'wp_footer', array(
				$this,
				'klikbayi_global_inline_js' 
			) );
			
			
			add_filter( 'the_content', 'do_shortcode' );
			add_filter( 'widget_text', 'do_shortcode' );
			if ( $klikbayi_settings['active'] )
				add_filter( 'the_content', 'klikbayi_filter_the_content', 10 );
			add_action( 'klikbayi', 'klik_bayi_com' );
		}
		add_action( current_filter(), array(
			$this,
			'load_file' 
		), 30 );
	}
	
	public function load_file()
	{
		foreach ( glob( KLIKBAYI_PATH_LIB . 'inc/load/*.php' ) as $file )
			include_once $file;
	}
	
	public function kb_global_enqueu_scripts()
	{
		if( ! is_admin() ) :
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( 'klikbayi-js', KLIKBAYI_PLUGIN_URL . 'lib/assets/js/jquery-klikbayi-global.min.js', array(
			 'jquery' 
		), KLIKBAYI_PLUGIN_VERSION, true );
		endif;
	}
	
	public function klikbayi_global_inline_js()
	{
		if( ! is_admin() ) :
		add_thickbox();
?>
<script type="text/javascript">
//<! [CDATA[
jQuery( document ).ready(function($) {
	$('#klikbayi').klikbayi();
});
//]]>
</script>
		<?php
		endif;
	}
	
}