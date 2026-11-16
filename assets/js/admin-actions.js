/* global NirmaanBuilderAdminConfig */
(function(){
 'use strict';const cfg=window.NirmaanBuilderAdminConfig;if(!cfg)return;
 const api=(path,opts={})=>fetch(cfg.apiRoot+path,{...opts,headers:{'Content-Type':'application/json','X-WP-Nonce':cfg.nonce}}).then(async r=>{const d=await r.json();if(!r.ok)throw new Error(d.message||'Request failed');return d;});
 const settingsForm=document.getElementById('nirmaan-theme-settings');
 if(settingsForm){
  settingsForm.addEventListener('submit',async e=>{
   e.preventDefault();const status=document.getElementById('nirmaan-settings-status');status.textContent='Saving…';
   const data=Object.fromEntries(new FormData(settingsForm).entries());
   data.logo_id=Number(data.logo_id||0);data.favicon_id=Number(data.favicon_id||0);
   try{await api('settings',{method:'POST',body:JSON.stringify(data)});status.textContent='Settings saved.';}catch(err){status.textContent='Save failed: '+err.message;}
  });
  document.getElementById('nirmaan-reset-settings')?.addEventListener('click',async()=>{
   if(!confirm('Reset all Theme Options to defaults?'))return;
   const defaults={logo_id:0,favicon_id:0,primary_color:'#2271b1',secondary_color:'#172033',text_color:'#1f2937',background_color:'#ffffff',font_family:'system',base_font_size:'16px',container_width:'1200px',section_spacing:'32px',button_radius:'4px',custom_css:''};
   try{await api('settings',{method:'POST',body:JSON.stringify(defaults)});location.reload();}catch(err){alert(err.message);}
  });
  document.querySelectorAll('.nb-select-media').forEach(button=>button.addEventListener('click',()=>{
   const frame=wp.media({title:'Select image',button:{text:'Use image'},multiple:false,library:{type:'image'}});
   frame.on('select',()=>{const item=frame.state().get('selection').first().toJSON();settingsForm.querySelector('[name="'+button.dataset.target+'"]').value=item.id;const preview=document.querySelector('[data-preview="'+button.dataset.preview+'"]');preview.innerHTML='';const img=document.createElement('img');img.src=item.sizes?.medium?.url||item.url;img.alt='Selected image';preview.appendChild(img);});
   frame.open();
  }));
  document.querySelectorAll('.nb-clear-media').forEach(button=>button.addEventListener('click',()=>{settingsForm.querySelector('[name="'+button.dataset.target+'"]').value='';const preview=document.querySelector('[data-preview="'+button.dataset.preview+'"]');preview.textContent='No image selected';}));
 }
 const form=document.getElementById('nirmaan-create-template');
 if(form)form.addEventListener('submit',async e=>{e.preventDefault();try{const f=new FormData(form);const t=await api('templates',{method:'POST',body:JSON.stringify({title:f.get('title'),priority:Number(f.get('priority')||10)})});await api('templates/'+t.id,{method:'POST',body:JSON.stringify({conditions:{include:[{type:f.get('condition')}],exclude:[]}})});location.reload();}catch(err){alert(err.message);}});
 document.querySelectorAll('.nirmaan-save-template-rules').forEach(b=>b.addEventListener('click',async()=>{const id=b.dataset.id,row=b.closest('tr');const priority=Number(row.querySelector('[name=priority]').value||10);const include=Array.from(row.querySelector('[name=include]').selectedOptions).map(o=>({type:o.value}));const exclude=Array.from(row.querySelector('[name=exclude]').selectedOptions).map(o=>({type:o.value}));try{await api('templates/'+id,{method:'POST',body:JSON.stringify({priority,conditions:{include,exclude}})});b.textContent='Saved';setTimeout(()=>b.textContent='Save Rules',1400);}catch(e){alert(e.message);}}));
 document.querySelectorAll('.nirmaan-delete-template').forEach(b=>b.addEventListener('click',async()=>{if(!confirm('Delete this template and its parts?'))return;try{await api('templates/'+b.dataset.id,{method:'DELETE'});location.reload();}catch(e){alert(e.message);}}));
 document.querySelectorAll('.nirmaan-delete-library').forEach(b=>b.addEventListener('click',async()=>{if(!confirm('Delete this saved layout?'))return;try{await api('library/'+b.dataset.id,{method:'DELETE'});location.reload();}catch(e){alert(e.message);}}));
 const exportBtn=document.getElementById('nirmaan-export-library');if(exportBtn)exportBtn.onclick=async()=>{try{const data=await api('library/export');const a=document.createElement('a');a.href=URL.createObjectURL(new Blob([JSON.stringify(data,null,2)],{type:'application/json'}));a.download='nirmaan-builder-library.json';a.click();URL.revokeObjectURL(a.href);}catch(e){alert(e.message);}};
 const importInput=document.getElementById('nirmaan-import-library');if(importInput)importInput.onchange=async()=>{const file=importInput.files[0];if(!file)return;try{const data=JSON.parse(await file.text());const result=await api('library/import',{method:'POST',body:JSON.stringify(data)});alert('Imported '+result.imported+' layouts');location.reload();}catch(e){alert('Import failed: '+e.message);}finally{importInput.value='';}};
})();