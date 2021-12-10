=== URL Shortener ===

Description:	Create your own fully integrated URL shortener for your posts, pages and custom post types.
Version:		1.5.2
Tags:			url shortener, shortlink, shorturl, short_url, custom short url, shortcode
Author:			azurecurve
Author URI:		https://development.azurecurve.co.uk/
Plugin URI:		https://development.azurecurve.co.uk/classicpress-plugins/url-shortener/
Download link:	https://github.com/azurecurve/azrcrv-url-shortener/releases/download/v1.5.2/azrcrv-url-shortener.zip
Donate link:	https://development.azurecurve.co.uk/support-development/
Requires PHP:	5.6
Requires:		1.0.0
Tested:			4.9.99
Text Domain:	url-shortener
Domain Path:	/languages
License: 		GPLv2 or later
License URI: 	http://www.gnu.org/licenses/gpl-2.0.html

Create your own fully integrated URL shortener for your posts, pages and custom post types.

== Description ==

# Description

Create your own fully integrated URL shortener for your posts, pages and custom post types.

Shortcode `[short-url]` or `*function azrcrv_urls_get_custom_shortlink` can be called from themes or other plugins to retrieve the custom short URL.

Example shortcode usage:
```echo do_shortcode('[short-url]');```

Example function usage:
```
if (function_exists('azrcrv_urls_get_custom_shortlink')){
	printf(' <span class="short-link">%s</span>', '<a href="'.azrcrv_urls_get_custom_shortlink().'" title="Shortlink to '.the_title_attribute('echo=0').'" rel="bookmark">'.'Shortlink'.'</a>');
}
```

Settings page allows options for short URL generation to be configured.

This plugin is multisite compatible; each site will need settings to be configured in the admin dashboard.

== Installation ==

# Installation Instructions

