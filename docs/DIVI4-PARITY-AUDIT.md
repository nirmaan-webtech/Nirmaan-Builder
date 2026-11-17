# Divi 4 Visual Builder Parity Audit

**Repository:** nirmaan-webtech/Nirmaan-Builder  
**Audited branch:** `feature/divi4-canvas-parity`  
**Audited commit:** `b2778e848c1e0cad5065c6a531d554ae771c3479`  
**Audit date:** 2026-11-17  
**Status:** Phase 1 audit complete; implementation has not started.

## Executive finding

The branch contains a functioning builder foundation, but it is not yet a Divi 4-equivalent Visual Builder. The most significant gap is not the number of available controls; it is the absence of a complete, element-aware settings model whose values are sanitized, persisted, rendered in the editor, and reproduced on the frontend.

The editor currently has one large `settings()` function. It mixes module content controls, generic responsive CSS fields, layout controls, hover controls, and tab assembly. Section, Row, Column, and most modules therefore share largely the same generic styling interface rather than exposing purpose-built settings groups.

## 1. Existing implementation verified

### Editor and document model
- The canvas is rendered from a recursive JSON document with node IDs, node types, settings, and nested children.
- Existing node types include Section, Fullwidth Section, Specialty Section, Row, Column, Container, Columns, Query Loop, common content modules, forms, and WooCommerce elements.
- Selection, multi-selection, inline text editing, duplicate/delete, context menu, drag/drop, layer navigation, copy/paste, resize handle, wireframe styling, and device preview code are present.
- Device style buckets exist for desktop, tablet, and mobile.
- Autosave, browser-local recovery, undo/redo, and REST-backed document persistence are present in code.

### Existing content controls
- Heading: content and heading level.
- Text: content.
- Image: URL and alt text.
- Button: content and link URL.
- Video: URL.
- Gallery and Slider: item image URL, alt text, link URL, title.
- Tabs and Accordion: item title and content.
- Blog and Portfolio: public post type, posts per page, grid columns.
- Query Loop: post type, posts per page, order field and direction.
- Form: title, notification email, submit label, success message, webhook URL, field type, label, required state, options, and basic conditional visibility.
- WooCommerce catalog grids: limit, columns, category slug, ordering, direction, pagination.
- Most individual WooCommerce elements show an informational message rather than element-specific editable controls.

### Existing generic design controls
- Typography: font size, weight, line height, letter spacing, text color, text alignment.
- Box/layout: width, max width, min height, padding, margin, gap, display, flex direction/wrap, justification, alignment, grid columns.
- Background: solid background color and gradient string.
- Border: color, width, style, radius.
- Effects: shadow presets, opacity, filter, transform, limited animation presets.
- Positioning: position, top/right/bottom/left, z-index, overflow.
- Hover: color, background color, border color, opacity, transform, filter.
- Visibility: hide on desktop/tablet/mobile.
- These controls are generic CSS fields; they are not a complete Divi-style option set.

## 2. Critical gaps

### P0 — Settings architecture
1. No centralized element schema defining settings by element type and tab.
2. Section, Row, Column, and module settings are not modeled as distinct option groups.
3. Content/Design/Advanced tabs exist visually, but Advanced currently receives only custom fields; most advanced options are absent.
4. Settings UI, REST sanitization, and frontend CSS output are maintained separately, creating drift risk.
5. No consistent field metadata for defaults, responsive support, validation, units, dependencies, or conditional visibility.

### P0 — Layout element settings
1. Section settings lack dedicated width, sizing, spacing, background media/overlay, parallax, divider, scroll, and layout behavior controls.
2. Row settings lack dedicated sizing, gutter, equal-height, column alignment, and row-specific layout controls.
3. Column settings lack dedicated width, padding, background, vertical alignment, order, and responsive stacking controls.
4. Specialty Section is currently a layout node, not a full specialty layout editor with dedicated structure controls.

### P0 — Module settings
1. Many modules have only minimal content fields.
2. Image lacks sizing, fit, alignment, caption, link target, border, and lightbox options.
3. Button lacks full typography, icon, icon placement, sizing, alignment, and hover controls.
4. Gallery lacks layout modes, columns, spacing, image sizing, overlay, and lightbox controls.
5. Slider lacks navigation, pagination, autoplay, transition, timing, and responsive slide settings.
6. Tabs/Accordion lack visual state, icon, interaction, and spacing controls.
7. Blog/Portfolio lack full query filters, metadata toggles, image ratios, pagination design, and card layout controls.
8. WooCommerce modules lack per-element content/design configuration.

