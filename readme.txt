=== Buddypress xProfile Rich Text Field ===
Contributors: needle
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8MZNB9D3PF48S
Tags: buddypress, xprofile, field, tinymce, editor
Requires at least: 3.5
Tested up to: 4.4
Stable tag: 0.2.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Buddypress xProfile Rich Text Field adds a Rich-text Editor custom field type to Extended Profiles in BuddyPress.



== Description ==

The Buddypress xProfile Rich Text Field plugin adds a Rich-text Editor custom field type to Extended Profiles in BuddyPress.

Please note: this plugin is no longer required because its functionality has been implemented since BuddyPress 2.4. Having said that, if you already have data in xProfile fields of this type, you will need to keep this plugin active. See:

https://buddypress.trac.wordpress.org/ticket/5625

If you are using BuddyPress 2.0+ and your theme does not use compatibility mode (i.e it supplies its own BuddyPress template files) then you will have to update your theme's `members/single/profile/edit.php` and `registration/register.php` (or `members/register.php`) templates so that they match the new way of displaying xProfile fields. You can refer to the relevant BuddyPress files to see how that's now being done. These are `bp-templates/bp-legacy/buddypress/members/single/profile/edit.php`
and `bp-templates/bp-legacy/buddypress/members/register.php`.



== Installation ==

1. Extract the plugin archive
1. Upload plugin files to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress



== Changelog ==

= 0.2.4 =

Fixed support for BP Profile Search plugin - props nicolacirotto

= 0.2.3 =

Translation fixed
Support for BP Profile Search plugin - props nicolacirotto

= 0.2.2 =

Translation enabled
Migration notice

= 0.2.1 =

Implement content filters independently of 'the_content'
Allow tags now present in the teeny TinyMCE editor

= 0.2 =

Compatibility with BuddyPress 2.0 'BP_XProfile_Field_Type' API

= 0.1 =

Initial release
