# Visual Builder MVP – Implementation Status

This branch implements a usable first vertical slice for the selected pending areas. It does not claim full Divi 4 feature parity.

## Implemented
- Admin canvas editor with add/select/edit/delete/reorder for heading, text, image, button, divider, spacer and container elements.
- Authenticated REST endpoints to load and save page documents.
- Page/post editor metabox and list-row Edit with Nirmaan Builder links.
- Theme template create/list/delete workflow and separate Header, Body and Footer editor entry points.
- Template include/exclude matching for whole site, front page, all pages, all posts, singular ID, post type, archives, search and 404.
- Frontend rendering for saved page documents and assigned template parts.
- Server-side allow-list validation and module-specific escaping.

## Still incomplete
- Not full Divi 4 parity.
- Nested container canvas editing and full nested drag/drop tree.
- Advanced per-object condition builder and condition priority UI.
- Library saved layouts, categories, search, import/export and global synchronization.
- WooCommerce product/cart/checkout modules.
- Forms, advanced animation, device-specific responsive controls, history timeline, revisions UI, role editor, and full accessibility/performance testing.
- Header/footer output requires the active theme to call WordPress hooks wp_body_open() and wp_footer().
- Install and test on staging before production use.

## Verification
CI checks PHP syntax and builds JavaScript. Manual WordPress integration testing is required.
