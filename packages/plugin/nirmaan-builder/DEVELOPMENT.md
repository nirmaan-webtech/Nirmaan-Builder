# Nirmaan Builder development

## Requirements

- PHP 8.2+
- WordPress 6.5+
- Node.js 20+
- npm

## Build the React editor

From this directory:

```bash
npm install
npm run build
```

The Vite build writes production assets to `assets/build/`. These compiled assets are required for the WordPress admin editor screen.

## Current implementation scope

The current editor milestone includes:
- React admin application.
- Layout document creation, listing, loading and saving through the WordPress REST API.
- Heading, text, button, image, divider and spacer elements.
- HTML drag-and-drop reordering and insertion.
- Basic content settings.
- Draft publishing and a server-rendered frontend preview URL.
- PHP allowlist renderer for the six implemented module types.

Not yet implemented:
- True parent/child Section → Row → Column tree.
- Live postMessage iframe synchronization.
- Responsive per-property inheritance and generated media queries.
- Undo/redo, autosave, revision restore and save-conflict detection.
- Theme Builder conditions.
- Forms, dynamic content and WooCommerce modules.
- Production packaging and release signing.

Do not describe the current milestone as a complete Divi-class builder. New modules must be added to both the editor registry and the PHP renderer, with validation and tests.
