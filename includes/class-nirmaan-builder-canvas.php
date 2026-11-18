<?php
namespace NirmaanBuilder;
if(!defined('ABSPATH')){exit;}
final class Canvas {
 public function hooks(){
  add_action('admin_menu',array($this,'menu'),30);
  add_action('admin_enqueue_scripts',array($this,'assets'));
 }
 public function menu(){
  add_submenu_page('nirmaan-builder',__('Page Builder','nirmaan-builder'),__('Page Builder','nirmaan-builder'),'use_nirmaan_builder','nirmaan-canvas',array($this,'render'));
 }
 public function assets($hook){
  if('nirmaan-builder_page_nirmaan-canvas'!==$hook)return;
  wp_enqueue_media();
  wp_enqueue_style('nirmaan-builder-canvas',NIRMAAN_BUILDER_URL.'assets/css/canvas.css',array(),NIRMAAN_BUILDER_VERSION);
  wp_enqueue_script('nirmaan-builder-canvas',NIRMAAN_BUILDER_URL.'assets/js/canvas.js',array('jquery'),NIRMAAN_BUILDER_VERSION,true);
  $id=absint($_GET['post_id']??0);
  $post=$id?get_post($id):null;
  wp_localize_script('nirmaan-builder-canvas','NirmaanCanvasConfig',array(
   'apiRoot'=>esc_url_raw(rest_url('nirmaan-builder/v1/')),
   'nonce'=>wp_create_nonce('wp_rest'),
   'postId'=>$post?$id:0,
   'title'=>$post?$post->post_title:'',
   'previewUrl'=>$post?get_permalink($id):'',
   'canAdvanced'=>current_user_can('use_nirmaan_advanced_modules'),
   'labels'=>array('saving'=>__('Saving…','nirmaan-builder'),'saved'=>__('Saved','nirmaan-builder'),'unsaved'=>__('Unsaved changes','nirmaan-builder'))
  ));
 }
 public function render(){
  if(!current_user_can('use_nirmaan_builder'))wp_die(esc_html__('Permission denied.','nirmaan-builder'));
  $id=absint($_GET['post_id']??0);
  $post=$id?get_post($id):null;
  if($id&&(!$post||!current_user_can('edit_post',$id)))wp_die(esc_html__('You cannot edit this page.','nirmaan-builder'));
  if(!$post){
   $items=get_posts(array('post_type'=>array('page','post'),'post_status'=>array('publish','draft','private'),'numberposts'=>100,'orderby'=>'modified','order'=>'DESC'));
   echo '<div class="wrap"><h1>'.esc_html__('Nirmaan Page Builder','nirmaan-builder').'</h1><p>'.esc_html__('Choose a page or post to edit in the visual canvas.','nirmaan-builder').'</p><table class="widefat striped"><thead><tr><th>'.esc_html__('Title','nirmaan-builder').'</th><th>'.esc_html__('Type','nirmaan-builder').'</th><th>'.esc_html__('Status','nirmaan-builder').'</th><th>'.esc_html__('Action','nirmaan-builder').'</th></tr></thead><tbody>';
   foreach($items as $item){$url=add_query_arg(array('page'=>'nirmaan-canvas','post_id'=>$item->ID),admin_url('admin.php'));echo '<tr><td>'.esc_html($item->post_title?:__('(no title)','nirmaan-builder')).'</td><td>'.esc_html($item->post_type).'</td><td>'.esc_html($item->post_status).'</td><td><a class="button button-primary" href="'.esc_url($url).'">'.esc_html__('Open Canvas','nirmaan-builder').'</a></td></tr>';}
   echo '</tbody></table></div>';return;
  }
  echo '<div class="nb-canvas-app" id="nb-canvas-app"><header class="nb-canvas-top"><a class="nb-canvas-back" href="'.esc_url(admin_url('admin.php?page=nirmaan-canvas')).'">‹ '.esc_html__('All pages','nirmaan-builder').'</a><strong>'.esc_html($post->post_title).'</strong><div class="nb-canvas-actions"><button type="button" data-action="undo" title="Undo">↶</button><button type="button" data-action="redo" title="Redo">↷</button><button type="button" data-action="preview">'.esc_html__('Preview','nirmaan-builder').'</button><button type="button" data-action="export">'.esc_html__('Export','nirmaan-builder').'</button><label class="nb-canvas-import">'.esc_html__('Import','nirmaan-builder').'<input type="file" accept=".json,application/json" data-action="import"></label><button type="button" class="is-primary" data-action="save">'.esc_html__('Save','nirmaan-builder').'</button></div></header><div class="nb-canvas-workspace"><aside class="nb-canvas-sidebar nb-canvas-elements"><div class="nb-canvas-sidebar-head">'.esc_html__('Elements','nirmaan-builder').'</div><div class="nb-canvas-add-actions"><button type="button" data-add="section">+ '.esc_html__('Section','nirmaan-builder').'</button><button type="button" data-add="row">+ '.esc_html__('Row','nirmaan-builder').'</button><button type="button" data-add="column">+ '.esc_html__('Column','nirmaan-builder').'</button></div><div class="nb-canvas-module-list" id="nb-canvas-module-list"></div></aside><main class="nb-canvas-main"><div class="nb-canvas-devicebar"><button type="button" data-device="desktop" class="is-active">Desktop</button><button type="button" data-device="tablet">Tablet</button><button type="button" data-device="mobile">Mobile</button><span id="nb-canvas-status" role="status">'.esc_html__('Loading…','nirmaan-builder').'</span></div><div class="nb-canvas-stage"><div id="nb-canvas-viewport" class="nb-canvas-viewport is-desktop"><div id="nb-canvas-root"></div></div></div></main><aside class="nb-canvas-sidebar nb-canvas-settings"><div class="nb-canvas-sidebar-head">'.esc_html__('Settings','nirmaan-builder').'</div><div id="nb-canvas-inspector"><p class="nb-canvas-empty">'.esc_html__('Select an element on the canvas to edit its settings.','nirmaan-builder').'</p></div></aside></div></div>';
 }
}
