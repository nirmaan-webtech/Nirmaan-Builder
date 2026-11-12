# Nirmaan Builder

A WordPress theme and independently installable visual-builder plugin.

## Repository status

This repository was empty at initialization. The first commit establishes the project structure and a minimal installable foundation. It is **not** a completed Divi-equivalent builder.

## Packages

- `packages/theme/nirmaan-starter/` — independent classic/hybrid WordPress theme.
- `packages/plugin/nirmaan-builder/` — independent Builder plugin foundation.
- `docs/` — architecture, requirements traceability and release notes.
- `.github/workflows/` — static checks and WordPress/PHP compatibility checks.

## Local installation

1. Copy `packages/theme/nirmaan-starter` into `wp-content/themes/nirmaan-starter`.
2. Copy `packages/plugin/nirmaan-builder` into `wp-content/plugins/nirmaan-builder`.
3. Activate the theme and plugin separately from WordPress Admin.
4. The theme must remain usable when the plugin is deactivated.

## Development requirements

- WordPress 6.5+
- PHP 8.2+
- Composer only if/when runtime dependencies are introduced.
- Node.js tooling is isolated to the editor build and must not be required for frontend rendering.

## Security baseline

All privileged operations must use WordPress capabilities and nonce verification. Validate input, sanitize stored values and escape output at render time. Never execute user-supplied PHP. Do not expose secrets to frontend code.

## Project maturity

Foundation only. Feature coverage, compatibility, accessibility, security and performance claims require automated and manual verification before a stable release.
