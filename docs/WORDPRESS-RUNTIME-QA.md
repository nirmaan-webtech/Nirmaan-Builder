# WordPress Runtime QA

The repository CI checks JavaScript syntax, JavaScript source-level feature tests, and PHP syntax. Those checks do not boot WordPress, execute REST requests against a database, render WooCommerce templates, or drive a browser.

## 1. REST persistence and revision round-trip

From the WordPress installation root, with the Nirmaan Builder theme active and an administrator account granted the required Nirmaan Builder capabilities:

```bash
wp eval-file wp-content/themes/nirmaan-builder/tests/wp/round-trip.php
```

Expected output:

```
PASS: REST save, reload, revision restore and autosave round-trip.
```

The script creates a temporary draft page and deletes it after the test. Run on staging first, not on production.

## 2. Browser QA checklist

Use a staging site with a disposable test page. Sign in as an administrator and open the Nirmaan Builder editor.

- Add Section > Row > Column > Heading; save, reload, and verify the complete nested tree.
- Drag a column across a row, then move a module into a nested column. Verify the layer tree and canvas agree.
- Change column widths and gaps at desktop, tablet, and mobile breakpoints. Verify each viewport and the public frontend.
- Add Image, Button, Gallery, Slider, Tabs, and Accordion. Configure every exposed option, save, reload, and inspect frontend output.
- Test slider arrows, dots, autoplay, pause on hover/focus, fade transition, and reduced-motion behavior.
- Test Forms with required, email, URL, telephone, date, select, radio, checkbox, conditional fields, consent, honeypot, invalid submissions, success, notification email, and optional webhook.
- With WooCommerce active, test product modules on a real product page, product grids with category/price/sale/stock filters, and cart/checkout/mini-cart on their correct pages.
- Compare canvas and public frontend for typography, backgrounds, borders, shadows, transforms, sticky positioning, scroll effects, and responsive visibility.
- Save multiple versions, inspect revision history, restore an earlier revision, and verify both database content and frontend output.
- Check keyboard navigation, focus visibility, screen-reader labels, console errors, network failures, and mobile overflow.

## 3. Required environment for browser sign-off

- Staging URL and a dedicated test administrator account.
- WooCommerce enabled for commerce tests.
- A test email inbox; a test webhook endpoint if webhook delivery is to be verified.
- Browser access to the staging site.

Do not send production credentials in chat. Create a temporary staging account and revoke it after QA.
