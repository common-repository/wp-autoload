=== WP Autoload ===
Contributors: invisnet
Tags: theme, javascript, css, templates
Requires at least: 3.2.0
Tested up to: 3.4.2
Stable tag: 2.5.1

Automatically load per-template JavaScript, CSS, and PHP files.

== Description ==

Most themes have a single stylesheet, a few JavaScript files, and all the PHP in `functions.php`. *WP Autoload* makes it trivial for theme developers to split up CSS, JS, and PHP per template, simplifying development and maintenance. It also makes it much easier to write per-template unit tests.

== Installation ==

1. Upload the plugin to your plugins directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Check the settings on the configuration page (Settings -> WP Autoload)

== Frequently Asked Questions ==

= How do I use this in my theme? =

*WP Autoload* looks for files in the same way WordPress looks for templates, but it also loads 'parent' files. Say your theme looks like this:

	themes/
	  foobar/
	    css/
	      archive.css
	      page.css
	      page-bar.css
	    inc/
	      page.php
	      page-bar.php
	    js/
	      archive.js
	      page.js
	      page-bar.js

For an archive page, *WP Autoload* will load:

* `css/archive.css`
* `js/archive.js`

No surprises there.

For a normal page, *WP Autoload* will load:

* `css/page.css`
* `inc/page.php`
* `js/page.js`

*WP Autoload* will then try to create a new `WP_Autoload_page` object. The class __must__ extend `WP_Autoload_Template`, e.g.:

	class WP_Autoload_page extends WP_Autoload_Template
	{
	  ...
	}

For a page with a slug of `bar`, *WP Autoload* will load:

* `page.css` _and_ `page-bar.css`, with `page.css` as a dependency of `page-bar.css`
* `page.php` _and_ `page-bar.php`, in that order
* `page.js` _and_ `page-bar.js`, with `page.js` as a dependency of `page-bar.js`

*WP Autoload* will then try to create a new `WP_Autoload_page_bar` object; because `page.php` is loaded first you can do this:

	class WP_Autoload_page_bar extends WP_Autoload_page
	{
	  ...
	}

= What about performance? =

For templates where there is no specific stylesheet or script file the performance implications of *WP Autoload* are negligible.
For templates with both a specific stylesheet and script file *WP Autoload* will add two requests to the overall page load sequence. Typically these extra requests have no significant impact on page load time because they are offset by the reduced size of the common stylesheet.


== Changelog ==

= 2.5.0 =
* Support for hierarchical post types.

= 2.1.3 =
* Bugfix: fix debug warning with E_ALL.

= 2.1.2 =
* Bugfix: fix loading base classes.

= 2.1.1 =
* Bugfix: remove debug code.

= 2.1.0 =
* Support for child themes.

= 2.0.0 =
* Per-template classes.
* Better sidebar handling.

= 1.1.1 =
* Dependencies configuration bug fix.

= 1.1.0 =
* Improved template part list filtering.

= 1.0.4 =
* Add enqueue_script() method to allow scripts to be enqueued after wp_head().

= 1.0.3 =
* Tag fix.

= 1.0.2 =
* Handle single dependencies better.

= 1.0.1 =
* Fix minor PHP warning.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.1.0 =
* Improved template part list filtering.

= 1.0.4 =
* Stable release.
