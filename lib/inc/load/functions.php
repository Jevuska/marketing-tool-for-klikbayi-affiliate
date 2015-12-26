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
	
	$result = klikbayi_create_html( $a['type'], $a['form_title'], $a['button_text'], $a['size'], $a['style'], $a['post__in'] , $a['post__not_in'], $content = null, $option );

	$author_id         = $post->post_author;
	$can_publish_posts = user_can( $author_id, klikbayi_capability_filter( 'publish_posts' ) );
	if ( ! $can_publish_posts )
		return;
	return $result;
}

function klikbayi_capability_filter( $cap )
{
	//avalible option refer to https://codex.wordpress.org/Roles_and_Capabilities
	$cap = apply_filters( 'klikbayi_capability_filter', $cap );
	return sanitize_key( $cap );
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
	
	$result = klikbayi_create_html( $a['type'], $a['form_title'], $a['button_text'], $a['size'], $a['style'], $a['post__in'] , $a['post__not_in'], $a['editor']['content'] );

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
		
			foreach ( $new_d as $kk => $vv ) :
				if ( ! array_key_exists( $kk, $i ) )
					unset( $new_d[ $kk ] );
			endforeach;
		endforeach;
		
		$b = wp_parse_args( $new_d, $b );
	endif;

	return $b;
}


function klikbayi_create_html( $type, $form_title, $button_text, $size = '', $style = '', $post_in = array(), $post_not_in = array(), $content = null, $option = null )
{
	if( array_filter( $post_in ) && ! is_single( $post_in ) )
		return;

	if( array_filter( $post_not_in ) && is_single( $post_not_in ) )
		return;
	
	$output = $head = '';
		
	if ( null != $content )
		$head .= sprintf( '<div class="klikbayi-content">%s</div>',
			$content
		);
			
	if ( '' != $form_title )
		$head .= sprintf( '<h3>%s</h3>',
		__( $form_title, 'klikbayi' )
		);
		
	$hwstring = form_hwstring( $size[0], $size[1], $size[2] );
		
	$html = '<div id="klikbayi-container';
	if ( 'form' == $type )  :
		if ( '' != $hwstring )
			$size = " style='$hwstring'";
		else
			$size = '';
		
		$form   = klikbayi_form_order( $button_text, 'submit', $head, $style );
		$html  .= '"%s>%s</div>';
		$output = sprintf( $html,  
			$size,
			$form 
		);
	endif;
		
	if ( 'button' == $type ) :
			
		$html  .= '-popup" style="display:none;'. '">%1$s</div>%2$s';
		$form   = klikbayi_form_order( $button_text, 'submit', '', $style );
		$output = sprintf( $html,
			$form, 
			klikbayi_button_order( $button_text, 'button', 'input-klikbayi', true, $form_title, $size )
		);
	endif;
		
	return $output;
}

if ( ! function_exists( 'form_hwstring' ) ) :
	function form_hwstring( $width, $height, $unit )
	{
		$hwstring = '';
		$unit = str_replace( '%%', '%', $unit);
		if ( 0 < $width )
			$hwstring .= 'width:' . intval($width) . $unit . ';';
		if ( 0 < $height )
			$hwstring .= 'height:' . intval($height) . $unit;
		return $hwstring;
	}
endif;

function klikbayi_form_order( $button_text, $type, $head = '', $style )
{
	global $klikbayi_sanitize;
	
	$url   = klikbayi_url( 'aff' );
	$html  = $head;
	$html .= sprintf( '<form action="%1$s" id="klikbayi" method="post"><table id="klikbayi-%2$s">',
		esc_url( $url ),
		sanitize_html_class( $style )
	);
	
	$item  = kb_order_array();
	
	foreach ( $item as $k => $v ) :
		$input_type  = ( 'email' == $k || 'tel' == $k ) ? $k : 'text';
		$placeholder = ( 'placeholder' != $style ) ? '' : $v[0] ;		
		$html .= klikbayi_input_order( $k, $v[0], $input_type, $placeholder, $v[1], $style );
	endforeach;
	
	$html .= '</table>';
	$html .= klikbayi_button_order( $button_text, $type, 'btn-klikbayi' );
	$html .= '</form>';
	
	return $html;
}

function klikbayi_input_order( $item = '', $item_title = '', $input_type = '', $placeholder = '', $name = '', $style )
{
	$pos   = '<th><label>%1$s</label></th>';
	$html  = '<tr>';
	
	if ( 'inline' == $style ) :
		$html .= $pos;
		$html .= '</tr><tr>';
	endif;
	
	if ( 'left' == $style )
		$html .= $pos;
	
	$html .= '<td><input type="%2$s" value="" class="form-control %3$s" placeholder="%4$s" name="%5$s" required /></td>';
	
	if ( 'right' == $style )
		$html .= $pos;
	
	$html .= '</tr>';
	
	$array = array(
		$item_title,
		$input_type,
		$item,
		$placeholder,
		$name
	);
	
	$arr = array_map( 'esc_attr', $array);
	
	$output = sprintf( $html,
		$arr[0],
		$arr[1],
		$arr[2],
		$arr[3],
		$arr[4]
	);
	
	return $output;
}

function klikbayi_button_order( $button_text, $type, $id, $btn_input = false, $form_title = '', $size = '' )
{
	$class_btn = 'button button-primary';
	
	if( false != $btn_input ) :
		$hw = '';
		
		if ( 0 < $size[0] )
			$hw .= 'width=' . intval( $size[0] );
		
		if ( 0 < $size[1] )
			$hw .= '&amp;height=' . intval( $size[1] );
		
		if ( '' == $hw )
			$hw .= 'width=500&amp;height=500';
		
		$html = sprintf( '<input alt="#TB_inline?%1$s&amp;inlineId=klikbayi-container-popup" title="%2$s" value="%3$s" class="%4$s thickbox" type="%5$s" id="%6$s" />',
			esc_attr( $hw ),
			esc_attr( $form_title ),
			esc_attr( $button_text ),
			esc_attr( $class_btn ),
			esc_attr( $type ),
			esc_attr( $id )
		);
	else :
		$html = sprintf( '<button class="%1$s" type="%2$s" id="%3$s">%4$s</button>',
			esc_attr( $class_btn ),
			esc_attr( $type ),
			esc_attr( $id ),
			$button_text
		);
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
		'tel'     => array( __( 'Mobile Phone', 'klikbayi' ), 'telpon' )
	);
	return $arg;
}

function klikbayi_style_uri()
{
	$uri_css = KLIKBAYI_URL_PLUGIN_CSS . 'style.css';
	return esc_url( $uri_css );
}

function klikbayi_url( $option )
{
	global $klikbayi_settings;
	
	$args = array();
	
	if ( '' == $option )
		return;
	
	$opt = get_option( 'klikbayi_domain' );
	
	if ( $opt ) :
		$aff    = sanitize_user( $klikbayi_settings['aff'] );
		$url    = str_replace( 'www.', '', esc_url( $opt ) );
		$domain = parse_url( $url, PHP_URL_HOST );
		
		$args   = array(
			'domain' => $domain,
			'url'    => esc_url( $domain ),
			'aff'    => trailingslashit( esc_url( $domain ) ) . 'order1.php?ref=' . $aff,
		);
	endif;
	
	if ( isset( $args[ $option ] ) )
		return $args[ $option ];
	return;
}

function klikbayi_blog()
{
	$option = get_option( 'klikbayi_blog' );
	if ( $option )
		$option = esc_url( $option );
	return $option;
}