### P1 — Design and Advanced
1. No complete typography control (font family, text transform, decoration, text shadow, etc.).
2. No background image/media library workflow, background size/position/repeat, video background, or overlay controls.
3. No complete border-side/radius controls or custom shadow editor.
4. No complete spacing controls with per-side values and linked/unlinked states.
5. No transforms editor with origin and individual transform controls.
6. No interaction/scroll effects, sticky options, transition editor, or custom CSS per element.
7. No robust responsive value inheritance and breakpoint-specific field UX.

### P1 — Canvas and interaction
1. Current drag/drop is a basic HTML drag/drop implementation, not a fully featured freeform layout interaction model.
2. Resize is a pointer-based width/min-height handle, not full column/row resizing with constraints.
3. No complete element-specific floating toolbar or full context actions.
4. Canvas previews for dynamic/query/WooCommerce elements are not equivalent to the published frontend output.
5. Real WordPress browser behavior has not been verified in this audit.

### P0 — Persistence and frontend parity
1. Every new control must be added to REST sanitization or it will be discarded on save.
2. Every supported property must be mapped to safe frontend CSS or implemented as a frontend behavior.
3. Responsive and hover styles need editor/frontend parity.
4. Existing sanitization supports a limited allow-list of CSS properties and constrained values.
5. Automated static/smoke tests do not prove save/reload behavior or browser parity.

## 3. Current CSS property pipeline

The REST sanitizer currently accepts a limited set of simple dimensions/spacing properties, colors, selected layout enums, grid-template-columns, shadow presets, gradient-only backgroundImage, selected filters/transforms, animation presets, and hover styles.

The frontend renderer maps the same general set to inline CSS and emits responsive media-query rules for tablet/mobile. Any expansion must update, test, and keep these layers synchronized. Raw arbitrary CSS must not be accepted.

## 4. Implementation sequence derived from this audit

1. **Settings schema and field engine:** introduce declarative schemas, shared field components, defaults, responsive metadata, validation, and conditional controls.
2. **REST contract:** add matching per-element sanitizers and schema-aware persistence; add round-trip tests.
3. **Section settings:** implement Content, Design, and Advanced options with actual frontend output.
4. **Row settings:** implement row-specific layout, sizing, gutter, and responsive behavior.
5. **Column settings:** implement column-specific sizing, spacing, alignment, background, and responsive stacking.
6. **Core modules:** implement complete settings in dependency order: Text/Heading, Image, Button, Divider/Spacer, Video, Gallery, Slider, Tabs, Accordion.
7. **Dynamic modules:** implement Blog, Portfolio, Query Loop, Forms, and WooCommerce-specific settings.
8. **Design system:** typography, backgrounds, borders, spacing, shadows, filters, transforms, animation, hover, sticky, visibility, and custom CSS.
9. **Canvas integration:** live preview, interactions, resize, and responsive preview must consume the same schema/state.
10. **Verification:** automated unit/static tests, PHP lint, JS syntax, WordPress staging tests, save/reload round trips, and frontend visual checks.

## 5. Definition of done for every setting

A setting is not complete merely because an input appears in the panel. It must:
- be visible only for the relevant element and tab;
- have a sensible default and validated input type/unit;
- update the selected element's document state;
- survive REST save, reload, and revision restore;
- be sanitized server-side without silently dropping valid values;
- update the canvas preview;
- render correctly on the published frontend;
- support desktop/tablet/mobile where applicable;
- have regression coverage.

## 6. Verification limitations

This audit inspected repository source and existing automated tests on the audited branch. It did not run the theme inside the user's local XAMPP/WordPress installation, and it did not perform browser-based end-to-end or visual regression testing. No claim of Divi 4 parity or production readiness is made.

## 7. Audit conclusion

The next implementation task is **Phase 2 of the roadmap: build the centralized, schema-driven settings engine and REST contract**. Do not begin by adding a large number of isolated inputs to the current `settings()` function. That would increase UI complexity without guaranteeing persistence or frontend behavior.


## 8. Follow-up source audit — exact editor/REST/frontend findings

The follow-up pass traced the settings panel, document sanitizer, frontend renderer, and existing smoke-test strategy. It confirms that the initial audit's gaps are structural, not just missing labels.

### 8.1 Settings-panel behavior verified

The current `settings()` implementation is a single function and builds controls through conditional branches. The following behaviors are directly visible in source:

