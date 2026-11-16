# Nirmaan Builder – Expanded Feature Status

This branch expands the original MVP into a broader builder foundation. It is still not a complete Divi 4 replacement and must be validated on a real WordPress/WooCommerce staging site.

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
