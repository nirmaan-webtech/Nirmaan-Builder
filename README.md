# Nirmaan Builder

An independent WordPress visual page and theme builder plugin by Nirmaan WebTech.

## Current feature set
- Recursive page canvas with nested containers/columns, module settings and drag/drop.
- Desktop, tablet and mobile style controls with per-device visibility.
- Theme Options for site logo, favicon, global colors, typography, layout defaults and custom CSS.
- Theme Builder with Header, Body and Footer parts, include/exclude conditions and priority ordering.
- Reusable Library layouts, JSON import/export and editable live-reference Global Elements.
- WooCommerce product, product archive, cart, checkout and mini-cart modules using native WooCommerce output.
- Authenticated REST API with server-side allow-list sanitization.

This is an evolving builder foundation, **not a complete Divi 4 replacement**. Review [implementation status](docs/IMPLEMENTATION-STATUS.md) before use.

## Requirements
- WordPress 6.4+
- PHP 8.0+
- Node.js 20+ for JavaScript syntax checks and smoke tests
- WooCommerce for WooCommerce modules

## Installation
1. Download the branch ZIP or the reviewed pull request.
2. Copy the plugin folder into `wp-content/plugins/nirmaan-builder/`.
3. Activate Nirmaan Builder.
4. Edit a Page/Post with the Nirmaan Builder metabox, or open **Nirmaan Builder → Theme Builder**.
5. Configure branding and global design defaults under **Nirmaan Builder → Theme Option**.
6. Manage reusable layouts and global elements under **Nirmaan Builder → Library**.

## Development checks
- `npm run build` – JavaScript syntax validation.
- `npm test` – builder feature smoke tests.
- GitHub Actions – PHP syntax validation and JavaScript checks.

Install on staging and validate with the target theme, WooCommerce version, checkout/payment gateways and extensions before production deployment.
