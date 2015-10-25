<?php
/*
 * @package KLIKBAYI
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'KLIKBAYI_PLUGIN_FILE' ) )
	exit;

function klikbayi_register_shortcodes()
{
	add_shortcode( 'klikbayi', 'klikbayi_shortcode' );
}

function klikbayi_shortcode( $atts = null, $result = '' )
{
	global $post, $klikbayi_settings, $klikbayi_sanitize;
	
	$post_id = ( null === $post->ID ) ? get_the_ID() : (int) $post->ID;
	
	$option = 'shortcode';
	
	$b = shortcode_atts( klikbayi_default_setting( 'shortcode' ), $atts, 'klikbayi' );

	$a = $klikbayi_sanitize->sanitize( $b );
	
	$result = klikbayi_create_html( $a['type'], $a['form_title'], $a['button_text'], $a['size'], $a['style'], $a['post__in'] , $a['post__not_in'], $option);

	$author_id         = $post->post_author;
	$can_publish_posts = user_can( $author_id, klikbayi_capability_filter() );
	if ( ! $can_publish_posts )
		return;
	return $result;
}

function klikbayi_capability_filter()
{
	$option = 'publish_posts';
	return apply_filters( 'klikbayi_capability_filter', $option );
}

function klikbayi_filter_the_content( $content )
{
	global $post;
	$content = $content . klikbayi_set_form_order( $post );
	return $content;
}

function klik_bayi_com( $args = '' )
{
	global $post;
	
	$content = klikbayi_set_form_order( $post, $args );

	if ( empty( $content ) )
		return;
	echo $content;
}

function klikbayi_set_form_order( $post, $arg = '' )
{
	$a = klikbayi_do_your_settings( $post, $arg );
	
	$post_id = ( null === $post->ID ) ? get_the_ID() : (int) $post->ID;
	
	remove_filter( 'the_content', 'klikbayi_filter_the_content' );
	
	$result = klikbayi_create_html( $a['type'], $a['form_title'], $a['button_text'], $a['size'], $a['style'], $a['post__in'] , $a['post__not_in'] );

	add_filter( 'the_content', 'klikbayi_filter_the_content', 10 );

	if ( ! $result )
		return;
	
	return $result;
}

function klikbayi_do_your_settings( $post, $arg = '' )
{
	global $klikbayi_settings, $klikbayi_sanitize;
	$b = $klikbayi_sanitize->sanitize( $klikbayi_settings );
	
	if ( !empty( $arg ) ):

		$i     = klikbayi_default_setting( 'shortcode' );
		$new_d = $klikbayi_sanitize->sanitize( $arg );
		
		foreach ( $b as $k => $v ) :
			if ( ! array_key_exists( $k, $i ) )
				unset( $b[ $k ] );
		
			foreach ( $new_d as $kk => $vv )
			{
				if ( ! array_key_exists( $kk, $i ) )
					unset( $new_d[ $kk ] );
			}
		endforeach;
		
		$b = wp_parse_args( $new_d, $b );
	endif;

	return $b;
}


function klikbayi_create_html( $type, $form_title = '', $button_text, $size = '', $style = '', $post_in = array(), $post_not_in = array(), $option = '' )
{
	if( array_filter( $post_in ) )
	{
		if ( ! is_single( $post_in ) )
			return;
	}

	if( array_filter( $post_not_in ) )
	{

		if ( is_single( $post_not_in ) )
			return;
	}
	
		$output = '';
		$head = '';

		if ( '' != $form_title )
			$head = sprintf( '<h3>%s</h3>', __( $form_title, 'klikbayi' ) );
		
		$hwstring = form_hwstring( $size[0], $size[1], $size[2] );
		
		$html = '<div id="klikbayi';
		if ( 'form' == $type )
		{
			if( '' != $hwstring )
			{
				$size = " style='$hwstring'";
			}else{
				$size = '';
			}
		
			$form = klikbayi_form_order( $button_text, 'submit', $head, $style );
			$html .= '"%s>%s</div>';
			$output = sprintf( $html,  
				$size,
				$form 
			);
		}
		
		if ( 'button' == $type ) {
			
			$html .= 'Popup" style="display:none;'. '">%1$s</div>%2$s';
			$form = klikbayi_form_order( $button_text, 'submit', '',$style );
			$output = sprintf( $html,
				$form, 
				klikbayi_button_order( $button_text, '', 'btn-klikbayi', true, $form_title, $size )
			);
		}
		return $output;
}

if ( ! function_exists( 'form_hwstring' ) ) :
	function form_hwstring( $width, $height, $unit )
	{
		$out = '';
		$unit = str_replace('%%','%',$unit);
		if ( 0 < $width )
			$out .= 'width:'.intval($width) . $unit .';';
		if ( 0 < $height )
			$out .= 'height:'.intval($height) . $unit;
		return $out;
	}
endif;

function klikbayi_form_order( $button_text, $type, $head = '', $style )
{
	global $klikbayi_sanitize;
	
	$url = esc_url( klikbayi_url( 'aff' ) );
	
	$html = $head . '<form action="' . $url . '" id="klikbayi" method="post"><table id="klikbayi-' . $style . '">';

	$item = kb_order_array();
	
	foreach ( $item as $k => $v ) :
		$input_type = 'email' == $k ? 'email' : 'text';
		$placeholder = 'placeholder' != $style ? '' : $v[0] ;		
		$html .= klikbayi_input_order( $k, $v[0], $input_type, $placeholder, $v[1], $style );
	endforeach;

	$html .= '</table>' . klikbayi_button_order( $button_text, $type );
	
	$html .= '</form>';
	return $html;
}

function klikbayi_input_order( $item = '', $item_title = '', $input_type = '', $placeholder = '', $name = '', $style )
{
	$pos   = '<th><label>%1$s</label></th>';
	$html = '';
	$html .= '<tr>';
	
	if ( 'inline' == $style ) {
		$html .= $pos;
		$html .= '</tr><tr>';
	};
	
	if ( 'left' == $style )
		$html .= $pos;
	
	$html .= '<td><input type="%2$s" class="form-control %3$s" value="" placeholder="%4$s" name="%5$s"  required></td>';
	
	if ( 'right' == $style )
		$html .= $pos;
	
	$html .= '</tr>';
	
	$output = sprintf( $html, 
		$item_title, 
		$input_type, 
		$item, 
		$placeholder,
		$name
	);
	
	return $output;
}

function klikbayi_button_order( $button_text, $type = '', $id = '', $btn_input = false, $form_title = '', $size = '' )
{
	$class_btn = 'button button-primary';
	
	if( ! empty( $type ) )
		$type = 'type="' . $type . '"';
	
	if( ! empty( $id ) )
		$id = 'id="' . $id . '"';
	
	if( $btn_input ) :
		$out = '';
		if ( 0 < $size[0] )
			$out .= 'width=' . intval($size[0]);
		if ( 0 < $size[1] )
			$out .= '&amp;height=' . intval($size[1]);
		if ( '' == $out )
			$out = 'width=500&amp;height=500';
		$html = sprintf( '<input alt="#TB_inline?'. $out . '&amp;inlineId=klikbayiPopup" title="%s" type="button" class="%s thickbox" value="%s" %s>', __( $form_title, 'klikbayi' ), $class_btn, __($button_text, 'klikbayi'), $id );
	else :
		$html = sprintf( '<button class="%s %s" %s>%s</button>', $class_btn, $type, $id, __($button_text, 'klikbayi') );
	endif;
	return $html;
}

function kb_order_array()
{
	$arg = array(
		'name'    => array( __( 'Full Name', 'klikbayi' ), 'nama' ),
		'email'   => array( __( 'Email', 'klikbayi' ), 'email' ),
		'address' => array( __( 'Shipping Address &amp; Zip Code', 'klikbayi' ), 'alamat' ),
		'citizen' => array( __( 'City &amp; Province', 'klikbayi' ), 'kota' ),
		'phone'   => array( __( 'Mobile Phone', 'klikbayi' ), 'telpon' )
	);
	return $arg;
}

function klikbayi_style_uri()
{
	$path_css = KLIKBAYI_URL_PLUGIN_CSS;
	return $path_css . 'style.css';
}

function klikbayi_url( $option )
{
	global $klikbayi_settings;
	
	$args = array();
	if ( get_option('klikbayi_domain') ) {
		$aff = $klikbayi_settings['aff'];
		$url = str_replace( 'www.','', get_option('klikbayi_domain' ) );
		$domain = parse_url( 'http://' . $url, PHP_URL_HOST );
		$args = array(
			'domain' => $domain,
			'url' => 'http://' . $domain,
			'aff' => 'http://' . $domain . '/order1.php?ref=' . sanitize_user( $aff ),
		);
	}
	return $args[ $option ];
}

function klikbayi_blog()
{
	$url = '';
	if ( get_option('klikbayi_blog') )
		$url = 'http://' . get_option('klikbayi_blog');
	return $url;
}