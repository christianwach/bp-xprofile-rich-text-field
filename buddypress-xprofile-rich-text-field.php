<?php 
/*
--------------------------------------------------------------------------------
Plugin Name: Buddypress xProfile Rich Text Field
Description: Add a Rich Text custom field type (editable using the WordPress Visual Editor) to Extended Profiles in BuddyPress.
Version: 0.2
Author: Christian Wach
Author URI: http://haystack.co.uk
Plugin URI: http://haystack.co.uk
--------------------------------------------------------------------------------
Forked from: Buddypress Xprofile Custom Fields Type
With many thanks to the original author: Atallos Cloud
Original Author's URI: http://www.atallos.com/
Original Plugin's URI: http://www.atallos.com/portfolio/buddypress-xprofile-custom-fields-type/
--------------------------------------------------------------------------------
*/



// set our version here - bumping this will cause CSS and JS files to be reloaded
define( 'BP_XPROFILE_RICH_TEXT_FIELD_VERSION', '0.2' );

// experimental: allow "Add Media" button
define( 'BP_XPROFILE_RICH_TEXT_FIELD_ADD_MEDIA', false );



/*
--------------------------------------------------------------------------------
BP_XProfile_Rich_Text_Field Class
--------------------------------------------------------------------------------
*/

class BP_XProfile_Rich_Text_Field {
	
	
	
	/** 
	 * Initialises this object
	 *
	 * @return object
	 */
	function __construct() {
		
		// there's a new API in BuddyPress 2.0
		if ( function_exists( 'bp_xprofile_get_field_types' ) ) {
		
			// include class
			require_once( 'buddypress-xprofile-rich-text-field-class.php' );
			
			// register with BP the 2.0 way...
			add_filter( 'bp_xprofile_get_field_types', array( $this, 'add_field_type' ) );
			
			// we need to parse the edit value in BP 2.0
			add_filter( 'bp_get_the_profile_field_edit_value', array( $this, 'get_field_value' ), 30, 3 );
			
		} else {
		
			// register field type
			add_filter( 'xprofile_field_types', array( $this, 'register_field_type' ) );
		
			// preview field type
			add_filter( 'xprofile_admin_field', array( $this, 'preview_admin_field'), 9, 2 );
		
			// test for a function in BP 1.7+
			if ( function_exists( 'bp_is_network_activated' ) ) {

				// in BP 1.7+ show our field type in edit mode via pre_visibility hook
				add_action( 'bp_custom_profile_edit_fields_pre_visibility', array( $this, 'edit_field' ) );
			
			} else {
		
				// show our field type in edit mode via the previous hook
				add_action( 'bp_custom_profile_edit_fields', array( $this, 'edit_field' ) );
			
			}
		
			// enqueue javascript on admin screens
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_js' ) );
		
		}
		
		// override and augment allowed tags
		add_filter( 'xprofile_allowed_tags', array( $this, 'allowed_tags' ), 30, 1 );
			
		// show our field type in read mode after all BuddyPress filters
		add_filter( 'bp_get_the_profile_field_value', array( $this, 'get_field_value' ), 30, 3 );
		
		// filter for those who use xprofile_get_field_data instead of get_field_value
		add_filter( 'xprofile_get_field_data', array( $this, 'get_field_data' ), 15, 3 );
		
		// enqueue basic stylesheet on public-facing pages
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_css' ) );

		// --<
		return $this;

	}
	
	
	
	//##########################################################################
	
	
	
	/**
	 * Add details of our xProfile field type (BuddyPress 2.0)
	 *
	 * @param array Key/value pairs (field type => class name).
	 * @return array Key/value pairs (field type => class name).
	 * @since 0.2
	 */
	function add_field_type( $fields ) {
		
		// make sure we get an array
		if ( is_array( $fields ) ) {
	
			// add our field to the array
			$fields['richtext'] = 'BP_XProfile_Field_Type_Richtext';
			
		} else {
		
			// create array with our item
			$fields = array( 'richtext' => 'BP_XProfile_Field_Type_Richtext' );
		
		}

		// --<
		return $fields;
		
	}
	
	
	
	/**
	 * Allow tags so that we can have images, for example
	 *
	 * @param array $allowedtags The array of allowed tags
	 * @return array $allowedtags The modified array of allowed tags
	 * @since 0.2
	 */
	function allowed_tags( $allowedtags ) {
		
		// make sure we get an array
		if ( is_array( $allowedtags ) ) {
	
			// add our tags to the array
			$allowedtags['img'] = array( 'id' => 1, 'class' => 1, 'src' => 1, 'alt' => 1, 'width' => 1, 'height' => 1 );
			
		} else {
		
			// create array with our tags
			$allowedtags = array( 
				'img' => array( 'id' => 1, 'class' => 1, 'src' => 1, 'alt' => 1, 'width' => 1, 'height' => 1 ) 
			);
		
		}
		
		// --<
		return $allowedtags;
		
	}
	
	
	
