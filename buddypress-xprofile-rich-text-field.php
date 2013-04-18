<?php
/*
Plugin Name: Buddypress Profile Rich Text Field
Description: Add a Rich-text Editor custom field type to Extended Profiles in BuddyPress.
Version: 1.0
Author: Christian Wach
Author URI: http://haystack.co.uk
Plugin URI: http://haystack.co.uk
--------------------------------------------------------------------------------
Forked from: Buddypress Xprofile Custom Fields Type
Original Author: Atallos Cloud
Original Author URI: http://www.atallos.com/
Original Plugin URI: http://www.atallos.com/portfolio/buddypress-xprofile-custom-fields-type/
--------------------------------------------------------------------------------
*/



/**
 * Register our field type
 */
function bpxprofilertf_add_xprofile_field_type( $field_types ) {
	
	// make sure we get an array
	if ( is_array( $field_types ) ) {
	
		// append our item
		$field_types[] = 'richtext';
		
	} else {

		// set array with our item
		$field_types = array( 'richtext' );
		
	}
	
	// --<
	return $field_types;

}
add_filter( 'xprofile_field_types', 'bpxprofilertf_add_xprofile_field_type' );





/**
 * Preview our field type
 */
function bpxprofilertf_xprofile_admin_field( $field, $echo = true ) {

	// is it our type?
	if ( $field->type == 'richtext' ) {
		
		// init
		$html = '';
		
		// start buffering
		ob_start();
		
		// get data and show
		$data = BP_XProfile_ProfileData::get_value_byid( $field->id );

		wp_editor( 
		
			esc_html( stripslashes( $data ) ), 
			'xprofile_richtext',
			array( 
				'media_buttons' => false, 
				'teeny' => true, 
				'quicktags' => false,
				'tinymce' => array(
					'theme_advanced_buttons1' => 'bold,italic,underline,blockquote,strikethrough,link,unlink,spellchecker,removeformat,fullscreen',
					'theme_advanced_buttons2' => '',
					'theme_advanced_buttons3' => ''
				)
			)
			
		);
		
		// clean up
		$html = ob_get_contents();
		ob_end_clean();
		
		if ( $echo ) {
			echo $html;
			return;
		} else {
			return $html;
		}

	}

}
add_filter( 'xprofile_admin_field', 'bpxprofilertf_xprofile_admin_field', 9 );





/**
 * Show our field type in edit mode
 */
function bpxprofilertf_edit_xprofile_field( $echo = true ) {
	
	// only for our filed type...
	if ( bp_get_the_profile_field_type() == 'richtext' ) {

		global $field;
		
		// start buffering
		ob_start();
		
		// init data
		$data = '';
		
		// do we have data yet?
		if ( isset( $field->data->value ) ) { 
			
			// yes, grab it
			$data = $field->data->value; 
			
		}

		?>
		<div class="input-richtext">
			<label class="label-form <?php if ( bp_get_the_profile_field_is_required() ) : ?>required<?php endif; ?>" for="<?php bp_the_profile_field_input_name() ?>"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) { echo __('*', 'bpxprofilertf'); } ?></label>
			<?php
			
			// define our editor
			wp_editor( 
			
				wpautop( stripslashes( $data ) ),
				bp_get_the_profile_field_input_name(),
				array(
					'media_buttons' => false, 
					'teeny' => true, 
					'quicktags' => false,
					'tinymce' => array(
						'theme_advanced_buttons1' => 'bold,italic,underline,blockquote,strikethrough,|,link,unlink,|,spellchecker,removeformat,fullscreen',
						'theme_advanced_buttons2' => '',
						'theme_advanced_buttons3' => ''
					)
				)
				
			);
			
			?>
		</div>
		<?php
		
		// clean up
		$output = ob_get_contents();
		ob_end_clean();

		if ( $echo ) {
			echo $output;
			return;
		} else {
			return $output;
		}

	} 

}
add_action( 'bp_custom_profile_edit_fields_pre_visibility', 'bpxprofilertf_edit_xprofile_field' );






/**
 * Show our field type in read mode
 */