| Element type | Current editable content/settings | Finding |
|---|---|---|
| Heading | Text, heading level, dynamic binding, generic CSS | Basic controls only; no module-specific typography/design groups |
| Text | Content, dynamic binding, generic CSS | No rich text/editor toolbar or text-specific advanced options |
| Image | URL, alt text, dynamic binding, generic CSS | URL is manually entered; no media picker, dimensions, fit, caption, link target, lightbox |
| Button | Text, URL, dynamic binding, generic CSS | No target/rel, icon, icon placement, button sizing or dedicated button states |
| Video | URL, generic CSS | No provider/embed options, poster, aspect ratio, autoplay/mute/controls |
| Gallery | Item URL, alt, link, title, generic CSS | No gallery mode, columns, gap, crop, lightbox or overlay controls |
| Slider | Item URL, alt, link, title, generic CSS | No controls, pagination, autoplay, transition or timing settings |
| Tabs | Item title/content, generic CSS | No active-state styling, orientation or interaction settings |
| Accordion | Item title/content, generic CSS | No icon, multi-open behavior or state styling |
| Blog / Portfolio | Post type, count, columns, generic CSS | No taxonomy/filter controls, metadata toggles, pagination design or card configuration |
| Query Loop | Post type, count, order, child template | Basic query only; no taxonomy/date/author/exclude controls or pagination controls |
| Form | Title, email, submit text, success text, webhook, fields/options/required/conditional display | More developed than basic modules, but no field-level design, notifications workflow UI, spam controls or robust conditional operators |
| WooCommerce grids | Count, columns, category, sort, pagination | Basic query options |
| Individual WooCommerce elements | Informational note only | No element-specific controls |
| Section / Fullwidth / Specialty / Row / Column / Container / Columns | Generic content field, generic CSS/layout controls, column add/remove for Columns | No dedicated layout settings groups; irrelevant Content field is also exposed |

**Confirmed UI defect:** the fallback branch `else if(n.type!=='form')` adds a generic Content textarea to almost every type not explicitly handled in the immediately preceding conditional chain. That includes layout nodes, Blog/Portfolio, Video-adjacent cases excluded by their explicit branch, and WooCommerce element nodes. This creates irrelevant controls and obscures the actual settings model. The fix belongs in the schema-driven renderer, not another set of special-case conditions.

**Confirmed layout gap:** Section, Fullwidth Section, Specialty Section, Row and Column are not distinguished in the settings panel. They share generic fields. The Columns node has add/remove column buttons, but no proper per-column width/ratio controls beyond a generic grid-template-columns string.

**Confirmed media workflow gap:** image, gallery and slider assets are URL text fields. No WordPress media modal is wired into those module settings.

### 8.2 REST persistence contract verified

The document sanitizer reconstructs each node's settings from an explicit allow-list. It persists common content fields, module items, form fields, dynamic binding, WooCommerce query settings, generic desktop/tablet/mobile style buckets, visibility flags and style preset references.

The current `sanitize_style()` accepts a limited generic property set:
- dimensions/spacing and typography primitives as filtered strings;
- hex colors;
- selected layout tokens;
- grid-template-columns with a constrained grammar;
- preset shadows;
- gradient-only background image values;
- constrained filter and transform functions;
- animation presets/duration/delay/iteration/timing;
- recursively sanitized hover styles.

Consequences:
1. A new editor field is not persistent until its value is added to the node sanitizer or a dedicated sanitizer.
2. A value may survive REST but still do nothing unless the renderer maps it to CSS/markup/behavior.
3. Several generic enum fields are passed through `sanitize_key()` rather than checked against a strict allow-list. This is not a raw-CSS injection path by itself, but it can preserve unsupported CSS tokens that render ineffectively.
4. `sanitize_module_items()` uses one shared item shape for gallery, slider, tabs and accordion; this is insufficient for future module-specific item schemas.
5. `sanitize_nodes()` limits nesting depth to 10 and each sibling list to 200 nodes; the frontend renderer has a separate depth limit of 8 and renders up to 300 siblings. The editor/REST/frontend limits are therefore not identical and should be normalized.

### 8.3 Frontend renderer parity verified

The renderer outputs:
- Heading and text markup;
- image and button markup;
- divider and spacer;
- nested layout wrappers;
- form output through the Forms class;
- native WooCommerce callbacks/shortcodes;
- Gallery/Slider image lists;
- CSS-radio Tabs and native details Accordion;
- Blog/Portfolio WP_Query grids;
- Video through WordPress oEmbed;
- Query Loop child templates and custom module output through extension hooks.

Important limitations:
- Canvas module previews are hand-built DOM previews, not the same renderer output; e.g. Video is shown as URL text, Tabs/Accordion as stacked cards, and dynamic/WooCommerce output is not live-equivalent.
- Gallery and Slider currently share the same horizontal/scroll-oriented item rendering path. The frontend CSS makes Gallery a grid and Slider a horizontal scroller, but there are no slider controls or autoplay behavior.
- Tabs use a CSS selector implementation with hard-coded support for up to 12 panels.
- Accordion renders the first item open by default; no configurable initial/open state is exposed.
- Blog/Portfolio output is a simple image/title/excerpt card grid; the editor's post type/count/columns controls do not expose broader query or card options.
- WooCommerce individual elements call native template functions with no element-specific setting payload; several types share the same callback (for example product image/gallery).
- The renderer emits per-node `<style>` blocks for responsive and hover rules during node rendering. This works as a basic mechanism but is not an optimized consolidated stylesheet strategy.

