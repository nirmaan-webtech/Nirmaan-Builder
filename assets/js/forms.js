(function(){
 'use strict';
 const t=(value)=>window.wp&&window.wp.i18n?window.wp.i18n.__(value,'nirmaan-builder'):value;
 document.addEventListener('change',function(event){
  const form=event.target.closest('form[data-nb-form]');if(!form)return;
  form.querySelectorAll('[data-nb-depends]').forEach(function(field){
   const name=field.getAttribute('data-nb-depends'),expected=field.getAttribute('data-nb-equals');
   const controls=Array.from(form.querySelectorAll('[name="fields['+CSS.escape(name)+']"]'));
   const current=controls.length?(controls[0].type==='radio'?(controls.find(control=>control.checked)||{}).value||'':controls[0].type==='checkbox'?(controls[0].checked?'1':''):controls[0].value):'';
   const visible=current===expected;field.hidden=!visible;
   field.querySelectorAll('input,select,textarea').forEach(control=>{control.disabled=!visible;});
  });
 });
 const initializeForms=function(){document.querySelectorAll('form[data-nb-form]').forEach(function(form){form.dispatchEvent(new Event('change',{bubbles:true}));});};
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',initializeForms,{once:true});else initializeForms();
 document.addEventListener('submit',async function(event){
  const form=event.target.closest('form[data-nb-form]');if(!form)return;event.preventDefault();
  const button=form.querySelector('button[type="submit"]'),status=form.querySelector('.nb-form-status'),originalLabel=button?button.textContent:'';if(button){button.disabled=true;button.textContent=button.dataset.processingLabel||t('Sending…');}if(status)status.textContent=t('Sending…');
  const data=new FormData(form),fields={};for(const [key,value] of data.entries()){const match=key.match(/^fields\[([^\]]+)\]$/);if(match)fields[match[1]]=value;}
  const payload={token:data.get('token'),website:data.get('website')||'',consent:data.get('consent')||'',fields};
  try{const response=await fetch(form.action,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});const result=await response.json();if(!response.ok)throw new Error(result.message||t('Submission failed'));form.reset();if(status)status.textContent=result.message||t('Thank you. Your submission has been received.');}
  catch(error){if(status)status.textContent=error.message||t('Could not send the form. Please try again.');form.querySelectorAll('[aria-invalid="true"]').forEach(function(control){control.removeAttribute('aria-invalid');});try{const details=error&&error.fields?error.fields:null;if(details&&typeof details==='object')Object.keys(details).forEach(function(name){const control=form.querySelector('[name="fields['+CSS.escape(name)+']"]');if(control){control.setAttribute('aria-invalid','true');control.setAttribute('aria-describedby','nb-error-'+name);const wrapper=control.closest('.nb-form-field');if(wrapper&&!wrapper.querySelector('.nb-field-error')){const message=document.createElement('small');message.className='nb-field-error';message.id='nb-error-'+name;message.textContent=details[name];wrapper.appendChild(message);}}});}catch(ignore){}}
  finally{if(button){button.disabled=false;button.textContent=originalLabel;}}
 });
})();