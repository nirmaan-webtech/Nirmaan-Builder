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
  $document=get_post_meta($post_id,'_nirmaan_builder_document',true);
  if(!$post_id||!current_user_can('edit_post',$post_id)||!get_post_meta($post_id,'_nirmaan_builder_enabled',true)||!is_array($document)||empty($document['nodes'])||'1'!==sanitize_text_field(wp_unslash($_GET['nirmaan_frontend_edit']??'')))return;
  wp_enqueue_style('nirmaan-builder-frontend-edit',NIRMAAN_BUILDER_URL.'assets/css/frontend-edit.css',array(),NIRMAAN_BUILDER_VERSION);wp_enqueue_script('nirmaan-builder-frontend-editor',NIRMAAN_BUILDER_URL.'assets/js/frontend-edit.js',array(),NIRMAAN_BUILDER_VERSION,true);
  wp_localize_script('nirmaan-builder-frontend-editor','NirmaanBuilderFrontendConfig',array('apiRoot'=>esc_url_raw(rest_url('nirmaan-builder/v1/')),'nonce'=>wp_create_nonce('wp_rest'),'postId'=>$post_id));
 }
 public function add_frontend_edit_link($bar){
  if(is_admin()||!is_singular()||!is_user_logged_in())return;
  $post_id=get_queried_object_id();$document=get_post_meta($post_id,'_nirmaan_builder_document',true);if(!$post_id||!current_user_can('edit_post',$post_id)||!get_post_meta($post_id,'_nirmaan_builder_enabled',true)||!is_array($document)||empty($document['nodes']))return;
  $url=add_query_arg('nirmaan_frontend_edit','1',get_permalink($post_id));
  $bar->add_node(array('id'=>'nirmaan-frontend-edit','title'=>__('Edit Frontend','nirmaan-builder'),'href'=>$url));
 }

 private function assigned_template(){$templates=get_posts(array('post_type'=>'nirmaan_template','post_status'=>'publish','numberposts'=>200,'meta_key'=>'_nirmaan_priority','orderby'=>'meta_value_num','order'=>'DESC'));foreach($templates as $t){$c=get_post_meta($t->ID,'_nirmaan_conditions',true);if($this->matches_conditions(is_array($c)?$c:array()))return $t->ID;}return 0;}
 private function matches_conditions($c){$inc=$c['include']??array();$exc=$c['exclude']??array();$match=false;foreach($inc as $r)if($this->matches_rule($r)){$match=true;break;}if(!$match)return false;foreach($exc as $r)if($this->matches_rule($r))return false;return true;}
 private function part_is_active($template_id,$part){$settings=get_post_meta($template_id,'_nirmaan_part_settings',true);$settings=is_array($settings)?$settings:array();$config=$settings[$part]??array();if(isset($config['active'])&&!$config['active'])return false;$condition=sanitize_key($config['condition']??'');return ''===$condition||$this->matches_rule(array('type'=>$condition));}
 private function matches_rule($r){if(!is_array($r)||empty($r['type']))return false;$t=sanitize_key($r['type']);$v=absint($r['value']??0);switch($t){case'entire_site':return true;case'front_page':return is_front_page();case'singular':return is_singular()&&$v===get_queried_object_id();case'all_pages':return is_page();case'all_posts':return is_single()&&'post'===get_post_type();case'post_type':return is_singular(sanitize_key($r['post_type']??''));case'archive':return is_archive();case'search':return is_search();case'404':return is_404();case'product':return function_exists('is_product')&&is_product();case'product_archive':return function_exists('is_product_category')&&(is_shop()||is_product_taxonomy());case'shop':return function_exists('is_shop')&&is_shop();case'cart':return function_exists('is_cart')&&is_cart();case'checkout':return function_exists('is_checkout')&&is_checkout();default:return false;}}
 public function filter_content($content){if(is_admin()||!is_main_query()||!in_the_loop())return $content;$doc=get_post_meta(get_the_ID(),'_nirmaan_builder_document',true);$doc=Multilingual::translate_document($doc,get_the_ID(),'_nirmaan_builder_document');if(is_array($doc)&&!empty($doc['nodes'])&&(!metadata_exists('post',get_the_ID(),'_nirmaan_builder_enabled')||get_post_meta(get_the_ID(),'_nirmaan_builder_enabled',true)))return $this->render_nodes($doc['nodes']);$id=$this->assigned_template();if($id&&$this->part_is_active($id,'body')){$parts=get_post_meta($id,'_nirmaan_template_parts',true);$parts=Multilingual::translate_document($parts,$id,'_nirmaan_template_parts');if(!empty($parts['body']['nodes']))return $this->render_nodes($parts['body']['nodes']);}return $content;}
 public function has_active_part($part){if(!in_array($part,array('header','body','footer'),true))return false;$id=$this->assigned_template();if(!$id||!$this->part_is_active($id,$part))return false;$parts=get_post_meta($id,'_nirmaan_template_parts',true);return is_array($parts)&&!empty($parts[$part]['nodes'])&&is_array($parts[$part]['nodes']);}
 public function render_header(){$this->render_part('header');}public function render_footer(){$this->render_part('footer');}
 private function render_part($part){if(is_admin())return;$id=$this->assigned_template();if(!$id||!in_array($part,array('header','footer'),true)||!$this->part_is_active($id,$part))return;$parts=get_post_meta($id,'_nirmaan_template_parts',true);$parts=Multilingual::translate_document($parts,$id,'_nirmaan_template_parts');if(isset($parts[$part]['nodes'])&&is_array($parts[$part]['nodes']))echo $this->render_nodes($parts[$part]['nodes']);}
 private function woo($type,$settings=array()){
  if(!function_exists('wc_get_product'))return '<p class="nb-notice">'.esc_html__('WooCommerce is required for this element.','nirmaan-builder').'</p>';
  $options=is_array($settings['woo']??null)?$settings['woo']:array();
  $limit=min(24,max(1,absint($options['limit']??8)));
  $columns=min(6,max(1,absint($options['columns']??4)));
  $orderby=sanitize_key($options['orderby']??'date');
  if(!in_array($orderby,array('date','title','menu_order','popularity','rating','price','rand'),true))$orderby='date';
  $order='ASC'===strtoupper((string)($options['order']??'DESC'))?'ASC':'DESC';
  $category=sanitize_title($options['category']??'');
  $paginate=!empty($options['paginate'])?'true':'false';
  $shortcode=function($tag,$attributes){$parts=array();foreach($attributes as $key=>$value){if(''===$value||null===$value)continue;$parts[]=$key.'="'.esc_attr((string)$value).'"';}return do_shortcode('['.$tag.' '.implode(' ',$parts).']');};
  if('cart'===$type)return do_shortcode('[woocommerce_cart]');
  if('checkout'===$type)return do_shortcode('[woocommerce_checkout]');
  if('mini_cart'===$type){ob_start();if(function_exists('woocommerce_mini_cart'))woocommerce_mini_cart();return '<div class="nb-mini-cart">'.ob_get_clean().'</div>';}
  if('shop_products'===$type||'product_archive'===$type)return $shortcode('products',array('limit'=>$limit,'columns'=>$columns,'orderby'=>$orderby,'order'=>$order,'category'=>$category,'paginate'=>$paginate));
  if('related_products'===$type)return $shortcode('related_products',array('limit'=>$limit,'columns'=>$columns));
  if('product_breadcrumbs'===$type){ob_start();if(function_exists('woocommerce_breadcrumb'))woocommerce_breadcrumb();return ob_get_clean();}
  global $product;
  if(!$product&&get_the_ID())$product=wc_get_product(get_the_ID());
  if(!$product)return '';
  if('product_sku'===$type){$sku=$product->get_sku();return $sku?'<span class="nb-product-sku">'.esc_html__('SKU:','nirmaan-builder').' '.esc_html($sku).'</span>':'';}
  if('product_stock'===$type)return function_exists('wc_get_stock_html')?wc_get_stock_html($product):'';
  if('product_categories'===$type){$terms=get_the_terms($product->get_id(),'product_cat');if(is_wp_error($terms)||empty($terms))return '';return '<div class="nb-product-categories">'.esc_html__('Categories:','nirmaan-builder').' '.wp_kses_post(wc_get_product_category_list($product->get_id(),', ')).'</div>';}
  $map=array(
   'product_title'=>'woocommerce_template_single_title',
   'product_price'=>'woocommerce_template_single_price',
   'product_image'=>'woocommerce_show_product_images',
   'product_gallery'=>'woocommerce_show_product_images',
   'product_add_to_cart'=>'woocommerce_template_single_add_to_cart',
   'product_description'=>'woocommerce_template_single_excerpt',
   'product_rating'=>'woocommerce_template_single_rating',
   'product_meta'=>'woocommerce_template_single_meta',
   'product_sale_flash'=>'woocommerce_show_product_sale_flash',
   'product_tabs'=>'woocommerce_output_product_data_tabs'
  );
  if(empty($map[$type])||!function_exists($map[$type]))return '';
  ob_start();call_user_func($map[$type]);return ob_get_clean();
 }
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
  if(in_array($type,array('product_title','product_price','product_image','product_gallery','product_add_to_cart','product_description','product_rating','product_meta','product_sale_flash','product_tabs','product_sku','product_stock','product_categories','product_breadcrumbs','related_products','shop_products','cart','checkout','mini_cart','product_archive'),true)){$woo_style=$style;if(in_array($type,array('related_products','shop_products','product_archive'),true))$woo_style.='--nb-woo-columns:'.min(6,max(1,absint($s['woo']['columns']??4))).';';$html.='<div class="nb-woo-element nb-woo-'.esc_attr($type).' nb-node '.$id.'" style="'.$woo_style.'">'.$this->woo($type,$s).'</div>';continue;}
  if(in_array($type,array('gallery','video','slider','tabs','accordion','blog','portfolio'),true)){
   $html.=$this->render_content_module($type,$s,$id,$style,$depth);continue;
  }
  if('form'===$type){$html.=Forms::render($n['id']??'',$s,esc_attr($id),$style);continue;}
  $custom_modules=apply_filters('nirmaan_builder_custom_module_definitions',array());if(is_array($custom_modules)&&isset($custom_modules[$type])){$custom_output=apply_filters('nirmaan_builder_render_custom_module','',$type,$s,$n);if(is_string($custom_output)&&''!==$custom_output){$html.=wp_kses_post($custom_output);continue;}}
  switch($type){case'heading':$l=min(6,max(1,absint($s['level']??2)));$html.=sprintf('<h%d class="nb-heading nb-node %s" style="%s">%s</h%d>',$l,esc_attr($id),esc_attr($style),esc_html($content),$l);break;case'text':$html.='<div class="nb-text nb-node '.esc_attr($id).'" style="'.esc_attr($style).'">'.wp_kses_post($content).'</div>';break;case'image':$u=esc_url($s['url']??'');if($u)$html.='<figure class="nb-image nb-node '.esc_attr($id).'" style="'.esc_attr($style).'"><img src="'.$u.'" alt="'.esc_attr($s['alt']??'').'" loading="lazy"></figure>';break;case'button':$html.='<p class="nb-button-wrap nb-node '.esc_attr($id).'" style="'.esc_attr($style).'"><a class="nb-button" href="'.esc_url($s['url']??'#').'">'.esc_html($content?:__('Button','nirmaan-builder')).'</a></p>';break;case'divider':$html.='<hr class="nb-divider nb-node '.esc_attr($id).'" style="'.esc_attr($style).'">';break;case'spacer':$h=min(1200,absint($s['height']??32));$html.='<div class="nb-spacer nb-node '.esc_attr($id).'" style="height:'.$h.'px;'.$style.'"></div>';break;case'section':case'fullwidth_section':case'specialty_section':case'row':case'column':case'container':case'columns':$layout_class='nb-layout nb-layout-'.$type.' nb-node '.$id;if('fullwidth_section'===$type)$layout_class.=' nb-fullwidth-section';if('specialty_section'===$type)$layout_class.=' nb-specialty-section';$html.='<div class="'.esc_attr($layout_class).'" style="'.esc_attr($style).'">'.$this->render_nodes($n['children']??array(),$depth+1).'</div>';break;}
 }return $html;}
 private function render_content_module($type,$settings,$id,$style,$depth){
  $items=is_array($settings['items']??null)?$settings['items']:array();$out='';
  if('video'===$type){$url=esc_url($settings['url']??'');if(!$url)return '<div class="nb-video nb-node '.$id.'" style="'.esc_attr($style).'"></div>';$embed=wp_oembed_get($url,array('width'=>1200));return '<div class="nb-video nb-node '.$id.'" style="'.esc_attr($style).'">'.($embed?:'<a href="'.$url.'" rel="noopener noreferrer" target="_blank">'.esc_html__('Watch video','nirmaan-builder').'</a>').'</div>';}
  if('gallery'===$type||'slider'===$type){$class='nb-'.$type.' nb-node '.$id;$out.='<div class="'.esc_attr($class).'" style="'.esc_attr($style).'">';foreach(array_slice($items,0,30) as $item){$url=esc_url($item['url']??'');if(!$url)continue;$img='<img src="'.$url.'" alt="'.esc_attr($item['alt']??'').'" loading="lazy">';$title=trim((string)($item['title']??''));$link=esc_url($item['link']??'');$out.='<figure class="nb-media-item">';$out.=$link?'<a href="'.$link.'">'.$img.'</a>':$img;if($title)$out.='<figcaption>'.esc_html($title).'</figcaption>';$out.='</figure>';}$out.='</div>';return $out;}
  if('tabs'===$type){$items=array_slice($items,0,12);$out.='<div class="nb-tabs nb-node '.$id.'" style="'.esc_attr($style).'">';foreach($items as $index=>$item){$tab_id='nb-tab-'.sanitize_html_class($id).'-'.$index;$out.='<input class="nb-tab-radio" type="radio" name="nb-tabs-'.sanitize_html_class($id).'" id="'.esc_attr($tab_id).'" '.($index===0?'checked':'').'>';}$out.='<div class="nb-tab-labels">';foreach($items as $index=>$item){$tab_id='nb-tab-'.sanitize_html_class($id).'-'.$index;$out.='<label for="'.esc_attr($tab_id).'">'.esc_html($item['title']??sprintf(__('Tab %d','nirmaan-builder'),$index+1)).'</label>';}$out.='</div><div class="nb-tab-panels">';foreach($items as $index=>$item){$out.='<section class="nb-tab-panel">'.wp_kses_post(wpautop($item['content']??'')).'</section>';}$out.='</div></div>';return $out;}
  if('accordion'===$type){$out.='<div class="nb-accordion nb-node '.$id.'" style="'.esc_attr($style).'">';foreach(array_slice($items,0,20) as $index=>$item){$out.='<details'.($index===0?' open':'').'><summary>'.esc_html($item['title']??sprintf(__('Item %d','nirmaan-builder'),$index+1)).'</summary><div>'.wp_kses_post(wpautop($item['content']??'')).'</div></details>';}$out.='</div>';return $out;}
  if(in_array($type,array('blog','portfolio'),true)){
   $query=is_array($settings['query']??null)?$settings['query']:array();$post_type='portfolio'===$type?sanitize_key($query['post_type']??'portfolio'):'post';$public=get_post_types(array('public'=>true),'names');if(!in_array($post_type,$public,true))$post_type='post';$limit=min(24,max(1,absint($query['posts_per_page']??6)));$columns=min(6,max(1,absint($query['columns']??3)));
   $loop=new \WP_Query(array('post_type'=>$post_type,'post_status'=>'publish','posts_per_page'=>$limit,'orderby'=>in_array(($query['orderby']??'date'),array('date','title','menu_order','rand'),true)?$query['orderby']:'date','order'=>'ASC'===strtoupper($query['order']??'DESC')?'ASC':'DESC','no_found_rows'=>true));$out.='<div class="nb-'.$type.' nb-node '.$id.'" style="--nb-list-columns:'.$columns.';'.esc_attr($style).'">';while($loop->have_posts()){$loop->the_post();$out.='<article class="nb-listing-card">';if(has_post_thumbnail())$out.='<a href="'.esc_url(get_permalink()).'" class="nb-listing-image">'.get_the_post_thumbnail(get_the_ID(),'large').'</a>';$out.='<h3><a href="'.esc_url(get_permalink()).'">'.esc_html(get_the_title()).'</a></h3>';if('blog'===$type)$out.='<div class="nb-listing-excerpt">'.wp_kses_post(wpautop(get_the_excerpt())).'</div>';$out.='</article>';}$out.='</div>';wp_reset_postdata();return $out;
  }
  return '';
 }
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
