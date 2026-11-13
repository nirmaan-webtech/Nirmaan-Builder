import React, { useEffect, useMemo, useRef, useState } from 'react';
import { createRoot } from 'react-dom/client';
import './editor.css';

const MODULES = [
  { type: 'heading', label: 'Heading', category: 'Content', defaults: { text: 'Your heading', level: 'h2', align: 'left' } },
  { type: 'text', label: 'Text', category: 'Content', defaults: { text: 'Add your content here.', align: 'left' } },
  { type: 'button', label: 'Button', category: 'Content', defaults: { text: 'Click here', url: '#', align: 'left' } },
  { type: 'image', label: 'Image', category: 'Media', defaults: { url: '', alt: '', align: 'center' } },
  { type: 'divider', label: 'Divider', category: 'Layout', defaults: {} },
  { type: 'spacer', label: 'Spacer', category: 'Layout', defaults: { height: 32 } },
];

const api = window.NirmaanBuilderConfig || {};

function request(path, options = {}) {
  return fetch(api.restUrl + path, {
    credentials: 'same-origin',
    ...options,
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': api.nonce,
      ...(options.headers || {}),
    },
  }).then(async (response) => {
    const body = await response.json();
    if (!response.ok) throw new Error(body.message || body.error?.message || 'Request failed');
    return body;
  });
}

function makeId() {
  return (window.crypto?.randomUUID?.() || ('nb-' + Date.now() + '-' + Math.random().toString(16).slice(2)));
}

