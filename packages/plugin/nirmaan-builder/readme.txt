=== Nirmaan Builder ===
Contributors: nirmaan-webtech
Tags: visual-builder, page-builder
Requires at least: 6.5
Requires PHP: 8.2
Stable tag: 0.1.0
License: GPL-2.0-or-later

Independent foundation for the Nirmaan visual page builder.

== Description ==

This is an initial plugin foundation. It registers a private Builder layout post type and a permission-checked REST endpoint for structured document JSON. A visual editor and production rendering engine are not included yet.

== Installation ==

1. Upload the nirmaan-builder directory to /wp-content/plugins/.
2. Activate it through the WordPress Plugins screen.

== Security ==

Document access uses WordPress edit capabilities. REST requests require authenticated WordPress REST API access and the normal REST nonce for cookie authentication. Values must be escaped by future renderers.

== Changelog ==

= 0.1.0 =
* Initial plugin foundation.
