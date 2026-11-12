# Nirmaan Builder

A WordPress theme and independently installable visual-builder plugin by Nirmaan WebTech.

## Repository packages

- `packages/theme/nirmaan-starter/` — standalone WordPress theme.
- `packages/plugin/nirmaan-builder/` — independent Builder plugin and React editor.
- `.github/workflows/quality.yml` — PHP lint, React build and package checks.
- `.github/workflows/release.yml` — stable-tag unified Nirmaan Suite ZIP packaging.

## Implemented in the current development branch

### WordPress theme
- Independent theme bootstrap and navigation locations.
- Header, footer, fallback, page, single, archive, search and 404 templates.
- Custom logo, featured images, block editor supports and `theme.json`.
- Responsive base styling and keyboard skip link.

### Builder plugin and editor
- Independent plugin bootstrap and private document storage.
- React admin editor with module panel, HTML drag-and-drop insertion/reordering, element settings, document list/create/load/save.
- Autosave after edits and basic undo/redo history.
- Heading, Text, Button, Image, Divider and Spacer modules.
- REST API with WordPress authentication, nonce-based cookie authentication, capability checks, strict version-1 module schema validation, duplicate-ID checks, safe URL-scheme checks, module/text limits and a 1 MB document payload ceiling.
- PHP allowlist renderer for implemented modules.
- Published Builder layouts with a frontend template and preview URL.
- Header/Footer Theme Builder replacement adapter for the Nirmaan Starter theme, with display conditions and priority resolution. Other themes require an explicit adapter; generic header/footer replacement is not claimed.
- Data is preserved when the plugin is uninstalled.

## Build the editor

Requirements: Node.js 20+, npm, PHP 8.2+, WordPress 6.5+.

From `packages/plugin/nirmaan-builder/`:

```bash
npm install
npm run build
```

The build generates `assets/build/editor.js` and `assets/build/editor.css`. These assets are required by the WordPress admin editor. GitHub Actions builds them and uploads the build artifact.

## Install the unified suite

1. Download the single `nirmaan-suite.zip` asset from a stable GitHub Release.
2. In WordPress, open **Appearance → Themes → Add New → Upload Theme** and upload that ZIP.
3. Activate **Nirmaan Starter**. On activation, the theme copies the bundled Builder plugin into `wp-content/plugins/` and activates it automatically.
4. Open **Nirmaan Builder** in WordPress Admin.

The user installs one ZIP and activates one theme. Internally, the theme and Builder remain separate WordPress packages so Builder data survives theme changes and the plugin can be reused independently. Automatic setup requires filesystem write access and the activating account to have plugin-install and plugin-activation capabilities. If the host blocks filesystem writes, the admin notice explains the failure; install the bundled plugin manually or correct permissions and switch to the theme again.

For local development, build the plugin assets, then install `packages/theme/nirmaan-starter` as the theme and `packages/plugin/nirmaan-builder` as the plugin. Stable tags beginning with `v` create one unified suite ZIP. Tags containing a hyphen are excluded from stable releases.

## Explicitly not complete yet

This branch is active development, not a finished Divi-class builder. Remaining major scope includes:

- Nested Section → Row → Column editor tree and full layout controls.
- Live iframe postMessage synchronization (current preview is a saved/published frontend URL).
- Per-property responsive inheritance and generated responsive CSS.
- Full revisions, save-conflict recovery and autosave recovery UI.
- Complete module library and custom module SDK.
- Theme Builder body, archive, product and advanced condition types; generic adapter support for third-party themes.
- Dynamic content providers, ACF mapping and query/loop builder.
- Form Builder, CAPTCHA providers, submissions, notifications and retention.
- WooCommerce Builder.
- Popup and mega-menu systems.
- Global presets/components and complete design tokens UI.
- Import/export, cross-site migration, backup and restore.
- SEO, multilingual, accessibility audit and complete role-based permissions.
- Signed update packages, automatic update integration and recovery.
- End-to-end WordPress integration tests, browser tests, database transaction/concurrency protection, and production security audit.

## Security and compatibility

The current REST endpoints use WordPress capability checks and REST nonce-based cookie authentication. Version-1 documents are validated against a strict module/settings allowlist, with element-count, text-size, duplicate-ID and URL-scheme checks. Supported module types are allowlisted and renderer output is escaped. Save input is validated before writes, but the persistence flow is not yet a database transaction and does not yet provide concurrent-edit conflict detection. This is a security baseline, not a guarantee of complete security. New modules and integrations require their own validation, escaping and tests.

Target baseline: WordPress 6.5+ and PHP 8.2+. Compatibility with specific hosting stacks, third-party plugins, browsers and future WordPress versions must be verified.

## Product direction

The target is a Divi 4 Classic-inspired, independently implemented WordPress theme and visual builder. Do not copy Divi proprietary code, branding or assets. Do not claim feature parity until each feature is implemented and tested.
