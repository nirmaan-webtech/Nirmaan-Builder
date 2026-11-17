# Translations and multilingual setup

Nirmaan Builder uses the `nirmaan-builder` text domain and loads translations from this directory.

## Build translation catalogs

From the repository root, with WP-CLI and its i18n package installed:

```bash
wp i18n make-pot . languages/nirmaan-builder.pot --domain=nirmaan-builder --exclude=node_modules,vendor,tests
wp i18n make-json languages --no-purge
```

Translate the POT file, then generate WordPress JavaScript JSON catalogs with `wp i18n make-json`. Keep generated locale files in this directory and commit them with the translation.

The editor, admin actions, Gutenberg sidebar and frontend form scripts declare the `wp-i18n` dependency and call `wp_set_script_translations()`. PHP interface strings use the same text domain.

## WPML

The root `wpml-config.xml` registers Theme Builder templates and Library layouts as translatable post types. Builder document/meta structures are copied intact; user-facing node strings are registered separately with WPML String Translation and translated during frontend rendering. This avoids asking translators to edit serialized JSON.

## Polylang

The integration registers builder templates and Library layouts as translatable post types and includes builder document, template-part and Library metadata in translated-post copying. User-facing node strings are registered with Polylang String translations and translated at render time.

## Staging verification

1. Install WPML or Polylang on a disposable WordPress staging site.
2. Create a page with headings, text, image alt text, a form title, labels, options and a custom success message in the default language.
3. Save the page and create its translated counterpart using the multilingual plugin's translation workflow.
4. Translate the registered Nirmaan Builder strings in the plugin's string-translation UI.
5. Confirm the translated page renders translated strings while its layout structure and responsive styles remain intact.
6. Create language-specific Theme Builder templates and verify the active language receives its own template.
7. Repeat after editing and resaving the source page. Confirm roles and REST permissions are unchanged for translator/editor roles.

Automated repository checks verify integration hooks and translation wiring; they do not replace testing against the actual commercial multilingual plugin versions installed on the target site.
