const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const builder = fs.readFileSync('assets/js/builder.js', 'utf8');
const actions = fs.readFileSync('assets/js/admin-actions.js', 'utf8');
const rest = fs.readFileSync('includes/class-nirmaan-builder-rest.php', 'utf8');
const renderer = fs.readFileSync('includes/class-nirmaan-builder-renderer.php', 'utf8');

test('editor supports nested nodes and responsive device settings', () => {
  assert.match(builder, /const find=/);
  assert.match(builder, /containsId/);
  assert.match(builder, /nb-nested-children/);
  assert.ok(builder.includes("['desktop','tablet','mobile']"));
  assert.ok(builder.includes("hide_'+device"));
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
  assert.match(rest, /function save_library_item/);
  assert.match(renderer, /global_element/);
});

test('template rules include priorities and exclusions', () => {
  assert.match(rest, /_nirmaan_priority/);
  assert.match(rest, /sanitize_conditions/);
  assert.match(renderer, /matches_conditions/);
  assert.match(actions, /nirmaan-save-template-rules/);
});

test('Theme Options are persisted through a privileged REST route', () => {
  const admin = fs.readFileSync('includes/class-nirmaan-builder-admin.php', 'utf8');
  const settings = fs.readFileSync('includes/class-nirmaan-builder-settings.php', 'utf8');
  assert.match(rest, /'\/settings'/);
  assert.match(rest, /can_manage_settings/);
  assert.match(rest, /Settings::sanitize/);
  assert.match(admin, /nirmaan-theme-settings/);
  assert.match(admin, /nb-select-media/);
  assert.match(settings, /sanitize_hex_color/);
  assert.match(settings, /get_custom_logo/);
  assert.match(settings, /get_site_icon_url/);
});

test('editor exposes layers, duplicate, undo/redo and keyboard shortcuts', () => {
  assert.match(builder, /function renderLayers/);
  assert.match(builder, /nirmaan-undo/);
  assert.match(builder, /nirmaan-redo/);
  assert.match(builder, /const checkpoint=/);
  assert.match(builder, /textContent='Duplicate'/);
  assert.match(builder, /e\.key\.toLowerCase\(\)==='d'/);
  assert.match(builder, /e\.shiftKey\?redo\(\):undo\(\)/);
  const admin = fs.readFileSync('includes/class-nirmaan-builder-admin.php', 'utf8');
  assert.match(admin, /id="nirmaan-layers"/);
});

test('layers support collapse, rename, visibility, lock and wireframe view', () => {
  assert.match(builder, /collapsedLayers/);
  assert.match(builder, /Rename layer/);
  assert.match(builder, /Toggle visibility/);
  assert.match(builder, /Toggle lock/);
  assert.match(builder, /settings\.hidden/);
  assert.match(builder, /settings\.locked/);
  assert.match(builder, /nb-wireframe/);
  const admin = fs.readFileSync('includes/class-nirmaan-builder-admin.php', 'utf8');
  assert.match(admin, /id="nirmaan-view-toggle"/);
});

test('layers support safe same-level drag reordering', () => {
  assert.match(builder, /nb-layer-drop-target/);
  assert.match(builder, /source\.nodes!==target\.nodes/);
  assert.match(builder, /const insertAt=current\.index/);
  assert.match(builder, /wrap\.draggable=!n\.settings\.locked/);
});


test('editor includes autosave, crash recovery and revision restore controls', () => {
  assert.match(builder, /scheduleAutosave/);
  assert.match(builder, /saveLocalDraft/);
  assert.match(builder, /recoveryKey/);
  assert.match(builder, /nirmaan-revisions/);
  assert.match(rest, /function save_autosave/);
  assert.match(rest, /function list_revisions/);
  assert.match(rest, /function restore_revision/);
  assert.match(rest, /_nirmaan_builder_revisions/);
  assert.match(rest, /_nirmaan_builder_autosaves/);
});

test('editor supports desktop, tablet and mobile viewport previews', () => {
  assert.match(builder, /previewDevice/);
  assert.match(builder, /applyPreviewDevice/);
  assert.match(builder, /n\.settings\['hide_'\+previewDevice\]/);
  assert.match(builder, /responsiveStyle/);
  const admin = fs.readFileSync('includes/class-nirmaan-builder-admin.php', 'utf8');
  assert.match(admin, /data-preview-device="mobile"/);
  const css = fs.readFileSync('assets/css/editor.css', 'utf8');
  assert.match(css, /data-preview-device=mobile/);
});

test('design system exposes sanitized global tokens and advanced element styles', () => {
  const settings = fs.readFileSync('includes/class-nirmaan-builder-settings.php', 'utf8');
  assert.match(settings, /accent_color/);
  assert.match(settings, /heading_font_family/);
  assert.match(settings, /spacing_scale/);
  assert.match(settings, /--nb-accent/);
  assert.match(settings, /--nb-line-height/);
  assert.match(rest, /fontWeight/);
  assert.match(rest, /borderColor/);
  assert.match(rest, /boxShadow/);
  assert.match(rest, /flexDirection/);
  assert.match(builder, /Letter spacing/);
  assert.match(builder, /Shadow preset/);
  assert.match(builder, /Flex direction/);
  const renderer = fs.readFileSync('includes/class-nirmaan-builder-renderer.php', 'utf8');
  assert.match(renderer, /'boxShadow'=>'box-shadow'/);
  assert.match(renderer, /$shadows=array/);
});
