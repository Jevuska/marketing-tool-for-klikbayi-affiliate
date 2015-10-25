<?php
/*
 * @package KLIKBAYI
 * @category Core
 * @author Jevuska
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'KLIKBAYI_PLUGIN_FILE' ) )
	exit;

function klikbayi_sanitize()
{
	return new KLIKBAYI_Sanitize;
}

class KLIKBAYI_Sanitize
{
	protected $source;
	
	public function __construct()
	{
		$this->source = 'S0xJS0JBWUk=';
	}
	
	public function domain( $string ){
		$d = '';
		if ( false === strpos( $string, base64_decode( $this->source ) ) )
		{
			return $d;
		}
		return strtolower( $string );
	}
	
	public function post_ids( $list )
	{
		$result = array();
		
		if ( is_array( $list ) )
			$arr = $list;
		else
			$arr = ( $list != "" ) ? explode( ',', $list ) : array();
		
		if ( array_filter( $arr ) )
		{
			foreach ( array_unique( $arr ) as $post_id ) :
				$id = absint( $post_id );
				if ( is_string( get_post_status( $id ) ) )
					$result[] = $id;
			endforeach;
		}
		return $result;
	}
	
	public function create_list_post_ids( $array )
	{
		$list = '';
		
		if( empty( $array ) )
			return $list;
		
		if ( is_array( $array ) )
		{
			$result = array();
			foreach ( $array as $id )
			{
				$result[] = absint( $id );
			}
			if ( array_filter( $result ) )
				$list = implode( ',', $result );
		}
		else
		{
			$list = $array;
		}
		return $list;
	}
	
	public function type($type)
	{		
		$form = 'form';
		
		if ( in_array( $type, $this->type_array() ) )
			return $type;
		
		return $form;
	}
	
	public function size( $size )
	{	
	    $size_arr = array( (int) 0, (int) 0, 'px' );
		if( is_array( $size ) )
			return $size;
		if ( strpos( $size, ',' ) )
			$size_arr = explode( ',', $size );
		return $size_arr;
	}
	
	public function style( $style )
	{	
	    $_style = 'left';
		if ( in_array( $style, $this->style_array() ) )
			$_style = esc_attr( $style );
		return $_style;
	}
	
	public function type_array()
	{
		$type = array(
			'form',
			'button'
		);		
		return $type;
	}
	
	public function style_array()
	{
		$style = array(
		    'inline',
			'left',
			'right',
			'placeholder'
		);		
		return $style;
	}
	
	public function sanitize( $c = '' )
	{
		$default = $this->array_default_setting();
		$a       = array_keys( $default );
		
		if ( empty( $c ) )
			$c = $default;
		
		$key = array_keys( $c );
		$b   = wp_parse_args( $c, $default );
		
		$args = array(
			$a[0]  => $this->domain( $b['domain'] ),
			$a[1]  => $this->domain( $b['blog'] ),
			$a[2]  => wp_validate_boolean( $b['active'] ),
			$a[3]  => $this->post_ids( $b['post__in'] ),
			$a[4]  => $this->post_ids( $b['post__not_in'] ),
			$a[5]  => sanitize_user( $b['aff'] ),
			$a[6]  => $this->type( $b['type'] ),
			$a[7]  => sanitize_text_field( $b['form_title'] ),	
			$a[8]  => sanitize_text_field( $b['button_text'] ),
			$a[9]  => $this->size( $b['size'] ),
			$a[10]  => $this->style( $b['style'] )
		);
		
		foreach ( $args as $k => $v ):
			if ( ! in_array( $k, $key ) )
				unset( $args[$k] );
		endforeach;
		
		return $args;
	}
	
	public function array_default_setting()
	{
		$args = array(
			'domain'       => 'WWW.KLIKBAYI.COM',
			'blog'         => 'BLOG.KLIKBAYI.COM',
			'active'       => (bool) 1,
			'post__in'     => array(),
			'post__not_in' => array(),
			'aff'          => '',
			'type'         => 'form',
			'form_title'   => __( 'Form Order','klikbayi' ),
			'button_text'  => __( 'Order','klikbayi' ),
			'size'         => array(
				(int) 0,
				(int) 0,
				'px'
			),
			'style'       => 'inline'
		);
		return $args;
	}
}