const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');

const canvas = fs.readFileSync('includes/class-nirmaan-builder-canvas.php', 'utf8');
const bootstrap = fs.readFileSync('includes/class-nirmaan-builder.php', 'utf8');
const script = fs.readFileSync('assets/js/canvas.js', 'utf8');
const styles = fs.readFileSync('assets/css/canvas.css', 'utf8');
const renderer = fs.readFileSync('includes/class-nirmaan-builder-renderer.php', 'utf8');
const rest = fs.readFileSync('includes/class-nirmaan-builder-rest.php', 'utf8');

test('visual canvas is registered as a dedicated WordPress admin screen', () => {
  assert.match(bootstrap, /class-nirmaan-builder-canvas\.php/);
  assert.match(bootstrap, /new Canvas\(\)/);
  assert.match(canvas, /nirmaan-canvas/);
  assert.match(canvas, /wp_enqueue_media/);
  assert.match(canvas, /NirmaanCanvasConfig/);
});

test('canvas exposes hierarchy, device previews, settings, history and persistence', () => {
  for (const token of ['section','row','column','heading','text','image','button','gallery','slider','accordion','gridTemplateColumns','animationName','animationTimingFunction','restoreRevision']) {
    assert.ok(script.includes(token), 'missing builder capability: ' + token);
  }
  for (const token of ['undo','redo','autosave','restoreRevision','revisions/','targetPath','data-device','data-action']) {
    assert.ok(script.includes(token), 'missing editor workflow: ' + token);
  }
  assert.match(script, /targetPath/);
  assert.match(script, /cfg\.targetId/);
  assert.match(script, /targetType==='template'/);
  assert.match(rest, /public function save_content/);
  assert.match(rest, /public function save_autosave/);
  assert.match(rest, /public function restore_revision/);\n  assert.match(rest, /public function restore_revision/);
  assert.match(styles, /nb-canvas-workspace/);
});

test('frontend renderer continues rendering builder documents saved by the canvas', () => {
  assert.match(renderer, /function render_nodes/);
  assert.match(renderer, /_nirmaan_builder_document/);
});
