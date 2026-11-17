<?php
namespace NirmaanBuilder;
use WP_REST_Server;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Rest {
 public function register_hooks() { add_action( 'rest_api_init', array( $this, 'register_routes' ) ); }
 public function register_routes() {
  register_rest_route( 'nirmaan-builder/v1', '/status', array( 'methods'=>WP_REST_Server::READABLE, 'callback'=>array($this,'status'), 'permission_callback'=>'__return_true' ) );
  register_rest_route( 'nirmaan-builder/v1', '/settings', array(
   array('methods'=>WP_REST_Server::READABLE,'callback'=>array($this,'get_settings'),'permission_callback'=>array($this,'can_manage_settings')),
   array('methods'=>WP_REST_Server::CREATABLE,'callback'=>array($this,'save_settings'),'permission_callback'=>array($this,'can_manage_settings'))
  ) );
  register_rest_route( 'nirmaan-builder/v1', '/style-presets', array(
   array('methods'=>WP_REST_Server::READABLE,'callback'=>array($this,'list_style_presets'),'permission_callback'=>array($this,'can_manage_presets')),
   array('methods'=>WP_REST_Server::CREATABLE,'callback'=>array($this,'create_style_preset'),'permission_callback'=>array($this,'can_manage_presets'))
  ) );
  register_rest_route( 'nirmaan-builder/v1', '/style-presets/(?P<id>[a-zA-Z0-9_-]+)', array(
   array('methods'=>'POST','callback'=>array($this,'update_style_preset'),'permission_callback'=>array($this,'can_manage_presets')),
   array('methods'=>WP_REST_Server::DELETABLE,'callback'=>array($this,'delete_style_preset'),'permission_callback'=>array($this,'can_manage_presets'))
  ) );
  register_rest_route( 'nirmaan-builder/v1', '/content/(?P<id>\\d+)', array(
   array('methods'=>WP_REST_Server::READABLE,'callback'=>array($this,'get_content'),'permission_callback'=>array($this,'can_edit_content')),
   array('methods'=>WP_REST_Server::CREATABLE,'callback'=>array($this,'save_content'),'permission_callback'=>array($this,'can_edit_content')) ) );
  register_rest_route( 'nirmaan-builder/v1', '/autosave/(?P<type>content|template|library)/(?P<id>\\d+)', array(
   array('methods'=>WP_REST_Server::READABLE,'callback'=>array($this,'get_autosave'),'permission_callback'=>array($this,'can_access_document')),
   array('methods'=>WP_REST_Server::CREATABLE,'callback'=>array($this,'save_autosave'),'permission_callback'=>array($this,'can_access_document')),
   array('methods'=>WP_REST_Server::DELETABLE,'callback'=>array($this,'delete_autosave'),'permission_callback'=>array($this,'can_access_document'))
  ) );
  register_rest_route( 'nirmaan-builder/v1', '/revisions/(?P<type>content|template|library)/(?P<id>\\d+)', array(
   array('methods'=>WP_REST_Server::READABLE,'callback'=>array($this,'list_revisions'),'permission_callback'=>array($this,'can_access_document')),
   array('methods'=>WP_REST_Server::CREATABLE,'callback'=>array($this,'restore_revision'),'permission_callback'=>array($this,'can_access_document'))
  ) );
  register_rest_route( 'nirmaan-builder/v1', '/templates', array(
   array('methods'=>WP_REST_Server::READABLE,'callback'=>array($this,'list_templates'),'permission_callback'=>array($this,'can_manage_templates')),
   array('methods'=>WP_REST_Server::CREATABLE,'callback'=>array($this,'create_template'),'permission_callback'=>array($this,'can_manage_templates')) ) );
  register_rest_route( 'nirmaan-builder/v1', '/templates/(?P<id>\\d+)', array(
   array('methods'=>WP_REST_Server::READABLE,'callback'=>array($this,'get_template'),'permission_callback'=>array($this,'can_manage_templates')),
   array('methods'=>'POST','callback'=>array($this,'save_template'),'permission_callback'=>array($this,'can_manage_templates')),
   array('methods'=>WP_REST_Server::DELETABLE,'callback'=>array($this,'delete_template'),'permission_callback'=>array($this,'can_manage_templates')) ) );
  register_rest_route( 'nirmaan-builder/v1', '/library', array(
   array('methods'=>WP_REST_Server::READABLE,'callback'=>array($this,'list_library'),'permission_callback'=>array($this,'can_manage_library')),
   array('methods'=>WP_REST_Server::CREATABLE,'callback'=>array($this,'create_library_item'),'permission_callback'=>array($this,'can_manage_library')) ) );
  register_rest_route( 'nirmaan-builder/v1', '/library/export', array('methods'=>WP_REST_Server::READABLE,'callback'=>array($this,'export_library'),'permission_callback'=>array($this,'can_manage_library')) );
  register_rest_route( 'nirmaan-builder/v1', '/library/import', array('methods'=>WP_REST_Server::CREATABLE,'callback'=>array($this,'import_library'),'permission_callback'=>array($this,'can_manage_library')) );
  register_rest_route( 'nirmaan-builder/v1', '/library/(?P<id>\\d+)', array(
   array('methods'=>WP_REST_Server::READABLE,'callback'=>array($this,'get_library_item'),'permission_callback'=>array($this,'can_manage_library')),
   array('methods'=>'POST','callback'=>array($this,'save_library_item'),'permission_callback'=>array($this,'can_manage_library')),
   array('methods'=>WP_REST_Server::DELETABLE,'callback'=>array($this,'delete_library_item'),'permission_callback'=>array($this,'can_manage_library')) ) );
 }
 public function can_manage_presets(){return current_user_can('edit_theme_options');}
 private function style_presets(){ $items=get_option('nirmaan_builder_style_presets',array());return is_array($items)?$items:array(); }
 private function sanitize_preset_payload($p){
  if(!is_array($p))return new \WP_Error('invalid_preset',__('Invalid style preset.','nirmaan-builder'),array('status'=>400));
  $name=sanitize_text_field($p['name']??'');
  if(''===$name||strlen($name)>80)return new \WP_Error('invalid_preset_name',__('Preset name is required and must be 80 characters or fewer.','nirmaan-builder'),array('status'=>400));
  $styles=is_array($p['styles']??null)?$p['styles']:array();
  $clean=array();
  foreach(array('desktop','tablet','mobile') as $device){$clean[$device]=$this->sanitize_style(is_array($styles[$device]??null)?$styles[$device]:array());}
  if(!array_filter($clean))return new \WP_Error('empty_preset',__('Add at least one supported style before saving.','nirmaan-builder'),array('status'=>400));
  $category=sanitize_key($p['category']??'element');
  if(!in_array($category,array('element','typography','color','button','container'),true))$category='element';
  return array('name'=>$name,'category'=>$category,'styles'=>$clean,'version'=>1,'updated_at'=>current_time('mysql',true));
 }
 public function list_style_presets(){ $items=$this->style_presets();return rest_ensure_response(array_values($items)); }
 public function create_style_preset($r){
  $preset=$this->sanitize_preset_payload($r->get_json_params());if(is_wp_error($preset))return $preset;
  $items=$this->style_presets();if(count($items)>=200)return new \WP_Error('preset_limit',__('Maximum of 200 style presets reached.','nirmaan-builder'),array('status'=>400));
  $id='preset-'.str_replace('-','',wp_generate_uuid4());$preset['id']=$id;$items[$id]=$preset;update_option('nirmaan_builder_style_presets',$items,false);
  return rest_ensure_response(array('created'=>true,'preset'=>$preset));
 }
 public function update_style_preset($r){
  $id=sanitize_key($r['id']);$items=$this->style_presets();if(!isset($items[$id]))return new \WP_Error('preset_not_found',__('Style preset not found.','nirmaan-builder'),array('status'=>404));
  $preset=$this->sanitize_preset_payload($r->get_json_params());if(is_wp_error($preset))return $preset;
  $preset['id']=$id;$items[$id]=$preset;update_option('nirmaan_builder_style_presets',$items,false);
  return rest_ensure_response(array('saved'=>true,'preset'=>$preset));
 }
 private function snapshot_preset_nodes($nodes,$id,$styles){
  if(!is_array($nodes))return array(false,$nodes);$changed=false;
  foreach($nodes as &$node){if(!is_array($node))continue;$settings=is_array($node['settings']??null)?$node['settings']:array();
   if(($settings['style_preset_id']??'')===$id){$fallback=is_array($settings['preset_fallback']??null)?$settings['preset_fallback']:array();foreach(array('desktop','tablet','mobile') as $device){$settings[$device]=array_merge($fallback[$device]??array(),$styles[$device]??array(),is_array($settings[$device]??null)?$settings[$device]:array());}unset($settings['style_preset_id'],$settings['preset_fallback']);$node['settings']=$settings;$changed=true;}
   if(isset($node['children'])&&is_array($node['children'])){list($child_changed,$node['children'])=$this->snapshot_preset_nodes($node['children'],$id,$styles);$changed=$changed||$child_changed;}
  }unset($node);return array($changed,$nodes);
 }
 private function preserve_deleted_preset_references($id,$styles){
  $post_types=array_values(get_post_types(array(),'names'));$page=1;
  do{
   $post_ids=get_posts(array('post_type'=>$post_types,'post_status'=>array_keys(get_post_stati()),'numberposts'=>100,'paged'=>$page,'fields'=>'ids','meta_query'=>array('relation'=>'OR',array('key'=>'_nirmaan_builder_document','compare'=>'EXISTS'),array('key'=>'_nirmaan_library_document','compare'=>'EXISTS'),array('key'=>'_nirmaan_template_parts','compare'=>'EXISTS'),array('key'=>'_nirmaan_builder_autosaves','compare'=>'EXISTS'),array('key'=>'_nirmaan_builder_revisions','compare'=>'EXISTS'))));
   foreach($post_ids as $post_id){
    foreach(array('_nirmaan_builder_document','_nirmaan_library_document') as $key){$doc=get_post_meta($post_id,$key,true);if(is_array($doc)&&isset($doc['nodes'])){list($changed,$doc['nodes'])=$this->snapshot_preset_nodes($doc['nodes'],$id,$styles);if($changed)update_post_meta($post_id,$key,$doc);}}
    $parts=get_post_meta($post_id,'_nirmaan_template_parts',true);if(is_array($parts)){$changed=false;foreach($parts as &$part){if(is_array($part)&&isset($part['nodes'])){list($part_changed,$part['nodes'])=$this->snapshot_preset_nodes($part['nodes'],$id,$styles);$changed=$changed||$part_changed;}}unset($part);if($changed)update_post_meta($post_id,'_nirmaan_template_parts',$parts);}
    $drafts=get_post_meta($post_id,'_nirmaan_builder_autosaves',true);if(is_array($drafts)){$changed=false;foreach($drafts as &$draft){if(is_array($draft['document']['nodes']??null)){list($draft_changed,$draft['document']['nodes'])=$this->snapshot_preset_nodes($draft['document']['nodes'],$id,$styles);$changed=$changed||$draft_changed;}}unset($draft);if($changed)update_post_meta($post_id,'_nirmaan_builder_autosaves',$drafts);}
    $revisions=get_post_meta($post_id,'_nirmaan_builder_revisions',true);if(is_array($revisions)){$changed=false;foreach($revisions as &$revision){if(is_array($revision['document']['nodes']??null)){list($revision_changed,$revision['document']['nodes'])=$this->snapshot_preset_nodes($revision['document']['nodes'],$id,$styles);$changed=$changed||$revision_changed;}}unset($revision);if($changed)update_post_meta($post_id,'_nirmaan_builder_revisions',$revisions);}
   }
   $page++;
  }while(count($post_ids)===100);
 }
 public function delete_style_preset($r){
  $id=sanitize_key($r['id']);$items=$this->style_presets();if(!isset($items[$id]))return new \WP_Error('preset_not_found',__('Style preset not found.','nirmaan-builder'),array('status'=>404));
  $this->preserve_deleted_preset_references($id,$items[$id]['styles']??array());
  unset($items[$id]);update_option('nirmaan_builder_style_presets',$items,false);return rest_ensure_response(array('deleted'=>true,'references_preserved'=>true));
 }
 public function status(){return rest_ensure_response(array('name'=>'Nirmaan Builder','version'=>NIRMAAN_BUILDER_VERSION,'status'=>'builder-expanded'));}
 public function can_manage_settings(){return current_user_can('manage_options');}
 public function get_settings(){return rest_ensure_response(Settings::get());}
 public function save_settings($request){
  $params=$request->get_json_params();
  $settings=Settings::sanitize($params);
  update_option(Settings::OPTION,$settings,false);
  return rest_ensure_response(array('saved'=>true,'settings'=>$settings));
 }
 public function can_edit_content($r){$id=absint($r['id']);return $id && current_user_can('edit_post',$id);}
 public function can_access_document($r){$type=sanitize_key($r['type']??'');if('content'===$type)return $this->can_edit_content($r);if('template'===$type)return $this->can_manage_templates($r);if('library'===$type)return $this->can_manage_library($r);return false;}
 public function can_manage_templates($r=null){if(!current_user_can('edit_theme_options'))return false;if($r&&isset($r['id'])){$p=get_post(absint($r['id']));return $p&&'nirmaan_template'===$p->post_type;}return true;}
 public function can_manage_library($r=null){if(!current_user_can('edit_posts'))return false;if($r&&isset($r['id'])){$p=get_post(absint($r['id']));return $p&&'nirmaan_library'===$p->post_type;}return true;}
 public function get_content($r){$id=absint($r['id']);return rest_ensure_response(array('id'=>$id,'document'=>get_post_meta($id,'_nirmaan_builder_document',true)?:array('nodes'=>array())));}
 public function save_content($r){$id=absint($r['id']);$doc=$this->sanitize_document($r->get_json_params());$old=get_post_meta($id,'_nirmaan_builder_document',true);if(is_array($old)&&$old!==$doc)$this->record_revision('content',$id,$old,'');update_post_meta($id,'_nirmaan_builder_document',$doc);return rest_ensure_response(array('saved'=>true,'id'=>$id,'document'=>$doc));}
 public function list_templates(){$items=get_posts(array('post_type'=>'nirmaan_template','post_status'=>array('publish','draft'),'numberposts'=>100,'orderby'=>'meta_value_num','meta_key'=>'_nirmaan_priority','order'=>'DESC'));$out=array();foreach($items as $p)$out[]=array('id'=>$p->ID,'title'=>$p->post_title,'status'=>$p->post_status,'priority'=>(int)get_post_meta($p->ID,'_nirmaan_priority',true),'conditions'=>get_post_meta($p->ID,'_nirmaan_conditions',true)?:array(),'parts'=>get_post_meta($p->ID,'_nirmaan_template_parts',true)?:array());return rest_ensure_response($out);}
 public function create_template($r){$p=$r->get_json_params();$title=sanitize_text_field($p['title']??__('New Template','nirmaan-builder'));$id=wp_insert_post(array('post_type'=>'nirmaan_template','post_status'=>'publish','post_title'=>$title),true);if(is_wp_error($id))return $id;update_post_meta($id,'_nirmaan_priority',absint($p['priority']??10));update_post_meta($id,'_nirmaan_conditions',array('include'=>array(array('type'=>'entire_site')),'exclude'=>array()));update_post_meta($id,'_nirmaan_template_parts',array('header'=>array('nodes'=>array()),'body'=>array('nodes'=>array()),'footer'=>array('nodes'=>array())));update_post_meta($id,'_nirmaan_part_settings',array('header'=>array('active'=>true,'condition'=>''),'body'=>array('active'=>true,'condition'=>''),'footer'=>array('active'=>true,'condition'=>'')));return rest_ensure_response(array('id'=>$id,'title'=>$title));}
 public function get_template($r){$id=absint($r['id']);return rest_ensure_response(array('id'=>$id,'title'=>get_the_title($id),'priority'=>(int)get_post_meta($id,'_nirmaan_priority',true),'conditions'=>get_post_meta($id,'_nirmaan_conditions',true)?:array(),'parts'=>get_post_meta($id,'_nirmaan_template_parts',true)?:array()));}
 public function save_template($r){$id=absint($r['id']);$p=$r->get_json_params();if(isset($p['title']))wp_update_post(array('ID'=>$id,'post_title'=>sanitize_text_field($p['title'])));if(isset($p['priority']))update_post_meta($id,'_nirmaan_priority',absint($p['priority']));if(isset($p['conditions'])&&is_array($p['conditions']))update_post_meta($id,'_nirmaan_conditions',$this->sanitize_conditions($p['conditions']));if(isset($p['part_settings'])&&is_array($p['part_settings'])){$current=get_post_meta($id,'_nirmaan_part_settings',true);$current=is_array($current)?$current:array();foreach(array('header','body','footer') as $section){if(!isset($p['part_settings'][$section])||!is_array($p['part_settings'][$section]))continue;$input=$p['part_settings'][$section];$condition=sanitize_key($input['condition']??'');$allowed=array('','front_page','all_pages','all_posts','archive','search','404','product','product_archive','shop','cart','checkout');$current[$section]=array('active'=>!empty($input['active']),'condition'=>in_array($condition,$allowed,true)?$condition:'');}update_post_meta($id,'_nirmaan_part_settings',$current);}if(isset($p['part'])&&in_array($p['part'],array('header','body','footer'),true)&&isset($p['document'])){$parts=get_post_meta($id,'_nirmaan_template_parts',true);$parts=is_array($parts)?$parts:array();$new_doc=$this->sanitize_document($p['document']);if(isset($parts[$p['part']])&&$parts[$p['part']]!==$new_doc)$this->record_revision('template',$id,$parts[$p['part']],$p['part']);$parts[$p['part']]=$new_doc;update_post_meta($id,'_nirmaan_template_parts',$parts);}return rest_ensure_response(array('saved'=>true,'id'=>$id));}
 public function delete_template($r){wp_delete_post(absint($r['id']),true);return rest_ensure_response(array('deleted'=>true));}
 public function list_library(){$items=get_posts(array('post_type'=>'nirmaan_library','post_status'=>'publish','numberposts'=>200));$out=array();foreach($items as $p)$out[]=array('id'=>$p->ID,'title'=>$p->post_title,'global'=>(bool)get_post_meta($p->ID,'_nirmaan_global',true),'category'=>get_post_meta($p->ID,'_nirmaan_category',true),'document'=>get_post_meta($p->ID,'_nirmaan_library_document',true)?:array('nodes'=>array()));return rest_ensure_response($out);}
 public function create_library_item($r){$p=$r->get_json_params();$title=sanitize_text_field($p['title']??__('Saved Layout','nirmaan-builder'));$id=wp_insert_post(array('post_type'=>'nirmaan_library','post_status'=>'publish','post_title'=>$title),true);if(is_wp_error($id))return $id;update_post_meta($id,'_nirmaan_library_document',$this->sanitize_document($p['document']??array()));update_post_meta($id,'_nirmaan_global',!empty($p['global'])?1:0);update_post_meta($id,'_nirmaan_category',sanitize_text_field($p['category']??''));return rest_ensure_response(array('id'=>$id,'title'=>$title));}
 public function get_library_item($r){$id=absint($r['id']);return rest_ensure_response(array('id'=>$id,'title'=>get_the_title($id),'global'=>(bool)get_post_meta($id,'_nirmaan_global',true),'document'=>get_post_meta($id,'_nirmaan_library_document',true)?:array('nodes'=>array())));}
 public function save_library_item($r){$id=absint($r['id']);$p=$r->get_json_params();if(isset($p['title']))wp_update_post(array('ID'=>$id,'post_title'=>sanitize_text_field($p['title'])));if(isset($p['document'])){$new_doc=$this->sanitize_document($p['document']);$old=get_post_meta($id,'_nirmaan_library_document',true);if(is_array($old)&&$old!==$new_doc)$this->record_revision('library',$id,$old,'');update_post_meta($id,'_nirmaan_library_document',$new_doc);}if(isset($p['global']))update_post_meta($id,'_nirmaan_global',!empty($p['global'])?1:0);if(isset($p['category']))update_post_meta($id,'_nirmaan_category',sanitize_text_field($p['category']));return rest_ensure_response(array('saved'=>true,'id'=>$id));}
 public function delete_library_item($r){wp_delete_post(absint($r['id']),true);return rest_ensure_response(array('deleted'=>true));}
 public function export_library(){return rest_ensure_response(array('format'=>'nirmaan-builder-library','version'=>1,'items'=>$this->list_library()->get_data()));}
 public function import_library($r){$p=$r->get_json_params();$items=$p['items']??array();if(!is_array($items)||count($items)>100)return new WP_Error('invalid_import',__('Invalid import file.','nirmaan-builder'),array('status'=>400));$created=array();foreach($items as $item){if(!is_array($item))continue;$title=sanitize_text_field($item['title']??'Imported Layout');$id=wp_insert_post(array('post_type'=>'nirmaan_library','post_status'=>'publish','post_title'=>$title),true);if(is_wp_error($id))continue;update_post_meta($id,'_nirmaan_library_document',$this->sanitize_document($item['document']??array()));update_post_meta($id,'_nirmaan_global',!empty($item['global'])?1:0);$created[]=$id;}return rest_ensure_response(array('imported'=>count($created),'ids'=>$created));}

 public function get_document_for_target($type,$id,$part='body'){
  if('content'===$type)return get_post_meta($id,'_nirmaan_builder_document',true)?:array('nodes'=>array());
  if('library'===$type)return get_post_meta($id,'_nirmaan_library_document',true)?:array('nodes'=>array());
  $parts=get_post_meta($id,'_nirmaan_template_parts',true);return is_array($parts)&&isset($parts[$part])?$parts[$part]:array('nodes'=>array());
 }
 private function set_document_for_target($type,$id,$document,$part='body'){
  if('content'===$type){update_post_meta($id,'_nirmaan_builder_document',$document);return;}
  if('library'===$type){update_post_meta($id,'_nirmaan_library_document',$document);return;}
  $parts=get_post_meta($id,'_nirmaan_template_parts',true);$parts=is_array($parts)?$parts:array();$parts[$part]=$document;update_post_meta($id,'_nirmaan_template_parts',$parts);
 }
 private function record_revision($type,$id,$document,$part=''){
  $key='_nirmaan_builder_revisions';$items=get_post_meta($id,$key,true);$items=is_array($items)?$items:array();
  $items[]=array('revision_id'=>wp_generate_uuid4(),'type'=>$type,'part'=>$part,'document'=>$document,'created_at'=>current_time('mysql',true),'author'=>get_current_user_id());
  if(count($items)>30)$items=array_slice($items,-30);update_post_meta($id,$key,$items);
 }
 public function get_autosave($r){$id=absint($r['id']);$type=sanitize_key($r['type']);$part=sanitize_key($r->get_param('part')?:'body');$drafts=get_post_meta($id,'_nirmaan_builder_autosaves',true);$drafts=is_array($drafts)?$drafts:array();$draft=$drafts[get_current_user_id().'_'.$part]??null;return rest_ensure_response(array('autosave'=>$draft));}
 public function save_autosave($r){$id=absint($r['id']);$type=sanitize_key($r['type']);$p=$r->get_json_params();$part=sanitize_key($p['part']??'body');if('template'===$type&&!in_array($part,array('header','body','footer'),true))return new \WP_Error('invalid_part',__('Invalid template part.','nirmaan-builder'),array('status'=>400));$doc=$this->sanitize_document($p['document']??array());$drafts=get_post_meta($id,'_nirmaan_builder_autosaves',true);$drafts=is_array($drafts)?$drafts:array();$drafts[get_current_user_id().'_'.$part]=array('type'=>$type,'part'=>$part,'document'=>$doc,'saved_at'=>time());update_post_meta($id,'_nirmaan_builder_autosaves',$drafts);return rest_ensure_response(array('saved'=>true,'saved_at'=>time()));}
 public function delete_autosave($r){$id=absint($r['id']);$part=sanitize_key($r->get_param('part')?:'body');$drafts=get_post_meta($id,'_nirmaan_builder_autosaves',true);if(is_array($drafts)){unset($drafts[get_current_user_id().'_'.$part]);update_post_meta($id,'_nirmaan_builder_autosaves',$drafts);}return rest_ensure_response(array('deleted'=>true));}
 public function list_revisions($r){$id=absint($r['id']);$type=sanitize_key($r['type']);$part=sanitize_key($r->get_param('part')?:'body');$items=get_post_meta($id,'_nirmaan_builder_revisions',true);$items=is_array($items)?$items:array();$out=array();foreach(array_reverse($items) as $item){if(($item['type']??'')!==$type)continue;if('template'===$type&&($item['part']??'')!==$part)continue;$out[]=array('revision_id'=>$item['revision_id'],'created_at'=>$item['created_at'],'author'=>(int)$item['author']);}return rest_ensure_response(array('revisions'=>$out));}
 public function restore_revision($r){$id=absint($r['id']);$type=sanitize_key($r['type']);$p=$r->get_json_params();$part=sanitize_key($p['part']??'body');$revision_id=sanitize_text_field($p['revision_id']??'');$items=get_post_meta($id,'_nirmaan_builder_revisions',true);$items=is_array($items)?$items:array();foreach($items as $item){if(($item['revision_id']??'')!==$revision_id||($item['type']??'')!==$type||('template'===$type&&($item['part']??'')!==$part))continue;$current=$this->get_document_for_target($type,$id,$part);if($current!==$item['document'])$this->record_revision($type,$id,$current,$part);$doc=$this->sanitize_document($item['document']);$this->set_document_for_target($type,$id,$doc,$part);return rest_ensure_response(array('restored'=>true,'document'=>$doc));}return new \WP_Error('revision_not_found',__('Revision not found.','nirmaan-builder'),array('status'=>404));}
 private function sanitize_conditions($c){$out=array('include'=>array(),'exclude'=>array());$allowed=array('entire_site','front_page','singular','all_pages','all_posts','post_type','archive','search','404','product','product_archive','shop','cart','checkout');foreach(array('include','exclude') as $g){foreach(array_slice($c[$g]??array(),0,30) as $rule){if(!is_array($rule)||empty($rule['type']))continue;$type=sanitize_key($rule['type']);if(!in_array($type,$allowed,true))continue;$out[$g][]=array('type'=>$type,'value'=>absint($rule['value']??0),'post_type'=>sanitize_key($rule['post_type']??''));}}return $out;}
 private function sanitize_document($data){$nodes=is_array($data)&&is_array($data['nodes']??null)?$data['nodes']:array();return array('nodes'=>$this->sanitize_nodes($nodes,0));}
 private function sanitize_nodes($nodes,$depth){if($depth>10)return array();$clean=array();$allowed=array('heading','text','image','button','divider','spacer','container','columns','product_title','product_price','product_image','product_add_to_cart','product_description','product_rating','related_products','shop_products','cart','checkout','mini_cart','product_archive','global_element');foreach(array_slice($nodes,0,200) as $node){if(!is_array($node)||empty($node['type']))continue;$type=sanitize_key($node['type']);if(!in_array($type,$allowed,true))continue;$s=is_array($node['settings']??null)?$node['settings']:array();$item=array('id'=>sanitize_key($node['id']??wp_generate_uuid4()),'type'=>$type,'settings'=>array('content'=>sanitize_textarea_field($s['content']??''),'label'=>sanitize_text_field($s['label']??''),'hidden'=>!empty($s['hidden']),'locked'=>!empty($s['locked']),'url'=>esc_url_raw($s['url']??''),'alt'=>sanitize_text_field($s['alt']??''),'level'=>min(6,max(1,absint($s['level']??2))),'height'=>min(1200,absint($s['height']??32)),'library_id'=>absint($s['library_id']??0),'style_preset_id'=>sanitize_key($s['style_preset_id']??''),'preset_fallback'=>array('desktop'=>is_array($s['preset_fallback']['desktop']??null)?$this->sanitize_style($s['preset_fallback']['desktop']):array(),'tablet'=>is_array($s['preset_fallback']['tablet']??null)?$this->sanitize_style($s['preset_fallback']['tablet']):array(),'mobile'=>is_array($s['preset_fallback']['mobile']??null)?$this->sanitize_style($s['preset_fallback']['mobile']):array()),'desktop'=>is_array($s['desktop']??null)?$this->sanitize_style($s['desktop']):array(),'tablet'=>is_array($s['tablet']??null)?$this->sanitize_style($s['tablet']):array(),'mobile'=>is_array($s['mobile']??null)?$this->sanitize_style($s['mobile']):array(),'hide_desktop'=>!empty($s['hide_desktop']),'hide_tablet'=>!empty($s['hide_tablet']),'hide_mobile'=>!empty($s['hide_mobile'])));if(in_array($type,array('container','columns'),true))$item['children']=$this->sanitize_nodes($node['children']??array(),$depth+1);$clean[]=$item;}return $clean;}
 private function sanitize_style($style){$out=array();foreach(array('padding','margin','fontSize','fontWeight','lineHeight','letterSpacing','color','backgroundColor','borderColor','borderWidth','borderStyle','textAlign','width','maxWidth','minHeight','borderRadius','boxShadow','opacity','display','gap','flexDirection','justifyContent','alignItems') as $key){if(isset($style[$key])){$v=$style[$key];if(in_array($key,array('color','backgroundColor','borderColor'),true))$out[$key]=sanitize_hex_color($v)?:'';elseif(in_array($key,array('textAlign','display','borderStyle','flexDirection','justifyContent','alignItems'),true))$out[$key]=sanitize_key($v);else $out[$key]=preg_replace('/[^0-9a-zA-Z.%\- ]/','',substr((string)$v,0,32));}}return $out;}
}