/* global NirmaanBuilderConfig */
(function () {
	'use strict';
	const cfg = window.NirmaanBuilderConfig; const canvas = document.getElementById('nirmaan-canvas'); if (!cfg || !canvas) return;
	const api = (path, opts = {}) => fetch(cfg.apiRoot + path, { ...opts, headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce } }).then(async r => { const d = await r.json(); if (!r.ok) throw new Error(d.message || 'Request failed'); return d; });
	const types = [['heading','Heading'],['text','Text'],['image','Image'],['button','Button'],['divider','Divider'],['spacer','Spacer'],['container','Container']];
	let doc = { nodes: [] }; let selected = null;
	const id = () => 'nb-' + Math.random().toString(36).slice(2,10);
	const status = t => { const el = document.querySelector('.nirmaan-save-status'); if (el) el.textContent = t; };
	const make = type => ({ id:id(), type, settings:{ content:type==='heading'?'Your heading':type==='text'?'Add your text here.':type==='button'?'Click here':'', url:'', alt:'', level:2, height:32 }, ...(type==='container'?{children:[]}: {}) });
	const list = document.getElementById('nirmaan-module-list');
	types.forEach(([type,label]) => { const b=document.createElement('button'); b.type='button'; b.className='button nirmaan-add-module'; b.textContent='+ '+label; b.onclick=()=>{doc.nodes.push(make(type));render();status('Unsaved changes');}; list.appendChild(b); });
	const nodeById = (key,nodes=doc.nodes) => { for(const n of nodes){if(n.id===key)return n;if(n.children){const found=nodeById(key,n.children);if(found)return found;}} return null; };
	function renderNode(n) {
		const el=document.createElement('div'); el.className='nb-editor-element'+(selected===n.id?' is-selected':''); el.draggable=true; el.dataset.id=n.id;
		let v;
		if(n.type==='heading'){v=document.createElement('h2');v.textContent=n.settings.content||'Heading';}
		else if(n.type==='text'){v=document.createElement('p');v.textContent=n.settings.content||'Text block';}
		else if(n.type==='image'){v=document.createElement('div');v.className='nb-image-placeholder';v.textContent=n.settings.url||'Image – set URL in settings';}
		else if(n.type==='button'){v=document.createElement('span');v.className='nb-button';v.textContent=n.settings.content||'Button';}
		else if(n.type==='divider'){v=document.createElement('hr');}
		else if(n.type==='spacer'){v=document.createElement('div');v.style.height=Math.min(400,Number(n.settings.height)||32)+'px';v.className='nb-spacer-preview';}
		else{v=document.createElement('div');v.className='nb-container-preview';v.textContent='Container – nested canvas editing is not available yet';}
		el.appendChild(v); const tools=document.createElement('div');tools.className='nb-element-controls';const tag=document.createElement('small');tag.textContent=n.type;tools.appendChild(tag);
		const del=document.createElement('button');del.type='button';del.textContent='Delete';del.onclick=e=>{e.stopPropagation();doc.nodes=doc.nodes.filter(x=>x.id!==n.id);selected=null;render();status('Unsaved changes');};tools.appendChild(del);el.appendChild(tools);
		el.onclick=()=>{selected=n.id;render();};el.ondragstart=e=>e.dataTransfer.setData('text/plain',n.id);el.ondragover=e=>e.preventDefault();el.ondrop=e=>{e.preventDefault();const from=doc.nodes.findIndex(x=>x.id===e.dataTransfer.getData('text/plain'));const to=doc.nodes.findIndex(x=>x.id===n.id);if(from<0||to<0||from===to)return;const [m]=doc.nodes.splice(from,1);doc.nodes.splice(to,0,m);render();};return el;
	}
	function settings() {
		const panel=document.getElementById('nirmaan-settings');panel.replaceChildren();const n=selected?nodeById(selected):null;if(!n){panel.textContent='Select an element on the canvas.';return;}
		const fields=n.type==='image'?[['url','Image URL'],['alt','Alt text']]:n.type==='spacer'?[['height','Height (px)']]:[['content','Content'],...(n.type==='button'?[['url','Link URL']]:[]),...(n.type==='heading'?[['level','Heading level']]:[])];
		fields.forEach(([key,label])=>{const l=document.createElement('label');l.textContent=label;const input=key==='content'?document.createElement('textarea'):document.createElement('input');input.value=n.settings[key]||'';input.oninput=()=>{n.settings[key]=['height','level'].includes(key)?Number(input.value):input.value;status('Unsaved changes');};l.appendChild(input);panel.appendChild(l);});
	}
	function render(){canvas.replaceChildren();if(!doc.nodes.length){const p=document.createElement('p');p.className='nb-empty';p.textContent='Canvas is empty. Add an element from the left panel.';canvas.appendChild(p);}doc.nodes.forEach(n=>canvas.appendChild(renderNode(n)));settings();}
	async function load(){try{if(cfg.postId){const d=await api('content/'+cfg.postId);doc=d.document||{nodes:[]};}else{const d=await api('templates/'+cfg.templateId);doc=d.parts?.[cfg.part]||{nodes:[]};}render();}catch(e){status(e.message);}}
	document.getElementById('nirmaan-save').onclick=async()=>{try{status('Saving…');if(cfg.postId)await api('content/'+cfg.postId,{method:'POST',body:JSON.stringify(doc)});else await api('templates/'+cfg.templateId,{method:'POST',body:JSON.stringify({part:cfg.part,document:doc})});status('Saved');}catch(e){status('Save failed: '+e.message);}};
	const form=document.getElementById('nirmaan-create-template');if(form)form.onsubmit=async e=>{e.preventDefault();try{const f=new FormData(form);const t=await api('templates',{method:'POST',body:JSON.stringify({title:f.get('title')})});await api('templates/'+t.id,{method:'POST',body:JSON.stringify({conditions:{include:[{type:f.get('condition')}],exclude:[]}})});location.reload();}catch(err){alert(err.message);}};
	document.querySelectorAll('.nirmaan-delete-template').forEach(b=>b.onclick=async()=>{if(!confirm('Delete this template and its parts?'))return;try{await api('templates/'+b.dataset.id,{method:'DELETE'});location.reload();}catch(e){alert(e.message);}});
	load();
})();
