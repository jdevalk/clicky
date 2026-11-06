=== Clicky for WordPress ===
Contributors: joostdevalk
Tags: analytics, statistics, clicky, getclicky, affiliate
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Requires at least: 5.9
Tested up to: 6.1
Requires PHP: 7.4
Stable tag: 2.1

Integrates the Clicky web analytics service into your blog and adds features for comment tracking & more.

== Description ==

Integrates the [Clicky web analytics](http://clicky.com/145844) service into your blog.

* Automatically adding your Clicky tracking code everywhere.
* Option to ignore admins.
* Option to store names of commenters.
* Option to disable the use of cookies.
* Stores comments as an action using the Clicky [internal data logging API](https://secure.getclicky.com/help/customization/manual#internal). This requires a [pro account](http://clicky.com/145844) to work.
* Option to track posts &amp; pages as goals and assign a revenue to that page or post.
* An overview of your site's statistics on your dashboard.
* Easily add outbound link pattern matching for affiliate links etc.
* Adds a small stats indicator of visitors in the last 48 to the WordPress toolbar.

=== Have you found an issue? ===

If you have bugs to report, please go to [the plugin's GitHub repository](https://github.com/jdevalk/clicky). For security issues, please use our [vulnerability disclosure program](https://patchstack.com/database/vdp/clicky), which is managed by PatchStack. They will assist you with verification, CVE assignment, and, of course, notify us.

== Installation ==

1. Upload the `clicky` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Enter your Site ID, Key and Admin Key.
1. You're done, Clicky should start working.

== Frequently Asked Questions ==

= Was this plugin previously called Clicky by Yoast? =

Yes. [Joost de Valk](https://joost.blog) originally wrote this plugin while he was still at Yoast, which is why it was published as Clicky by Yoast. When he left the company, he took the plugin with him. He now maintains it independently, under the name Clicky for WordPress.

= Why don't I see any stats, or why is Clicky not tracking? =

First, check that your Site ID, Site Key and Admin Site Key are entered under Settings &rarr; Clicky. You can find them in your Clicky account. The stats shown inside WordPress specifically require a correct Admin Site Key.

If you have enabled "Ignore Admin users" under Settings &rarr; Clicky &rarr; Advanced Settings, your own visits while logged in as an administrator are not tracked, by design. Visit your site while logged out, or in a private browser window, to test tracking.

If you use a caching plugin, make sure it does not serve cached pages to logged in users. Otherwise the setting above can't work, and admin users will still be tracked.

= How do I disable the stats graph in the admin bar? =

Go to Settings &rarr; Clicky &rarr; Advanced Settings and check "Disable Admin Bar stats".

= Does this plugin work with WordPress multisite? =

Yes. The settings are stored per site, so each site in the network has its own Site ID, Site Key and Admin Site Key, and can point to its own Clicky site.

= Can I add my own clicky_custom data? =

Yes. The plugin outputs its tracking script in your site's `wp_head` and never overwrites an existing `clicky_custom` object, so you can define your own `clicky_custom` values before that and the plugin will leave them alone.

= Where is the tracking code inserted, and does it slow my site down? =

The tracking code is added to the `<head>` of your site and loads asynchronously from Clicky's CDN, so it does not block your pages from rendering. Nothing else is loaded for your visitors.

= Can I use Clicky without cookies? =

Yes. Go to Settings &rarr; Clicky &rarr; Advanced Settings and check "Disable cookies". Visitors will then be told apart based on their IP address instead, which does reduce the quality of the tracking.

= Does this plugin track WooCommerce orders? =

No, there is no built-in WooCommerce integration. You can track posts and pages as goals and assign a revenue to them, using the Clicky Goal Tracking box in the editor. That works for any post type.

== Screenshots ==

1. The Clicky settings page: basic settings.
2. The Clicky settings page: advanced settings and outbound link patterns.
3. The Clicky metabox on posts and pages.
4. The stats indicator on the WordPress Toolbar.

== Changelog ==

= 2.1 =

Released November 6th, 2026

* Drops support for PHP versions below 7.4. The plugin now requires PHP 7.4 or higher.

* Rebrands the plugin as Clicky for WordPress, with new plugin artwork.
* Removes the Yoast news and social links box from the settings page.
* Deprecates the `Yoast\WP\Clicky\admin_footer` action hook in favour of the `clicky/admin_footer` hook.
* Renames the `Yoast_Clicky` class to `Clicky_Plugin`. The old name remains available as an alias.
* Removes the translation promo module and its unprotected dismiss handler, and links to the plugin's translation project on translate.wordpress.org from the settings page instead.
* Fixes the setting descriptions on the Advanced settings tab, which showed HTML tags such as `<em>` and `<code>` as text.

= 2.0 =

Released November 12th, 2020

* Loads the admin-side scripts on the Clicky settings page only. Props to [erommel](https://github.com/erommel).
* Deprecates the `clicky_admin_footer` action hook in favour of the `Yoast\WP\Clicky\admin_footer` hook.
* Fixes a frontend CSS bug.
* Fixes a bug where the visitor graph in the admin bar would be shown even if the site information was invalid.
* Fixes a bug where the Clicky blog feed wasn't retrieved properly.
* Stops explicit support for PHP < 5.6 / WP < 5.2.

= 1.9 =

Released November 25th, 2019

* Updated required PHP version to 5.6.
* Lots of code style and escaping improvements.
* Fixes a PHP warning for a file that's not found in some setups, props [Miller Media](https://github.com/Miller-Media). 

= 1.8 =

Released November 25th, 2019

* Lots of code style and escaping improvements.
* Removed banner for the Yoast website review service as it's no longer offered.
* Added RTL stylesheets.

= 1.7 =

Released February 26th, 2019

* Other improvements:
 	* Updates the Clicky tracking code to support the new format. Props to [drkskwlkr](https://github.com/drkskwlkr).

= 1.6 =

Released June 30th, 2016

* Minor security improvements:
	* Only allow expected characters in user settings thanks to a report by [Netsparker](https://netsparker.com).
	* Proper escaping of translated string in image attributes.

* Other improvements:
	* Only load the resources when required settings are entered.
	* Properly handle erroneous request responses.
	* Updated translations.
	* Updated i18n module.

= 1.5 =

Released November 22nd, 2014

Major refactor of the plugins code, without changing much functionality but improving how easy it is to maintain.

= 1.4.3 =

Released July 3rd, 2014.

* Minor security improvements:
	* Escape goal value before outputting them, both on frontend and in the admin.
	* Block direct file access to plugin files.
	* Sanitize user settings before saving.
	* Properly load CSS over HTTPS when admin is using HTTPS.

* Other improvements:
	* Remove unused functions in admin class.
	* Made clicky script output smaller by removing extraneous newlines.
	* Slight cosmetic changes to admin.
	* Moved screenshots to assets directory.

* Inline documentation:
	* Change link to goals setup to link straight to current site's goal setup page.
	* Change links to point to clicky.com instead of getclicky.com.
	* Change all links to yoast.com to point to https instead of http.

= 1.4.2.4 =

* Minor stability improvements to code.
* Improved code formatting.
* i18n updates
	* Replaced mangled fr_FR files
	* Updated tr_TK

= 1.4.2.3 =

* Don't overwrite the `clicky_custom` variable when it's already there.
* Added phpDoc
* Use https instead of http for showing stats page
* The `<noscript>`-part now uses `//` instead of `http://` so it can switch to https.
* Added Turkish (tr_TK)

= 1.4.2.2 =

* Prevent collission with utm username param.

= 1.4.2.1 =

* Fixed a few notices.
* Fixed a possible crash due to not having imagemagick compiled in.
* Added a whole bunch of translations.

= 1.4.2 =

* Option to disable the use of cookies.
* Fix some notices.

= 1.4.1.3 =

* Fix for another "possible" error.

= 1.4.1.2 =

* Fixed divide by zero on site with empty stats.
* Removed a no longer used hook.

= 1.4.1.1 =

* Removed a ) too much. I suck at coding, sometimes.

= 1.4.1 =

* Forgot to remove a piece of code that's no longer used.

= 1.4 =

* Interface cleanup, interface no longer breaks on smaller resolutions.
* No more unneeded JavaScript being loaded on the admin pages.
* Made the Goal Tracking box work for all post types and made it smaller.
* Removed some code that was no longer used.
* Removed dashboard widget, added Yoast news widget to the settings page.
* Added a small stats indicator of visitors in the last 48 to the WordPress toolbar.

= 1.3 =

* Added support for the new [outbound link pattern matching](http://getclicky.com/blog/287/custom-outbound-link-pattern-matching-and-iframe-tracking).

= 1.2.3 =

* No longer track preview pages.
* Made the tracking code a bit simpler.

= 1.2.2 =

* Fixed error in pointing the script to in.getclicky.com instead of static.getclicky.com.

= 1.2.1 =

* Made the admin class load conditionally instead of always.
* Added donation button.
* Updated documentation.

= 1.2 =

* Update to work with the new CDN per [this post](http://getclicky.com/blog/264/important-were-moving-to-a-real-cdn-soon-depending-on-how-youve-set-up-tracking-you-may-need-to-take-action).
* Remove clicky.me integration that was no longer working anyway due to Twitter API changes.

= 1.1.5 =

* Tiny improvement in error handling to prevent "Cannot use object of type WP_Error as array" error.

= 1.1.4 =

* Minor backend improvements.

= 1.1.3 =

* Fixed bug that would cause tracking not to work if commenter name tracking was not enabled.

= 1.1.2 =

* Another tiny bugfix on the js outputted.

= 1.1.1 =

* Removed tracking of category and author due to complaints. We'll see later if there's a way to add it back in more wisely.

= 1.1 =

* Switched to the new asynchronous javascript.
* Added tracking of category and author as custom variables.
* Fixed the bug that caused tweeting of updated posts.
* Some slight updates to the backend.

= 1.0.6 =

* Auto-tweeting now only happens when a post is first published.
* Made sure there are no spaces in site ID, site key and admin site key are always trimmed.
* Added extra check to make sure clicky.me returned a valid short URL before tweeting.

= 1.0.5 =

* Minor copy changes.

= 1.0.4 =

* Made sure there's no spaces in the Site ID when displaying it, should solve blank Dashboard Stats Page issue.

= 1.0.3 =

* Made all strings localizable (is that even a word).
* Added .pot file to allow localization.
* Added a Dutch translation.

= 1.0.2 =

* Added option to auto tweet articles, removing the checkbox from the add post screen. This makes sure auto tweet works when you're posting from within an external editor.

= 1.0.1 =

* Added prefix option for Tweets that are sent out on publish.

= 1.0 =

* Initial release.
