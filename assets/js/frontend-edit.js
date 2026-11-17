(function () {
 'use strict';
 const cfg = window.NirmaanBuilderFrontendConfig;
 if (!cfg || !cfg.postId) return;
 const api = (path, options) => fetch(cfg.apiRoot + path, Object.assign({
  headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce },
  credentials: 'same-origin'
 }, options || {})).then(async response => {
  const data = await response.json();
  if (!response.ok) throw new Error(data.message || 'Request failed');
  return data;
 });
 let documentData = null;
 let dirty = false;
 let editable = [];
 const findNode = (nodes, id) => {
  for (const node of nodes || []) {
   if (String(node.id) === String(id)) return node;
   const child = findNode(node.children, id);
   if (child) return child;
  }
  return null;
 };
 const nodeId = element => {
  const wrapper = element.closest('.nb-node');
  if (!wrapper) return null;
  const token = Array.from(wrapper.classList).find(name => name.indexOf('nb-node-') === 0);
  return token ? token.slice('nb-node-'.length) : null;
 };
 const bar = document.createElement('div');
 bar.id = 'nb-frontend-edit-bar';
 bar.innerHTML = '<strong>Nirmaan Frontend Editor</strong><span class="nb-frontend-edit-hint">Click text to edit it directly.</span><button type="button" data-action="save">Save changes</button><button type="button" data-action="cancel">Cancel</button><span class="nb-frontend-edit-status" aria-live="polite">Loading…</span>';
 document.body.appendChild(bar);
 const status = message => { const target = bar.querySelector('.nb-frontend-edit-status'); if (target) target.textContent = message; };
 const setDirty = () => { dirty = true; status('Unsaved changes'); };
 const activate = () => {
  editable = Array.from(document.querySelectorAll('.nb-heading, .nb-text, .nb-button')).filter(el => nodeId(el));
  editable.forEach(element => {
   element.contentEditable = 'true';
   element.classList.add('nb-frontend-editable');
   element.setAttribute('spellcheck', 'true');
   element.addEventListener('input', setDirty);
   element.addEventListener('click', event => {
    if (element.matches('a')) event.preventDefault();
   });
  });
  document.addEventListener('click', event => {
   const link = event.target.closest('a');
   if (link && link.classList.contains('nb-button')) event.preventDefault();
  }, true);
  status(editable.length ? 'Editing enabled' : 'No editable text elements found');
 };
 const save = async () => {
  try {
   const changed = new Set();
   editable.forEach(element => {
    const id = nodeId(element);
    const node = findNode(documentData.nodes, id);
    if (!node) return;
    node.settings = node.settings || {};
    node.settings.content = element.innerText || element.textContent || '';
    changed.add(id);
   });
   if (!changed.size) { status('Nothing to save'); return; }
   status('Saving…');
   await api('content/' + cfg.postId, { method: 'POST', body: JSON.stringify(documentData) });
   dirty = false;
   status('Saved successfully');
  } catch (error) {
   status('Save failed: ' + error.message);
  }
 };
 bar.addEventListener('click', event => {
  const action = event.target.dataset.action;
  if (action === 'save') save();
  if (action === 'cancel') {
   if (dirty && !window.confirm('Discard your unsaved frontend edits?')) return;
   window.location.href = window.location.href.split('?')[0].split('#')[0];
  }
 });
 window.addEventListener('beforeunload', event => {
  if (!dirty) return;
  event.preventDefault();
  event.returnValue = '';
 });
 api('content/' + cfg.postId).then(result => {
  documentData = result.document || { nodes: [] };
  activate();
 }).catch(error => status('Could not load editable content: ' + error.message));
})();
