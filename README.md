# Nirmaan Builder

A WordPress theme and independently installable visual-builder plugin by Nirmaan WebTech.

## Repository packages

- `packages/theme/nirmaan-starter/` — standalone WordPress theme.
- `packages/plugin/nirmaan-builder/` — independent Builder plugin and React editor source.
- `.github/workflows/quality.yml` — PHP lint, React production build and package-structure checks.
- `.github/workflows/release.yml` — stable-tag ZIP packaging and GitHub Releases.

## Current implemented foundation

### Theme
- WordPress theme metadata and setup.
- Header, footer, fallback, page, single, archive, search and 404 templates.
- Navigation locations, custom logo, featured images and block editor theme.json settings.
- Responsive base styling and accessibility skip link.

### Builder plugin
- Independent plugin bootstrap.
- Private Builder document storage using a custom post type.
- React admin editor source with module panel, HTML drag-and-drop insertion/reordering, basic settings and document create/load/save.
- WordPress REST endpoints with capability checks, nonce-based cookie authentication, document structure validation and payload size limit.
- Allowlisted PHP renderer for Heading, Text, Button, Image, Divider and Spacer.
- Published layout frontend template and preview URL.
- Frontend module CSS.
- Plugin data is preserved on uninstall.

## Build the editor

Requirements: Node.js 20+, npm, PHP 8.2+, WordPress 6.5+.

From `packages/plugin/nirmaan-builder/`:

```bash
npm install
npm run build
```

The build generates `assets/build/editor.js` and `assets/build/editor.css`. Those generated files are required by the WordPress admin screen. The quality workflow also builds and uploads them as a CI artifact.

## Install locally

1. Build the plugin assets as described above.
2. Copy `packages/theme/nirmaan-starter` to `wp-content/themes/nirmaan-starter`.
3. Copy `packages/plugin/nirmaan-builder` to `wp-content/plugins/nirmaan-builder`.
4. Activate the theme and plugin.
5. Open **Nirmaan Builder** in WordPress Admin.

Stable version tags beginning with `v` generate separate theme and plugin ZIPs through the release workflow. Prerelease tags containing a hyphen are excluded.

## Not yet implemented

This repository is an active development project, not a completed Divi-class product. The following major systems remain incomplete:

- Nested Section → Row → Column layout tree.
- True live iframe postMessage synchronization.
- Per-property responsive inheritance and compiled responsive CSS.
- Autosave, undo/redo, revisions and concurrent-save conflict recovery.
- Full module library and custom module SDK.
- Theme Builder templates and condition-priority engine.
- Dynamic content providers and query/loop builder.
- Form Builder, submissions, CAPTCHA integrations and email workflow.
- WooCommerce Builder.
- Popup and mega-menu systems.
- Global design presets/components.
- Full import/export, cross-site migration, backup and restore.
- SEO tools, multilingual integration and accessibility audit dashboard.
- Complete role-based permissions, audit logs and signed update mechanism.
- End-to-end WordPress integration tests, browser tests and production security audit.

## Security and compatibility

The current REST routes use WordPress authentication, REST nonce handling and capability checks. Supported renderer output is escaped and module types are allowlisted. This is a baseline, not a guarantee of complete security. All new modules and integrations require validation, output escaping and tests.

Target baseline: WordPress 6.5+ and PHP 8.2+. Compatibility with specific hosting stacks, plugins, browsers and future WordPress versions must be verified through testing.

## Product direction

The long-term target is a Divi 4 Classic-inspired, independently implemented WordPress theme and visual builder. Do not copy Divi proprietary code, branding or assets. Do not claim feature parity until the relevant feature has been implemented and tested.
