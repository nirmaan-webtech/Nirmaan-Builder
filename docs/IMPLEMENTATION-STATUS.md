# Nirmaan Builder – Expanded Feature Status

This branch expands the original MVP into a broader builder foundation. It is still not a complete Divi 4 replacement and must be validated on a real WordPress/WooCommerce staging site.

## Additional implementation in the current development branch
- Theme Options screen with persistent logo and favicon attachment selection, global color palette, typography, container width, section spacing, button radius and custom CSS.
- Privileged REST settings endpoints restricted to users with `manage_options`; settings values are sanitized and design tokens are exposed as frontend CSS variables.
- Configured logo and favicon are integrated through WordPress `get_custom_logo` and `get_site_icon_url` filters.
- Frontend base typography, colors, button color/radius and builder container width consume the global design tokens.

These Theme Options additions were merged into `main` in PR #2. They still require runtime verification on WordPress.

## Implemented in this branch
- Recursive nested containers and columns in the editor document model; nested node selection, insertion, deletion and drag/drop into containers.
- Per-element desktop, tablet and mobile style settings, including visibility toggles. Responsive settings are sanitized server-side and rendered as media-query CSS.
- Theme Builder include/exclude multi-select rules and template priority. Higher priority wins; exclusions veto a template match.
- Template conditions for entire site, front page, pages, posts, archives, search, 404, WooCommerce product, shop, product archive, cart and checkout contexts.
- Library JSON export/import with server-side document sanitization.
- Global elements stored as library records and rendered by live reference, so editing the global library document updates every page that references it.
- WooCommerce product modules plus product archive, cart, checkout and mini-cart modules using WooCommerce's native shortcodes/template functions.
- Node-based JavaScript syntax checks and feature smoke tests in CI; PHP syntax checks remain enabled.

## Known limitations / not yet production complete
- Nested editing is a practical first pass, not a polished freeform tree editor: no arbitrary column resizing, multi-select, clipboard, undo/redo, or full keyboard accessibility.
- Responsive controls cover common CSS properties and hide/show per device; no breakpoint manager, visual viewport preview, or complete responsive style inheritance UI.
- Template conditions are a multi-select set of common contexts; per-taxonomy, per-author, per-user-role, custom URL and arbitrary object targeting are not yet exposed. Singular-ID and custom post-type rules are supported by the data model but not yet fully surfaced in the admin UI.
- Global elements are live references for frontend rendering and can be edited from the Library screen; there is no revision history, permissions/locking, or change preview.
- Library import/export currently supports the Nirmaan Builder JSON format only; no third-party builder conversion or media bundle migration.
- Cart/checkout output delegates to WooCommerce native shortcodes. This is not a full visual cart/checkout component designer and must be checked against the active theme, checkout blocks, payment gateways and extensions.
- No custom form builder, animation timeline, role editor, or complete revision/history interface.
- Automated tests are smoke/static feature checks plus PHP lint, not full WordPress integration or browser end-to-end tests.
- Header/footer rendering requires the active theme to call `wp_body_open()` and `wp_footer()`.
- Test on staging with the target theme, WordPress version, WooCommerce version, payment/shipping plugins and product types before production deployment.

## Verification
GitHub Actions checks PHP syntax, JavaScript syntax and builder feature smoke tests. Passing these checks does not establish full runtime compatibility or production readiness.

## Latest editor development batch (PR in progress)
- Added a Layers panel that lists nested elements and selects a node when clicked.
- Added element duplication from canvas controls and `Ctrl/Cmd+D`.
- Added in-memory Undo/Redo snapshots with `Ctrl/Cmd+Z`, `Ctrl/Cmd+Shift+Z` and `Ctrl/Cmd+Y`.
- Added Delete/Backspace keyboard deletion when focus is outside form controls.
- Added smoke tests for these editor controls.

Limitations: the Layers panel is a basic hierarchical selector, not yet drag-reorderable, collapsible, renameable, or independently hide/lock capable. Undo/Redo is session-memory only; it is not autosave, revision history, or crash recovery. Keyboard shortcuts are not a replacement for complete keyboard accessibility. These changes require browser testing in WordPress before production use.

## Expanded Layers and Wireframe batch (PR in progress)
- Layers can expand/collapse nested containers, rename nodes, toggle visibility, and lock/unlock nodes.
- Hidden nodes are omitted from the editor canvas; locked nodes cannot be dragged from the canvas.
- Added a basic Wireframe/Visual view toggle.

Limitations: layer drag-and-drop reordering is not implemented yet. Wireframe mode is a lightweight CSS view, not a separate rendering engine. Locked nodes can still be selected from the Layers list and their settings can be changed; permission-grade locking is not intended. Verify behavior in a real browser before production.

## Layer drag-reordering batch (PR in progress)
- Layers can be reordered by drag-and-drop among siblings at the same hierarchy level.
- Cross-parent moves are intentionally rejected to avoid accidental tree restructuring; use canvas drag/drop for moves into containers.
- Locked layers cannot be dragged from the Layers panel.


## Autosave, revisions and crash recovery batch (PR in progress)
- Editor changes are saved to browser local storage immediately and autosaved to the server after a short idle period.
- On reopening the editor, a recoverable local or server draft is offered before continuing.
- Main saves create capped per-object revision snapshots (up to 30), including template-part revisions.
- Added a Revisions control to list and restore saved snapshots; restoring first snapshots the current document.
- Autosaves are user- and template-part-specific and are cleared after a successful manual save.
- This remains subject to WordPress staging tests, especially multi-user editing, storage quotas and browser crash scenarios.
