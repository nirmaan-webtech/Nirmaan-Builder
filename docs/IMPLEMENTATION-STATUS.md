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
- Nested editing is a practical first pass, not a polished freeform tree editor: no arbitrary column resizing or full keyboard accessibility. Multi-selection and responsive style copy/paste/extend are now implemented.
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

## Responsive viewport preview batch (PR in progress)
- Added desktop (1200px), tablet (768px) and mobile (390px) canvas preview controls.
- Canvas preview applies device-specific styles with desktop-to-tablet-to-mobile override order.
- Device visibility settings now hide elements in the matching editor preview.
- Preview is a constrained editor canvas, not a full browser/device emulator; verify breakpoints on real devices and staging.

## Design System and advanced styling batch (PR in progress)
- Extended global design tokens: accent, surface, border and muted colors; heading font; base font weight and line-height; spacing unit; shadow preset.
- Added corresponding Theme Options controls with strict allowlists and CSS custom properties.
- Expanded per-device element styling with typography, sizing, spacing, border, shadow, opacity and flex layout properties.
- Added renderer CSS property mapping and smoke coverage. Real WordPress UI, frontend rendering and theme-compatibility validation remain required.

## Global Styles and Reusable Design Presets (PR #10 merged)
- Added a permission-protected REST collection for global style presets, stored in a WordPress option and shared across editor documents/pages.
- Presets capture desktop, tablet and mobile element style values; editor controls support create, apply, update and delete.
- Added preset name/category validation, responsive style sanitization, and a 200-preset cap.
- PR #10 originally applied styles as snapshots; PR #11 replaces that behavior with persistent element-to-preset references and live preset resolution in the frontend renderer.
- Updating a preset now updates every linked element on the next frontend render and in the editor after the preset list refreshes.
- Elements can be detached while retaining their effective responsive styles. Deleting a preset snapshots its latest styles into linked documents, templates, library items, autosaves and revisions before removing the shared preset.
- Automated smoke tests cover the reference/sanitization contract, editor controls, renderer resolution and deletion fallback. Live WordPress staging and multi-user browser tests remain required.

## Copy, Paste and Extend Styles (PR in progress)
- Copy captures effective desktop, tablet and mobile styles, including resolved global preset values and per-device visibility.
- Paste applies the captured values as independent local styles and detaches any destination global preset link; content, element identity, children, lock/hidden state and other settings remain unchanged.
- Ctrl/Cmd-click on canvas or in Layers adds/removes elements from the selection. Extend copies the first selected element's responsive styles to the remaining selected elements.
- Copy/paste supports Ctrl/Cmd+Shift+C and Ctrl/Cmd+Shift+V; the editor controls are also available in the settings panel.
- Clipboard is session-memory only and is not persisted across reloads. Browser-level WordPress staging tests remain required.


## Visual Builder interaction improvements (PR #15)
- Added a fullscreen editor mode with an Escape-key exit.
- Added canvas zoom controls from 50% to 150%, with a reset to 100%.
- Added a right-click element context menu for duplicate, edit text, hide/show, lock/unlock and delete actions.
- Added double-click inline editing for heading, text and button content; edits are written into the document model and autosaved.
- Reused shared duplicate/delete handlers from both keyboard shortcuts and context menu actions.
- Added automated smoke coverage for the new controls and interactions.
- Remaining Phase 7 work includes live frontend preview, robust builder loading/error states, full-width preview mode and broader editor state/accessibility handling. Browser-level interaction testing remains deferred.


## Layout and Structure System – foundational controls (PR #16)
- Added Section, Row, Column and Columns modules; these nodes support nested child documents and are accepted by REST sanitization.
- New Columns layouts start with two Column children and expose Add Column / Remove Last Column controls.
- Added responsive column track definitions using CSS Grid syntax limited to safe unit tokens (fr, %, px, rem); existing width controls can size individual columns.
- Added layout settings for flex wrapping, grid columns, positioning offsets, z-index and overflow, with REST whitelist sanitization and frontend renderer mappings.
- Existing spacing, dimensions, background, border and shadow settings are now marked as covered in the roadmap; actual per-theme visual verification remains deferred.
- Automated smoke tests cover module registration, REST acceptance, safe grid-column syntax and frontend style mappings.
- Not yet covered in this batch: pointer-based drag-resizing handles, dedicated section background image/gradient controls, and advanced alignment UI.