### 8.4 Existing automated tests: coverage quality

The smoke tests largely assert that expected strings, function names, or code fragments exist. They are useful regression tripwires but do not execute the settings UI in a DOM, invoke REST sanitization with representative payloads, render actual frontend HTML in WordPress, or verify CSS in a browser.

Therefore, a green smoke-test run can confirm source wiring, but it cannot establish:
- that a setting control appears for the correct element;
- that changing it updates state correctly;
- that the REST sanitizer preserves the intended value;
- that a saved/reloaded document retains it;
- that the frontend output visibly changes;
- that desktop/tablet/mobile inheritance matches the editor preview.

### 8.5 Required architecture decisions before adding controls

1. Introduce a single declarative registry keyed by element type, with Content / Design / Advanced groups, field type, default, options, unit, responsive support, validation and visibility dependencies.
2. Use the same schema metadata to drive editor field rendering and client-side defaults; keep server-side sanitization authoritative and separately tested.
3. Replace the generic fallback Content field with explicit content schemas per module.
4. Define separate settings schemas for Section, Row, Column, Container and Columns. Do not model a layout as a generic module.
5. Use per-module item schemas for Gallery, Slider, Tabs and Accordion.
6. Add WordPress media-library selection for image-bearing fields.
7. Create a shared style-property contract so editor, REST sanitizer and frontend renderer cannot silently drift.
8. Add executable tests for field visibility, defaults, sanitizer round trips, responsive inheritance and rendered output before declaring a setting complete.

### 8.6 Follow-up audit status

This is a deeper source-level audit of the settings panel, REST sanitizer, renderer and smoke tests. It is still **not** a browser-level or XAMPP audit. No real WordPress save/reload, media modal, WooCommerce checkout, or frontend visual regression test has been performed. The next implementation phase can now begin with the schema/field engine and REST contract, but should retain staging/browser verification as a release blocker.


## 9. Phase 2 implementation progress

The first implementation slice has now been committed to `feature/divi4-canvas-parity`:

- Added a declarative `contentSchemas` registry for Heading, Text, Image, Button, Video and Spacer.
- Added a shared `renderContentSchema()` field renderer that builds those controls from schema metadata.
- Removed the broad fallback that exposed an irrelevant generic Content textarea on layout and unrelated module types.
- Blog/Portfolio, Query Loop, Forms, Gallery/Slider/Tabs/Accordion and WooCommerce retain their existing dedicated control paths; they are not yet migrated to the new registry.
- Added a source-level regression test for the core schema registry and removal of the generic fallback.

**Scope boundary:** this is the first slice of the schema engine, not the completed engine. The generic Design controls are still hand-built; Section/Row/Column schemas, module-specific Design/Advanced schemas, shared REST schema metadata, media-library selection, and end-to-end round-trip tests remain outstanding. The existing REST allow-list already stores the core fields used by this slice (`content`, `level`, `url`, `alt`, and `height`), so no new persisted keys were introduced in this commit.

No PHP/JS test command or WordPress browser session was executed by this commit workflow. The new smoke test is committed but still needs to run in CI. Do not treat this tranche as proof of functional Divi 4 parity.


## 10. Layout settings implementation — first slice

A dedicated responsive layout schema has been added for Section, Fullwidth Section, Specialty Section, Row and Column. The shared schema renderer exposes layout-specific controls and writes values into the active desktop/tablet/mobile style bucket. The selected properties reuse the existing REST style allow-list and frontend CSS mapping rather than introducing unpersisted settings keys.

Implemented in this slice:
- Section and Fullwidth Section: width, max width where applicable, minimum height, padding, background color and overflow.
- Specialty Section: width, max width, padding, column gap and background color.
- Row: display, width, max width, flex direction/wrap, horizontal/vertical alignment, column gutter and padding.
- Column: width, minimum height, padding, margin, background color and overflow.
- Added source-level regression assertions for schema presence and REST/renderer property mapping.

Scope limitations:
- These are responsive CSS layout controls, not a complete Divi 4 layout engine. They do not yet implement true specialty-section column templates, drag-resizable column ratios, equal-height behavior, row/column structural constraints, or per-side spacing controls.
- The schema uses the existing generic style pipeline; no new REST settings object was added.
- Browser-level WordPress testing and executable test runs remain pending.
