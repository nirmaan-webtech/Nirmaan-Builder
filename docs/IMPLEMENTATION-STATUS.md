# Visual Builder MVP – Implementation Status

This branch implements a first functional vertical slice for the requested areas. It does not claim complete Divi 4 feature parity.

## Implemented
- Admin canvas editor with add, select, edit, delete and reorder for heading, text, image, button, divider, spacer, container and WooCommerce product elements.
- Authenticated REST endpoints to load and save page documents.
- Page/post editor metabox and list-row Edit with Nirmaan Builder links.
- Theme template create, list and delete workflow with separate Header, Body and Footer editor entry points.
- Include/exclude template matching for whole site, front page, all pages, all posts, singular ID, post type, archives, search and 404.
- Frontend rendering for saved page documents and assigned template parts.
- Reusable Library: save current design, list saved layouts, insert a saved layout into the current canvas, and delete layouts.
- WooCommerce product title, price, image, add-to-cart, description, rating, related products and shop product modules. WooCommerce must be active.
- Server-side allow-list validation, type-specific escaping and resource-type checks on destructive REST endpoints.

## Still incomplete
- Not full Divi 4 parity.
- Nested container canvas editing and full nested drag-and-drop tree.
- Advanced per-object condition builder, priorities and template conflict UI.
- Library categories, search, import/export, shared global-element synchronization and layout versioning.
- WooCommerce custom cart/checkout layout design, product archive query controls, variations and mini-cart module.
- Forms, advanced animation, device-specific responsive controls, history timeline, revisions UI, role editor, and comprehensive automated WordPress integration tests.
- Header/footer output requires the active theme to call wp_body_open() and wp_footer().
- Install and test on staging before production use.

## Verification
GitHub Actions checks PHP syntax and builds the JavaScript bundle. A passing CI run does not replace manual WordPress and WooCommerce integration testing.
