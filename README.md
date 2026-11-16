# Nirmaan Builder

An independent WordPress visual theme and page builder plugin.

## Version 0.2.0 – Visual Builder MVP
- Page/post canvas editor with basic content modules.
- Authenticated REST save/load endpoints.
- Header, Body and Footer template-part editing.
- Template creation and common display-condition matching.
- Server-rendered frontend output.

This is not full Divi 4 feature parity. See docs/IMPLEMENTATION-STATUS.md and docs/DEVELOPMENT-ROADMAP.md for the implemented scope and remaining work.

## Requirements
- WordPress 6.4+
- PHP 8.0+
- Node.js 20+ for JavaScript build tooling

## Installation
1. Download this branch as ZIP.
2. Copy the nirmaan-builder plugin folder into wp-content/plugins/.
3. Activate Nirmaan Builder.
4. Open a Page or Post and use the Nirmaan Builder metabox or row action.
5. Open Nirmaan Builder → Theme Builder to create Header, Body and Footer parts.

Install and test on a staging WordPress site before production use.