* Download the latest release of the plugin from [GitHub](https://github.com/azurecurve/azrcrv-url-shortener/releases/latest/).
* Upload the entire zip file using the Plugins upload function in your ClassicPress admin panel.
* Activate the plugin.
* Configure relevant settings via the configuration page in the admin control panel (azurecurve menu).

== Frequently Asked Questions ==

# Frequently Asked Questions

### Can I translate this plugin?
Yes, the .pot file is in the plugins languages folder; if you do translate this plugin, please sent the .po and .mo files to translations@azurecurve.co.uk for inclusion in the next version (full credit will be given).

### Is this plugin compatible with both WordPress and ClassicPress?
This plugin is developed for ClassicPress, but will likely work on WordPress.

== Changelog ==

# Changelog

### [Version 1.5.2](https://github.com/azurecurve/azrcrv-url-shortener/releases/tag/v1.5.2)
 * Update azurecurve menu.
 * Update readme files.

### [Version 1.5.1](https://github.com/azurecurve/azrcrv-url-shortener/releases/tag/v1.5.1)
 * Update azurecurve menu and logo.
 
### [Version 1.5.0](https://github.com/azurecurve/azrcrv-url-shortener/releases/tag/v1.5.0)
 * Update azurecurve menu.

### [Version 1.4.1](https://github.com/azurecurve/azrcrv-url-shortener/releases/tag/v1.4.1)
 * Fix version number bug.
 
### [Version 1.4.0](https://github.com/azurecurve/azrcrv-url-shortener/releases/tag/v1.4.0)
 * Fix plugin action link to use admin_url() function.
 * Rewrite option handling so defaults not stored in database on plugin initialisation.
 * Add plugin icon and banner.
 * Update azurecurve plugin menu.

### [Version 1.3.0](https://github.com/azurecurve/azrcrv-url-shortener/releases/tag/v1.3.0)
 * Return custom short URL instead of standard short URL when one set.

### [Version 1.2.5](https://github.com/azurecurve/azrcrv-url-shortener/releases/tag/v1.2.5)
 * Fix bug with short URL retrieval.
 
### [Version 1.2.4](https://github.com/azurecurve/azrcrv-url-shortener/releases/tag/v1.2.4)
 * Fix bug with setting of default options.
 * Fix bug with plugin menu.
 * Update plugin menu css.

### [Version 1.2.3](https://github.com/azurecurve/azrcrv-url-shortener/releases/tag/v1.2.3)
 * Rewrite default option creation function to resolve several bugs.
 * Upgrade azurecurve plugin to store available plugins in options.

### [Version 1.2.2](https://github.com/azurecurve/azrcrv-url-shortener/releases/tag/v1.2.2)
 * Update Update Manager class to v2.0.0.
 * Update action link.
 * Update azurecurve menu icon with compressed image.

### [Version 1.2.1](https://github.com/azurecurve/azrcrv-url-shortener/releases/tag/v1.2.1)
 * Fix bug with incorrect language load text domain.

### [Version 1.2.0](https://github.com/azurecurve/azrcrv-url-shortener/releases/tag/v1.2.0)
 * Add integration with Update Manager for automatic updates.
 * Fix issue with display of azurecurve menu.
 * Change settings page heading.
 * Update admin panel icon.
 * Add load_plugin_textdomain to handle translations.

### [Version 1.1.0](https://github.com/azurecurve/azrcrv-url-shortener/releases/tag/v1.1.0)
 * Update azurecurve menu for better loading of plugins.
 * Fix bug with lookup of short url.

### [Version 1.0.1](https://github.com/azurecurve/azrcrv-url-shortener/releases/tag/v1.0.1)
 * Update azurecurve menu for easier maintenance.
 * Move require of azurecurve menu below security check.

### [Version 1.0.0](https://github.com/azurecurve/azrcrv-url-shortener/releases/tag/v1.0.0)
 * Initial release for ClassicPress forked from azurecurve URL Shortener WordPress Plugin.

== Other Notes ==

# About azurecurve

**azurecurve** was one of the first plugin developers to start developing for Classicpress; all plugins are available from [azurecurve Development](https://development.azurecurve.co.uk/) and are integrated with the [Update Manager plugin](https://directory.classicpress.net/plugins/update-manager) for fully integrated, no hassle, updates.

Some of the other plugins available from **azurecurve** are:
 * BBCode - [details](https://development.azurecurve.co.uk/classicpress-plugins/bbcode/) / [download](https://github.com/azurecurve/azrcrv-bbcode/releases/latest/)
 * Breadcrumbs - [details](https://development.azurecurve.co.uk/classicpress-plugins/breadcrumbs/) / [download](https://github.com/azurecurve/azrcrv-breadcrumbs/releases/latest/)
 * Code - [details](https://development.azurecurve.co.uk/classicpress-plugins/code/) / [download](https://github.com/azurecurve/azrcrv-code/releases/latest/)
 * Filtered Categories - [details](https://development.azurecurve.co.uk/classicpress-plugins/filtered-categories/) / [download](https://github.com/azurecurve/azrcrv-filtered-categories/releases/latest/)
 * Flags - [details](https://development.azurecurve.co.uk/classicpress-plugins/flags/) / [download](https://github.com/azurecurve/azrcrv-flags/releases/latest/)
 * Get GitHub File - [details](https://development.azurecurve.co.uk/classicpress-plugins/get-github-file/) / [download](https://github.com/azurecurve/azrcrv-get-github-file/releases/latest/)
 * Maintenance Mode - [details](https://development.azurecurve.co.uk/classicpress-plugins/maintenance-mode/) / [download](https://github.com/azurecurve/azrcrv-maintenance-mode/releases/latest/)
 * Markdown - [details](https://development.azurecurve.co.uk/classicpress-plugins/markdown/) / [download](https://github.com/azurecurve/azrcrv-markdown/releases/latest/)
 * Series Index - [details](https://development.azurecurve.co.uk/classicpress-plugins/series-index/) / [download](https://github.com/azurecurve/azrcrv-series-index/releases/latest/)
 * Shortcodes in Comments - [details](https://development.azurecurve.co.uk/classicpress-plugins/shortcodes-in-comments/) / [download](https://github.com/azurecurve/azrcrv-shortcodes-in-comments/releases/latest/)
