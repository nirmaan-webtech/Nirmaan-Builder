const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const admin = fs.readFileSync('includes/class-nirmaan-builder-admin.php', 'utf8');
const renderer = fs.readFileSync('includes/class-nirmaan-builder-renderer.php', 'utf8');

test('visual canvas editor is fully removed from the admin interface', () => {
  assert.doesNotMatch(admin, /nirmaan-editor|render_editor|enqueue_gutenberg_assets/);
  assert.equal(fs.existsSync(path.join('assets', 'js', 'builder.js')), false);
  assert.equal(fs.existsSync(path.join('assets', 'js', 'gutenberg.js')), false);
  assert.equal(fs.existsSync(path.join('assets', 'css', 'editor.css')), false);
});

test('frontend renderer remains available for previously saved builder content', () => {
  assert.match(renderer, /class Renderer/);
  assert.match(renderer, /render_nodes|render_node/);
});