function bpxprofilertf_get_field_value( $value='', $type='', $id='') {
	
	// is it our field type?
	if ( $type == 'richtext' ) {
	
		// we want the raw data, unfiltered
		global $field;
		$value = $field->data->value;
		
		// apply basic content filters
		$value = wpautop( convert_chars( wptexturize( stripslashes( $value ) ) ) );
		
	}
	
	// --<
	return $value;

}
add_filter( 'bp_get_the_profile_field_value', 'bpxprofilertf_get_field_value', 30, 3 );






/**
 * Filter for those who use xprofile_get_field_data instead of get_field_value.
 * @param type $value
 * @param type $field_id
 * @return string
 */
function bpxprofilertf_get_field_data( $value, $field_id, $user_id ) {
	
	// get field object
    $field = new BP_XProfile_Field( $field_id );
	
	// is it ours?
	if ( $field->type == 'richtext' ) {

		// apply basic content filters
		$value = wpautop(convert_chars(wptexturize( stripslashes( $value ) )));

	}
	
	// --<
	return $value;

}
add_filter( 'xprofile_get_field_data', 'bpxprofilertf_get_field_data', 15, 3 );





/**
 * Function replacing the original buddypress filter.
 * @param type $field_value
 * @param type $field_type
 * @return string
 */
function bpxprofilertf_xprofile_filter_link_profile_data( $field_value, $field_type = 'textbox' ) {
	if ( 'richtext' == $field_type)
		return $field_value;

	if ( !strpos( $field_value, ',' ) && ( count( explode( ' ', $field_value ) ) > 5 ) )
		return $field_value;

	$values = explode( ',', $field_value );

	if ( !empty( $values ) ) {
		foreach ( (array) $values as $value ) {
			$value = trim( $value );

			// If the value is a URL, skip it and just make it clickable.
			if ( preg_match( '@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', $value ) ) {
				$new_values[] = make_clickable( $value );

			// Is not clickable
			} else {

				// More than 5 spaces
				if ( count( explode( ' ', $value ) ) > 5 ) {
					$new_values[] = $value;

				// Less than 5 spaces
				} else {
					$search_url   = add_query_arg( array( 's' => urlencode( $value ) ), bp_get_members_directory_permalink() );
					$new_values[] = '<a href="' . $search_url . '" rel="nofollow">' . $value . '</a>';
				}
			}
		}

		$values = implode( ', ', $new_values );
	}

	return $values;
}

/**
 * Replacing the buddypress filter link profile is it has the filter.
 * If user deactivated the filter, we don't add another filter.
 */
function bpxprofilertf_remove_xprofile_links() {
    if (has_filter('bp_get_the_profile_field_value', 'xprofile_filter_link_profile_data')) {
        remove_filter( 'bp_get_the_profile_field_value', 'xprofile_filter_link_profile_data', 9, 2 );
        add_filter( 'bp_get_the_profile_field_value', 'bpxprofilertf_xprofile_filter_link_profile_data', 9, 2);
    }
}
add_action( 'bp_setup_globals', 'bpxprofilertf_remove_xprofile_links', 9999 );





/**
 * JS files
 */
function bpxprofilertf_add_js($hook) {    

	if ('users_page_bp-profile-setup' != $hook && 'buddypress_page_bp-profile-setup' != $hook)
		return;

	wp_enqueue_script( 

		'bpxprofilertf-js', 
		plugins_url( 'assets/js/buddypress-xprofile-rich-text-field.js', __FILE__ ), 
		array( 'jquery' ), // deps
		'1.0' // version

	);

	$params = array(

		'richtext' => __('Rich Text', 'bpxprofilertf')

	);

	wp_localize_script('bpxprofilertf-js', 'RichTextParams', $params);

}
add_action( 'admin_enqueue_scripts', 'bpxprofilertf_add_js' );




/**
 * CSS files
 */
function bpxprofilertf_add_css($hook) {    

	wp_enqueue_style( 

		'bpxprofilertf-css', 
		plugins_url( 'assets/css/buddypress-xprofile-rich-text-field.css', __FILE__ ), 
		array( 'bp-legacy-css' ), // deps
		'1.0', // version
		'all' // media

	);

}
add_action( 'wp_enqueue_scripts', 'bpxprofilertf_add_css' );




