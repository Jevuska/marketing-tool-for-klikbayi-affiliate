<?php
/*
 * @package KLIKBAYI
 * @category Core
 * @author Jevuska
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'KLIKBAYI_PLUGIN_FILE' ) )
	exit;

class KLIKBAYI_Admin
{
	private $data;
	private $validate;
	private $options;
	private $groups;
	private $default_setting;
	protected $plugin_uri;
	protected $text_domain = 'klikbayi';
	protected $setting_slug = 'setting-admin-klikbayi';
	
	static public function init()
	{
		$class = __CLASS__;
		new $class;
	}
	
	public function __construct()
	{
		$domain = get_option('klikbayi_domain');
		
		if( false === strpos( $domain, $this->text_domain ) )
			return;
		
		add_action( 'admin_init', array(
			$this,
			'page_init' 
		) );
		
		add_action( 'admin_menu', array(
			$this,
			'plugin_page' 
		) );
		
		add_action( 'admin_footer', array(
			$this,
			'klikbayi_inline_js' 
		) );
		
		add_action( 'admin_enqueue_scripts', array(
			$this,
			'klikbayi_enqueu_scripts' 
		) );
		
		add_action( 'klikbayi_info_blog', array(
			$this,
			'feed_blog' 
		) );

	}
	
	public function page_init()
	{
		global 
			$klikbayi_settings,
			$klikbayi_sanitize;
		
		$this->options         = $klikbayi_settings;
		
		$this->groups          = $this->mb_group();
		$this->default_setting = klikbayi_default_setting('reset');
		$this->url             = esc_url( klikbayi_url('url') );
		$this->domain          = klikbayi_url('domain');
		$this->blog            = klikbayi_blog();
		$this->validate        = $klikbayi_sanitize;
		$this->type            = $this->validate->type_array();
		$this->style           = $this->validate->style_array();
		$this->plugin_data 	   = get_plugin_data( KLIKBAYI_PLUGIN_PATH . '/klikbayi.php' );

		register_setting( 'option_klikbayi', 'klikbayi_option', array(
			$this,
			'sanitize' 
		) );
		
		foreach ( $this->groups as $t => $arr ) :
			add_settings_section( 'setting_section_' . $t, '', array(
				$this,
				'print_section_info_' . $t 
			), 'klikbayi_option_' . $t );
			
			add_meta_box( 'meta-box-' . $t, __( $arr[0], 'klikbayi' ), array(
				$this,
				'box_' . $t 
			), 'klikbayi_option_' . $t, 'normal', 'high' );
			
			foreach ( $arr[1] as $k => $v ) :
				if ( 'shortcode' == $k || 'sidebar' == $k )
					continue;
				add_settings_field( $k, __( $v, 'klikbayi' ), array(
					$this,
					$k . '_cb' 
				), 'klikbayi_option_' . $t, 'setting_section_' . $t );
			endforeach;
		endforeach;
	}
	
	public function plugin_page()
	{
		global $admin_klikbayi;
		
		$this->tabs = array( 
			'overview' => array(
				'title' => __( 'Overview', 'klikbayi' ),
				'content' => __( 'This plugin is especially designed to give KlikBayi.com&#39;s affiliates freedom to place the order button/form order (with its affiliate ID included) so the customer can order from their wordpress blog directly and they don&#39;t need to open KlikBayi.com main site anymore.<br>There are many option to place the order button/ form order from single/specific post to widget sidebar. Just make sure you give the necessary description needed for KlikBayi.com product&#39;s.', 'klikbayi' )
			),
			'troubleshooting' => array(
				'title' => __( 'Troubleshooting', 'klikbayi' ), 
				'content' => __( 'If you get an error, try to deactivate or uninstall the plugin and activate it again.', 'klikbayi' )
			), 
			'faq' => array(
				'title' => __( 'FAQ', 'klikbayi' ),
				'content' => sprintf( 
					wp_kses( __( '<h4>How do I setup my WordPress theme to work with Klik Bayi plugin?</h4><p>You can use php code <code>&lt;?php do_action(&#39;klikbayi&#39;); ?&gt;</code> and add this single line code after the_content code. Single or sitewide pages is welcome. More advance code is available.</p>', 'klikbayi' ),
						array(
							'h4'  => array(),
							'code' => array()
						)
					)
				)
			)
		);
		
		$admin_klikbayi = 
		
		add_options_page( 
			esc_attr__( 'Marketing Tool for KlikBayi Affiliate', 'klikbayi' ), 
			esc_attr__( 'KlikBayi Affiliate', 'klikbayi' ), 
			'manage_options', 
			esc_attr( $this->setting_slug ), array(
				$this,
				'create_page' 
				)
		);
		
		add_action( 'load-' . $admin_klikbayi, array(
			$this,
			'help_tab' 
		), 20 );
	}
	
	public function create_page()
	{
		$col      = 1 == get_current_screen()->get_columns() ? '1' : '2';
		
		printf( '
		<div id="%s" class="wrap">
			<h1>%s</h1>
			<div>%s</div>',
				esc_attr( $this->text_domain ),
				esc_attr__( 'Marketing Tool for KlikBayi Affiliate', 'klikbayi' ),
				esc_attr__( 'Marketing plugin for KlikBayi affiliate. The killers plugin for KlikBayi.com&#39;s product.', 'klikbayi' )
		);
		
		printf( '
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-%s">',
					$col
		);
			
		printf( '
						<div id="postbox-container-1"   class="postbox-container">'
		);
						do_meta_boxes( 'klikbayi_option_sidebar', 'normal', 'klikbayi_option' );
			
		printf(
						'</div>' 
		);
		

				
		printf (
					'<div id="postbox-container-2" class="postbox-container">
						<form method="post" action="options.php">' 
		);
						if( empty( $this->options['aff'] ) ) :
							do_meta_boxes( 'klikbayi_option_activated', 'normal', 'klikbayi_option' );
						else :
							do_meta_boxes( 'klikbayi_option_general', 'normal', 'klikbayi_option' );
						endif;
							settings_fields( 'option_klikbayi' );
							wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
							wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
		printf(
					'</form>
					</div>'
		);

				if( ! empty( $this->options['aff'] ) ) :
		
					printf (
									'<div id="postbox-container-2" class="postbox-container">');

										do_meta_boxes( 'klikbayi_option_shortcode', 'normal', 'klikbayi_option' );
					printf(
									'</div>' );
				endif;

		printf( '</div>
			</div>
		</div>' 
		);
		
		add_filter(
			'admin_footer_text', 
			array( 
				$this, 
				'admin_footer_text' 
			) 
		);
		
		add_filter(
			'update_footer', 
			array( 
				$this, 
				'update_footer' 
			), 
			20 
		);
	}
	
	public function box_activated()
	{
		do_settings_sections( 'klikbayi_option_activated' );
		submit_button( __( 'Activate', 'klikbayi' ), 'primary', 'klikbayi_option[activate]', false );
	}

	public function box_general()
	{
		do_settings_sections( 'klikbayi_option_general' ); ?>
		<div id="major-publishing-actions">
					<div id="publishing-action">
						<?php submit_button( __( 'Reset', 'klikbayi' ), 'secondary', 'klikbayi_option[reset]', false ); ?>
					</div>
					<div class="publishing-action">
						<?php submit_button( __( 'Save Changes', 'klikbayi' ), 'primary', 'submit', false ); ?>	
					</div>
					
		<div class="clear"></div>
		</div>
				
		<?php
	}

	public function box_shortcode()
	{
		do_settings_sections( 'klikbayi_option_shortcode' ); ?>
		
		<div id="major-publishing-actions">
			<div id="publishing-action">
				<?php submit_button( __( 'Reset', 'klikbayi' ), 'large', false, false, array( 'id' => 'reset_code') ); ?>
			</div>
			<div class="publishing-action">
				<?php submit_button( __( 'Generate Code', 'klikbayi' ), 'primary large', false, false, array( 'id' => 'generate') ); ?>
			</div>
		<div class="clear"></div>
		</div>
		
		<?php
		$generate_code = '<p><label for="%1$s"><span class="description">%3$s</span></label><textarea id="%1$s" class="%2$s large-text"></textarea><textarea id="%4$s" class="%2$s large-text"></textarea><label for="%4$s"><span class="description">%5$s</span></label></p>';
		
		printf( $generate_code,
			'shortcode',
			'shortcode-result',
			__( 'Shortcode result: add into post area or text widget.', 'klikbayi' ),
			'phpcode',
			__( 'PHP Code result: add into your current theme. Use opening and closing php tags if this code not surrounding other php code.', 'klikbayi' )
		);
	}
	
	public function box_sidebar()
	{
		do_settings_sections( 'klikbayi_option_sidebar' );
	}

	public function sanitize( $input )
	{
		$new_input = array();

		$keys = array_keys( $this->default_setting );
		
		if ( isset( $input['activate'] ) && wp_validate_boolean( $input['activate'] ) )
		{
			if ( '' == sanitize_user( $input['aff'] ) ) {
				$msg       = __( 'Your affiliate ID still empty. Try to add yours.', 'klikbayi' );
				$flag      = 'error';
				$new_input = false;
			} else if (  '' !== sanitize_user( $this->options['aff'] ) ) {
				$text      = __( 'Your Affiliate ID was added.', 'klikbayi' );
				$msg       = $text . $this->options['aff'];
				$flag      = 'notice-warning';
				$new_input = false;
			} else {
				$text = __( 'Success to add your affiliate ID.', 'klikbayi' );
				$msg  = $text . '<kbd>' . sanitize_user( $input['aff'] ) . '</kbd>';
				$flag = 'updated';
				$input['active'] = (bool) 1;
				$new_input = wp_parse_args( $input, $this->default_setting );
			}
			add_settings_error( 'klikbayi-notices', 'active-notice', $msg, esc_attr( $flag ) );
			
			return $this->validate->sanitize( $new_input );
		}

		if ( isset( $input['reset'] ) && 'Reset' == sanitize_text_field( $input['reset'] ) )
		{
			$new_input = wp_parse_args(
				array(
					'active' => (bool) 1,
					'aff' => sanitize_user( $this->options['aff'] ) 
				),
				$this->default_setting
			);
			
			$msg = __( 'Success to reset your data.', 'klikbayi' );
			add_settings_error( 'klikbayi-notices', 'reset-notice', $msg, 'updated' );
			
			return $new_input;
		}
		
		if ( isset( $input['size_1'] ) || isset( $input['size_2'] ) )
			$input['size'] = array(
				absint( $input['size_1'] ),
				absint( $input['size_2'] ),
				( 'px' == sanitize_text_field( $input['size_3'] ) ) ? 'px' : '%%'
			);
		
		foreach ( $keys as $k ):
			$new_input[ $k ] = $input[ $k ];
		endforeach;
		
		return $this->validate->sanitize( $new_input );
	}
	
	public function help_tab()
	{
		global $admin_klikbayi;
		$screen = get_current_screen();
		if ( $screen->id != $admin_klikbayi )
			return;
		
		foreach ( $this->tabs as $id => $data ) {
			$screen->add_help_tab( array(
				'id'       => $id,
				'title'    => __( $data['title'], 'klikbayi' ),
				'callback' => array(
					$this,
					'prepare' 
				) 
			) );
		}
		
		$screen->set_help_sidebar( '<p><strong>' . __( 'For more information:', 'klikbayi' ) . '</strong></p>' . '<p><a href="' . $this->plugin_data['PluginURI'] . '" target="_blank">' . __( 'Plugin Page', 'klikbayi' ) . '</a></p>' );
	}
	
	public function prepare( $screen, $tab )
	{
		printf( '<p>%s</p>',
			__( $tab['callback'][0]->tabs[ $tab['id'] ]['content'],'klikbayi' )
		);
	}
	
	public function print_section_info_activated()
	{
		$info = __( 'You need to add your KlikBayi.Com&#39;s affiliate ID first.', 'klikbayi' );
		printf( '<p>%s</p>', $info );
	}
	
	public function print_section_info_general()
	{
		$info = __( 'General settings of form order.', 'klikbayi' );
		printf( '<p>%s</p>', $info );
	}
	
	public function print_section_info_shortcode()
	{
		$info = '<p><h5>%2$s</h5><kbd class="mainsc">[%3$s]</kbd></p>';
		$info .= '<p><h5>%4$s</h5><kbd>[%3$s type="button"]</kbd></p>';
		$info .= '<p><h5>%5$s</h5><kbd class="mainphp">&lt;?php do_action( &#39;%3$s&#39; ); ?&gt;</kbd></p>';
		$info .= '<p>%6$s</p>';
		printf( $info,
			__( 'Shortcode of form order.', 'klikbayi' ),
			__( 'Main Shortcode', 'klikbayi' ),
			 $this->text_domain,
			__( 'With parameters','klikbayi' ),
			__( 'PHP Code for template:', 'klikbayi' ),
			__( 'USAGE: Shortcode and PHP Code for theme is available. Select your parameters, Choose option values, and push Generate Code button. Add your code into post area, widget or into theme directly.','klikbayi' )
		);
		
		$text_btn = __( 'Generate Shortcode', 'klikbayi' );
		$sc_desc  = __( 'Shortcode result: add into post area or text widget.', 'klikbayi' );
		$php_desc = __( 'PHP Code result: add into your current theme. Use opening and closing php tags if this code not surrounding other php code.', 'klikbayi' );
		
		$shortcodetable = new Klik_Bayi_Shortcode_Table();
        $shortcodetable->prepare_items();
		$shortcodetable->display();
	}

	public function print_section_info_sidebar()
	{
		$book_cover      = KLIKBAYI_PLUGIN_URL . 'lib/assets/img/klikbayi-cover-book.jpg';
		$class           = 'klikbayi-quote';
		$textright       = 'textright';
		$dashicons_email = 'dashicons dashicons-email-alt';
		$title           = __( 'Contact Page' );
		$email           = 'kontak@' . $this->domain;
		
		$html = wp_kses(
			__( '<p><img src="%1$s"><i class="%2$s">Book of  KlikBayi.Com.</i></p><p class="%3$s"> &#126; by dr. Eiyta Ardinasari</p><p class="%3$s">Here our support contact <i class="%4$s"></i> <a href="%5$s" title="%6$s">%7$s</a></p>', 'klikbayi' ),
			array(
				'p' => array(
					'class' => array()
				),
				'i' => array(
					'class' => array()
				),
				'a' => array(
					'href' => array(),
					'title' => array()
				),
				'img' => array(
					'src' => array()
				)
			)
		);
		
		printf( $html,
			esc_url_raw( $book_cover ),
			esc_attr( $class ),
			esc_attr( $textright ),
			esc_attr( $dashicons_email ),
			esc_url( $this->url ),
			esc_attr( $title ),
			sanitize_email( $email )
		);
		do_action( 'klikbayi_info_blog' );
	}
	
	public function active_cb()
	{
		$text    = __( 'Uncheck to hide.', 'klikbayi' );
		$checked = $this->checkthis( 'active' );
		
		printf( '<p>%s</p>',
			$this->check_box_form( 'active', $checked, $text )
		);
	}
	
	public function post__in_cb()
	{
		$description = __( 'Include Post ID, separate them by comma.', 'klikbayi' );
		
		printf( $this->textarea( 'post__in', $description ) );
	}
	
	public function post__not_in_cb()
	{
		$description = __( 'Exclude Post ID, separate them by comma.', 'klikbayi' );
		printf( $this->textarea( 'post__not_in', $description ) );
	}
	
	public function aff_cb()
	{	
		if ( empty( $this->options['aff'] ) )
			$description = sprintf(
				wp_kses( __( 'Affiliate ID is your KlikBayi.Com&#39;s affiliate login or username.<br/>Don&#39;t have one? <a target="_blank" href="%s/affiliasi.php">Become Affiliate</a>.', 'klikbayi' ),
					array(
						'a' => array(
							'href' => array(),
							'target' => array()
						),
						'br' => array()
					)
				),
				$this->url
			);
		else
			$description = __( 'Make sure its your affiliate ID.', 'klikbayi' );
		
		printf( $this->input( 'aff', $description, 'text' ) );
	}
	
	public function type_cb()
	{
		foreach ( $this->type as $t ):
			$selected = $this->options['type'] == $t ? 'checked="checked"' : '';
			printf( '<label for="type-%1$s"><input type="radio" id="type-%1$s" name="klikbayi_option[type]" value="%1$s" %3$s/> <span>%2$s</span></label> ', esc_attr( $t ), ucwords( esc_attr( $t ) ), $selected );
		endforeach;
	}
	
	public function form_title_cb()
	{	
	    $description = __( 'Change the form title or leave empty to hide it.', 'klikbayi' );
		printf( $this->input( 'form_title', $description, 'text' ) );
	}
	
	public function button_text_cb()
	{	
	    $description = __( 'Change the button text','klikbayi' );
		printf( $this->input( 'button_text', $description, 'text' ) );
	}
	
	public function size_cb()
	{
		$text = __( 'Set size in pixel or percent. 0 for unset.','klikbayi' );
		$html = '';
		if ( '' == $this->options['size'] )
			$this->options['size'] = array( 0, 0, 'px' );
		
		$size_txt_arr = array(
			'',
			__( 'Width', 'klikbayi' ),
			__( 'Height', 'klikbayi' ),
			'px',
			'%%'
		);

		for ( $i = 1 ; $i < count( $size_txt_arr ) ; $i++ ) :
			if ( 3 > $i )
			{
				$html .= '<label for="size_' . $i . '">' . $size_txt_arr[$i] . '</label> <input id="size_' . $i . '" type="number" step="1" min="0" class="small-text" value="%' . $i . '$s" name="klikbayi_option[size_' . $i . ']"> ';
				continue;
			};
			
			$selected = $this->options['size'][2] == $size_txt_arr[$i] ? 'checked="checked"' : '';
			
			$html .= '<label for="size_' . $i . '">' . $size_txt_arr[$i] . '</label> <input id="size_' . $i . '" type="radio" value="%' . $i . '$s" name="klikbayi_option[size_3]" ' . $selected . '> ';
			
		endfor;
		
			$html .='<p class="description">%5$s</p>';
			
		printf( $html,
			intval( $this->options['size'][0] ), 
			intval( $this->options['size'][1] ),
			'px',
			'%%',
			$text 
		);
	}

	public function style_cb()
	{
		foreach ( $this->style as $t ) :
			$selected = $this->options['style'] == $t ? 'checked="checked"' : '';
			printf( '<label for="style-%1$s"><input type="radio" id="style-%1$s" name="klikbayi_option[style]" value="%1$s" %3$s/> <span>%2$s</span></label> ',
			esc_attr( $t ),
			ucwords( esc_attr( $t ) ),
			$selected
			);
		endforeach;
	}
	
	public function editor_cb()
	{		
		$this->wp_editor();
	}
	
	public function wp_editor()
	{
		global $wp_version;
		
		$content = ( isset( $this->options['editor']['content'] ) ) ? $this->options['editor']['content'] : '';
		
		$settings = array(
			'textarea_name' => 'klikbayi_option[editor][content]',
			'textarea_rows' => 5
		);
		
		if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) :
			ob_start();
			wp_editor( $content, 'form_editor', $settings );
			$html = ob_get_clean();
		else :
			$html = '<textarea id="form_editor" name="klikbayi_option[editor][content]" rows="5">' . esc_textarea( stripslashes( $content ) ) . '</textarea>';
		endif;
		
		$html .= '<label for="form_editor"><p class="description">' . __( 'Content before form.', 'klikbayi' ) . '</p></label>';
		
		echo $html;
	}
	
	protected function input( $type, $description, $inputtype )
	{
		$required = '';
		if ( 'aff' == $type || 'button_text' == $type )
			 $required = ' required';
		 
		$input = sprintf( '<input type="%1$s" class="%2$s" name="klikbayi_option[%2$s]" value="%3$s"%4$s/><p id="%3$s-description" class="description">%5$s</p>',
			esc_attr( $inputtype ),
			$type,
			( isset( $this->options[ $type ] ) ) ? sanitize_text_field( $this->options[ $type ] ) : $this->default_setting[ $type ], 
			$required,
			__( $description, 'klikbayi' )
		);

		return $input;
	}
	
	protected function textarea( $type, $description )
	{
		$value = $this->validate->create_list_post_ids( $this->options[ $type ] );
		
		$textarea = sprintf( '<textarea class="large-text %1$s" name="klikbayi_option[%1$s]">%2$s</textarea><p id="%1$s-description" class="description">%3$s</p>',
		$type,
		esc_textarea( $value ),
		__( $description, 'klikbayi' ) );

		return $textarea;
	}
	
	protected function checkthis( $type )
	{
		$checked = '';
		if ( ( isset( $this->options[ $type ] ) && $this->options[ $type ] == (bool) 1 ) )
			$checked = 'checked="checked"';
		return $checked;
	}
	
	protected function check_box_form( $type, $checked = "", $description = "" )
	{
		$desc = sprintf( '<span id="%s-description" class="description">%s</span>', esc_attr( $type ), __( $description, 'klikbayi' ) );
		
		$typeclass = "checkbox-$type";

		$output = sprintf( '<label for="%1$s"><input type="checkbox" id="%1$s" class="%2$s" name="klikbayi_option[%1$s]" value="%3$s" %4$s/>%5$s</label>', esc_attr( $type ), esc_attr( $typeclass ), isset( $this->options[ $type ] ) ? (bool) 1 : (bool) 0, $checked, $desc );
		return $output;
	}
	
	public function feed_blog()
	{
		include_once( ABSPATH . WPINC . '/feed.php' );

		$rss = fetch_feed( esc_url( $this->blog ) );
		$maxitems = 0;

		if ( ! is_wp_error( $rss ) ) :
			$maxitems = $rss->get_item_quantity( 5 ); 
			$rss_items = $rss->get_items( 0, $maxitems );
		endif;

		$html = '';
		$r = [];
		if ( 0 == $maxitems ) :
			$html .= '<li>' . __( 'No items', 'klikbayi' ) . '</li>';
		else :
			foreach ( $rss_items as $item ) :
				$r[] = 
					sprintf( '<a href="%s" title="%s">%s</a>',
						$item->get_permalink(),
						$item->get_date('j F Y | g:i a'),
						$item->get_title()
					);
			endforeach;
		endif;
			$html .= implode( '</li><li>', $r );
			$content = '<p><strong>%s %s</strong></p><ul><li>%s</li></ul>';
		printf( $content,
			ucwords( $this->domain ),
			__( 'Feed', 'klikbayi' ), 
			$html
		);
	}
	
	protected function mb_group()
	{	
		$group = array(
		
			'activated' => array(
				strtoupper( $this->mb_title_array()[0] ),
				$this->activated_array()  
			),
			
			'general' => array(
				strtoupper( $this->mb_title_array()[1] ),
				$this->general_array() 
			),
			
			'shortcode' => array(
				strtoupper( $this->mb_title_array()[2] ),
				array()
			), 
			
			'sidebar' => array(
				strtoupper( $this->mb_title_array()[3] ),
				array()
			)
		);
		return $group;
	}
	
	protected function mb_title_array()
	{
		$mb_title = array(
				__( 'Plugin Activated', 'klikbayi' ),
				__( 'General Settings', 'klikbayi' ),
				__( 'Shortcode', 'klikbayi' ),
				__( 'Update News', 'klikbayi' )
			);
		return $mb_title;
	}
	
	protected function activated_array()
	{
		$activated = array(
			'aff'      => __( 'Add Your Affiliate ID', 'klikbayi' )
		);
		return $activated;
	}
	
	protected function general_array()
	{
		$general_array = array(
			'active'       => __( 'Show Under Post', 'klikbayi' ),
			'post__in'     => __( 'Include Post', 'klikbayi' ),
			'post__not_in' => __( 'Exclude Post', 'klikbayi' ),
			'aff'          => __( 'Affiliate ID', 'klikbayi' ),
			'type'         => __( 'Form Order Type', 'klikbayi' ),
			'form_title'   => __( 'Form Title', 'klikbayi' ),
			'button_text'  => __( 'Button Text', 'klikbayi' ),
			'size'         => __( 'Form Size', 'klikbayi' ),
			'style'        => __( 'Form Style', 'klikbayi' ),
			'editor'       => __( 'Form Content', 'klikbayi' )
		);
		return $general_array;
	}

	public function klikbayi_enqueu_scripts()
	{
		global $admin_klikbayi;
		$screen = get_current_screen();
		
		if ( $screen->id != $admin_klikbayi )
			return;
		
		wp_enqueue_style( 'klikbayi',
			klikbayi_style_uri(), 
			array(), 
			KLIKBAYI_PLUGIN_VERSION, 
			false 
		);
		wp_enqueue_script( 'postbox' );
		wp_enqueue_script( 'klikbayi-script-handle', 
			KLIKBAYI_PLUGIN_URL . 'lib/assets/js/jquery-klikbayi.js', 
			array(
			 'wp-color-picker' 
			), 
			KLIKBAYI_PLUGIN_VERSION, 
			true 
		);
		
		wp_enqueue_media();
		
		wp_localize_script( 
			'klikbayi-script-handle', 
			'klikbayiL10n', array(
				'notice1' => __( 'Error', 'klikbayi' ),
				'ok'      => __( 'OK', 'klikbayi' ),
				'null'    => __( 'empty', 'klikbayi' )
			)
		);
		
		wp_localize_script( 
			'klikbayi-script-handle', 
			'klikbayiSet', array(
				'textdomain'    => $this->text_domain
			) 
		);
	}
	
	public function admin_footer_text()
	{
		$html = '<span id="footer-thankyou">&copy; 2015 - %s %s %s</p>';
		
		printf( $html,
			esc_html( $this->plugin_data['Name'] ), 
			__( 'plugin by', 'klikbayi' ), 
			$this->plugin_data['Author']
		);
	}
	
	public function update_footer()
	{
		$txt = '%s %s';
		
		printf( $txt, 
			__( 'Version', 'klikbayi' ), 
			esc_html( $this->plugin_data['Version'] )
		);
	}
	
	public function klikbayi_inline_js()
	{
		global $admin_klikbayi;
		
		$screen = get_current_screen();
		
		if ( $screen->id != $admin_klikbayi )
			return;

		print "<script type='text/javascript'>\n";
		print "jQuery( document ).ready( function( $ ) {\n";
		print "$('#" . $this->text_domain . "').klikbayi();\n";
		print "} );\n";
		print "</script>\n";
	}
}

if( ! class_exists( 'WP_List_Table' ) )
{
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Klik_Bayi_Shortcode_Table extends WP_List_Table
{
	public function prepare_items()
    {
		$columns     = $this->get_columns();
        $hidden      = $this->get_hidden_columns();
        $sortable    = $this->get_sortable_columns();
		$table_class = $this->get_table_classes();
        $data        = $this->table_data();
		
        usort( $data, array( &$this, 'sort_data' ) );

        $this->_column_headers = array( $columns, $hidden, $sortable,$table_class );
		
        $this->items = $data;
	}

	private function table_data()
	{
		global 
			$klikbayi_settings,
			$klikbayi_sanitize;
		
		$s = klikbayi_default_setting('shortcode');
		$p = array_keys( klikbayi_default_setting('shortcode') );
		
		$data = array();
		
		$data[] = array(
			'id'          => 1,
			'parameters'  => $p[0],
			'base'     	  => __( 'null', 'klikbayi' ),
			'optional'    => '<kbd>11,22,33</kbd>',
			'description' => __( 'Include post ID.', 'klikbayi' )
		);
		
		$data[] = array(
			'id'          => 2,
			'parameters'  => $p[1],
			'base'     	  => __( 'null', 'klikbayi' ),
			'optional'    => '<kbd>44,55,66</kbd>',
			'description' => __( 'Exclude post ID.', 'klikbayi' )
		);
		
		$data[] = array(
			'id'          => 3,
			'parameters'  => $p[2],
			'base'     	  => '<kbd>' . $s[$p[2]] . '</kbd>',
			'optional'    => '<kbd>' . implode( '</kbd><kbd>', $klikbayi_sanitize->type_array() ) . '</kbd>',
			'description' => __( 'Choose your type.', 'klikbayi' )
		);

		$data[] = array(
			'id'          => 4,
			'parameters'  => $p[3],
			'base'        => '<kbd>' . $s[$p[3]] . '</kbd>',
			'optional'    => '<kbd>Order</kbd>',
			'description' => __( 'Change the text. Leave empty to hide.', 'klikbayi' )
		);
		
		$data[] = array(
			'id'          => 5,
			'parameters'  => $p[4],
			'base'        => '<kbd>' . $s[$p[4]] . '</kbd>',
			'optional'    => '<kbd>Order Now</kbd>',
			'description' => __( 'Change the text.', 'klikbayi' )
		);
		
		$data[] = array(
			'id'          => 6,
			'parameters'  => $p[5],
			'base'        => '<kbd>' . implode( ',', $s[$p[5]] ) . '</kbd>',
			'optional'    => '<kbd>500,500,px</kbd>',
			'description' => __( 'Form size: ( width, height, unit) px or %. Unit is unset for popup for/button type. Default i pixels.', 'klikbayi' )
		);
		$data[] = array(
			'id'          => 7,
			'parameters'  => $p[6],
			'base'        => '<kbd>' . $s[$p[6]] . '</kbd>',
			'optional'    => '<ul><li><kbd>' . implode( '</kbd></li><li><kbd>', $klikbayi_sanitize->style_array() ) . '</kbd></li></ul>',
			'description' => __( 'Form Style.', 'klikbayi' )
		);
		
		return $data;
	}
	
	public function column_cb( $item )
	{
        return sprintf(
            '<label class="screen-reader-text" for="cb-%1$s">%1$s</label><input type="checkbox" value="" id="cb-%1$s" class="%1$s">',
			$item['parameters']
        );
	}
	
	public function get_columns()
    {
		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'parameters'  => __( 'Parameter', 'klikbayi' ),
			'base'        => __( 'Default', 'klikbayi' ),
			'optional'    => __( 'Option Value', 'klikbayi' ),
			'description' => __( 'Description', 'klikbayi' )
		);
		
        return $columns;
    }
	
	public function get_hidden_columns()
    {
        return array();
    }
	
	public function get_sortable_columns()
    {
		return array();
    }
	
	public function get_table_classes()
    {
		$classes = parent::get_table_classes();
		$classes[] = 'table-klikbayi';
		return $classes;
    }

	public function column_default( $item, $column_name )
    {
        switch( $column_name )
		{
            case 'id':
            case 'parameters':
            case 'base':
            case 'optional':
            case 'description':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }
	
	private function sort_data( $a, $b )
    {
        $orderby = 'id';
        $order   = 'desc';

        if( ! empty( $_GET['orderby'] ) )
            $orderby = $_GET['orderby'];

        if( ! empty( $_GET['order'] ) )
            $order = $_GET['order'];

        $result = strcmp( $a[ $orderby ], $b[ $orderby ] );

        if( 'desc' === $order )
            return $result;

        return -$result;
    }
}
