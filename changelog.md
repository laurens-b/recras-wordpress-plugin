# Changelog

## 3.3.3 (2020-02-04)
* Update online booking library version: update event sending for Google Analytics integrated through Google Tag Manager

## 3.3.2 (2020-02-03)
* Update online booking library version: fix amount inputs in Firefox

## 3.3.1 (2020-01-30)
* Update online booking library version: update event sending for Google Analytics

## 3.3.0 (2020-01-23)
* Update online booking library version:
  - Don't fetch available days when no products have been selected
  - Add missing error message for minimum amount
  - Recheck vouchers when changing product amounts
  - Improve interaction (particularly on mobile)

## 3.2.2 (2020-01-07)
* Fix pre-filling amounts form

## 3.2.1 (2019-12-18)
* Update online booking library version: fix error when trying to book a product that has no material

## 3.2.0 (2019-12-09)
* Update online booking library version: show error when input is higher than allowed
* It is now possible to show a selection of packages during online booking

## 3.1.2 (2019-11-19)
* Support pre-filling package in contact forms using GET parameter "package"
* Fix layout of contact form when presented as table without labels

## 3.1.1 (2019-11-14)
* Fix whitespace in online booking/voucher sales causing problems in edge cases
* Update online booking library version: this fixes 'require X per Y' requirements (instead of just 1 per Y)
* Fix "Error: no ID set" when only having a single contact form/package/product/voucher template in Gutenberg blocks

## 3.1.0 (2019-11-12)
* Improve online booking styling in Internet Explorer
* When a contact form has a required package field, and there is only one package, pre-fill it
* Support pre-filling package in online booking using GET parameter "package"

## 3.0.3 (2019-11-11)
* Defer loading of JS polyfill & JS library
* Improve product loading & add info text when no/not all products are found

## 3.0.2 (2019-10-16)
* Fix duration and programme of some packages

## 3.0.1 (2019-10-10)
* Packages in contact forms use internal name instead of display name - fixed

## 3.0.0 (2019-10-08)
* Include widget previews for WordPress 5.3
* Online booking theme didn't set the version properly - fixed
* Update online booking library version:
  - Show discount fields straight from the start, not after entering date
  - Styling adjustment
  - Fix position of styling in the `head`, making overriding styles easier
  - Fix checking discount codes/vouchers
* Improve online booking styling in Edge
* Small online booking styling fixes/changes in both integrated themes

## 2.4.9 (2019-08-28)
* Make readme shorter and move documentation to page within WordPress
* Update online booking library version. This adds a small header to the quantity form and placeholders for its inputs
* Small styling improvements for online booking themes

## 2.4.8 (2019-07-31)
* Make plugin more robust when no Recras name has been set yet
* Small accessibility improvement

## 2.4.7 (2019-06-24)
* Make time input increase/decrease time in steps of 5 minutes
* Clarify online booking methods

## 2.4.6 (2019-05-29)
* Update online booking library version. This fixes new online booking in IE when invalid tags are used in online booking texts.
  - This shouldn't affect most people, most notably it caused problems when using the Google Analytics domain linker.

## 2.4.5 (2019-05-28)
* Fix for package duration/programme not showing in some edge cases

## 2.4.4 (2019-05-22)
* Update online booking library version. This fixes the availability check for packages with "booking size" in some edge cases

## 2.4.3 (2019-05-14)
* Fix for empty non-required "booking - package" field in contact forms

## 2.4.2 (2019-05-06)
* Not selecting a pre-filled package with new online booking was broken - fixed
* Add info messages for packages not showing up

## 2.4.1 (2019-05-06)
* Styling fix for "Recras Blue" theme
* Fix Gutenberg translations

## 2.4.0 (2019-04-30)
* Date/Time input update:
  - Localise date/time placeholders
  - Remove time picker
  - Replace date picker (saves over 110 kB, 1 DNS request, and 4 HTTP requests)
* Add some German translations
* Add ability to show voucher information

## 2.3.9 (2019-04-23)
* Update online booking library version. This fixed "customer type" fields in contact forms used during online bookings.

## 2.3.8 (2019-04-19)
* Update online booking library version:
  - Styling fix for Internet Explorer
  - Add missing maximum value for the booking size field

## 2.3.7 (2019-04-15)
* Update online booking library version. This fixes new online booking in IE.

