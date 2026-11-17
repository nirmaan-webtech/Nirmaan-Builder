/* global NirmaanBuilderAdminConfig */
(function(){
 'use strict';const t=(value)=>window.wp&&window.wp.i18n?window.wp.i18n.__(value,'nirmaan-builder'):value;const cfg=window.NirmaanBuilderAdminConfig;if(!cfg)return;
 const api=(path,opts={})=>fetch(cfg.apiRoot+path,{...opts,headers:{'Content-Type':'application/json','X-WP-Nonce':cfg.nonce}}).then(async r=>{const d=await r.json();if(!r.ok)throw new Error(d.message||'Request failed');return d;});
 const settingsForm=document.getElementById('nirmaan-theme-settings');
 if(settingsForm){
  settingsForm.addEventListener('submit',async e=>{
   e.preventDefault();const status=document.getElementById('nirmaan-settings-status');status.textContent=t('Saving…');
   const data=Object.fromEntries(new FormData(settingsForm).entries());
   data.logo_id=Number(data.logo_id||0);data.favicon_id=Number(data.favicon_id||0);
   try{await api('settings',{method:'POST',body:JSON.stringify(data)});status.textContent=t('Settings saved.');}catch(err){status.textContent=t('Save failed: ')+err.message;}
  });
  document.getElementById('nirmaan-reset-settings')?.addEventListener('click',async()=>{
   if(!confirm(t('Reset all Theme Options to defaults?')))return;
   const defaults={logo_id:0,favicon_id:0,primary_color:'#2271b1',secondary_color:'#172033',text_color:'#1f2937',background_color:'#ffffff',font_family:'system',base_font_size:'16px',container_width:'1200px',section_spacing:'32px',button_radius:'4px',custom_css:''};
   try{await api('settings',{method:'POST',body:JSON.stringify(defaults)});location.reload();}catch(err){alert(err.message);}
  });
  document.querySelectorAll('.nb-select-media').forEach(button=>button.addEventListener('click',()=>{
   const frame=wp.media({title:'Select image',button:{text:'Use image'},multiple:false,library:{type:'image'}});
   frame.on('select',()=>{const item=frame.state().get('selection').first().toJSON();settingsForm.querySelector('[name="'+button.dataset.target+'"]').value=item.id;const preview=document.querySelector('[data-preview="'+button.dataset.preview+'"]');preview.innerHTML='';const img=document.createElement('img');img.src=item.sizes?.medium?.url||item.url;img.alt='Selected image';preview.appendChild(img);});
   frame.open();
  }));
  document.querySelectorAll('.nb-clear-media').forEach(button=>button.addEventListener('click',()=>{settingsForm.querySelector('[name="'+button.dataset.target+'"]').value='';const preview=document.querySelector('[data-preview="'+button.dataset.preview+'"]');preview.textContent=t('No image selected');}));
 }
 const form=document.getElementById('nirmaan-create-template');
 if(form)form.addEventListener('submit',async e=>{e.preventDefault();try{const f=new FormData(form);const t=await api('templates',{method:'POST',body:JSON.stringify({title:f.get('title'),priority:Number(f.get('priority')||10)})});await api('templates/'+t.id,{method:'POST',body:JSON.stringify({conditions:{include:[{type:f.get('condition')}],exclude:[]}})});location.reload();}catch(err){alert(err.message);}});
 const templateRows=Array.from(document.querySelectorAll('.nb-template-row'));
 const searchTemplates=()=>{const q=(document.getElementById('nb-template-search')?.value||'').trim().toLowerCase();const status=document.getElementById('nb-template-status-filter')?.value||'all';let visible=0;templateRows.forEach(row=>{const matchName=(row.querySelector('.nb-template-title')?.value||'').toLowerCase().includes(q);const matchStatus='all'===status||row.querySelector('.nb-template-status')?.value===status;row.hidden=!(matchName&&matchStatus);if(!row.hidden)visible++;});const count=document.getElementById('nb-template-count');if(count)count.textContent=visible+' of '+templateRows.length+' templates';const empty=document.querySelector('.nb-template-empty-filter');if(empty)empty.hidden=visible>0||templateRows.length===0;};
 document.getElementById('nb-template-search')?.addEventListener('input',searchTemplates);
 document.getElementById('nb-template-status-filter')?.addEventListener('change',searchTemplates);
 if(templateRows.length)searchTemplates();
 document.querySelectorAll('.nirmaan-save-template-details').forEach(b=>b.addEventListener('click',async()=>{const row=b.closest('tr');const title=row.querySelector('.nb-template-title').value.trim();const status=row.querySelector('.nb-template-status').value;if(!title){alert(t('Template name cannot be empty.'));return;}b.disabled=true;try{await api('templates/'+b.dataset.id,{method:'POST',body:JSON.stringify({title,status})});row.dataset.templateName=title.toLowerCase();row.dataset.templateStatus=status;b.textContent=t('Saved');searchTemplates();setTimeout(()=>{b.textContent=t('Save Details');b.disabled=false;},1200);}catch(e){alert('Could not save template details: '+e.message);b.disabled=false;}}));
 document.querySelectorAll('.nirmaan-duplicate-template').forEach(b=>b.addEventListener('click',async()=>{b.disabled=true;try{await api('templates/'+b.dataset.id+'/duplicate',{method:'POST',body:JSON.stringify({})});location.reload();}catch(e){alert('Could not duplicate template: '+e.message);b.disabled=false;}}));
 document.querySelectorAll('.nirmaan-save-template-rules').forEach(b=>b.addEventListener('click',async()=>{const id=b.dataset.id,row=b.closest('tr');const priority=Number(row.querySelector('[name=priority]').value||10);const include=Array.from(row.querySelector('[name=include]').selectedOptions).map(o=>({type:o.value}));const exclude=Array.from(row.querySelector('[name=exclude]').selectedOptions).map(o=>({type:o.value}));try{await api('templates/'+id,{method:'POST',body:JSON.stringify({priority,conditions:{include,exclude}})});b.textContent=t('Saved');setTimeout(()=>b.textContent=t('Save Rules'),1400);}catch(e){alert(e.message);}}));
 document.querySelectorAll('.nirmaan-save-part-settings').forEach(b=>b.addEventListener('click',async()=>{const row=b.closest('tr');const part_settings={};['header','body','footer'].forEach(part=>{part_settings[part]={active:!!row.querySelector('[name="part_active_'+part+'"]')?.checked,condition:row.querySelector('[name="part_condition_'+part+'"]')?.value||''};});try{await api('templates/'+b.dataset.id,{method:'POST',body:JSON.stringify({part_settings})});b.textContent=t('Saved');setTimeout(()=>b.textContent=t('Save Sections'),1400);}catch(e){alert('Could not save section settings: '+e.message);}}));
 document.querySelectorAll('.nirmaan-delete-template').forEach(b=>b.addEventListener('click',async()=>{if(!confirm(t('Delete this template and its parts?')))return;try{await api('templates/'+b.dataset.id,{method:'DELETE'});location.reload();}catch(e){alert(e.message);}}));
 document.querySelectorAll('.nirmaan-delete-library').forEach(b=>b.addEventListener('click',async()=>{if(!confirm(t('Delete this saved layout?')))return;try{await api('library/'+b.dataset.id,{method:'DELETE'});location.reload();}catch(e){alert(e.message);}}));
 const exportBtn=document.getElementById('nirmaan-export-library');if(exportBtn)exportBtn.onclick=async()=>{try{const data=await api('library/export');const a=document.createElement('a');a.href=URL.createObjectURL(new Blob([JSON.stringify(data,null,2)],{type:'application/json'}));a.download='nirmaan-builder-library.json';a.click();URL.revokeObjectURL(a.href);}catch(e){alert(e.message);}};
 const importInput=document.getElementById('nirmaan-import-library');if(importInput)importInput.onchange=async()=>{const file=importInput.files[0];if(!file)return;try{const data=JSON.parse(await file.text());const result=await api('library/import',{method:'POST',body:JSON.stringify(data)});alert('Imported '+result.imported+' layouts');location.reload();}catch(e){alert('Import failed: '+e.message);}finally{importInput.value='';}};
})();