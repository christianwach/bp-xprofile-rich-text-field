BuddyPress xProfile Rich Text Field
===========================

This WordPress plugin adds a Rich-text Editor custom field type to Extended Profiles in BuddyPress.

Please note that if you are using BuddyPress 2.0+ and your theme does not use compatibility mode (i.e it supplies its own BuddyPress template files) then you will have to update your theme's `members/single/profile/edit.php` and `registration/register.php` (or `members/register.php`) templates so that they match the new way of displaying xProfile fields. You can refer to the relevant BuddyPress files to see how that's now being done. These are `bp-templates/bp-legacy/buddypress/members/single/profile/edit.php`
and `bp-templates/bp-legacy/buddypress/members/register.php`.

## Installation ##

### GitHub ###

There are two ways to install from GitHub:

#### ZIP Download ####

If you have downloaded *BuddyPress xProfile Rich Text Field* as a ZIP file from the GitHub repository, do the following to install and activate the plugin and theme:

1. Unzip the .zip file and, if needed, rename the enclosing folder so that the plugin's files are located directly inside `/wp-content/plugins/bp-xprofile-rich-text-field`
2. Activate the plugin
4. You are done!

#### git clone ####

If you have cloned the code from GitHub, it is assumed that you know what you're doing.

## Changelogs ##

### 0.2 ###

Compatibility with BuddyPress 2.0 'BP_XProfile_Field_Type' API

### 0.1 ###

Initial commit