<?php
/*
--------------------------------------------------------------------------------
Plugin Name: Buddypress xProfile Rich Text Field
Description: Add a Rich Text custom field type (editable using the WordPress Visual Editor) to Extended Profiles in BuddyPress.
Version: 0.2.5
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
define( 'BP_XPROFILE_RICH_TEXT_FIELD_VERSION', '0.2.5' );

// experimental: allow "Add Media" button
define( 'BP_XPROFILE_RICH_TEXT_FIELD_ADD_MEDIA', false );



/**
 * Buddypress xProfile Rich Text Field Class.
 *
 * A class that encapsulates plugin functionality.
 *
 * @since 3.0
 */
class BP_XProfile_Rich_Text_Field {



	/**
	 * Initialises this object.
	 *
	 * @since 0.1
	 */
	function __construct() {

		// use translation files
		$this->enable_translation();

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

		// create custom filters that mirror 'the_content'
		add_filter( 'bp_xprofile_field_type_richtext_content', 'wptexturize'        );
		add_filter( 'bp_xprofile_field_type_richtext_content', 'convert_smilies'    );
		add_filter( 'bp_xprofile_field_type_richtext_content', 'convert_chars'      );
		add_filter( 'bp_xprofile_field_type_richtext_content', 'wpautop'            );
		add_filter( 'bp_xprofile_field_type_richtext_content', 'shortcode_unautop'  );

		// override and augment allowed tags
		add_filter( 'xprofile_allowed_tags', array( $this, 'allowed_tags' ), 30, 2 );

		// show our field type in read mode after all BuddyPress filters
		add_filter( 'bp_get_the_profile_field_value', array( $this, 'get_field_value' ), 30, 3 );

		// filter for those who use xprofile_get_field_data instead of get_field_value
		add_filter( 'xprofile_get_field_data', array( $this, 'get_field_data' ), 15, 3 );

		// enqueue basic stylesheet on public-facing pages
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_css' ) );

		// add BP Profile Search compatibility
		$this->bps_compat();

	}



	/**
	 * Load translation files.
	 *
	 * A good reference on how to implement translation in WordPress:
	 * http://ottopress.com/2012/internationalization-youre-probably-doing-it-wrong/
	 *
	 * @since 0.1
	 */
	public function enable_translation() {

		// not used, as there are no translations as yet
		load_plugin_textdomain(

			// unique name
			'buddypress-xprofile-rich-text-field',

			// deprecated argument
			false,

			// relative path to directory containing translation files
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'

		);

	}



	//##########################################################################



	/**
	 * Add details of our xProfile field type. (BuddyPress 2.0)
	 *
	 * @since 0.2
	 *
	 * @param array Key/value pairs (field type => class name).
	 * @return array Key/value pairs (field type => class name).
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
	 * Allow tags so that we can have images, for example.
	 *
	 * @since 0.2
	 *
	 * @param array $allowedtags The array of allowed tags
	 * @param object $data_obj The xProfile data object (BP 2.1+)
	 * @return array $allowedtags The modified array of allowed tags
	 */
	function allowed_tags( $allowedtags, $data_obj = null ) {

		// test if BP has sent the data object
		if ( ! is_null( $data_obj ) ) {

			// get field from data object
			$field = new BP_XProfile_Field( $data_obj->field_id );

			// if this isn't our field, skip amending allowed tags
			if ( $field->type != 'richtext' ) return $allowedtags;

		}

		// make sure we get an array
		if ( is_array( $allowedtags ) ) {

			// add our tags to the array
			$allowedtags['img'] = array( 'id' => 1, 'class' => 1, 'src' => 1, 'alt' => 1, 'width' => 1, 'height' => 1 );
			$allowedtags['ul'] = array( 'id' => 1, 'class' => 1 );
			$allowedtags['ol'] = array( 'id' => 1, 'class' => 1 );
			$allowedtags['li'] = array( 'id' => 1, 'class' => 1 );
			$allowedtags['span'] = array( 'style' => 1 );
			$allowedtags['p'] = array( 'style' => 1 );

		} else {

			// create array with our tags
			$allowedtags = array(
				'img' => array( 'id' => 1, 'class' => 1, 'src' => 1, 'alt' => 1, 'width' => 1, 'height' => 1 ),
				'ul' => array( 'id' => 1, 'class' => 1 ),
				'ol' => array( 'id' => 1, 'class' => 1 ),
				'li' => array( 'id' => 1, 'class' => 1 ),
				'span' => array( 'style' => 1 ),
				'p' => array( 'style' => 1 ),
			);

		}

		// --<
		return apply_filters( 'bp_xprofile_field_type_richtext_allowedtags', $allowedtags, $data_obj );

	}



	//##########################################################################



