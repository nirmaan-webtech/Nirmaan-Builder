/* global NirmaanBuilderAdminConfig */
(function () {
	'use strict';
	const cfg=window.NirmaanBuilderAdminConfig;if(!cfg)return;
	const api=(path,opts={})=>fetch(cfg.apiRoot+path,{...opts,headers:{'Content-Type':'application/json','X-WP-Nonce':cfg.nonce}}).then(async r=>{const d=await r.json();if(!r.ok)throw new Error(d.message||'Request failed');return d;});
	const form=document.getElementById('nirmaan-create-template');
	if(form)form.addEventListener('submit',async e=>{e.preventDefault();try{const f=new FormData(form);const t=await api('templates',{method:'POST',body:JSON.stringify({title:f.get('title')})});await api('templates/'+t.id,{method:'POST',body:JSON.stringify({conditions:{include:[{type:f.get('condition')}],exclude:[]}})});window.location.reload();}catch(err){window.alert(err.message);}});
	document.querySelectorAll('.nirmaan-delete-template').forEach(b=>b.addEventListener('click',async()=>{if(!window.confirm('Delete this template and its parts?'))return;try{await api('templates/'+b.dataset.id,{method:'DELETE'});window.location.reload();}catch(e){window.alert(e.message);}}));
	document.querySelectorAll('.nirmaan-delete-library').forEach(b=>b.addEventListener('click',async()=>{if(!window.confirm('Delete this saved layout?'))return;try{await api('library/'+b.dataset.id,{method:'DELETE'});window.location.reload();}catch(e){window.alert(e.message);}}));
})();
