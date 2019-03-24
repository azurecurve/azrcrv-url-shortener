=== URL Shortener ===
Contributors: azurecurve
Tags: url shortener, shortlink, shorturl, short_url, custom short url, shortcode
Plugin URI: https://development.azurecurve.co.uk/classicpress-plugins/url-shortener/
Donate link: https://development.azurecurve.co.uk/support-development/
Requires at least: 1.0.0
Tested up to: 1.0.0
Requires PHP: 5.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Generates a short url for all posts, pages and custom post types.

== Description ==
Generates a short url for all posts, pages and custom post types.

Shortcode [short-url] or function azrcrv_urls_get_custom_shortlink can be called from themes or other plugins to retrieve the custom short url.

Example shortcode usage: <?php echo do_shortcode('[short-url]'); ?>

Example function usage:
	<?php
	if (function_exists('azrcrv_urls_get_custom_shortlink')){
		printf(' <span class="short-link">%s</span>', '<a href="'.azrcrv_urls_get_custom_shortlink().'" title="Shortlink to '.the_title_attribute('echo=0').'" rel="bookmark">'.'Shortlink'.'</a>');
	}
	?>

Settings page allows options for short url generation to be configured.

== Installation ==
* Copy the <em>azrcrv-url-shortener</em> folder into your plugin directory.
* Activate the plugin.
* Configure relevant settings via the configuration page in the admin control panel (azurecurve menu).

== Changelog ==
Changes and feature additions for the URL Shortener plugin:
= 1.0.0 =
* First version for ClassicPress forked from azurecurve URL Shortener WordPress Plugin.

== Frequently Asked Questions ==
= Can I translate this plugin? =
* Yes, the .pot fie is in the plugin's languages folder and can also be downloaded from the plugin page on https://development.azurecurve.co.uk/; if you do translate this plugin please sent the .po and .mo files to translations@azurecurve.co.uk for inclusion in the next version (full credit will be given).
= Is this plugin compatible with both WordPress and ClassicPress? =
* This plugin is developed for ClassicPress, but will likely work on WordPress.