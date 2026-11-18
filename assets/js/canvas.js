(function(){
'use strict';
const cfg=window.NirmaanCanvasConfig, root=document.getElementById('nb-canvas-root'), inspector=document.getElementById('nb-canvas-inspector'), status=document.getElementById('nb-canvas-status');
if(!cfg||!root||!cfg.targetId)return;\nconst targetPath=()=>cfg.targetType==='template'?'templates/'+cfg.targetId:'content/'+cfg.targetId;
const types=['heading','text','image','button','video','divider','spacer','gallery','slider','tabs','accordion','blog','portfolio','query_loop','form','product_title','product_price','product_image','product_gallery','product_add_to_cart','product_description','product_rating','product_meta','product_tabs','related_products','shop_products','cart','checkout','mini_cart'];
const labels={heading:'Heading',text:'Text',image:'Image',button:'Button',video:'Video',divider:'Divider',spacer:'Spacer',gallery:'Gallery',slider:'Slider',tabs:'Tabs',accordion:'Accordion',blog:'Blog',portfolio:'Portfolio',query_loop:'Query Loop',form:'Form',product_title:'Product Title',product_price:'Product Price',product_image:'Product Image',product_gallery:'Product Gallery',product_add_to_cart:'Add to Cart',product_description:'Product Description',product_rating:'Product Rating',product_meta:'Product Meta',product_tabs:'Product Tabs',related_products:'Related Products',shop_products:'Shop Products',cart:'Cart',checkout:'Checkout',mini_cart:'Mini Cart'};
const api=(path,opts={})=>fetch(cfg.apiRoot+path,{...opts,credentials:'same-origin',headers:{'Content-Type':'application/json','X-WP-Nonce':cfg.nonce,...(opts.headers||{})}}).then(async r=>{const d=await r.json();if(!r.ok)throw new Error(d.message||'Request failed');return d;});
const clone=o=>JSON.parse(JSON.stringify(o));
const uid=()=> 'nb_'+Math.random().toString(36).slice(2,10);
let doc={nodes:[]}, selected='', device='desktop', tab='content', dirty=false, past=[], future=[], snapshot='', saving=false;
const setStatus=s=>{if(status)status.textContent=s;};
const node=(id, type, settings={}, children=[])=>({id:id||uid(),type,settings,children});
const section=()=>node('', 'section',{desktop:{width:'100%',padding:'32px',backgroundColor:'#ffffff'}},[row()]);
const row=()=>node('', 'row',{desktop:{display:'flex',gap:'16px',width:'100%',flexWrap:'wrap'}},[column()]);
const column=()=>node('', 'column',{desktop:{flexBasis:'100%',padding:'10px'}},[]);
const find=(nodes,id,parent=null)=>{for(const n of nodes||[]){if(n.id===id)return {n,parent,nodes};const hit=find(n.children,id,n);if(hit)return hit;}return null;};
const selectedNode=()=>find(doc.nodes,selected)?.n||null;
const all=(nodes,fn)=>{(nodes||[]).forEach(n=>{fn(n);all(n.children,fn);});};
const record=()=>{past.push(JSON.stringify(doc));if(past.length>80)past.shift();future=[];dirty=true;setStatus('Unsaved changes');};
const applyStyle=(el,settings)=>{const s={...(settings.desktop||{}),...(settings[device]||{})};Object.entries(s).forEach(([k,v])=>{if(k==='hover'||v===''||v==null)return;const map={backgroundColor:'background-color',borderColor:'border-color',borderWidth:'border-width',borderRadius:'border-radius',fontSize:'font-size',fontWeight:'font-weight',lineHeight:'line-height',textAlign:'text-align',maxWidth:'max-width',minHeight:'min-height',flexBasis:'flex-basis',justifyContent:'justify-content',alignItems:'align-items',flexDirection:'flex-direction',flexWrap:'flex-wrap',gridTemplateColumns:'grid-template-columns',zIndex:'z-index'};el.style.setProperty(map[k]||k.replace(/[A-Z]/g,m=>'-'+m.toLowerCase()),String(v));});};
const tool=(label,action,id)=>{const b=document.createElement('button');b.type='button';b.textContent=label;b.title=label;b.addEventListener('click',e=>{e.stopPropagation();action(id);});return b;};
function modulePreview(n){
 const s=n.settings||{}, c=s.content||s.label||labels[n.type]||n.type;
 if(n.type==='heading'){const e=document.createElement('h'+Math.min(6,Math.max(1,Number(s.level)||2)));e.textContent=c||'Heading';return e;}
 if(n.type==='text'){const e=document.createElement('div');e.textContent=c||'Add your text here';return e;}
 if(n.type==='image'||n.type==='product_image'||n.type==='product_gallery'){const e=document.createElement('img');e.src=s.url||'https://placehold.co/640x360?text=Image';e.alt=s.alt||'';return e;}
 if(n.type==='button'||n.type==='product_add_to_cart'){const e=document.createElement('a');e.className='nb-button';e.href=s.url||'#';e.textContent=c||'Button';return e;}
 if(n.type==='divider'){return document.createElement('hr');}
 if(n.type==='spacer'){const e=document.createElement('div');e.style.height=(s.height||48)+'px';return e;}
 if(n.type==='video'){const e=document.createElement('div');e.textContent=s.url?'Video: '+s.url:'Add video URL';return e;}
 if(['gallery','slider','tabs','accordion'].includes(n.type)){const e=document.createElement('div');e.textContent=(labels[n.type]||n.type)+' · '+((s.items||[]).length)+' items';return e;}
 if(n.type==='form'){const e=document.createElement('div');e.textContent='Form · '+(s.form_title||'Website enquiry');return e;}
 if(['blog','portfolio','query_loop','shop_products','related_products'].includes(n.type)){const e=document.createElement('div');e.textContent=(labels[n.type]||n.type)+' preview';return e;}
 const e=document.createElement('div');e.textContent=labels[n.type]||n.type;return e;
}
function renderNode(n){
 const structural=['section','fullwidth_section','specialty_section','row','column','container','columns','query_loop'].includes(n.type);
 const el=document.createElement(structural?'div':'div');
 el.className='nbc-node '+(structural?'nbc-'+n.type:'nbc-module')+(selected===n.id?' is-selected':'');
 el.dataset.id=n.id;el.dataset.type=n.type;applyStyle(el,n.settings||{});
 if(structural){(n.children||[]).forEach(ch=>el.appendChild(renderNode(ch)));if(!n.children?.length){const empty=document.createElement('div');empty.className='nbc-empty-col';empty.textContent='Empty · add a module';el.appendChild(empty);}}
 else el.appendChild(modulePreview(n));
 const tools=document.createElement('div');tools.className='nbc-node-tools';tools.append(tool('⚙',()=>{selected=n.id;tab='content';render();},n.id),tool('＋',()=>addSibling(n.id),n.id),tool('↑',()=>move(n.id,-1),n.id),tool('↓',()=>move(n.id,1),n.id),tool('⧉',()=>duplicate(n.id),n.id),tool('×',()=>remove(n.id),n.id));el.appendChild(tools);
 el.addEventListener('click',e=>{e.stopPropagation();selected=n.id;render();});
 return el;
}
function renderCanvas(){root.innerHTML='';if(!doc.nodes.length){const p=document.createElement('p');p.className='nb-canvas-empty';p.textContent='Canvas is empty. Add a Section to begin.';root.appendChild(p);return;}doc.nodes.forEach(n=>root.appendChild(renderNode(n)));}
function field(label,key,value,type='text',options=null,change=null){
 const wrap=document.createElement('label');wrap.className='nbc-field';wrap.append(document.createTextNode(label));
 let input;
 if(type==='textarea'){input=document.createElement('textarea');input.value=value??'';}
 else if(type==='select'){input=document.createElement('select');(options||[]).forEach(o=>{const op=document.createElement('option');op.value=o[0];op.textContent=o[1];input.appendChild(op);});input.value=value??'';}
 else{input=document.createElement('input');input.type=type;input.value=value??'';}
 input.addEventListener(type==='textarea'?'input':'change',()=>change(input.value));wrap.appendChild(input);return wrap;
}
function group(text){const e=document.createElement('div');e.className='nbc-group';e.textContent=text;return e;}
function inspectorRender(){
 inspector.innerHTML='';const n=selectedNode();if(!n){inspector.innerHTML='<p class="nb-canvas-empty">Select an element on the canvas to edit its settings.</p>';return;}
 const tabs=document.createElement('div');tabs.className='nbc-inspector-tabs';['content','design','hover','advanced'].forEach(t=>{const b=document.createElement('button');b.textContent=t[0].toUpperCase()+t.slice(1);b.className=tab===t?'is-active':'';b.onclick=()=>{tab=t;inspectorRender();};tabs.appendChild(b);});inspector.appendChild(tabs);
 const body=document.createElement('div');body.className='nbc-inspector-body';inspector.appendChild(body);
 const s=n.settings||(n.settings={});
 const set=(key,val)=>{record();s[key]=val;renderCanvas();};
 if(tab==='content'){
  body.append(group('Element content'));
  if(!['section','row','column','container','columns','specialty_section','fullwidth_section'].includes(n.type)){
   if(['heading','text','button'].includes(n.type))body.append(field('Text / Content','content',s.content||'',n.type==='text'?'textarea':'text',null,v=>set('content',v)));
   if(n.type==='heading')body.append(field('Heading level','level',String(s.level||2),'select',[['1','H1'],['2','H2'],['3','H3'],['4','H4'],['5','H5'],['6','H6']],v=>set('level',Number(v))));
   if(['image','product_image','product_gallery','video','button'].includes(n.type))body.append(field(n.type==='video'?'Video URL':'URL','url',s.url||'','text',null,v=>set('url',v)));
   if(['image','product_image','product_gallery'].includes(n.type)){body.append(field('Alt text','alt',s.alt||'','text',null,v=>set('alt',v)));const pick=document.createElement('button');pick.type='button';pick.textContent='Choose from Media Library';pick.onclick=()=>{const frame=wp.media({title:'Select media',button:{text:'Use media'},multiple:false});frame.on('select',()=>{const a=frame.state().get('selection').first().toJSON();set('url',a.url);set('alt',a.alt||'');});frame.open();};body.append(pick);}
   if(n.type==='spacer')body.append(field('Height (px)','height',s.height||48,'number',null,v=>set('height',Math.max(0,Number(v)||0))));
   if(['gallery','slider','tabs','accordion'].includes(n.type)){body.append(field('Items JSON','items',JSON.stringify(s.items||[],null,2),'textarea',null,v=>{try{set('items',JSON.parse(v));}catch(e){setStatus('Items must be valid JSON');}}));}
   if(n.type==='form'){body.append(field('Form title','form_title',s.form_title||'Website enquiry','text',null,v=>set('form_title',v)));body.append(field('Submit label','submit_label',s.submit_label||'Submit','text',null,v=>set('submit_label',v)));}
   if(['query_loop','blog','portfolio','shop_products','related_products'].includes(n.type)){body.append(field('Columns','columns',String(s.query?.columns||3),'number',null,v=>set('query',{...(s.query||{}),columns:Math.min(6,Math.max(1,Number(v)||3))})));body.append(field('Items per page','posts_per_page',String(s.query?.posts_per_page||6),'number',null,v=>set('query',{...(s.query||{}),posts_per_page:Math.min(24,Math.max(1,Number(v)||6))})));}
  }else{body.append(field('Admin label','label',s.label||'', 'text',null,v=>set('label',v)));}
 }else if(tab==='design'||tab==='hover'){
  const style=s[device]||(s[device]={});const target=tab==='hover'?(style.hover||(style.hover={})):style;
  body.append(group('Layout & spacing'));
  const keys=[['Width','width'],['Max width','maxWidth'],['Min height','minHeight'],['Padding','padding'],['Margin','margin'],['Gap','gap'],['Display','display'],['Flex direction','flexDirection'],['Flex wrap','flexWrap'],['Justify content','justifyContent'],['Align items','alignItems']];
  keys.forEach(([label,key])=>body.append(field(label,key,(target[key]??''),'text',null,v=>{record();target[key]=v;renderCanvas();})));
  body.append(group('Typography & appearance'));
  [['Font size','fontSize'],['Font weight','fontWeight'],['Line height','lineHeight'],['Text align','textAlign'],['Text color','color'],['Background color','backgroundColor'],['Border color','borderColor'],['Border width','borderWidth'],['Border radius','borderRadius'],['Box shadow','boxShadow']].forEach(([label,key])=>body.append(field(label,key,target[key]||'', 'text',null,v=>{record();target[key]=v;renderCanvas();})));
  body.append(group('Background'));
  body.append(field('Background gradient','backgroundImage',target.backgroundImage||'','text',null,v=>{record();target.backgroundImage=v;renderCanvas();}));
 }else{
  body.append(group('CSS & attributes'));
  body.append(field('HTML ID','html_id',s.html_id||'','text',null,v=>set('html_id',v)));
  body.append(field('CSS classes','css_class',s.css_class||'','text',null,v=>set('css_class',v)));
  body.append(field('Custom CSS declarations','custom_css',s.custom_css||'','textarea',null,v=>set('custom_css',v)));
  body.append(field('Scroll effect','scroll_effect',s.scroll_effect||'none','select',[['none','None'],['fade','Fade'],['slide-up','Slide up'],['zoom','Zoom']],v=>set('scroll_effect',v)));
  ['hide_desktop','hide_tablet','hide_mobile','hidden','locked'].forEach(k=>{const l=document.createElement('label');l.className='nbc-field';const i=document.createElement('input');i.type='checkbox';i.checked=!!s[k];i.onchange=()=>set(k,i.checked);l.append(i,document.createTextNode(' '+k.replace(/_/g,' ')));body.append(l);});
 }
 const actions=document.createElement('div');actions.className='nbc-inspector-actions';actions.append(tool('Duplicate',()=>duplicate(n.id)),tool('Delete',()=>remove(n.id)));inspector.appendChild(actions);
}
function render(){renderCanvas();inspectorRender();document.querySelectorAll('[data-device]').forEach(b=>b.classList.toggle('is-active',b.dataset.device===device));}
function parentTarget(type){
 let n=selectedNode();if(type==='section')return doc.nodes;
 if(type==='row'){if(n?.type==='section')return n.children||(n.children=[]);const sec=doc.nodes[doc.nodes.length-1];return sec?.children||(sec.children=[]);}
 if(type==='column'){if(n?.type==='row')return n.children||(n.children=[]);const sec=doc.nodes[doc.nodes.length-1];const r=sec?.children?.[sec.children.length-1];return r?.children||(r.children=[]);}
 if(type==='module'){if(n?.type==='column')return n.children||(n.children=[]);let col=null;all(doc.nodes,x=>{if(x.type==='column')col=x;});if(!col){if(!doc.nodes.length)doc.nodes.push(section());const sec=doc.nodes[doc.nodes.length-1];if(!sec.children?.length)sec.children=[row()];if(!sec.children[0].children?.length)sec.children[0].children=[column()];col=sec.children[0].children[0];}return col.children||(col.children=[]);}
 return doc.nodes;
}
function add(type){record();if(type==='section')doc.nodes.push(section());else if(type==='row')parentTarget('row').push(row());else if(type==='column')parentTarget('column').push(column());else parentTarget('module').push(node('',type,{content:labels[type]||type,desktop:{}}));selected=doc.nodes[doc.nodes.length-1].id;render();}
function addSibling(id){const hit=find(doc.nodes,id);if(!hit)return;record();const fresh=node('',hit.n.type,{content:labels[hit.n.type]||hit.n.type,desktop:{}});hit.nodes.splice(hit.nodes.indexOf(hit.n)+1,0,fresh);selected=fresh.id;render();}
function duplicate(id){const h=find(doc.nodes,id);if(!h)return;record();const cp=clone(h.n);const regen=x=>{x.id=uid();(x.children||[]).forEach(regen);};regen(cp);h.nodes.splice(h.nodes.indexOf(h.n)+1,0,cp);selected=cp.id;render();}
function remove(id){const h=find(doc.nodes,id);if(!h)return;record();h.nodes.splice(h.nodes.indexOf(h.n),1);selected='';render();}
function move(id,dir){const h=find(doc.nodes,id);if(!h)return;const i=h.nodes.indexOf(h.n),j=i+dir;if(j<0||j>=h.nodes.length)return;record();[h.nodes[i],h.nodes[j]]=[h.nodes[j],h.nodes[i]];render();}
async function save(){if(saving)return;saving=true;setStatus('Saving…');try{const payload=cfg.targetType==='template'?{part:cfg.part||'body',document:{nodes:doc.nodes}}:{nodes:doc.nodes};await api(targetPath(),{method:'POST',body:JSON.stringify(payload)});dirty=false;snapshot=JSON.stringify(doc);setStatus('Saved');}catch(e){setStatus('Save failed: '+e.message);}finally{saving=false;}}
async function autosave(){if(!dirty||saving)return;try{await api('autosave/'+(cfg.targetType||'content')+'/'+cfg.targetId,{method:'POST',body:JSON.stringify({part:cfg.targetType==='template'?(cfg.part||'body'):'body',document:{nodes:doc.nodes}})});setStatus('Autosaved');}catch(e){setStatus('Autosave failed');}}
function restore(raw){doc=raw&&Array.isArray(raw.nodes)?raw:{nodes:[]};selected='';snapshot=JSON.stringify(doc);dirty=false;render();}
function historyMove(back){const source=back?past:future,target=back?future:past;if(!source.length)return;target.push(JSON.stringify(doc));const next=source.pop();doc=JSON.parse(next||'{"nodes":[]}');dirty=JSON.stringify(doc)!==snapshot;render();setStatus(back?'Undo':'Redo');}
async function showLibrary(){try{const items=await api('library');if(!Array.isArray(items)||!items.length){if(confirm('Library is empty. Save the current layout?'))saveToLibrary();return;}const choice=prompt('Library layouts:\\n'+items.map((x,i)=>(i+1)+'. '+x.title).join('\\n')+'\\nEnter a number to insert, or leave blank to save current layout.');if(choice===null)return;if(choice.trim()===''){saveToLibrary();return;}const item=items[Number(choice)-1];if(!item)throw new Error('Invalid layout number');record();doc.nodes.push(...clone(item.document?.nodes||[]));selected='';render();}catch(e){setStatus('Library error: '+e.message);}}\nasync function saveToLibrary(){const title=prompt('Name this reusable layout:');if(!title)return;try{await api('library',{method:'POST',body:JSON.stringify({title,document:{nodes:doc.nodes},category:'layout'})});setStatus('Layout saved to Library');}catch(e){setStatus('Library save failed: '+e.message);}}\nfunction exportJSON(){const a=document.createElement('a');a.href=URL.createObjectURL(new Blob([JSON.stringify(doc,null,2)],{type:'application/json'}));a.download='nirmaan-page-'+cfg.targetId+'.json';a.click();URL.revokeObjectURL(a.href);}
async function load(){setStatus('Loading…');try{const d=await api(targetPath());restore(cfg.targetType==='template'?(d.parts?.[cfg.part||'body']||{nodes:[]}):(d.document||{nodes:[]}));setStatus('Ready');}catch(e){setStatus('Load failed: '+e.message);}}
document.querySelectorAll('[data-add]').forEach(b=>b.addEventListener('click',()=>add(b.dataset.add)));
const advancedTypes=['form','product_title','product_price','product_image','product_gallery','product_add_to_cart','product_description','product_rating','product_meta','product_tabs','related_products','shop_products','cart','checkout','mini_cart'];\nconst moduleList=document.getElementById('nb-canvas-module-list');types.filter(t=>cfg.canAdvanced||!advancedTypes.includes(t)).forEach(t=>{const b=document.createElement('button');b.type='button';b.textContent=labels[t]||t;b.onclick=()=>add(t);moduleList.appendChild(b);});
document.querySelectorAll('[data-device]').forEach(b=>b.addEventListener('click',()=>{device=b.dataset.device;const v=document.getElementById('nb-canvas-viewport');v.classList.remove('is-desktop','is-tablet','is-mobile');v.classList.add('is-'+device);render();}));
document.querySelectorAll('[data-action]').forEach(el=>{const a=el.dataset.action;if(a==='import'){el.addEventListener('change',async()=>{const f=el.files[0];if(!f)return;try{const data=JSON.parse(await f.text());if(!Array.isArray(data.nodes))throw new Error('Invalid document');record();restore(data);dirty=true;}catch(e){setStatus('Import failed: '+e.message);}el.value='';});return;}el.addEventListener('click',()=>{if(a==='save')save();if(a==='undo')historyMove(true);if(a==='redo')historyMove(false);if(a==='preview'&&cfg.previewUrl)window.open(cfg.previewUrl,'_blank','noopener');if(a==='export')exportJSON();if(a==='library')showLibrary();});});
document.addEventListener('keydown',e=>{if((e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='s'){e.preventDefault();save();}if((e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='z'){e.preventDefault();historyMove(!e.shiftKey);}});
window.addEventListener('beforeunload',e=>{if(dirty){e.preventDefault();e.returnValue='';}});
load();window.setInterval(autosave,30000);
})();