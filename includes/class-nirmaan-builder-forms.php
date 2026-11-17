<?php
namespace NirmaanBuilder;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Forms {
 public function hooks() {
  add_action( 'init', array( $this, 'register_submission_type' ) );
  add_action( 'rest_api_init', array( $this, 'register_routes' ) );
  add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
  add_action( 'admin_post_nirmaan_delete_submission', array( $this, 'delete_submission' ) );
 }
 public function register_submission_type() {
  register_post_type( 'nirmaan_submission', array( 'labels'=>array('name'=>__('Form Submissions','nirmaan-builder'),'singular_name'=>__('Form Submission','nirmaan-builder')), 'public'=>false, 'show_ui'=>false, 'show_in_rest'=>false, 'supports'=>array('title'), 'capability_type'=>'post', 'map_meta_cap'=>true ) );
 }
 public function register_routes() {
  register_rest_route( 'nirmaan-builder/v1', '/form-submit', array( 'methods'=>'POST', 'callback'=>array($this,'submit'), 'permission_callback'=>'__return_true' ) );
 }
 public function register_admin_page() {
  add_submenu_page( 'nirmaan-builder', 'Form Submissions', 'Form Submissions', 'manage_options', 'nirmaan-form-submissions', array($this,'render_submissions') );
 }
 private function token( $payload ) {
  $body = rtrim(strtr(base64_encode(wp_json_encode($payload)), '+/', '-_'), '=');
  return $body.'.'.hash_hmac('sha256',$body,wp_salt('auth'));
 }
 private function decode_token( $token ) {
  $parts=explode('.',(string)$token);
  if(count($parts)!==2 || !hash_equals(hash_hmac('sha256',$parts[0],wp_salt('auth')),$parts[1]))return false;
  $json=base64_decode(strtr($parts[0], '-_', '+/'));
  $data=json_decode((string)$json,true);
  if(!is_array($data)||empty($data['expires'])||$data['expires']<time()||!is_array($data['fields']??null))return false;
  return $data;
 }
 public static function render( $node_id, $settings, $class, $style ) {
  wp_enqueue_script('nirmaan-builder-forms',NIRMAAN_BUILDER_URL.'assets/js/forms.js',array('wp-i18n'),NIRMAAN_BUILDER_VERSION,true);wp_set_script_translations('nirmaan-builder-forms','nirmaan-builder',NIRMAAN_BUILDER_PATH.'languages');
  $fields=is_array($settings['form_fields']??null)?array_slice($settings['form_fields'],0,20):array();
  $fields=array_values(array_filter($fields,'is_array'));
  $payload=array('form_id'=>sanitize_key($node_id),'title'=>sanitize_text_field($settings['form_title']??'Website enquiry'),'notify'=>sanitize_email($settings['notification_email']??get_option('admin_email')),'fields'=>$fields,'success_message'=>sanitize_text_field($settings['success_message']??__('Thank you. Your submission has been received.','nirmaan-builder')),'webhook_url'=>esc_url_raw($settings['webhook_url']??''),'consent_required'=>!empty($settings['form_consent_required']),'consent_label'=>sanitize_text_field($settings['form_consent_label']??__('I agree to the privacy policy','nirmaan-builder')),'privacy_policy_url'=>esc_url_raw($settings['privacy_policy_url']??''),'expires'=>time()+7200);
  $body=rtrim(strtr(base64_encode(wp_json_encode($payload)), '+/', '-_'), '=');
  $token=$body.'.'.hash_hmac('sha256',$body,wp_salt('auth'));
  $html='<form class="nb-form '.$class.'" method="post" action="'.esc_url(rest_url('nirmaan-builder/v1/form-submit')).'" data-nb-form style="'.esc_attr($style).'">';
  $html.='<input type="hidden" name="token" value="'.esc_attr($token).'"><label class="nb-form-honeypot" aria-hidden="true">Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label>';
  foreach($fields as $index=>$field){
   $name=sanitize_key($field['name']??('field_'.($index+1)));if(!$name)continue;
   $label=sanitize_text_field($field['label']??$name);$type=sanitize_key($field['type']??'text');$required=!empty($field['required']);$attr=$required?' required':'';
   $show_if=is_array($field['show_if']??null)?$field['show_if']:array();$depends_on=sanitize_key($show_if['field']??'');$equals=sanitize_text_field($show_if['equals']??'');$condition=$depends_on?' data-nb-depends="'.esc_attr($depends_on).'" data-nb-equals="'.esc_attr($equals).'"':'';
   $html.='<div class="nb-form-field"'.$condition.'><span>'.esc_html($label).($required?' *':'').'</span>';
   if('textarea'===$type){$html.='<textarea name="fields['.esc_attr($name).']"'. $attr .' maxlength="5000"></textarea>';}
   elseif(in_array($type,array('select','checkbox'),true)&&'select'===$type){$html.='<select name="fields['.esc_attr($name).']"'.$attr.'><option value="">'.esc_html__('Choose an option','nirmaan-builder').'</option>';foreach(array_slice((array)($field['options']??array()),0,30) as $option)$html.='<option value="'.esc_attr($option).'">'.esc_html($option).'</option>';$html.='</select>';}
   elseif('checkbox'===$type){$html.='<input type="checkbox" name="fields['.esc_attr($name).']" value="1"'.($required?' required':'').'>';}
   elseif('radio'===$type){foreach(array_slice((array)($field['options']??array()),0,30) as $option){$html.='<label class="nb-form-choice"><input type="radio" name="fields['.esc_attr($name).']" value="'.esc_attr($option).'"'.($required?' required':'').'> '.esc_html($option).'</label>';}}
   else{$input_type=in_array($type,array('email','number','tel','url','date'),true)?$type:'text';$html.='<input type="'.esc_attr($input_type).'" name="fields['.esc_attr($name).']"'. $attr .' maxlength="500">';}
   $html.='</div>';
  }
  if(!empty($payload['consent_required'])){$html.='<label class="nb-form-consent"><input type="checkbox" name="consent" value="1" required> '.esc_html($payload['consent_label']);if(!empty($payload['privacy_policy_url']))$html.=' <a href="'.esc_url($payload['privacy_policy_url']).'" target="_blank" rel="noopener noreferrer">'.esc_html__('Privacy policy','nirmaan-builder').'</a>';$html.='</label>';}$html.='<button type="submit" data-processing-label="'.esc_attr($settings['processing_label']??__('Sending…','nirmaan-builder')).'">'.esc_html($settings['submit_label']??__('Submit','nirmaan-builder')).'</button><p class="nb-form-status" role="status" aria-live="polite"></p></form>';
  return $html;
 }
 public function submit( $request ) {
  $params=$request->get_json_params();if(!is_array($params))$params=$request->get_body_params();
  if(!empty($params['website']))return new \WP_Error('form_spam',__('Submission rejected.','nirmaan-builder'),array('status'=>400));
  $data=$this->decode_token($params['token']??'');if(!$data)return new \WP_Error('invalid_form_token',__('This form has expired. Refresh the page and try again.','nirmaan-builder'),array('status'=>403));if(!empty($data['consent_required'])&&empty($params['consent']))return new \WP_Error('form_consent_required',__('Please accept the privacy policy before submitting.','nirmaan-builder'),array('status'=>400));
  $ip=(string)($_SERVER['REMOTE_ADDR']??'unknown');$rate_key='nb_form_rate_'.hash_hmac('sha256',$ip,wp_salt('auth'));
  $count=(int)get_transient($rate_key);if($count>=8)return new \WP_Error('form_rate_limited',__('Too many submissions. Please try again later.','nirmaan-builder'),array('status'=>429));
  set_transient($rate_key,$count+1,HOUR_IN_SECONDS);
  $submitted=is_array($params['fields']??null)?$params['fields']:array();$clean=array();$errors=array();
  foreach(array_slice($data['fields'],0,20) as $index=>$field){
   if(!is_array($field))continue;$name=sanitize_key($field['name']??('field_'.($index+1)));if(!$name)continue;
   $type=sanitize_key($field['type']??'text');$value=$submitted[$name]??'';$required=!empty($field['required']);$condition=is_array($field['show_if']??null)?$field['show_if']:array();$dependency=sanitize_key($condition['field']??'');if($dependency&&sanitize_text_field((string)($submitted[$dependency]??''))!==sanitize_text_field((string)($condition['equals']??'')))continue;
   if('checkbox'===$type)$value=!empty($value)?'Yes':'';
   elseif('textarea'===$type)$value=sanitize_textarea_field(is_scalar($value)?(string)$value:'');
   elseif('email'===$type){$value=sanitize_email(is_scalar($value)?(string)$value:'');if($value&&!is_email($value))$errors[$name]=__('Enter a valid email address.','nirmaan-builder');}
   elseif('number'===$type){$value=is_scalar($value)?(string)$value:'';if(''!==$value&&!is_numeric($value))$errors[$name]=__('Enter a valid number.','nirmaan-builder');}
   elseif('url'===$type){$value=esc_url_raw(is_scalar($value)?(string)$value:'');if(''!==$value&&!wp_http_validate_url($value))$errors[$name]=__('Enter a valid URL.','nirmaan-builder');}
   elseif('tel'===$type){$value=sanitize_text_field(is_scalar($value)?(string)$value:'');if(''!==$value&&!preg_match('/^[0-9+().\\s-]{5,30}$/',$value))$errors[$name]=__('Enter a valid phone number.','nirmaan-builder');}
   elseif('date'===$type){$value=sanitize_text_field(is_scalar($value)?(string)$value:'');$date=\\DateTime::createFromFormat('!Y-m-d',$value);if(''!==$value&&(!$date||$date->format('Y-m-d')!==$value))$errors[$name]=__('Enter a valid date.','nirmaan-builder');}
   elseif(in_array($type,array('select','radio'),true)){$value=sanitize_text_field(is_scalar($value)?(string)$value:'');$options=array_map('sanitize_text_field',array_slice((array)($field['options']??array()),0,30));if(''!==$value&&!in_array($value,$options,true))$errors[$name]=__('Choose a valid option.','nirmaan-builder');}
   else $value=sanitize_text_field(is_scalar($value)?(string)$value:'');
   if($required&&''===$value)$errors[$name]=__('This field is required.','nirmaan-builder');
   $clean[$name]=is_string($value)?substr($value,0,5000):$value;
  }
  if($errors)return new \WP_Error('invalid_form_fields',__('Please check the highlighted form fields.','nirmaan-builder'),array('status'=>400,'fields'=>$errors));
  $post_id=wp_insert_post(array('post_type'=>'nirmaan_submission','post_status'=>'private','post_title'=>sanitize_text_field($data['title']??'Form submission').' — '.current_time('mysql')),true);
  if(is_wp_error($post_id))return $post_id;
  update_post_meta($post_id,'_nb_form_id',sanitize_key($data['form_id']??''));update_post_meta($post_id,'_nb_submission_fields',$clean);
  $lines=array();foreach($clean as $key=>$value)$lines[]=sanitize_text_field($key).': '.(is_scalar($value)?(string)$value:'');
  $to=is_email($data['notify']??'')?$data['notify']:get_option('admin_email');
  wp_mail($to,'New form submission: '.sanitize_text_field($data['title']??'Website enquiry'),implode("\n",$lines),array('Content-Type: text/plain; charset=UTF-8'));
  do_action('nirmaan_builder_form_submitted',$post_id,$clean,$data);
  $webhook=esc_url_raw($data['webhook_url']??'');if($webhook&&wp_http_validate_url($webhook)){wp_safe_remote_post($webhook,array('timeout'=>5,'redirection'=>0,'headers'=>array('Content-Type'=>'application/json'),'body'=>wp_json_encode(array('form_id'=>$data['form_id']??'','submission_id'=>$post_id,'fields'=>$clean))));}
  return rest_ensure_response(array('submitted'=>true,'message'=>sanitize_text_field($data['success_message']??__('Thank you. Your submission has been received.','nirmaan-builder'))));
 }
 public function render_submissions() {
  if(!current_user_can('manage_options'))wp_die(esc_html__('Permission denied.','nirmaan-builder'));
  $items=get_posts(array('post_type'=>'nirmaan_submission','post_status'=>'private','numberposts'=>100,'orderby'=>'date','order'=>'DESC'));
  echo '<div class="wrap"><h1>'.esc_html__('Form Submissions','nirmaan-builder').'</h1>';
  if(!$items){echo '<p>'.esc_html__('No submissions received yet.','nirmaan-builder').'</p></div>';return;}
  echo '<table class="widefat striped"><thead><tr><th>'.esc_html__('Received','nirmaan-builder').'</th><th>'.esc_html__('Form','nirmaan-builder').'</th><th>'.esc_html__('Submitted data','nirmaan-builder').'</th></tr></thead><tbody>';
  foreach($items as $item){$fields=get_post_meta($item->ID,'_nb_submission_fields',true);echo '<tr><td>'.esc_html(get_the_date('Y-m-d H:i',$item)).'</td><td>'.esc_html(get_post_meta($item->ID,'_nb_form_id',true)).'</td><td>';if(is_array($fields))foreach($fields as $key=>$value)echo '<strong>'.esc_html($key).':</strong> '.esc_html((string)$value).'<br>';$delete=wp_nonce_url(admin_url('admin-post.php?action=nirmaan_delete_submission&submission_id='.$item->ID),'nirmaan_delete_submission_'.$item->ID);echo '<a href="'.esc_url($delete).'">'.esc_html__('Delete','nirmaan-builder').'</a></td></tr>';}
  echo '</tbody></table></div>';
 }
 public function delete_submission() {
  $id=absint($_GET['submission_id']??0);
  if(!current_user_can('manage_options')||!$id||'nirmaan_submission'!==get_post_type($id))wp_die(esc_html__('Permission denied.','nirmaan-builder'));
  check_admin_referer('nirmaan_delete_submission_'.$id);
  wp_delete_post($id,true);
  wp_safe_redirect(admin_url('admin.php?page=nirmaan-form-submissions'));
  exit;
 }
}