	/**
	 * Register our field type.
	 *
	 * @since 0.1
	 *
	 * @param array $field_types The existing array of field types
	 * @return array $field_types The modified array of field types
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
	 * Preview our field type.
	 *
	 * @since 0.1
	 *
	 * @param object $field The field object
	 * @param boolean $echo When true, echoes the output, otherwise returns it
	 * @return string $html The admin field preview markup
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

			// define buttons
			$buttons = array(
				'theme_advanced_buttons1' => 'bold,italic,underline,blockquote,strikethrough,|,link,unlink,|,spellchecker,removeformat,fullscreen',
				'theme_advanced_buttons2' => '',
				'theme_advanced_buttons3' => ''
			);

			// define our editor
			wp_editor(

				esc_html( stripslashes( $data ) ),
				'xprofile_richtext',
				array(
					'media_buttons' => BP_XPROFILE_RICH_TEXT_FIELD_ADD_MEDIA,
					'teeny' => apply_filters( 'bp_xprofile_field_type_richtext_teeny', true ),
					'quicktags' => apply_filters( 'bp_xprofile_field_type_richtext_quicktags', false ),
					'tinymce' => apply_filters( 'bp_xprofile_field_type_richtext_buttons', $buttons )
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
	 * Show our field type in edit mode.
	 *
	 * @since 0.1
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

				// define buttons
				$buttons = array(
					'theme_advanced_buttons1' => 'bold,italic,underline,blockquote,strikethrough,|,link,unlink,|,spellchecker,removeformat,fullscreen',
					'theme_advanced_buttons2' => '',
					'theme_advanced_buttons3' => ''
				);

				// define our editor
				wp_editor(

					wpautop( stripslashes( $data ) ),
					bp_get_the_profile_field_input_name(),
					array(
						'media_buttons' => BP_XPROFILE_RICH_TEXT_FIELD_ADD_MEDIA,
						'teeny' => apply_filters( 'bp_xprofile_field_type_richtext_teeny', true ),
						'quicktags' => apply_filters( 'bp_xprofile_field_type_richtext_quicktags', false ),
						'tinymce' => apply_filters( 'bp_xprofile_field_type_richtext_buttons', $buttons )
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
	 * Show our field type in read mode.
	 *
	 * @since 0.1
	 *
	 * @param string $value The existing value of the field
	 * @param string $type The type of field
	 * @param integer $user_id The numeric ID of the WordPress user
	 * @return string $value The modified value of the field
	 */
	function get_field_value( $value = '', $type = '', $user_id = '' ) {

		// is it our field type?
		if ( $type == 'richtext' ) {

			// we want the raw data, unfiltered
			global $field;
			$value = $field->data->value;

			// apply content filter
			$value = apply_filters( 'bp_xprofile_field_type_richtext_content', stripslashes( $value ) );

			// return filtered value
			return apply_filters( 'bp_xprofile_field_type_richtext_value', $value );

		}

		// fallback
		return $value;

	}



	/**
	 * Filter for those who use xprofile_get_field_data instead of get_field_value.
	 *
	 * @since 0.1
	 *
	 * @param string $value The existing value of the field
	 * @param string $type The type of field
	 * @param integer $user_id The numeric ID of the WordPress user
	 * @return string $value The modified value of the field
	 */
	function get_field_data( $value = '', $field_id = '', $user_id = '' ) {

		// check we get a field ID
		if ( $field_id === '' ) { return $value; }

		// get field object
		$field = new BP_XProfile_Field( $field_id );

		// is it ours?
		if ( $field->type == 'richtext' ) {

			// apply content filter
			$value = apply_filters( 'bp_xprofile_field_type_richtext_content', stripslashes( $value ) );

			// return filtered value
			return apply_filters( 'bp_xprofile_field_type_richtext_value', $value );

		}

		// fallback
		return $value;

	}



	/**
	 * Enqueue JS files.
	 *
	 * @since 0.1
	 *
	 * @param str $hook The identifier for the current admin page
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
	 * Enqueue CSS files.
	 *
	 * @since 0.1
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



	/**
	 * BP Profile Search compatibility.
	 *
	 * @see http://dontdream.it/bp-profile-search/custom-profile-field-types/
	 *
	 * @since 0.2.3
	 */
	public function bps_compat() {

		// bail unless BP Profile Search present
		if ( ! defined( 'BPS_VERSION' ) ) return;

		// add filters
		add_filter( 'bps_field_validation_type', array( $this, 'bps_field_compat' ), 10, 2 );
		add_filter( 'bps_field_html_type', array( $this, 'bps_field_compat' ), 10, 2 );
		add_filter( 'bps_field_criteria_type', array( $this, 'bps_field_compat' ), 10, 2 );
		add_filter( 'bps_field_query_type', array( $this, 'bps_field_compat' ), 10, 2 );

	}



	/**
	 * BP Profile Search field compatibility.
	 *
	 * @since 0.2.3
	 *
	 * @param string $field_type The existing xProfile field type
	 * @param object $field The xProfile field object
	 * @return string $field_type The modified xProfile field type
	 */
	public function bps_field_compat( $field_type, $field ) {

		// cast our field type as 'textbox'
		switch ( $field->type ) {
			case 'richtext':
				$field_type = 'textbox';
				break;
		}

		// --<
		return $field_type;

	}



} // class ends



/**
 * Initialise our plugin after BuddyPress initialises.
 *
 * @since 0.1
 */
function bp_xprofile_rich_text_field() {

	// make global in scope
	global $bp_xprofile_rich_text_field;

	// init plugin
	$bp_xprofile_rich_text_field = new BP_XProfile_Rich_Text_Field;

}

// add action for plugin loaded
add_action( 'bp_loaded', 'bp_xprofile_rich_text_field' );