function App() {
  const [documents, setDocuments] = useState([]);
  const [documentId, setDocumentId] = useState(null);
  const [title, setTitle] = useState('Untitled Layout');
  const [elements, setElements] = useState([]);
  const [selectedId, setSelectedId] = useState(null);
  const [device, setDevice] = useState('desktop');
  const [previewUrl, setPreviewUrl] = useState('');
  const [showPreview, setShowPreview] = useState(false);
  const [previewVersion, setPreviewVersion] = useState(0);
  const [dirty, setDirty] = useState(false);
  const [undoStack, setUndoStack] = useState([]);
  const [redoStack, setRedoStack] = useState([]);
  const [templateType, setTemplateType] = useState('none');
  const [conditionType, setConditionType] = useState('entire_site');
  const [conditionValue, setConditionValue] = useState('');
  const [templatePriority, setTemplatePriority] = useState(10);
  const [busy, setBusy] = useState(false);
  const [message, setMessage] = useState('Loading Builder…');
  const [error, setError] = useState('');
  const revisionRef = useRef(0);

  function markDirty() {
    revisionRef.current += 1;
    setDirty(true);
  }

  const selected = useMemo(() => elements.find((item) => item.id === selectedId) || null, [elements, selectedId]);

  async function loadDocuments() {
    const result = await request('documents');
    setDocuments(result.data || []);
    return result.data || [];
  }

  async function openDocument(id) {
    revisionRef.current += 1;
    setDirty(false);
    setBusy(true);
    setError('');
    try {
      const result = await request('documents/' + id);
      setDocumentId(id);
      setTitle(result.data.title || 'Untitled Layout');
      setPreviewUrl(result.data.preview_url || '');
      setTemplateType(result.data.template_type || 'none');
      setConditionType(result.data.template_condition?.type || 'entire_site');
      setConditionValue(result.data.template_condition?.value || '');
      setTemplatePriority(result.data.template_priority || 10);
      setDirty(false);
      setUndoStack([]);
      setRedoStack([]);
      setElements(Array.isArray(result.data.document?.elements) ? result.data.document.elements : []);
      setSelectedId(null);
      setMessage('Document loaded');
    } catch (e) {
      setError(e.message);
    } finally {
      setBusy(false);
    }
  }

  async function createDocument() {
    setBusy(true);
    setError('');
    try {
      const result = await request('documents', { method: 'POST', body: JSON.stringify({ title: 'Untitled Layout' }) });
      await loadDocuments();
      await openDocument(result.data.id);
    } catch (e) {
      setError(e.message);
    } finally {
      setBusy(false);
    }
  }

  async function saveDocument(status = null) {
    if (!documentId) return;
    const savedRevision = revisionRef.current;
    setBusy(true);
    setError('');
    try {
      const payload = { document: { schema_version: 1, elements }, title, template_type: templateType, template_condition: { type: conditionType, value: conditionValue }, template_priority: templatePriority };
      if (status) payload.status = status;
      const result = await request('documents/' + documentId, {
        method: 'PUT',
        body: JSON.stringify(payload),
      });
      setPreviewUrl(result.data.preview_url || '');
      setPreviewVersion((value) => value + 1);
      if (savedRevision === revisionRef.current) setDirty(false);
      else setDirty(true);
      setMessage(status === 'publish' ? 'Published successfully' : 'Saved successfully');
      await loadDocuments();
    } catch (e) {
      setError(e.message);
    } finally {
      setBusy(false);
    }
  }

  function commitElements(next) {
    setUndoStack((stack) => [...stack.slice(-49), elements]);
    setRedoStack([]);
    setElements(next);
    setDirty(true);
  }

  function undo() {
    if (!undoStack.length) return;
    const previous = undoStack[undoStack.length - 1];
    setUndoStack((stack) => stack.slice(0, -1));
    setRedoStack((stack) => [...stack, elements]);
    setElements(previous);
    setDirty(true);
  }

  function redo() {
    if (!redoStack.length) return;
    const next = redoStack[redoStack.length - 1];
    setRedoStack((stack) => stack.slice(0, -1));
    setUndoStack((stack) => [...stack, elements]);
    setElements(next);
    setDirty(true);
  }

  function addModule(type, index = elements.length) {
    const definition = MODULES.find((item) => item.type === type);
    if (!definition) return;
    const item = { id: makeId(), type, settings: { ...definition.defaults } };
    const next = [...elements];
    next.splice(index, 0, item);
    commitElements(next);
    setSelectedId(item.id);
  }

  function updateSelected(key, value) {
    commitElements(elements.map((item) => item.id === selectedId
      ? { ...item, settings: { ...item.settings, [key]: value } }
      : item));
  }

  function moveElement(fromId, toIndex) {
    const fromIndex = elements.findIndex((item) => item.id === fromId);
    if (fromIndex < 0) return;
    const next = [...elements];
    const item = next.splice(fromIndex, 1)[0];
    const adjustedIndex = fromIndex < toIndex ? toIndex - 1 : toIndex;
    next.splice(Math.max(0, adjustedIndex), 0, item);
    commitElements(next);
  }

  function renderElement(item) {
    const s = item.settings || {};
    const style = { textAlign: s.align || 'left' };
    if (item.type === 'heading') {
      const Tag = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'].includes(s.level) ? s.level : 'h2';
      return <Tag style={style}>{s.text || ''}</Tag>;
    }
    if (item.type === 'text') return <p style={style}>{s.text || ''}</p>;
    if (item.type === 'button') return <div style={style}><span className="nb-preview-button">{s.text || 'Button'}</span></div>;
    if (item.type === 'image') return s.url
      ? <div style={style}><img className="nb-preview-image" src={s.url} alt={s.alt || ''} /></div>
      : <div className="nb-image-placeholder">Choose an image in the settings panel</div>;
    if (item.type === 'divider') return <hr />;
    if (item.type === 'spacer') return <div style={{ height: (Math.min(500, Math.max(0, Number(s.height) || 32))) + 'px' }} />;
    return null;
  }

  useEffect(() => {
    if (!dirty || !documentId || busy) return undefined;
    const timer = window.setTimeout(async () => {
      setBusy(true);
      try {
        const savedRevision = revisionRef.current;
        await request('documents/' + documentId, {
          method: 'PUT',
          body: JSON.stringify({ document: { schema_version: 1, elements }, title, template_type: templateType, template_condition: { type: conditionType, value: conditionValue }, template_priority: templatePriority }),
        });
        if (savedRevision === revisionRef.current) setDirty(false);
        else setDirty(true);
        setMessage('Autosaved');
      } catch (e) {
        setError('Autosave failed: ' + e.message);
      } finally {
        setBusy(false);
      }
    }, 1500);
    return () => window.clearTimeout(timer);
  }, [dirty, documentId, busy, elements, title, templateType, conditionType, conditionValue, templatePriority]);

  useEffect(() => {
    loadDocuments()
      .then((items) => {
        if (items.length) openDocument(items[0].id);
        else setMessage('Create your first layout to begin.');
      })
      .catch((e) => setError(e.message));
  }, []);

  return (
    <div className="nb-app">
      <header className="nb-topbar">
        <div className="nb-brand"><span className="nb-mark">N</span><strong>Nirmaan Builder</strong></div>
        <div className="nb-document-title"><input aria-label="Layout title" value={title} onChange={(e) => { setTitle(e.target.value); setDirty(true); }} disabled={!documentId} /></div>
        <div className="nb-top-actions"><button onClick={undo} disabled={!undoStack.length || busy}>Undo</button><button onClick={redo} disabled={!redoStack.length || busy}>Redo</button><span className="nb-status">{busy ? 'Working…' : message}</span><button onClick={() => setShowPreview((value) => !value)} disabled={!documentId || !previewUrl}>{showPreview ? 'Editor' : 'Preview'}</button><button onClick={() => saveDocument('publish')} disabled={!documentId || busy}>Publish</button><button className="nb-primary" onClick={() => saveDocument()} disabled={!documentId || busy}>Save</button></div>
      </header>
      {error && <div className="nb-error" role="alert">{error}</div>}
      <div className="nb-workspace">
        <aside className="nb-sidebar nb-modules">
          <div className="nb-panel-heading"><strong>Elements</strong><button onClick={createDocument} disabled={busy}>+ New</button></div>
          <div className="nb-documents"><label>Layouts</label>
            {documents.map((doc) => <button key={doc.id} className={doc.id === documentId ? 'nb-doc active' : 'nb-doc'} onClick={() => openDocument(doc.id)}>{doc.title}</button>)}
          </div>
          {['Content', 'Media', 'Layout'].map((category) => (
            <section className="nb-module-group" key={category}>
              <h3>{category}</h3>
              <div className="nb-module-grid">
                {MODULES.filter((item) => item.category === category).map((item) => (
                  <button key={item.type} draggable onDragStart={(e) => e.dataTransfer.setData('text/nirmaan-module', item.type)} onClick={() => addModule(item.type)}>
                    <span>＋</span>{item.label}
                  </button>
                ))}
              </div>
            </section>
          ))}
        </aside>
        <main className={'nb-canvas-area nb-device-' + device}>
          <div className="nb-canvas-toolbar">
            <div className="nb-device-switch">{['desktop', 'tablet', 'phone'].map((value) => <button key={value} className={device === value ? 'active' : ''} onClick={() => setDevice(value)}>{value}</button>)}</div>
            <span>Editor canvas · local editor preview</span>
          </div>
          {showPreview ? <div className="nb-frontend-preview"><iframe key={previewVersion} title="Frontend preview" src={previewUrl} /></div> : <div className="nb-canvas-scroll">
            <div className="nb-canvas" onDragOver={(e) => e.preventDefault()} onDrop={(e) => {
              e.preventDefault();
              const type = e.dataTransfer.getData('text/nirmaan-module');
              const movingId = e.dataTransfer.getData('text/nirmaan-element');
              if (type) addModule(type);
              else if (movingId) moveElement(movingId, elements.length);
            }}>
              {!elements.length && <div className="nb-empty"><strong>Start building your page</strong><p>Drag an element here or select one from the left panel.</p></div>}
              {elements.map((item, index) => (
                <div key={item.id} className={selectedId === item.id ? 'nb-canvas-element selected' : 'nb-canvas-element'}
                  onClick={(e) => { e.stopPropagation(); setSelectedId(item.id); }}
                  draggable
                  onDragStart={(e) => e.dataTransfer.setData('text/nirmaan-element', item.id)}
                  onDragOver={(e) => e.preventDefault()}
                  onDrop={(e) => {
                    e.preventDefault(); e.stopPropagation();
                    const movingId = e.dataTransfer.getData('text/nirmaan-element');
                    const type = e.dataTransfer.getData('text/nirmaan-module');
                    if (type) addModule(type, index);
                    else if (movingId) moveElement(movingId, index);
                  }}>
                  <span className="nb-element-label">{MODULES.find((m) => m.type === item.type)?.label || item.type}</span>
                  {renderElement(item)}
                </div>
              ))}
            </div>
          </div>}
        </main>
        <aside className="nb-sidebar nb-settings">
          <div className="nb-panel-heading"><strong>Settings</strong></div>
          <section className="nb-template-settings">
            <h3>Theme Builder</h3>
            <label>Template type
              <select value={templateType} onChange={(e) => { setTemplateType(e.target.value); setDirty(true); }}>
                <option value="none">Regular layout</option>
                <option value="header">Global / conditional header</option>
                <option value="footer">Global / conditional footer</option>
              </select>
            </label>
            {templateType !== 'none' && <>
              <label>Display condition
                <select value={conditionType} onChange={(e) => { setConditionType(e.target.value); setDirty(true); }}>
                  <option value="entire_site">Entire site</option>
                  <option value="front_page">Homepage</option>
                  <option value="singular">All singular content</option>
                  <option value="post_type">Specific post type</option>
                  <option value="page_id">Specific page ID</option>
                  <option value="category_id">Specific category ID</option>
                  <option value="search">Search results</option>
                  <option value="404">404 page</option>
                </select>
              </label>
              {['post_type', 'page_id', 'category_id'].includes(conditionType) && <label>Condition value
                <input value={conditionValue} onChange={(e) => { setConditionValue(e.target.value); setDirty(true); }} placeholder={conditionType === 'post_type' ? 'post or page' : 'Numeric ID'} />
              </label>}
              <label>Priority (0–999)
                <input type="number" min="0" max="999" value={templatePriority} onChange={(e) => { setTemplatePriority(Number(e.target.value)); setDirty(true); }} />
              </label>
              <p className="nb-template-help">Publish this layout to activate it. Higher priority wins; more specific conditions break ties.</p>
            </>}
          </section>
          {!selected && <div className="nb-inspector-empty">Select an element on the canvas to edit its settings.</div>}
          {selected && <div className="nb-inspector">
            <h3>{MODULES.find((m) => m.type === selected.type)?.label || selected.type}</h3>
            {['heading', 'text', 'button'].includes(selected.type) && <label>Text<textarea value={selected.settings.text || ''} onChange={(e) => updateSelected('text', e.target.value)} /></label>}
            {selected.type === 'heading' && <label>Heading level<select value={selected.settings.level || 'h2'} onChange={(e) => updateSelected('level', e.target.value)}>{['h1', 'h2', 'h3', 'h4', 'h5', 'h6'].map((v) => <option key={v}>{v}</option>)}</select></label>}
            {selected.type === 'image' && <><label>Image URL<input value={selected.settings.url || ''} onChange={(e) => updateSelected('url', e.target.value)} placeholder="https://…" /></label><label>Alt text<input value={selected.settings.alt || ''} onChange={(e) => updateSelected('alt', e.target.value)} /></label></>}
            {selected.type === 'button' && <label>Link URL<input value={selected.settings.url || ''} onChange={(e) => updateSelected('url', e.target.value)} /></label>}
            {selected.type === 'spacer' && <label>Height (px)<input type="number" min="0" max="500" value={selected.settings.height || 32} onChange={(e) => updateSelected('height', Number(e.target.value))} /></label>}
            {['heading', 'text', 'button'].includes(selected.type) && <label>Alignment<select value={selected.settings.align || 'left'} onChange={(e) => updateSelected('align', e.target.value)}><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select></label>}
            <button className="nb-danger" onClick={() => { commitElements(elements.filter((item) => item.id !== selectedId)); setSelectedId(null); }}>Delete element</button>
          </div>}
        </aside>
      </div>
    </div>
  );
}

const rootElement = document.getElementById('nirmaan-builder-root');
if (rootElement) createRoot(rootElement).render(<App />);
