<?php
/*
Plugin Name: Rich Contact Widget
Plugin URI: http://remyperona.fr/rich-contact-widget/
Description: A simple contact widget enhanced with microdatas & microformats tags
Version: 0.3
Author: Rémy Perona
Author URI: http://remyperona.fr
License: GPL2
Text Domain: rich-contact-widget
	Copyright 2012  Rémy Perona  (email : remperona@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
/**
 * Adds RC_Widget widget.
 */
class RC_Widget extends WP_Widget {

	/**
	 * Array containing the keys for each value of the contact fields
	 */
	public function widget_keys() {
		$widget_keys = apply_filters( 'rc_widget_keys', array(
			'title',
			'type',
			'name',
			'activity',
			'address',
			'postal_code',
			'city',
			'country',
			'phone',
			'email'
			)
		);
		return $widget_keys;
	} 

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'rc_widget', // Base ID
			'Rich Contact Widget', // Name
			array( 'description' => __( 'A contact widget enhanced with microdatas & microformats tags', 'rich-contact-widget' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		if ( $instance['type'] == 'person' ) {
			$type = 'Person';
			$activity = 'jobTitle';
			$org = '';
		} else {
			$type = apply_filters('rc_widget_type', 'Corporation');
			$activity = 'description';
			$org = ' org';
		}

		$widget_output = '<ul class="vcard" itemscope itemtype="http://schema.org/'. $type. '">';
			if ( !empty( $instance['name'] ) )
				$widget_output .= '<li class="fn ' . $org . '" itemprop="name"><strong>' . $instance['name'] . '</strong></li>';
			if ( !empty( $instance['activity'] ) )
				$widget_output .= '<li itemprop="' . $activity . '">' . $instance['activity'] . '</li>';
			$widget_output .= '<ul class="adr" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">';
				if ( !empty( $instance['address'] ) ) {
					$widget_output .= '<li class="street-address" itemprop="streetAddress">' . $instance['address'] . '</li>';
					}
				if ( !empty( $instance['postal_code'] ) || !empty( $instance['city'] ) ) {
					$widget_output.= '<li>';
					if ( !empty( $instance['postal_code'] ) ) {
						$widget_output .= '<span class="postal-code" itemprop="postalCode">' . $instance['postal_code'] . '</span>&nbsp;';
					}
					if ( !empty( $instance['city'] ) ) {
						$widget_output .= '<span class="locality" itemprop="addressLocality">' . $instance['city'] . '</span>';
					}
					$widget_output .= '</li>';
				}
				if ( !empty( $instance['country'] ) ) {
					$widget_output .= '<li class="country-name" itemprop="addressCountry">' . $instance['country'] . '</li>';
				}
			$widget_output .= '</ul>';
			if ( !empty( $instance['phone'] ) ) {
				$widget_output .= '<li class="tel" itemprop="telephone"><a href="tel:' . $instance['phone'] . '">' . $instance['phone'] . '</a></li>';
			}
			if ( !empty( $instance['email'] ) ) {
				$widget_output .= '<li class="email" itemprop="email"><a href="mailto:' . antispambot($instance['email']) . '">' . $instance['email'] . '</a></li>';
			}

		$widget_output .= '</ul>';
		$widget_output = apply_filters( 'rc_widget_output', $widget_output, $instance );
		echo $widget_output;
		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		foreach ( $this->widget_keys() as $key=>$value ) {
			if ( $old_instance[ $value ] != $new_instance[ $value ] || !array_key_exists($value, $old_instance) ) {
				$new_instance[ $value ] = strip_tags( $new_instance[$value] );
			}
		}
		return $new_instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		foreach ( $this->widget_keys() as $key=>$value ) {
			if ( !array_key_exists( $value, $instance ) && $value == 'title' ) {
				${$value} = __( 'Contact', 'rich-contact-widget' );
			} elseif ( !array_key_exists( $value, $instance ) ) {
				${$value} = '';
			} else {
				${$value} = $instance[ $value ];
			}
		}
		if ( $type == 'person' ) {
			$checked_person = 'checked';
			$checked_company = '';
		} else if ( $type =='company' ) {
			$checked_company = 'checked';
			$checked_person = '';
		}

		$widget_form_output = '<p>
		<label for="' . $this->get_field_id( 'title' ) . '">' . __( 'Title :' , 'rich-contact-widget') . '</label> 
		<input class="widefat" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . esc_attr( $title ) . '" />
		</p>';
		$widget_form_output .= '<p>
			' . __('Type :', 'rich-contact-widget')  .'<br />
			<input id="' . $this->get_field_id( 'person' ) . '" name="' . $this->get_field_name( 'type' ) . '" type="radio" value="person" ' . $checked_person . ' />
			<label for="' . $this->get_field_id( 'person' ) . '">' . __('Person', 'rich-contact-widget') . '<br />
			<input id="' . $this->get_field_id( 'company' ) . '" name="'  . $this->get_field_name( 'type' )  . '" type="radio" value="company" ' . $checked_company . ' />
			<label for="' . $this->get_field_id( 'company' ) . '">' . __('Company', 'rich-contact-widget') . '
		</p>';
		$widget_form_output .= '<p>
			<label for="' . $this->get_field_id( 'name' ) . '">' . __( 'Company name/Your name :', 'rich-contact-widget' ) . '</label>
			<input class="widefat" id="' . $this->get_field_id( 'name' ). '" name="' . $this->get_field_name( 'name' ) . '" type="text" value="' . esc_attr( $name ) . '" />
		</p>';
		$widget_form_output .= '<p>
			<label for="' . $this->get_field_id('activity') . '">' . __('Activity/Job :', 'rich-contact-widget') . '</label>
			<input class="widefat" id="' . $this->get_field_id('activity') . '" name="' . $this->get_field_name('activity') . '" type="text" value="' . esc_attr( $activity ) . '" />
		</p>';
		$widget_form_output .= '<p>
			<label for="' . $this->get_field_id( 'address' ) . '">' . __( 'Company address :', 'rich-contact-widget' ) . '</label>
			<textarea class="widefat" id="' . $this->get_field_id( 'address' ) . '" name="' . $this->get_field_name( 'address' ) . '">' . esc_textarea( $address ) . '</textarea>
		</p>';
		$widget_form_output .= '<p>
			<label for="' . $this->get_field_id( 'postal_code' ) . '">' . __( 'Postal/ZIP code :', 'rich-contact-widget' ) . '</label>
			<input class="widefat" id="' . $this->get_field_id( 'postal_code' ) . '" name="' . $this->get_field_name( 'postal_code' ) . '" type="text" value="' . esc_attr( $postal_code ) . '" />
		</p>';
		$widget_form_output .= '<p>
			<label for="' . $this->get_field_id( 'city' ) . '">' . __( 'City :', 'rich-contact-widget' ) . '</label>
			<input class="widefat" id="' . $this->get_field_id( 'city' ) . '" name="' . $this->get_field_name( 'city' ) . '" type="text" value="' .  esc_attr( $city ) . '" />
		</p>';
		$widget_form_output .= '<p>
			<label for="' . $this->get_field_id( 'country' ) . '">' . __( 'Country :', 'rich-contact-widget' ) . '</label>
			<input class="widefat" id="' . $this->get_field_id( 'country' ) . '" name="' . $this->get_field_name( 'country' ) . '" type="text" value="' . esc_attr( $country ) . '" />
		</p>';
		$widget_form_output .= '<p>
			<label for="' . $this->get_field_id( 'phone' ) . '">' . __( 'Phone number :', 'rich-contact-widget' ) . '</label>
			<input class="widefat" id="' . $this->get_field_id( 'phone' ) . '" name="' . $this->get_field_name( 'phone' ) . '" type="text" value="' . esc_attr( $phone ) . '" />
		</p>';
		$widget_form_output .= '<p>
			<label for="' . $this->get_field_id( 'email' ) . '">' . __( 'Email address :', 'rich-contact-widget' ) . '</label>
			<input class="widefat" id="' . $this->get_field_id( 'email' ) . '" name="' . $this->get_field_name( 'email' ) . '" type="text" value="' . esc_attr( $email ) . '" />
		</p>';
		$widget_form_output = apply_filters( 'rc_widget_form_output', $widget_form_output, $instance );
		echo $widget_form_output;
	}

} // class RC_Widget

// register RC_Widget widget

function rcw_register_widget() {
	register_widget('RC_Widget');
}

// init RC_Widget widget
add_action( 'widgets_init', 'rcw_register_widget' );

// Loading languages for i18n
load_plugin_textdomain('rich-contact-widget', false, basename( dirname( __FILE__ ) ) . '/languages' );
?>