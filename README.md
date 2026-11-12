# Nirmaan Builder Theme

Nirmaan Builder is a single WordPress theme with its visual Builder engine integrated directly. There is no separate Builder plugin folder or companion plugin requirement.

## Repository structure

- `nirmaan-builder/` — complete installable WordPress theme, including theme templates, Builder PHP engine, REST API, React editor source, compiled editor assets, modules, and frontend renderer.
- `tests/unit/` — isolated validator tests.
- `.github/workflows/quality.yml` — PHP lint, validator tests, editor production build, and single-theme structure checks. Compiled editor assets are committed automatically on the development branch.

## Install

1. Open the repository's `nirmaan-builder/` folder.
2. Apply its contents as one theme directory under `wp-content/themes/nirmaan-builder/`.
3. Activate **Nirmaan Builder**. The compiled React editor assets are included; no separate plugin installation or local build step is required.

If upgrading from the earlier two-package setup and the old Nirmaan Builder plugin is still active, first activate this theme, then deactivate the old plugin. A compatibility guard prevents the theme engine from loading twice while the old plugin is active. Builder documents remain in the WordPress database.

## Current implemented functionality

- WordPress theme templates, navigation, logo support, featured images, theme.json and responsive base styling.
- Integrated Builder admin screen and React editor source plus production bundle.
- Heading, Text, Button, Image, Divider and Spacer modules.
- Drag/drop insertion and reordering, element settings, document list/create/load/save.
- Autosave and basic undo/redo.
- REST API with WordPress nonce authentication and capability checks.
- Strict document validation, safe URL scheme checks, duplicate ID checks and payload limits.
- PHP server-side rendering of supported modules.
- Header/Footer Theme Builder templates with display conditions and priority resolution, integrated with this theme.
- Builder document data stored in WordPress and retained in the database.

## Not yet complete

This is not yet Divi 4 feature parity. Outstanding work includes nested Section/Row/Column layout tree, live iframe message synchronization, responsive inheritance, complete module library, advanced Theme Builder conditions and body/archive templates, dynamic content and query loops, forms/CAPTCHA/submissions, WooCommerce Builder, popup/mega-menu, global presets, import/export, backup/restore, SEO tooling, multilingual support, accessibility audit tools, full browser/E2E testing and production security review.

## Compatibility and security

Target baseline: WordPress 6.5+ and PHP 8.2+. The current Builder validates the implemented document schema and applies WordPress capability checks, nonce authentication and output escaping. This is not a claim of complete security or production certification. Test on a staging WordPress site before production use.
