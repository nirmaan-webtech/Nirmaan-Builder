=== Nirmaan Builder ===
Contributors: nirmaan-webtech
Tags: visual-builder, page-builder
Requires at least: 6.5
Requires PHP: 8.2
Stable tag: 0.1.0
License: GPL-2.0-or-later

Independent foundation for the Nirmaan visual page builder.

== Description ==

The current development build includes a React admin editor with six basic modules, REST-backed layout documents, a server-side renderer, and a Theme Builder header/footer adapter for the companion Nirmaan Starter theme.

This is an active development build, not a complete Divi-class page builder or a production-certified release.

== Installation ==

1. Build the React editor assets from the plugin directory using npm install and npm run build.
2. Upload the nirmaan-builder directory to /wp-content/plugins/.
3. Activate it through the WordPress Plugins screen.
4. Open Nirmaan Builder in the WordPress admin menu.

== Security ==

REST access uses WordPress authentication, REST nonce checks for cookie authentication, and capability checks. Version-1 document payloads use a strict module/settings allowlist and bounded sizes. This is a security baseline, not a guarantee of complete security. Runtime, integration and penetration testing remain required.

== Compatibility ==

Target baseline: WordPress 6.5+ and PHP 8.2+. Header/footer replacement currently integrates with the companion Nirmaan Starter theme. Other themes require an explicit adapter.

== Changelog ==

= 0.1.0 =
* Initial development foundation.
* React editor with six basic modules.
* Strict version-1 document validation.
