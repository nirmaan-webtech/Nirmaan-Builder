<?php
namespace NirmaanBuilder;
if(!defined('ABSPATH')){exit;}
class Renderer {
 private $style_presets=null;
 private function get_style_preset($id){if(!$id)return array();if(null===$this->style_presets){$items=get_option('nirmaan_builder_style_presets',array());$this->style_presets=is_array($items)?$items:array();}return $this->style_presets[$id]['styles']??array();}
 public function hooks(){add_filter('the_content',array($this,'filter_content'),20);add_action('wp_body_open',array($this,'render_header'),5);add_action('wp_footer',array($this,'render_footer'),5);add_action('wp_enqueue_scripts',array($this,'enqueue_styles'));add_action('wp_enqueue_scripts',array($this,'enqueue_frontend_editor'),30);add_action('admin_bar_menu',array($this,'add_frontend_edit_link'),90);}
 public function enqueue_styles(){wp_enqueue_style('nirmaan-builder-frontend',NIRMAAN_BUILDER_URL.'assets/css/frontend.css',array(),NIRMAAN_BUILDER_VERSION);}
 public function enqueue_frontend_editor(){
  if(is_admin()||!is_singular()||!is_user_logged_in())return;
  $post_id=get_queried_object_id();
  if(!$post_id||!current_user_can('edit_post',$post_id)||'1'!==sanitize_text_field(wp_unslash($_GET['nirmaan_frontend_edit']??'')))return;
  wp_enqueue_script('nirmaan-builder-frontend-editor',NIRMAAN_BUILDER_URL.'assets/js/frontend-edit.js',array(),NIRMAAN_BUILDER_VERSION,true);
  wp_localize_script('nirmaan-builder-frontend-editor','NirmaanBuilderFrontendConfig',array('apiRoot'=>esc_url_raw(rest_url('nirmaan-builder/v1/')),'nonce'=>wp_create_nonce('wp_rest'),'postId'=>$post_id));
 }
 public function add_frontend_edit_link($bar){
  if(is_admin()||!is_singular()||!is_user_logged_in())return;
  $post_id=get_queried_object_id();if(!$post_id||!current_user_can('edit_post',$post_id))return;
  $url=add_query_arg('nirmaan_frontend_edit','1',get_permalink($post_id));
  $bar->add_node(array('id'=>'nirmaan-frontend-edit','title'=>__('Edit Frontend','nirmaan-builder'),'href'=>$url));
 }