## Layers & Wireframe – advanced layer management (PR #17)
- Added layer search that retains matching descendants and their parent hierarchy.
- Added Expand All and Collapse All controls.
- Added explicit layer reordering (up/down), nesting into the previous layout, and outdenting to the parent level.
- Added selected-element hierarchy breadcrumbs; selecting an ancestor from the navigator focuses that element.
- Drag/drop in the layer tree can now move nodes into layout containers as well as reorder siblings; cycle moves are blocked.
- Existing rename, visibility, lock, wireframe and visual-view controls retained.
- Automated smoke tests cover the new controls and cycle guard. WordPress/browser interaction testing remains deferred.

## Phase 19 – Forms & Advanced Features completion batch
- Added Radio fields alongside text, email, textarea, number, telephone, URL, date, select and checkbox fields.
- Added conditional field visibility driven by an earlier field's exact answer. Hidden conditional fields are disabled in the browser and excluded from required-field validation on the server.
- Added configurable success messages.
- Added optional webhook delivery using WordPress safe HTTP requests, bounded timeout and redirects disabled. Submission storage remains authoritative if the remote endpoint is unavailable.
- Added the `nirmaan_builder_form_submitted` action hook for custom PHP integrations; arbitrary PHP execution is intentionally not exposed in the visual editor.
- Added nonce- and capability-protected deletion for stored submissions.
- Extended JavaScript syntax validation to include the frontend forms script and added smoke tests for Phase 19 contracts.
- Updated the master roadmap. Automated smoke tests and PHP syntax checks must pass; live WordPress browser, email transport and webhook endpoint verification are still required before production deployment.

## Phase 20 – User Roles & Advanced Controls (initial implementation)
- Added granular capabilities for builder access, content editing, standard/advanced modules, global design presets, Library, Theme Builder, Theme Options and role management.
- Added the `Nirmaan Builder` WordPress role with restricted content editing; granted baseline builder access to the standard Editor role while keeping Theme Options, Theme Builder and role administration restricted.
- Added an administrator-protected Role Permissions screen. Administrator permissions are fixed; sensitive role-management and global-settings capabilities cannot be granted to non-administrator roles through this screen.
- Replaced broad WordPress capability checks in admin screens and REST routes with Nirmaan-specific capabilities.
- Advanced form, query-loop and WooCommerce modules are hidden from users without the advanced-module capability and rejected by REST sanitization. Users cannot edit documents/templates/library items that already contain restricted advanced modules.
- Role defaults are initialized once so later administrator changes in the Role Permissions screen are not silently overwritten on every request.
- The initial Phase 20 implementation is complete. Live WPML/Polylang and multi-role WordPress staging verification remains a release gate; see the completion notes below.


## Phase 20 – multilingual and translation-readiness completion
- Added WPML configuration for translatable Theme Builder templates and Library records. Builder JSON/meta structures are copied intact; visible node strings are registered independently with WPML String Translation to avoid editing serialized JSON.
- Added Polylang post-type registration, translated-post metadata copying and Polylang string registration/translation.
- Frontend rendering translates builder text, image alt text, form titles, labels, options and success messages using the current post/template translation context.
- Added WordPress `wp-i18n` dependencies and `wp_set_script_translations()` for the visual editor, admin actions, Gutenberg sidebar and frontend forms.
- Internationalized core admin/editor labels and documented POT/JSON catalog generation in `languages/README.md`.
- Added smoke tests for WPML/Polylang integration hooks and translation script wiring.
- Automated CI is repository-level validation only. Before production, verify the current supported WPML/Polylang versions on a real staging site, including translated page editing, language-specific templates, form labels/options, and role-based REST permissions.


## Phase 21 – WordPress Media Library integration (first implementation slice)
- Loaded the native WordPress media modal on the Visual Editor screen.
- Added a reusable Media Library field for selecting and clearing image attachments.
- Wired the Image module and Gallery/Slider item image controls to the native image picker; selected attachment URLs are stored in the existing document fields, so no new REST schema keys are introduced.
- Added source-level regression assertions for enqueueing and picker wiring.
- Remaining: verify attachment selection in a real WordPress browser, add image metadata/size controls, and implement media selection for any additional image-bearing modules. This source-level change is not browser QA.
\n\n## Phase 22 – centralized schema-driven settings field engine\n- Added one reusable typed field renderer for text, URL, number, color, textarea, select, checkbox and media controls.\n- Added shared schema dispatch for Content and layout settings so both panels use the same field pipeline.\n- Field definitions can supply options, min/max, step, required, placeholder and helper text; numeric bounds are enforced in the editor.\n- Kept existing call sites compatible through the legacy `field()` adapter.\n- Added source-level smoke coverage. Real WordPress interaction and browser validation remain outstanding.\n