## 2.3.6 (2019-04-12)
* Update online booking library version. This fixes the sending of Google Analytics events.

## 2.3.5 (2019-04-12)
* Fix online booking/voucher sales when using them from Advanced Custom Fields or similar solutions
* Update online booking library version:
  - Add message when entering an quantity more than the maximum of a line
  - If there is only one available timeslot for the selected date, select it automatically

## 2.3.4 (2019-03-27)
* Fix default contact form setting for Gutenberg contact form block
* Fix certain Gutenberg toggles on re-edit

## 2.3.3 (2019-03-26)
* Fix new online booking in IE

## 2.3.2 (2019-03-25)
* Package block only showed packages that were bookable online - fixed
* Voucher templates are now cached along with everything else

## 2.3.1 (2019-03-19)
* Fix missing "Start time" and "Show header" options in Package block

## 2.3.0 (2019-03-04)
* Add Google Analytics integration
* Add ability to pre-fill amounts form

## 2.2.2 (2019-02-28)
* Fix plugin on WordPress 4

## 2.2.1 (2019-02-28)
* Fix values not being set properly after opening a saved page (Gutenberg only)

## 2.2.0 (2019-02-26)
* Make plugin compatible with Gutenberg/WordPress 5+
* Update "classic editor" icons to reflect the icons used for Gutenberg blocks

## 2.1.2 (2019-01-30)
Update online booking library version. This fixes the minimum amount of "fixed programme" input fields.

## 2.1.1 (2019-01-24)
Update online booking library version. This fixes a few things with the new online booking method when you are logged in to your own Recras.

## 2.1.0 (2019-01-15)
Choose between drop-down or radio buttons for single-choice fields (customer type, package selection, gender, and single choice) in contact forms

## 2.0.7 (2019-01-08)
* "Price excl. VAT" for products is not supported anymore due to API change
* Update online booking library version:
  - Disable date selection if there are min/max amount or dependency errors
  - Fix "NaN" price when booking size input field was cleared
  - Add option to show/hide programme times preview for online bookings (hidden by default)
  - Add loading indicator when loading available time slots

## 2.0.6 (2018-11-30)
Update online booking library version:
* Don't scroll to amounts form when package is pre-selected
* Fixed attachments being shown even when "Send standard attachments" was disabled for a package
* Show console warning when you are logged in to the Recras being used

## 2.0.5 (2018-11-28)
Update online booking library version:
  * Fixed a bunch of minor bugs and inconsistencies
  * Show line price based on amount selected

## 2.0.4 (2018-11-20)
Update online booking library version

## 2.0.3 (2018-11-20)
* Voucher sales module without pre-selected template wasn't working - fixed
* Update online booking library version:
  * Implement `keuze_enkel` fields in contact form
  * Fix "NaN" price when amount input field was cleared
  * Fix "Programme amounts are invalid" error in some cases
  * Voucher sales showed templates without contact form when logged in - fixed

## 2.0.2 (2018-11-12)
Update online booking library version (check booking size lines for minimum amount)

## 2.0.1 (2018-11-09)
Fixed a problem with the previous release

## 2.0.0 (2018-11-09)
**Major release** This version might break things. Please read the following carefully:

* Added:
  - Ability to show package/product image tag (instead of bare URL and having to add `<img>` tag manually)
  - Add "Choice - single" field to contact forms
* Fixed:
  - Position of datepicker popup on mobile
  - "Customer type" selection in contact forms
