# Nirmaan Builder

An independent WordPress visual theme and page builder plugin.

## Status

This repository starts with the secure plugin foundation and an extensible architecture. The full product scope is tracked in [docs/DEVELOPMENT-ROADMAP.md](docs/DEVELOPMENT-ROADMAP.md). Features are not considered complete until implemented and tested.

## Requirements

- WordPress 6.4+
- PHP 8.0+
- Node.js 20+ (for editor development)
- Composer (optional, for PHP development tooling)

## Development

The plugin entry point is `nirmaan-builder.php`. PHP classes are loaded from `includes/`; admin and frontend assets live in `assets/`; the React editor source lives in `src/`.

Install JavaScript dependencies and build assets:

```bash
npm install
npm run build
```

For development with file watching:

```bash
npm run start
```

## Installation

Copy the `nirmaan-builder` directory into `wp-content/plugins/`, then activate **Nirmaan Builder** in WordPress. The plugin currently provides the foundation and admin navigation scaffold; builder editing and rendering capabilities are roadmap items until implemented.

## Security and compatibility

All write operations must use WordPress capabilities, REST permission callbacks, nonce authentication where applicable, strict input validation, sanitization, and escaped output. Never trust client-provided template or builder data.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