 private function assigned_template(){$templates=get_posts(array('post_type'=>'nirmaan_template','post_status'=>'publish','numberposts'=>200,'meta_key'=>'_nirmaan_priority','orderby'=>'meta_value_num','order'=>'DESC'));foreach($templates as $t){$c=get_post_meta($t->ID,'_nirmaan_conditions',true);if($this->matches_conditions(is_array($c)?$c:array()))return $t->ID;}return 0;}
 private function matches_conditions($c){$inc=$c['include']??array();$exc=$c['exclude']??array();$match=false;foreach($inc as $r)if($this->matches_rule($r)){$match=true;break;}if(!$match)return false;foreach($exc as $r)if($this->matches_rule($r))return false;return true;}
 private function part_is_active($template_id,$part){$settings=get_post_meta($template_id,'_nirmaan_part_settings',true);$settings=is_array($settings)?$settings:array();$config=$settings[$part]??array();if(isset($config['active'])&&!$config['active'])return false;$condition=sanitize_key($config['condition']??'');return ''===$condition||$this->matches_rule(array('type'=>$condition));}
 private function matches_rule($r){if(!is_array($r)||empty($r['type']))return false;$t=sanitize_key($r['type']);$v=absint($r['value']??0);switch($t){case'entire_site':return true;case'front_page':return is_front_page();case'singular':return is_singular()&&$v===get_queried_object_id();case'all_pages':return is_page();case'all_posts':return is_single()&&'post'===get_post_type();case'post_type':return is_singular(sanitize_key($r['post_type']??''));case'archive':return is_archive();case'search':return is_search();case'404':return is_404();case'product':return function_exists('is_product')&&is_product();case'product_archive':return function_exists('is_product_category')&&(is_shop()||is_product_taxonomy());case'shop':return function_exists('is_shop')&&is_shop();case'cart':return function_exists('is_cart')&&is_cart();case'checkout':return function_exists('is_checkout')&&is_checkout();default:return false;}}
 public function filter_content($content){if(is_admin()||!is_main_query()||!in_the_loop())return $content;$doc=get_post_meta(get_the_ID(),'_nirmaan_builder_document',true);if(is_array($doc)&&!empty($doc['nodes'])&&(!metadata_exists('post',get_the_ID(),'_nirmaan_builder_enabled')||get_post_meta(get_the_ID(),'_nirmaan_builder_enabled',true)))return $this->render_nodes($doc['nodes']);$id=$this->assigned_template();if($id&&$this->part_is_active($id,'body')){$parts=get_post_meta($id,'_nirmaan_template_parts',true);if(!empty($parts['body']['nodes']))return $this->render_nodes($parts['body']['nodes']);}return $content;}
 public function render_header(){$this->render_part('header');}public function render_footer(){$this->render_part('footer');}
 private function render_part($part){if(is_admin())return;$id=$this->assigned_template();if(!$id||!in_array($part,array('header','footer'),true)||!$this->part_is_active($id,$part))return;$parts=get_post_meta($id,'_nirmaan_template_parts',true);if(isset($parts[$part]['nodes'])&&is_array($parts[$part]['nodes']))echo $this->render_nodes($parts[$part]['nodes']);}
 private function woo($type){if(!function_exists('wc_get_product'))return '<p class="nb-notice">WooCommerce is required for this element.</p>';if('cart'===$type)return do_shortcode('[woocommerce_cart]');if('checkout'===$type)return do_shortcode('[woocommerce_checkout]');if('product_archive'===$type)return do_shortcode('[products limit="12" columns="4" paginate="true"]');if('mini_cart'===$type){ob_start();if(function_exists('woocommerce_mini_cart'))woocommerce_mini_cart();return '<div class="nb-mini-cart">'.ob_get_clean().'</div>';}if('related_products'===$type)return do_shortcode('[related_products limit="4"]');if('shop_products'===$type)return do_shortcode('[products limit="8" columns="4"]');global $product;if(!$product&&get_the_ID())$product=wc_get_product(get_the_ID());if(!$product)return ''; $map=array('product_title'=>'woocommerce_template_single_title','product_price'=>'woocommerce_template_single_price','product_image'=>'woocommerce_show_product_images','product_add_to_cart'=>'woocommerce_template_single_add_to_cart','product_description'=>'woocommerce_template_single_excerpt','product_rating'=>'woocommerce_template_single_rating');if(empty($map[$type])||!function_exists($map[$type]))return '';ob_start();call_user_func($map[$type]);return ob_get_clean();}
 private function dynamic_value($settings){
  $binding=$settings['dynamic']??array();if(empty($binding['enabled']))return null;$post_id=get_the_ID();$value='';
  switch($binding['source']??''){
   case'post_title':$value=get_the_title($post_id);break;
   case'post_excerpt':$value=get_the_excerpt($post_id);break;
   case'post_permalink':$value=get_permalink($post_id);break;
   case'post_date':$value=get_the_date('', $post_id);break;
   case'post_author':$value=get_the_author_meta('display_name',(int)get_post_field('post_author',$post_id));break;
   case'featured_image':$value=get_the_post_thumbnail_url($post_id,'full')?:'';break;
   case'custom_field':$key=sanitize_key($binding['field']??'');$fields=get_post_meta($post_id,'_nirmaan_builder_custom_fields',true);$fields=is_array($fields)?$fields:array();$value=$fields[$key]??'';break;
  }
  if(is_array($value)||is_object($value))$value='';return ''!== (string)$value ? $value : ($binding['fallback']??'');
 }
 public function render_nodes($nodes,$depth=0){if(!is_array($nodes)||$depth>8)return '';$html='';foreach(array_slice($nodes,0,300) as $n){if(!is_array($n))continue;$type=sanitize_key($n['type']??'');$s=is_array($n['settings']??null)?$n['settings']:array();$content=$s['content']??'';$dynamic=$this->dynamic_value($s);if(null!==$dynamic){if('image'===$type)$s['url']=$dynamic;elseif('button'===$type&&'post_permalink'===($s['dynamic']['source']??''))$s['url']=$dynamic;else $content=$dynamic;}$classes='nb-node';$fallback=is_array($s['preset_fallback']??null)?$s['preset_fallback']:array();$preset=$this->get_style_preset(sanitize_key($s['style_preset_id']??''));$base_style=array_merge($fallback['desktop']??array(),$preset['desktop']??array(),$s['desktop']??array());$style=$this->inline_style($base_style);$responsive='';if(!empty($base_style['hover'])){$hover=$this->inline_style($base_style['hover']);if($hover)$responsive.='.nb-node-'.sanitize_html_class($n['id']??'').':hover{'.$hover.'}';}$breakpoints=Settings::get()['breakpoints']??array('tablet'=>1024,'mobile'=>767);$tablet=absint($breakpoints['tablet']??1024);$mobile=absint($breakpoints['mobile']??767);$class='.nb-node-'.sanitize_html_class($n['id']??'');foreach(array('tablet','mobile') as $device){$device_style=array_merge($fallback[$device]??array(),$preset[$device]??array(),$s[$device]??array());$d=$this->inline_style($device_style);$max='tablet'===$device?$tablet:$mobile;$media='(max-width:'.$max.'px)';if('tablet'===$device)$media.=' and (min-width:'.($mobile+1).'px)';if($d)$responsive.='@media '.$media.'{'.$class.'{'.$d.'}}';if(!empty($device_style['hover'])){$hover=$this->inline_style($device_style['hover']);if($hover)$responsive.='@media '.$media.'{'.$class.':hover{'.$hover.'}}';}}foreach(array('desktop','tablet','mobile') as $device)if(!empty($s['hide_'.$device])){$media='desktop'===$device?'(min-width:'.($tablet+1).'px)':('tablet'===$device?'(min-width:'.($mobile+1).'px) and (max-width:'.$tablet.'px)':'(max-width:'.$mobile.'px)');$responsive.='@media '.$media.'{'.$class.'{display:none!important}}';}$id='nb-node-'.sanitize_html_class($n['id']??wp_generate_uuid4());if($responsive)$html.='<style>'.$responsive.'</style>';
  if('query_loop'===$type){
   $q=is_array($s['query']??null)?$s['query']:array();$post_type=sanitize_key($q['post_type']??'post');$public_types=get_post_types(array('public'=>true),'names');if(!in_array($post_type,$public_types,true))$post_type='post';
   $args=array('post_type'=>$post_type,'post_status'=>'publish','posts_per_page'=>min(24,max(1,absint($q['posts_per_page']??6))),'orderby'=>in_array(($q['orderby']??'date'),array('date','title','menu_order','rand'),true)?$q['orderby']:'date','order'=>'ASC'===strtoupper($q['order']??'DESC')?'ASC':'DESC','no_found_rows'=>true);
   $loop=new \WP_Query($args);$loop_html='';$previous_post=$GLOBALS['post']??null;
   while($loop->have_posts()){$loop->the_post();$loop_html.='<article class="nb-query-item">'.$this->render_nodes($n['children']??array(),$depth+1).'</article>';}
   wp_reset_postdata();if($previous_post instanceof \WP_Post){$GLOBALS['post']=$previous_post;setup_postdata($previous_post);}
   $html.='<div class="nb-query-loop nb-node '.$id.'" style="'.$style.'">'.$loop_html.'</div>';continue;
  }
  if('global_element'===$type){$gid=absint($s['library_id']??0);$g=get_post_meta($gid,'_nirmaan_library_document',true);if($gid&&get_post_meta($gid,'_nirmaan_global',true)&&is_array($g))$html.=$this->render_nodes($g['nodes']??array());continue;}
  if(in_array($type,array('product_title','product_price','product_image','product_add_to_cart','product_description','product_rating','related_products','shop_products','cart','checkout','mini_cart','product_archive'),true)){$html.='<div class="nb-node '.$id.'" style="'.$style.'">'.$this->woo($type).'</div>';continue;}
  switch($type){case'heading':$l=min(6,max(1,absint($s['level']??2)));$html.=sprintf('<h%d class="nb-heading nb-node %s" style="%s">%s</h%d>',$l,esc_attr($id),esc_attr($style),esc_html($content),$l);break;case'text':$html.='<div class="nb-text nb-node '.esc_attr($id).'" style="'.esc_attr($style).'">'.wp_kses_post($content).'</div>';break;case'image':$u=esc_url($s['url']??'');if($u)$html.='<figure class="nb-image nb-node '.esc_attr($id).'" style="'.esc_attr($style).'"><img src="'.$u.'" alt="'.esc_attr($s['alt']??'').'" loading="lazy"></figure>';break;case'button':$html.='<p class="nb-button-wrap nb-node '.esc_attr($id).'" style="'.esc_attr($style).'"><a class="nb-button" href="'.esc_url($s['url']??'#').'">'.esc_html($content?:__('Button','nirmaan-builder')).'</a></p>';break;case'divider':$html.='<hr class="nb-divider nb-node '.esc_attr($id).'" style="'.esc_attr($style).'">';break;case'spacer':$h=min(1200,absint($s['height']??32));$html.='<div class="nb-spacer nb-node '.esc_attr($id).'" style="height:'.$h.'px;'.$style.'"></div>';break;case'section':case'row':case'column':case'container':case'columns':$html.='<div class="nb-layout nb-layout-'.esc_attr($type).' nb-node '.esc_attr($id).'" style="'.esc_attr($style).'">'.$this->render_nodes($n['children']??array(),$depth+1).'</div>';break;}
 }return $html;}
 private function inline_style($s){
  if(!is_array($s))return '';
  $map=array('padding'=>'padding','margin'=>'margin','fontSize'=>'font-size','fontWeight'=>'font-weight','lineHeight'=>'line-height','letterSpacing'=>'letter-spacing','color'=>'color','backgroundColor'=>'background-color','borderColor'=>'border-color','borderWidth'=>'border-width','borderStyle'=>'border-style','textAlign'=>'text-align','width'=>'width','maxWidth'=>'max-width','minHeight'=>'min-height','borderRadius'=>'border-radius','boxShadow'=>'box-shadow','opacity'=>'opacity','display'=>'display','gap'=>'gap','flexDirection'=>'flex-direction','flexWrap'=>'flex-wrap','justifyContent'=>'justify-content','alignItems'=>'align-items','gridTemplateColumns'=>'grid-template-columns','position'=>'position','top'=>'top','right'=>'right','bottom'=>'bottom','left'=>'left','zIndex'=>'z-index','overflow'=>'overflow','backgroundImage'=>'background-image','filter'=>'filter','transform'=>'transform','animationName'=>'animation-name','animationDuration'=>'animation-duration','animationDelay'=>'animation-delay','animationIterationCount'=>'animation-iteration-count','animationTimingFunction'=>'animation-timing-function');
  $shadows=array('none'=>'none','soft'=>'0 2px 8px rgba(15,23,42,.08)','medium'=>'0 6px 18px rgba(15,23,42,.14)','strong'=>'0 12px 32px rgba(15,23,42,.22)');
  $out='';
  foreach($map as $key=>$property){
   if(!isset($s[$key])||''===$s[$key])continue;
   $value=$s[$key];
   if('boxShadow'===$key)$value=$shadows[$value]??'none';if('animationName'===$key&&'none'!==$value){$value='nb-'.$value;}
   if(in_array($key,array('color','backgroundColor','borderColor'),true))$value=sanitize_hex_color($value)?:'';
   else $value=esc_attr($value);
   if(''!==$value)$out.=$property.':'.$value.';';if(isset($s['animationName'])&&'none'!==$s['animationName'])$out.='animation-fill-mode:both;';
  }
  return $out;
 }
}