* Changed: the discount and voucher fields for online bookings are now combined. This means there are some backward incompatible CSS changes. If you are **not** using an online booking theme, you might need to make some changes to your CSS when installing this version. Details on these changes can be found in the [changelog for the library](https://github.com/Recras/online-booking-js/blob/master/changelog.md#080-2018-10-29)
* Removed: `[arrangement]` and `[recras-arrangement]` shortcodes. These have been replaced by `[recras-package]` over 1.5 years ago.

## 1.15.2 (2018-10-19)
* Update online booking library version (fixes prices sometimes being shown incorrectly)

## 1.15.1 (2018-10-05)
* Update online booking library version (fixes online bookings that can only be paid afterwards)

## 1.15.0 (2018-10-01)
* Add themes for new online booking method
* Enable "Use new library" by default
* Update online booking library version:
  - Show reasons why 'Book now' button is disabled
  - Fix disabled 'Book now' button after changing date/time
  - Fixes potential race condition

## 1.14.6 (2018-09-10)
* Better loading of polyfill
* Update online booking library version (fixes minimum amount of booking size row)

## 1.14.5 (2018-07-27)
* No changes. Releasing previous version failed, trying to re-release.

## 1.14.4 (2018-07-26)
* Update online booking library version

## 1.14.3 (2018-07-17)
* Update online booking library version

## 1.14.2 (2018-07-05)
* Fix online booking library not loading properly

## 1.14.1 (2018-07-05)
* Update online booking library version

## 1.14.0 (2018-06-13)
* Add option to try out the new online booking library

## 1.13.0 (2018-06-11)
* Add voucher sales module

## 1.12.3 (2018-06-08)
* Fix contact form submission when jQuery is loaded too late

## 1.12.2 (2018-06-08)
* Show error instead of crashing when package programme is empty

## 1.12.1 (2018-06-06)
* Enable automatic resizing initially for availability calendar

## 1.12.0 (2018-04-17)
* Add option to disable automatic resizing of online booking & availability iframes

## 1.11.5 (2018-03-27)
* Fix selection of newsletters in a contact form

## 1.11.4 (2017-11-27)
* Fix 500 error, sorry about that :(

## 1.11.3 (2017-11-24)
* Add explanation why sometimes packages are not available

## 1.11.2 (2017-07-03)
* Revert iframe change from previous version - did more harm than good

## 1.11.1 (2017-06-06)
* Show more helpful errors if something goes wrong
* Fix iframe heights if there is more than one iframe on a page

## 1.11.0 (2017-05-02)
* Added `[recras-availability]` shortcode to show availability calendar
* Rename "arrangement" to "package" to reflect text change in Recras
* Deprecated `[recras-arrangement]` shortcode in favour of `[recras-package]`
* New icons for TinyMCE buttons
* Fix loading icon when submitting a contact form
* Fix empty text on submit button after submitting a contact form

## 1.10.2 (2017-03-31)
Fix detailed description of arrangements

## 1.10.1 (2017-03-31)
Fix available arrangements for a contact form

## 1.10.0 (2017-03-06)
* Don't show seconds in arrangement/product durations
* Use display name instead of internal name for arrangements

## 1.9.1 & 1.9.2 (2017-02-20)
* Fix bug with iframe height

## 1.9.0 (2017-02-20)
* Listen for height-update message

## 1.8.1.1 (2016-12-09)
* Updated "Tested up to" version to 4.7

## 1.8.1 (2016-07-19)
* Fix problem with previous version not loading

## 1.8.0 (2016-07-18)
* Add image URL and description to arrangements
* The plugin is now available on Packagist, which means you can use Composer to install the plugin.
* Various small bug fixes

## 1.7.1 (2016-07-01)
* The Settings page is now hidden if you don't have permission to see it.

## 1.7.0 (2016-04-13)
* The online booking button now allows you to pre-select an arrangement. Only arrangements that are bookable online are included.

## 1.6.1 (2016-04-08)
Fixed a bug with contact form arrangements cache

## 1.6.0 (2016-03-23)
* Simplified emptying caches and added more explanation
* Arrangements in a contact form are now sorted alphabetically
* Added workaround for dropdown placeholders

## 1.5.0 (2016-03-08)
Succesfully submitting a contact form will now empty the form afterwards

## 1.4.0 (2016-02-23)
* Add optional date/time pickers

## 1.3.4 (2016-02-02)
* Fixed redirect URL after clearing cache
* Add placeholders to textareas
* Make "Unknown" the default gender, rather than "Male"
* Fix submitting a contact form on a page that has that same form multiple times

## 1.3.3 (2016-01-11)
* Sort products alphabetically
* Move stuff from Settings to a separate Recras page in the menu

## 1.3.2 (2016-01-08)
* Lowered minimum required WP version
* Applied new classes to date/time inputs

## 1.3.1 (2016-01-08)
Fixed online booking shortcode loading a contact form instead of the booking form

## 1.3.0 (2015-12-22)
* Add caching of all external data
* Add option to use a redirect after submitting a contact form
* Remove cURL requirement (unneeded as of 1.2.1)

## 1.2.1 (2015-12-22)
* Change "keuze" on a contact form from a dropdown to checkboxes (Fixes #5)
* Bypass our own serverside submit script, use XHR instead

## 1.2.0 (2015-12-21)
* Add the following possible properties to products: `description_long`, `duration`, `image_url`, and `minimum_amount`.

## 1.1.0 (2015-12-14)
* Only show arrangements in contact form shortcode editor that belong to that contact form
* Fix some styling issues (WP 4.4 only?)
* Show error message if a contact form does not have a field for arrangements, but one is set anyway (Fixes #3)
* If an invalid arrangement is set for a contact form, show dropdown of arrangements instead of generating an invalid form

## 1.0.0 (2015-11-09)
* Add shortcode for online bookings
* Add shortcode for products
* Change the way arrangement programmes spanning multiple days are shown
* Not all arrangements are available for all contact forms - the plugin now checks if the combination is valid
* Deprecated [arrangement] shortcode in favour of [recras-arrangement]

## 0.17.1 (2015-11-03)
Rename Subdomain to Recras name

## 0.17.0 (2015-10-27)
* When not showing labels, don't show an empty `li`/`td`/`dt` element
* Allow contact form submit button text to be changed

## 0.16.1 (2015-10-27)
Fix invalid HTML when using an `ol` or `table` for the contact form

## 0.16.0 (2015-10-27)
* Don't show asterisk for required fields if labels are disabled
* Show asterisk for required fields in placeholder
* Add option for decimal separator

## 0.15.1 (2015-10-27)
Move files out of assets folder, as WordPress handles this unexpectedly

## 0.15.0 (2015-10-27)
* Add logo for plugin repository
* Fix readme

## 0.14.5 (2015-10-27)
Workaround for array constants, which are not allowed by WordPress SVN

## 0.14.4 (2015-10-23)
Add Composer autoloader to prevent users from having to install Composer

## 0.14.3 (2015-10-23)
Update arrangement duration format

## 0.14.2 (2015-10-21)
Add missing arrangement shortcode button options (duration, location)

## 0.14.1 (2015-10-21)
* Replaced icons with GPL-compatible ones
* Update readme with more information
* Hack around not being allowed to load wp-load.php
* Translation update

## 0.14.0 (2015-10-20)
Add `location` and `duration` options to arrangement shortcode

## 0.13.3 (2015-10-20)
Fix translation not being loaded

## 0.13.2 (2015-10-19)
Add options added in 0.13.0 to the editor shortcode generator button

## 0.13.1 (2015-10-19)
Refactor

## 0.13.0 (2015-10-19)
* Add option to show contact forms as lists or tables
* Add option to hide labels on contact forms
* Placeholders added on contact forms, added option to hide them

## 0.12.1 (2015-10-09)
* Minor language fix
* Update Dutch translation

## 0.12.0 (2015-10-09)
Selection of arrangement and contact form is now possible via a dropdown rather than manually entering the ID

## 0.11.0 (2015-10-09)
WordPress' editors now have a button to insert a contact form without needing to know the syntax!

## 0.10.0 (2015-10-09)
WordPress' editors now have a button to insert an arrangement without needing to know the syntax!

## 0.9.0 (2015-10-08)
* Setting the `arrangement` parameter on a contact form will select this arrangement automatically and hide the field to the user.
* Fix empty option being the last option instead of the first option on arrangement dropdowns

## 0.8.0 (2015-10-08)
If a contact form has an "arrangements" field, show all arrangements in a dropdown

## 0.7.1 (2015-10-08)
Fix translations

## 0.7.0 (2015-10-08)
* Add loading indicator when sending a contact form
* Replace contact form popups with inline text boxes
* Fix placement of error messages on pages with multiple contact forms

## 0.6.2 (2015-10-08)
Fix placement of submit button on contact forms

## 0.6.1 (2015-10-08)
Fix a typo

## 0.6.0 (2015-10-08)
Add option to disable the header of a programme

## 0.5.1 (2015-10-07)
Show notice if cURL is not installed

## 0.5.0 (2015-10-07)
Add shortcode for contact forms

## 0.4.2 (2015-10-07)
Unified CSS class names

## 0.4.1 (2015-10-07)
Proper handling of debug mode

## 0.4.0 (2015-10-06)
Add currency option, defaults to Euro (€)

## 0.3.0 (2015-10-06)
* Add Dutch translation
* Wrap output of the shortcode in `<span>`s with different classes, for styling purposes

## 0.2.1 (2015-10-06)
Don't `die()` on errors, but return error message instead

## 0.2.0 (2015-10-06)
First version!