	//##########################################################################
	
	
	
	/**
	 * Register our field type
	 *
	 * @param array $field_types
	 * @return array
	 */
	function register_field_type( $field_types ) {
	
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
	
	
	
	/**
	 * Preview our field type
	 *
	 * @param object $field
	 * @param boolean $echo
	 * @return string
	 */
	function preview_admin_field( $field, $echo = true ) {

		// is it our type?
		if ( $field->type == 'richtext' ) {
		
			// init
			$html = '';
		
			// start buffering
			ob_start();
		
			// get data and show
			$data = BP_XProfile_ProfileData::get_value_byid( $field->id );
			
			// define our editor
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
	
	
	
	/**
	 * Show our field type in edit mode
	 *
	 * @return void
	 */
	function edit_field() {
	
		// only for our filed type...
		if ( bp_get_the_profile_field_type() == 'richtext' ) {

			global $field;
		
			// init data
			$data = '';
		
			// do we have data yet?
			if ( isset( $field->data->value ) ) { 
			
				// yes, grab it
				$data = $field->data->value; 
			
			}

			// start buffering
			ob_start();
		
			?>
			<div class="input-richtext">
				<label class="label-form <?php if ( bp_get_the_profile_field_is_required() ) { ?>required<?php } ?>" for="<?php bp_the_profile_field_input_name() ?>"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) { echo __('*', 'bpxprofilertf'); } ?></label>
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
			
			// print to screen
			echo $output;

		} 

	}
	
	
	
	/**
	 * Show our field type in read mode
	 *
	 * @param string $value
	 * @param string $type
	 * @param integer $id
	 * @return string
	 */
	function get_field_value( $value = '', $type = '', $id = '' ) {
	
		// is it our field type?
		if ( $type == 'richtext' ) {
	
			// we want the raw data, unfiltered
			global $field;
			$value = $field->data->value;
		
			// now, apply basic content filters
			$value = wpautop( convert_chars( wptexturize( stripslashes( $value ) ) ) );
		
		}
	
		// --<
		return $value;

	}
	
	
	
	/**
	 * Filter for those who use xprofile_get_field_data instead of get_field_value
	 *
	 * @param string $value
	 * @param integer $field_id
	 * @param integer $user_id
	 * @return string
	 */
	function get_field_data( $value = '', $field_id = '', $user_id = '' ) {
	
		// check we get a field ID
		if ( $field_id === '' ) { return $value; }
	
		// get field object
		$field = new BP_XProfile_Field( $field_id );
	
		// is it ours?
		if ( $field->type == 'richtext' ) {

			// apply basic content filters
			$value = wpautop( convert_chars( wptexturize( stripslashes( $value ) ) ) );

		}
	
		// --<
		return $value;

	}
	
	
	
	/**
	 * Enqueue JS files
	 *
	 * @return void
	 */
	function enqueue_js( $hook ) {    
		
		// only enqueue scripts on appropriate BP pages
		if ( 'users_page_bp-profile-setup' != $hook AND 'buddypress_page_bp-profile-setup' != $hook ) {
			return;
		}
		
		// enqueue it
		wp_enqueue_script( 

			'bpxprofilertf-js', 
			plugins_url( 'assets/js/buddypress-xprofile-rich-text-field.js', __FILE__ ), 
			array( 'jquery' ), // deps
			BP_XPROFILE_RICH_TEXT_FIELD_VERSION // version

		);
		
		// define translatable strings
		$params = array(
			'richtext' => __( 'Rich Text', 'bpxprofilertf' )
		);

		// localise
		wp_localize_script(
		
			'bpxprofilertf-js',
			'RichTextParams',
			$params
			
		);

	}
	
	
	
	/**
	 * Enqueue CSS files
	 *
	 * @return void
	 */
	function enqueue_css() {    
		
		// enqueue
		wp_enqueue_style( 

			'bpxprofilertf-css', 
			plugins_url( 'assets/css/buddypress-xprofile-rich-text-field.css', __FILE__ ), 
			array( 'bp-legacy-css' ), // deps
			BP_XPROFILE_RICH_TEXT_FIELD_VERSION, // version
			'all' // media

		);

	}

} // class ends





/**
 * Initialise our plugin after BuddyPress initialises
 *
 * @return void
 */
function bp_xprofile_rich_text_field() {    
	
	// make global in scope
	global $bp_xprofile_rich_text_field;
	
	// init plugin
	$bp_xprofile_rich_text_field = new BP_XProfile_Rich_Text_Field;

}

// add action for plugin loaded
add_action( 'bp_loaded', 'bp_xprofile_rich_text_field' );





