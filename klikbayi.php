<?php
/*
Plugin Name: Marketing Tool for KlikBayi Affiliate
Plugin URI: https://github.com/Jevuska/marketing-tool-for-klikbayi-affiliate
Description: Marketing plugin for KlikBayi affiliate. The easy way to selling <a href="http://klikbayi.com/">KlikBayi.com's</a> product.
Version: 1.0.2
Author: Jevuska
Author URI: http://www.jevuska.com
License: GPL3
Domain Path: /lib/languages
Text Domain: klikbayi

Klik Bayi is free software: you can only redistribute
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.

Klik Bayi is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Klik Bayi. If not, see https://www.gnu.org/licenses/gpl-3.0.html

* @package KLIKBAYI
* @category Core
* @author Jevuska
* @version 1.0
*/

if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Klik_Bayi' ) ):
	final class Klik_Bayi
	{
		private static $instance;
		public static function instance()
		{
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Klik_Bayi ) ):
				self::$instance = new Klik_Bayi;
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
				//add this for extensions (if any) to check if plugin is active.
				define( 'KLIKBAYI_RUNNING', true );
			endif;
			return self::$instance;
		}
		
		public function setup_constants()
		{
			if ( ! defined( 'KLIKBAYI_PLUGIN_VERSION' ) )
				define( 'KLIKBAYI_PLUGIN_VERSION', '1.0.2' );
			
			if ( ! defined( 'KLIKBAYI_PLUGIN_FILE' ) )
				define( 'KLIKBAYI_PLUGIN_FILE', __FILE__ );
			
			if ( ! defined( 'KLIKBAYI_PLUGIN_URL' ) )
				define( 'KLIKBAYI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			
			if ( ! defined( 'KLIKBAYI_PLUGIN_PATH' ) )
				define( 'KLIKBAYI_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
			
			if ( ! defined( 'KLIKBAYI_PATH_LIB' ) )
				define( 'KLIKBAYI_PATH_LIB', KLIKBAYI_PLUGIN_PATH . 'lib/' );
			
			if ( ! defined( 'KLIKBAYI_URL_PLUGIN_CSS' ) )
				define( 'KLIKBAYI_URL_PLUGIN_CSS', KLIKBAYI_PLUGIN_URL . 'lib/assets/css/' );
		}
		
		private function includes()
		{
			global $klikbayi_settings, $klikbayi_sanitize;
			
			require_once( KLIKBAYI_PATH_LIB . 'inc/class/class-klikbayi-sanitize.php' );
			
			require_once( KLIKBAYI_PATH_LIB . 'inc/settings.php' );
			
			$klikbayi_sanitize = klikbayi_sanitize();
			$klikbayi_settings = klikbayi_settings();

			require_once( KLIKBAYI_PATH_LIB . 'inc/class/class-klikbayi-load.php' );
			
			require_once( KLIKBAYI_PATH_LIB . 'inc/class/class-klikbayi-widget.php' );
			
			if ( is_admin() ):
				require_once( ABSPATH . 'wp-includes/pluggable.php' );
				if ( current_user_can( 'manage_options' ) ):
					require_once( KLIKBAYI_PATH_LIB . 'inc/admin-function.php' );
					require_once( KLIKBAYI_PATH_LIB . 'inc/class/class-klikbayi-admin.php' );
					require_once( KLIKBAYI_PATH_LIB . 'inc/class/class-klikbayi-setup.php' );
					do_action( 'load-klikbayi-admin-page' );
				endif;
			else:
			endif;
			require_once( KLIKBAYI_PATH_LIB . 'install.php' );
			
		}
		
		public function load_textdomain()
		{
			$domain          = 'klikbayi';
			$klikbayi_lang_dir = KLIKBAYI_PATH_LIB . 'languages/';
			$klikbayi_lang_dir = apply_filters( 'klikbayi_languages_directory', $klikbayi_lang_dir );
			$locale          = apply_filters( 'plugin_locale', get_locale(), $domain );
			$mofile          = sprintf( '%1$s-%2$s.mo', $domain, $locale );
			$mofile_local    = $klikbayi_lang_dir . $mofile;
			$mofile_global   = trailingslashit( WP_LANG_DIR ) . $domain . '/' . $mofile;
			
			if ( file_exists( $mofile_global ) ) {
				load_textdomain( $domain, $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				load_textdomain( $domain, $mofile_local );
			} else {
				load_plugin_textdomain( $domain, false, $klikbayi_lang_dir );
			}
		}
	}
endif;

function KLIKBAYI()
{
	return Klik_Bayi::instance();
}
KLIKBAYI();
