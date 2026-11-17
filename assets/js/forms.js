(function(){
 'use strict';
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
 document.addEventListener('DOMContentLoaded',function(){document.querySelectorAll('form[data-nb-form]').forEach(function(form){form.dispatchEvent(new Event('change',{bubbles:true}));});});
 document.addEventListener('submit',async function(event){
  const form=event.target.closest('form[data-nb-form]');if(!form)return;event.preventDefault();
  const button=form.querySelector('button[type="submit"]'),status=form.querySelector('.nb-form-status');if(button)button.disabled=true;if(status)status.textContent='Sending…';
  const data=new FormData(form),fields={};for(const [key,value] of data.entries()){const match=key.match(/^fields\[([^\]]+)\]$/);if(match)fields[match[1]]=value;}
  const payload={token:data.get('token'),website:data.get('website')||'',fields};
  try{const response=await fetch(form.action,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});const result=await response.json();if(!response.ok)throw new Error(result.message||'Submission failed');form.reset();if(status)status.textContent=result.message||'Thank you. Your submission has been received.';}
  catch(error){if(status)status.textContent=error.message||'Could not send the form. Please try again.';}
  finally{if(button)button.disabled=false;}
 });
})();