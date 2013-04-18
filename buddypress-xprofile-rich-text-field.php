<?php 
/*
--------------------------------------------------------------------------------
Plugin Name: Buddypress Profile Rich Text Field
Description: Add a Rich Text custom field type (editable using the WordPress Visual Editor) to Extended Profiles in BuddyPress.
Version: 0.1
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
define( 'BP_XPROFILE_RICH_TEXT_FIELD_VERSION', '0.1' );



/*
--------------------------------------------------------------------------------
BpXprofileRichTextField Class
--------------------------------------------------------------------------------
*/

class BpXprofileRichTextField {

	/** 
	 * @description: initialises this object
	 * @return object
	 */
	function __construct() {
	
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
		
		// show our field type in read mode after all BuddyPress filters
		add_filter( 'bp_get_the_profile_field_value', array( $this, 'get_field_value' ), 30, 3 );
		
		// filter for those who use xprofile_get_field_data instead of get_field_value
		add_filter( 'xprofile_get_field_data', array( $this, 'get_field_data' ), 15, 3 );
		
		// enqueue javascript on admin screens
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_js') );

		// enqueue stylesheet on public-facing pages
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_css') );

		// --<
		return $this;

	}

	/**
	 * @description: PHP 4 constructor
	 * @return object
	 */
	function BpXprofileRichTextField() {
		
		// is this php5?
		if ( version_compare( PHP_VERSION, "5.0.0", "<" ) ) {
		
			// call php5 constructor
			$this->__construct();
			
		}
		
		// --<
		return $this;

	}

	//##########################################################################
	
	/**
	 * @description: register our field type
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
	 * @description: preview our field type
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
	 * @description: show our field type in edit mode
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
	 * @description: show our field type in read mode
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
	 * @description: filter for those who use xprofile_get_field_data instead of get_field_value
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
	 * @description: enqueue JS files
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
	 * @description: enqueue CSS files
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
 * @description: initialise our plugin after BuddyPress initialises
 */
function bp_xprofile_rich_text_field() {    

	// init plugin
	$bp_xprofile_rich_text_field = new BpXprofileRichTextField;

}

// add action for plugin init
add_action( 'bp_init', 'bp_xprofile_rich_text_field' );





