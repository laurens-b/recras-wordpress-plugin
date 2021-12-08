=== Recras WordPress plugin ===
Contributors: zanderz
Tags: recras, recreation, reservation, booking, voucher
Tested up to: 5.9
Stable tag: 4.7.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily integrate data from your Recras instance, such as packages and contact forms, into your own website.

== Description ==
With this plugin, you can easily integrate data from your [Recras](https://recras.nl/) instance, such as packages and contact forms, into your own website.

To get started, go to the Recras → Settings page and enter your Recras name. For example, if you log in to Recras at `https://mysite.recras.nl/` then your Recras name is `mysite`. That's all there is to it! You can now use widgets to retrieve data. All data is retrieved via a secured connection (HTTPS) to ensure data integrity. Other than the request parameters, no data is sent to the Recras servers.

This plugin consists of the following "widgets". To use them, you first need to set your Recras name (see paragraph above).
* Availability calendar
* Contact forms
* Online booking
* Packages
* Products
* Voucher sales
* Voucher info

Widgets can be added to your site in three ways. Using Gutenberg blocks (recommended), using the buttons in the "classic editor" (limited functionality), or by entering the shortcode manually (discouraged).

= Date/Time picker =
By default, date and time pickers in contact forms use whatever the browser has available. Internet Explorer (all versions) does not have native date/time pickers and only shows a text field. If your website has a lot of visitors from IE, we recommend to enable the date picker we have included with the plugin. You can enable this on the Recras → Settings page. For time inputs, a proper fallback is included.

**Note**: this setting only applies to standalone contact forms, not to contact forms used during "new style" online booking.

= Styling =
No custom styling is applied by default, so it will integrate with your site easily. If you want to apply custom styling, see `css/style.css` for all available classes. Be sure to include these styles in your own theme, this stylesheet is not loaded by the plugin!
For styling the date picker, we refer you to the [Pikaday repository](https://github.com/Pikaday/Pikaday). Be sure to make any changes in your own theme or using WordPress' own Customizer.

= Cache =
All data from your Recras is cached for up to 24 hours. If you make important changes, such as increasing the price of a product, you can clear the cache to reflect those changes on your site immediately.

= Google Analytics integration =
You can enable basic Google Analytics integration by checking "Enable Google Analytics integration?" on the Recras Settings page. This will only work if there is a global `ga` JavaScript object. This should almost always be the case, but if you find out it doesn't work, please contact us!

== Installation ==

**Easy installation (preferred)**

1. Install the plugin from the Plugins > Add New page in your WordPress installation.

**Self install**

1. Download the zip file containing the plugin and extract it somewhere to your hard drive
1. Upload the `recras-wordpress-plugin` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

**Using Composer**

1. Type `composer require recras/recras-wordpress-plugin` in your terminal
1. The plugin will automatically be installed in the `/wp-content/plugins/` directory by using Composer Installers
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Do you support Gutenberg? =
Yes, since version 2.2.0! Please make sure you use the latest version of the plugin and please report any bugs you encounter.

= Do you support Visual Composer, Brizy, etc. ? =
We do not support page builders and have no plans to do so.

= Does the plugin support network installations? =
Yes it does. You can set different Recras names (all settings, for that matter) for each site.

= Can the plugin be installed as "must use plugin" ? =
No. "Must use" plugins don't appear in the update notifications nor show their update status on the plugins page (direct quote from the <a href="https://wordpress.org/support/article/must-use-plugins/">WordPress documentation</a>) which is reason enough for us not to support this.

== Screenshots ==

1. Example of a programme with the Twenty Fifteen theme
2. Example of package information, generated from Recras data
3. The Recras blocks in Gutenberg

== Changelog ==

= 4.7.2 =
* Fix page crashing when trying to show the programme of a multi-day package where the last line has no end time
* Small styling update for book process calendar

= 4.7.1 =
* Fix page crashing when trying to show the duration of a package that does not exist

= 4.7.0 =
* Fix "Class not found" error when using Composer in a theme
* Update themes for use in book processes and add two new themes

= 4.6.5 =
* Clearing cache didn't delete book process cache - fixed

= 4.6.4 =
* Fix book process not loading when using it from a widget or custom field

= 4.6.3 =
* Fix PHP 8.0 compatibility

= 4.6.2 =
* Fix book process not loading when using the Gutenberg block instead of the shortcode

= 4.6.1 =
* Prevent JavaScript error on pages without a book process

= 4.6.0 =
* Book processes can now be integrated through the plugin!
* Seamless online booking integration: Fixed slowness after clicking required checkboxes a few times

= 4.5.1 =
* Seamless online booking integration: Replace alerts with inline messages
* Seamless online booking integration: Make redirect without Mollie more robust
* Fix default selected country in contact forms

= 4.5.0 =
* The "Term for number of vouchers" is now used during voucher sales

= 4.4.0 =
* Add option to hide voucher sale quantity (defaults to 1)
* Add option to hide discount field during online booking
* Seamless online booking integration: pressing Enter in the amounts form sometimes broke the form

= 4.3.1 =
* Seamless online booking integration: Fix discount code with prefilled date

= 4.3.0 =
* Seamless online booking integration: Swedish translation is now available

= 4.2.2 =
* Setting "package_list" parameter in the online booking shortcode sometimes wasn't working properly - fixed

= 4.2.1 =
* Fixed default country for contact forms used during online booking

= 4.2.0 =
* Add localised country list (in contact forms) when the WordPress language is set to Swedish
* Fix default country for contact forms when the WordPress language is set to one of the following: Dutch (Belgium), English (Ireland), German (Austria)

= 4.1.8 =
* Fix empty package list after selecting and deselecting a package during online booking

= 4.1.7 =
* Seamless online booking integration: allow dates in the past for "relation extra field"

= 4.1.6 =
* Shortcode documentation: fix wrong example code & clarify option

= 4.1.5 =
* Fix date picker for "extra fields date type" in a contact form

= 4.1.4 =
* Seamless online booking integration: check if a discount is still valid after changing the date

= 4.1.3 =
* Fix potential conflict causing Gutenberg blocks not to load

= 4.1.2 =
* Fix potential conflict causing Gutenberg blocks not to load

= 4.1.1 =
* Seamless online booking integration: when a list of packages to show is given, don't show all of them after resetting package selection

= 4.1.0 =
* Fix potential conflict causing Gutenberg blocks not to load
* Simplify clearing the Recras cache
* Enable multiple package selection in classic (non-Gutenberg) editor

= 4.0.2 =
* Fix max length of various contact form fields

= 4.0.1 =
* A message has been added to online booking when the selected date no longer has available time slots. This can occur when the availability cache is enabled.

= 4.0.0 =
* Allow clearing of non-required radio buttons. Since this adds a button to the list which may require styling, we consider this a breaking change.
* Required checkboxes now notify you before sending the form
* Small styling fixes for WP 5.5

= Older versions =
See [the full changelog](https://github.com/Recras/recras-wordpress-plugin/blob/master/changelog.md) for older versions.

== Upgrade Notice ==
See changelog. We use semantic versioning for the plugin.

== Support ==
We would appreciate it if you use [our GitHub page](https://github.com/Recras/recras-wordpress-plugin/issues) for bug reports, pull requests and general questions. If you do not have a GitHub account, you can use the Support forum on wordpress.org.

We only support the plugin on the latest version of WordPress (which you should always use anyway!) and only on [actively supported PHP branches](https://www.php.net/supported-versions.php).

== Credits ==
* Icons from [Dashicons](https://github.com/WordPress/dashicons) by WordPress, released under the GPLv2 licence.
* Date picker is [Pikaday](https://github.com/Pikaday/Pikaday), released under the BSD/MIT licence.
* Country list is by [umpirsky](https://github.com/umpirsky/country-list), released under the MIT licence.
