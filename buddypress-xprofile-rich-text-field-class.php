<?php

/**
 * Rich Text xprofile field type.
 *
 * @since BuddyPress (2.0.0)
 */
class BP_XProfile_Field_Type_Richtext extends BP_XProfile_Field_Type {



	/**
	 * Constructor for the Rich Text field type.
	 *
	 * @since 0.2
 	 */
	public function __construct() {

		parent::__construct();

		$this->category = _x( 'Single Fields', 'xprofile field type category', 'buddypress-xprofile-rich-text-field' );
		$this->name     = _x( 'Rich Text Area', 'xprofile field type', 'buddypress-xprofile-rich-text-field' );
		$this->type     = 'richtext';

		// allow all values to pass validation
		$this->set_format( '/.*/', 'replace' );

		do_action( 'bp_xprofile_field_type_richtext', $this );

	}



	/**
	 * Output the edit field HTML for this field type.
	 *
	 * Must be used inside the {@link bp_profile_fields()} template loop.
	 *
	 * @since 0.2
	 *
	 * @param array $raw_properties Optional key/value array
	 */
	public function edit_field_html( array $raw_properties = array() ) {

		// user_id is a special optional parameter that certain other fields types pass to {@link bp_the_profile_field_options()}.
		if ( isset( $raw_properties['user_id'] ) ) {
			unset( $raw_properties['user_id'] );
		}

		?>
		<label for="<?php bp_the_profile_field_input_name(); ?>"><?php bp_the_profile_field_name(); ?> <?php if ( bp_get_the_profile_field_is_required() ) : ?><?php esc_html_e( '(required)', 'buddypress' ); ?><?php endif; ?></label>
		<?php do_action( bp_get_the_profile_field_errors_action() ); ?>
		<?php

		// define buttons
		$buttons = array(
			'theme_advanced_buttons1' => 'bold,italic,underline,blockquote,strikethrough,|,link,unlink,|,spellchecker,removeformat,fullscreen',
			'theme_advanced_buttons2' => '',
			'theme_advanced_buttons3' => ''
		);

		// define our editor
		wp_editor(

			bp_get_the_profile_field_edit_value(),
			bp_get_the_profile_field_input_name(),
			array(
				'media_buttons' => BP_XPROFILE_RICH_TEXT_FIELD_ADD_MEDIA,
				'teeny' => apply_filters( 'bp_xprofile_field_type_richtext_teeny', true ),
				'quicktags' => apply_filters( 'bp_xprofile_field_type_richtext_quicktags', false ),
				'tinymce' => apply_filters( 'bp_xprofile_field_type_richtext_buttons', $buttons )
			)

		);

	}



	/**
	 * Output HTML for this field type on the wp-admin Profile Fields screen.
	 *
	 * Must be used inside the {@link bp_profile_fields()} template loop.
	 *
	 * @since 0.2
	 *
	 * @param array $raw_properties Optional key/value array of permitted attributes that you want to add.
	 */
	public function admin_field_html( array $raw_properties = array() ) {

		// define buttons
		$buttons = array(
			'theme_advanced_buttons1' => 'bold,italic,underline,blockquote,strikethrough,|,link,unlink,|,spellchecker,removeformat,fullscreen',
			'theme_advanced_buttons2' => '',
			'theme_advanced_buttons3' => ''
		);

		// define our editor
		wp_editor(

			'',
			'xprofile_richtext',
			array(
				'media_buttons' => BP_XPROFILE_RICH_TEXT_FIELD_ADD_MEDIA,
				'teeny' => apply_filters( 'bp_xprofile_field_type_richtext_teeny', true ),
				'quicktags' => apply_filters( 'bp_xprofile_field_type_richtext_quicktags', false ),
				'tinymce' => apply_filters( 'bp_xprofile_field_type_richtext_buttons', $buttons )
			)

		);

	}



	/**
	 * This method usually outputs HTML for this field type's children options
	 * on the wp-admin Profile Fields "Add Field" and "Edit Field" screens, but
	 * for this field type, we don't want it, so it's stubbed out.
	 *
	 * @since 0.2
	 *
	 * @param BP_XProfile_Field $current_field The current profile field on the add/edit screen.
	 * @param string $control_type Optional. HTML input type used to render the current field's child options.
	 */
	public function admin_new_field_html( BP_XProfile_Field $current_field, $control_type = '' ) {}



} // class ends



