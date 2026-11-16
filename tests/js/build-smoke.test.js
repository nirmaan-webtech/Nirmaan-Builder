const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const builder = fs.readFileSync('assets/js/builder.js', 'utf8');
const actions = fs.readFileSync('assets/js/admin-actions.js', 'utf8');
const rest = fs.readFileSync('includes/class-nirmaan-builder-rest.php', 'utf8');
const renderer = fs.readFileSync('includes/class-nirmaan-builder-renderer.php', 'utf8');

test('editor supports nested nodes and responsive device settings', () => {
  assert.match(builder, /function find\(/);
  assert.match(builder, /nb-nested-children/);
  assert.match(builder, /desktop.*tablet.*mobile/);
  assert.match(builder, /hide_\+device/);
});

test('editor exposes WooCommerce layout modules', () => {
  for (const moduleName of ['product_archive', 'cart', 'checkout', 'mini_cart']) {
    assert.ok(builder.includes(moduleName), moduleName + ' module missing');
    assert.ok(rest.includes(moduleName), moduleName + ' is not accepted by REST');
    assert.ok(renderer.includes(moduleName), moduleName + ' has no renderer');
  }
});

test('library supports global elements and JSON import/export', () => {
  assert.match(rest, /_nirmaan_global/);
  assert.match(rest, /function export_library/);
  assert.match(rest, /function import_library/);
  assert.match(renderer, /global_element/);
});

test('template rules include priorities and exclusions', () => {
  assert.match(rest, /_nirmaan_priority/);
  assert.match(rest, /sanitize_conditions/);
  assert.match(renderer, /matches_conditions/);
  assert.match(actions, /nirmaan-save-template-rules/);
});
