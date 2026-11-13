# Nirmaan Builder Theme

Nirmaan Builder is a single, installable WordPress theme. The visual Builder engine is integrated inside the theme; there is no companion plugin or separate plugin package.

## Repository structure

- `nirmaan-builder/` — the complete installable theme: WordPress templates, Builder PHP engine, REST API, React editor source, compiled editor assets, module definitions and frontend renderer.
- `tests/unit/` — isolated PHP validator tests.
- `.github/workflows/quality.yml` — PHP lint, validator tests, editor build and single-theme package checks. CI uploads build artifacts; it does not write generated files back to the development branch.

## Development and installation

1. Develop and review code on the `development` branch. Keep `main` stable.
2. Install the contents of `nirmaan-builder/` as one directory at `wp-content/themes/nirmaan-builder/`.
3. Activate **Nirmaan Builder**. No plugin installation is part of the product architecture.
4. The GitHub Actions workflow builds the editor and publishes the installable theme ZIP as a workflow artifact.

## Current implementation baseline

- WordPress theme templates, navigation, logo support, featured images, theme.json and responsive base styling.
- Integrated Builder admin screen and React editor source plus production bundle.
- Six initial modules: Heading, Text, Button, Image, Divider and Spacer.
- Drag/drop insertion and reordering, module settings, document list/create/load/save, autosave and basic undo/redo.
- REST API with WordPress nonce authentication and capability checks.
- Versioned document validation, safe URL scheme checks, duplicate ID checks and payload limits.
- PHP server-side rendering for the implemented modules.
- Initial Header/Footer Theme Builder templates with basic display conditions and priority resolution.
- Builder documents stored in WordPress and retained in the database.

## Product status — foundation, not feature-complete

This baseline is not Divi 4 parity and is not production-certified. The following are product-development requirements, not completed features:

- Nested Section / Row / Column layout tree and recursive rendering.
- Real-time visual preview synchronization between editor and frontend iframe.
- Responsive device controls with inherited values and per-device overrides.
- Broad, extensible module library with advanced styling, interactions and accessibility controls.
- Full Theme Builder for headers, footers, body, archives, search, 404 and custom templates with granular include/exclude conditions.
- Dynamic data, custom fields, query loops and reusable/global elements.
- Form builder, validation, CAPTCHA integrations, email delivery and submissions storage.
- WooCommerce templates and product/cart/checkout components.
- Popup and mega-menu systems.
- Global design tokens, presets, variables and reusable styles.
- Import/export, revisions, backup/restore and migration tooling.
- SEO controls, schema support, multilingual compatibility and accessibility auditing.
- Automated WordPress integration tests, browser E2E tests, performance profiling and independent security review.

## Compatibility and security

Target baseline: WordPress 6.5+ and PHP 8.2+. Implemented endpoints use WordPress capability checks and REST nonce authentication; the current document validator restricts accepted data and the renderer escapes supported output. These controls do not constitute a complete security audit or production certification. Validate on a staging WordPress installation before production use.
