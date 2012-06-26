<?php
/*
Plugin Name: Rich Contact Widget
Plugin URI: http://remyperona.fr/rich-contact-widget/
Description: A simple contact widget enhanced with microdatas & microformats tags
Version: 0.1
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
	public $widget_keys = array(
			'title',
			'name',
			'address',
			'postal_code',
			'city',
			'country',
			'phone',
			'email'
		);

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
		?>
		<ul class="vcard" itemscope itemtype="http://schema.org/Corporation">
			<?php if ( !empty( $instance['name'] ) ) ?>
				<li class="fn org" itemprop='name'><strong><?php echo $instance['name']  ?></strong></li>
			<ul class="adr" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
				<?php if ( !empty( $instance['address'] ) ) { ?>
					<li class="street-address" itemprop='streetAddress'><?php echo $instance['address'] ?></li>
				<?php
					}
				if ( !empty( $instance['postal_code'] ) || !empty( $instance['city'] ) ) { ?>
					<li>
				<?php
					if ( !empty( $instance['postal_code'] ) ) { ?>
						<span class="postal-code" itemprop='postalCode'><?php echo  $instance['postal_code'] ?></span>&nbsp;
				<?php
					}
					if ( !empty( $instance['city'] ) ) { ?>
						<span class="locality" itemprop='addressLocality'><?php echo $instance['city'] ?></span>
				<?php	
					}
				?>
					</li>
				<?php
				}
				if ( !empty( $instance['country'] ) ) { ?>
					<li class="country-name" itemprop='addressCountry'><?php echo $instance['country'] ?></li>
				<?php
				}
				?>
			</ul>
			<?php if ( !empty( $instance['phone'] ) ) { ?>
				<li class="tel" itemprop='telephone'><a href="tel:<?php echo $instance['phone'] ?>"><?php echo $instance['phone'] ?></a></li>
			<?php
			}
			if ( !empty( $instance['email'] ) ) { ?>
				<li class="email" itemprop='email'><a href="mailto:<?php echo $instance['email'] ?>"><?php echo $instance['email'] ?></a></li>
			<?php
			}
			?>
		</ul>
		<?php
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
		$instance = $old_instance;
		foreach ( $this->widget_keys as $key=>$value ) {
			if ( $old_instance[$value] != $new_instance[$value] || !array_key_exists($value, $instance) ) {
				$instance[$value] = strip_tags( $new_instance[$value] );
			}
		}
		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		foreach ( $this->widget_keys as $key=>$value ) {
			if ( !array_key_exists( $value, $instance ) && $value == 'title' ) {
				${$value} = __( 'Contact', 'rich-contact-widget' );
			} elseif ( !array_key_exists( $value, $instance ) ) {
				${$value} = '';
			} else {
				${$value} = $instance[$value];
			}
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title :' , 'rich-contact-widget'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'name' ); ?>"><?php _e( 'Company name :', 'rich-contact-widget' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'name' ); ?>" name="<?php echo $this->get_field_name( 'name' ); ?>" type="text" value="<?php echo esc_attr( $name ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'address' ); ?>"><?php _e( 'Company address :', 'rich-contact-widget' ); ?></label>
			<textarea class="widefat" id="<?php echo $this->get_field_id( 'address' ); ?>" name="<?php echo $this->get_field_name( 'address' ); ?>"><?php echo esc_textarea( $address ); ?></textarea>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'postal_code' ); ?>"><?php _e( 'Postal/ZIP code :', 'rich-contact-widget' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'postal_code' ); ?>" name="<?php echo $this->get_field_name( 'postal_code' ); ?>" type="text" value="<?php echo esc_attr( $postal_code ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'city' ); ?>"><?php _e( 'City :', 'rich-contact-widget' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'city' ); ?>" name="<?php echo $this->get_field_name( 'city' ); ?>" type="text" value="<?php echo esc_attr( $city ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'country' ); ?>"><?php _e( 'Country :', 'rich-contact-widget' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'country' ); ?>" name="<?php echo $this->get_field_name( 'country' ); ?>" type="text" value="<?php echo esc_attr( $country ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'phone' ); ?>"><?php _e( 'Phone number :', 'rich-contact-widget' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'phone' ); ?>" name="<?php echo $this->get_field_name( 'phone' ); ?>" type="text" value="<?php echo esc_attr( $phone ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'email' ); ?>"><?php _e( 'Email address :', 'rich-contact-widget' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'email' ); ?>" name="<?php echo $this->get_field_name( 'email' ); ?>" type="text" value="<?php echo esc_attr( $email ); ?>" />
		</p>
		<?php 
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