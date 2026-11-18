# Nirmaan Builder WordPress Theme

Nirmaan Builder is an installable WordPress **theme** by Nirmaan WebTech. The visual builder, Theme Options, Theme Builder, Library, forms, role controls and WooCommerce elements are integrated into the theme. No separate Nirmaan Builder plugin is required.

## Included builder capabilities
- Dedicated visual Page Builder canvas with Section → Row → Column → Module hierarchy, device previews, live settings inspector, undo/redo, JSON import/export and autosave.
- Desktop, tablet and mobile styles with per-device visibility.
- Theme Options for branding, global colors, typography, layout defaults and custom CSS.
- Theme Builder with Header, Body and Footer parts, display conditions and priority.
- Reusable Library layouts, JSON import/export and live-reference Global Elements.
- Forms, submission management and role-based builder permissions.
- WPML and Polylang integration hooks and translation-ready strings.
- WooCommerce product, archive, cart, checkout and mini-cart elements when WooCommerce is active.

This remains an evolving builder foundation, **not a complete Divi 4 replacement**. Review docs/IMPLEMENTATION-STATUS.md.

## Requirements
- WordPress 6.4+
- PHP 8.0+
- Node.js 20+ for JavaScript syntax checks and smoke tests
- WooCommerce only for WooCommerce elements

## Install as a theme
1. Download the ZIP for branch `feature/divi4-canvas-parity` (this branch includes the theme conversion and the latest Canvas changes).
2. In WordPress, open **Appearance → Themes → Add New → Upload Theme**.
3. Upload the ZIP and click **Install Now → Activate**.
4. Open **Nirmaan Builder → Theme Option** to configure global design settings.
5. Open **Nirmaan Builder → Page Builder** to open a page in the visual canvas.\n6. Open **Nirmaan Builder → Theme Builder** to create Header, Body and Footer templates.
7. Use the Nirmaan Builder Page Builder screen to edit page and post content visually.

The theme includes a standard WordPress header/footer fallback. An assigned Nirmaan Builder Header or Footer template replaces the corresponding fallback.

## Important migration note
This release changes Nirmaan Builder from a plugin package to a theme package. WordPress stores posts, builder documents, templates, library entries and settings in the database, so switching the active theme does not intentionally delete those records. However, keep a database backup before changing themes. Do not keep an older Nirmaan Builder plugin active alongside this theme.

## Development checks
- npm run build – JavaScript syntax validation.
- npm test – builder feature smoke tests.
- GitHub Actions – PHP syntax validation and JavaScript checks.

Automated checks do not replace WordPress staging/browser testing. Validate the theme with the target WordPress version, active plugins, WooCommerce checkout/payment gateways and multilingual plugins before production use.


## Custom module API
Third-party theme extensions can register module definitions with the `nirmaan_builder_custom_module_definitions` filter. Return an associative array keyed by a lowercase module type, with `label` and optional `default_settings`. Sanitize custom settings through `nirmaan_builder_sanitize_custom_module_settings` and return frontend markup through `nirmaan_builder_render_custom_module`. The renderer applies `wp_kses_post()` to custom output; modules requiring markup outside that allow-list should provide an appropriate safe WordPress rendering integration rather than disabling output sanitization.
