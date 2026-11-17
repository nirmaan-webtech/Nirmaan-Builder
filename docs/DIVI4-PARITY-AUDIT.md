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
