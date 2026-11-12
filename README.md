# Nirmaan Builder Theme

Nirmaan Builder is a single WordPress theme with its visual Builder engine integrated directly. There is no separate Builder plugin folder or companion plugin requirement.

## Repository structure

- `nirmaan-builder/` — complete installable WordPress theme, including theme templates, Builder PHP engine, REST API, React editor source, modules, and frontend renderer.
- `tests/unit/` — isolated validator tests.
- `.github/workflows/quality.yml` — PHP lint, validator tests, editor production build, and single-theme structure checks.

## Install

1. Open the repository's `nirmaan-builder/` folder.
2. Apply its contents as one theme directory under `wp-content/themes/nirmaan-builder/`.
3. Build the React editor assets from `wp-content/themes/nirmaan-builder/builder/` using Node.js 20+:
   `npm install` then `npm run build`.
4. Activate **Nirmaan Builder** in WordPress.

The theme bootstraps the Builder engine itself. Do not install or activate a separate Nirmaan Builder plugin.

## Current implemented functionality

- WordPress theme templates, navigation, logo support, featured images, theme.json and responsive base styling.
- Integrated Builder admin screen and React editor source.
- Heading, Text, Button, Image, Divider and Spacer modules.
- Drag/drop insertion and reordering, element settings, document list/create/load/save.
- Autosave and basic undo/redo.
- REST API with WordPress nonce authentication and capability checks.
- Strict document validation, safe URL scheme checks, duplicate ID checks and payload limits.
- PHP server-side rendering of supported modules.
- Header/Footer Theme Builder templates with display conditions and priority resolution, integrated with this theme.
- Builder document data stored in WordPress and retained independently of theme file updates.

## Not yet complete

This is not yet Divi 4 feature parity. Outstanding work includes nested Section/Row/Column layout tree, live iframe message synchronization, responsive inheritance, complete module library, advanced Theme Builder conditions and body/archive templates, dynamic content and query loops, forms/CAPTCHA/submissions, WooCommerce Builder, popup/mega-menu, global presets, import/export, backup/restore, SEO tooling, multilingual support, accessibility audit tools, full browser/E2E testing and production security review.

## Compatibility and security

Target baseline: WordPress 6.5+ and PHP 8.2+. The current Builder validates the implemented document schema and applies WordPress capability checks, nonce authentication and output escaping. This is not a claim of complete security or production certification. Test on a staging WordPress site before production use.
