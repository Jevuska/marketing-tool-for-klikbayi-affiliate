<?php
/*
 * @package KLIKBAYI
 * @category Core
 * @author Jevuska
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'KLIKBAYI_PLUGIN_FILE' ) )
	exit;

class KlikBayiWidget extends WP_Widget
{
	function __construct()
	{	
		global 
			$klikbayi_settings,
			$klikbayi_sanitize;
			
		parent::__construct( false, 'KlikBayi.Com' );

		$this->validate        = $klikbayi_sanitize;
		$this->type            = $this->validate->type_array();
		$this->style           = $this->validate->style_array();
	}

	function widget( $args, $instance )
	{
		$a = $this->validate->sanitize( $instance );
		
		$result = klikbayi_create_html( $a['type'], $a['form_title'], $a['button_text'], $size = '0,0,px', $a['style'],  $a['post__in'] , $a['post__not_in'], $content = null, 'shortcode' );
		
		if ( ! empty( $result ) ) :
			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
			
			echo $args['before_widget'];
			echo '<div class="widget_form_klikbayi">';
			if ( $title ) 
			  echo $args['before_title'] . $title . $args['after_title'];
			
			if( '' != $instance['textarea'] )
				printf ( '<p class="klikbayi_textarea">%1$s</p>',
					$instance['textarea']
				);
			
			echo $result;
			echo '</div>';
			echo $args['after_widget'];
		endif;
	}

	function update( $new_instance, $old_instance )
	{
		$instance                 = $old_instance;
		$instance['title']        = strip_tags( $new_instance['title'] );
		$instance['textarea']     = $new_instance['textarea'];
		$instance['post__in']     = $this->validate->post_ids( $new_instance['post__in'] );
		$instance['post__not_in'] = $this->validate->post_ids( $new_instance['post__not_in'] );
		$instance['type']         = $this->validate->type( $new_instance['type'] );
		$instance['form_title']   = sanitize_text_field( $new_instance['form_title'] );
		$instance['button_text']  = sanitize_text_field( $new_instance['button_text'] );
		$instance['style']        = $this->validate->style( $new_instance['style'] );
		
		return $instance;
	}

	function form( $instance )
	{
		if ( $instance )  :
		
			$title        = sanitize_text_field( $instance['title'] );
			$textarea     = $instance['textarea'];
			$post__in     = $this->validate->create_list_post_ids(  $instance['post__in'] );
			$post__not_in = $this->validate->create_list_post_ids( $instance['post__not_in'] );
			$type         = $this->validate->type( $instance['type'] );
			$form_title   = sanitize_text_field( $instance['form_title'] );
			$text         = sanitize_text_field( $instance['button_text'] );
			$style        = $this->validate->style( $instance['style'] );
			 
		else :
			
			$book_cover_src = KLIKBAYI_PLUGIN_URL . 'lib/assets/img/klikbayi-cover-book.jpg';
			$book_cover_img = sprintf( '<img src="%s">', esc_url_raw( $book_cover_src ) );

			$title          = __( 'KlikBayi.Com&#39;s Product', 'klikbayi' );
			$textarea       = $book_cover_img;
			$post__in       = '';
			$post__not_in   = '';
			$type           = 'form';
			$form_title     = __( 'Form Order', 'klikbayi' );
			$text           = __( 'Order', 'klikbayi' );
			$style          = 'inline';
			
		endif;
		
		printf( '<p><label for="%1$s">%2$s</label><input class="widefat" id="%1$s" name="%3$s" type="text" value="%4$s" />
		</p>',
			$this->get_field_id( 'title' ),
			__( 'Title', 'klikbayi' ),
			$this->get_field_name( 'title' ),
			$title
		);
		
		printf( '<p><label for="%1$s">%2$s</label><textarea class="widefat" id="%1$s" name="%3$s">%4$s</textarea></p>',
			$this->get_field_id( 'textarea' ),
			__( 'Additional Text (HTML allowed)', 'klikbayi' ),
			$this->get_field_name( 'textarea' ),
			$textarea
		);
		
		printf( '<p><label for="%1$s">%2$s</label><textarea class="widefat" id=="%1$s" name="%3$s">%4$s</textarea></p>',
			$this->get_field_id( 'post__in' ),
			__( 'Include Post ID (separate them by comma)', 'klikbayi' ),
			$this->get_field_name( 'post__in' ),
			$post__in
		);
		
		printf( '<p><label for="%1$s">%2$s</label><textarea class="widefat" id=="%1$s" name="%3$s">%4$s</textarea></p>',
			$this->get_field_id( 'post__not_in' ),
			__( 'Exclude Post ID ( separate them by comma )', 'klikbayi' ),
			$this->get_field_name( 'post__not_in' ),
			$post__not_in
		);
		
		printf( '<p><label for="%1$s">%2$s</label><br>',
			$this->get_field_id('type'),
			__( 'Form Order Type', 'klikbayi' )
		);
		
		foreach ( $this->type as $t ):
			$checked = checked( $type, $t, false );
			printf( '<label for="type-%1$s"><input type="radio" id="type-%1$s" name="%2$s" value="%1$s" %3$s /> <span>%4$s</span> </label>',
				sanitize_key( $t ),
				$this->get_field_name( 'type' ),
				$checked,
				ucwords( esc_html( $t ) )
			);
		endforeach;
		
		printf( '</p>');

		printf( '<p><label for="%1$s">%2$s</label><input class="widefat" id="%1$s" name="%3$s" type="text" value="%4$s" /></p>',
			$this->get_field_id( 'form_title' ),
			__( 'Form Title', 'klikbayi' ),
			$this->get_field_name( 'form_title' ),
			$form_title
		);

		printf( '<p><label for="%1$s">%2$s</label><input class="widefat" id="%1$s" name="%3$s" type="text" value="%4$s" /></p>',
			$this->get_field_id( 'button_text' ),
			__( 'Button Text', 'klikbayi' ),
			$this->get_field_name( 'button_text' ),
			$text
		);

		printf( '<p><label for="%1$s">%2$s</label><br>',
			$this->get_field_id( 'style' ),
			__('Form Order Style', 'klikbayi')
		);
		
		foreach ( $this->style as $t ):
			$checked = checked( $style, $t, false );
			printf( '<label for="style-%1$s"><input type="radio" id="style-%1$s" name="%2$s" value="%1$s" %3$s /> <span>%4$s</span></label> ',
				sanitize_key( $t ),
				$this->get_field_name( 'style' ),
				$checked,
				ucwords( esc_attr( $t ) )
			);
		endforeach;
		
		printf( '</p>');
	}
}

function klikbayi_register_widgets()
{
	global $klikbayi_settings;
	
	if ( isset( $klikbayi_settings['aff'] ) && '' != sanitize_key( $klikbayi_settings['aff'] ) )
		register_widget( 'KlikBayiWidget' );
	else
		return;
}

add_action( 'widgets_init', 'klikbayi_register_widgets' );