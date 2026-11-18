const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const builder = fs.readFileSync('assets/js/builder.js', 'utf8');
const actions = fs.readFileSync('assets/js/admin-actions.js', 'utf8');
const admin = fs.readFileSync('includes/class-nirmaan-builder-admin.php', 'utf8');
const rest = fs.readFileSync('includes/class-nirmaan-builder-rest.php', 'utf8');
const renderer = fs.readFileSync('includes/class-nirmaan-builder-renderer.php', 'utf8');
const editorCss = fs.readFileSync('assets/css/editor.css', 'utf8');
const frontendEditor = fs.readFileSync('assets/js/frontend-edit.js', 'utf8');
const gutenberg = fs.readFileSync('assets/js/gutenberg.js', 'utf8');
const contentTypes = fs.readFileSync('includes/class-nirmaan-builder-content.php', 'utf8');
const plugin = fs.readFileSync('includes/class-nirmaan-builder.php', 'utf8');

test('editor supports nested nodes and responsive device settings', () => {
  assert.match(builder, /const find=/);
  assert.match(builder, /containsId/);
  assert.match(builder, /nb-nested-children/);
  assert.ok(builder.includes("['desktop','tablet','mobile']"));
  assert.ok(builder.includes("hide_'+device"));
});

test('editor exposes WooCommerce layout modules', () => {
  for (const moduleName of ['product_archive', 'cart', 'checkout', 'mini_cart']) {
    assert.ok(builder.includes(moduleName), moduleName + ' module missing');
    assert.ok(rest.includes(moduleName), moduleName + ' is not accepted by REST');
    assert.ok(renderer.includes(moduleName), moduleName + ' has no renderer');
  }
});

test('library supports global elements and JSON import/export', () => {
  assert.match(rest, /_nirmaan_global/);
  assert.match(rest, /function export_library/);
  assert.match(rest, /function import_library/);
  assert.match(rest, /function save_library_item/);
  assert.match(renderer, /global_element/);
});

test('template rules include priorities and exclusions', () => {
  assert.match(rest, /_nirmaan_priority/);
  assert.match(rest, /sanitize_conditions/);
  assert.match(renderer, /matches_conditions/);
  assert.match(actions, /nirmaan-save-template-rules/);
});

test('Theme Options are persisted through a privileged REST route', () => {
  const admin = fs.readFileSync('includes/class-nirmaan-builder-admin.php', 'utf8');
  const settings = fs.readFileSync('includes/class-nirmaan-builder-settings.php', 'utf8');
  assert.match(rest, /'\/settings'/);
  assert.match(rest, /can_manage_settings/);
  assert.match(rest, /Settings::sanitize/);
  assert.match(admin, /nirmaan-theme-settings/);
  assert.match(admin, /nb-select-media/);
  assert.match(settings, /sanitize_hex_color/);
  assert.match(settings, /get_custom_logo/);
  assert.match(settings, /get_site_icon_url/);
});

test('editor exposes layers, duplicate, undo/redo and keyboard shortcuts', () => {
  assert.match(builder, /function renderLayers/);
  assert.match(builder, /nirmaan-undo/);
  assert.match(builder, /nirmaan-redo/);
  assert.match(builder, /const checkpoint=/);
  assert.match(builder, /textContent=t\('Duplicate'\)/);
  assert.match(builder, /e\.key\.toLowerCase\(\)==='d'/);
  assert.match(builder, /e\.shiftKey\?redo\(\):undo\(\)/);
  const admin = fs.readFileSync('includes/class-nirmaan-builder-admin.php', 'utf8');
  assert.match(admin, /id="nirmaan-layers"/);
});

test('layers support collapse, rename, visibility, lock and wireframe view', () => {
  assert.match(builder, /collapsedLayers/);
  assert.match(builder, /Rename layer/);
  assert.match(builder, /Toggle visibility/);
  assert.match(builder, /Toggle lock/);
  assert.match(builder, /settings\.hidden/);
  assert.match(builder, /settings\.locked/);
  assert.match(builder, /nb-wireframe/);
  const admin = fs.readFileSync('includes/class-nirmaan-builder-admin.php', 'utf8');
  assert.match(admin, /id="nirmaan-view-toggle"/);
});

test('layers support safe drag reordering and nested moves', () => {
  assert.match(builder, /nb-layer-drop-target/);
  assert.match(builder, /containsId\(source\.node,target\.node\.id\)/);
  assert.match(builder, /refreshed\.node\.children\.push\(moving\)/);
  assert.match(builder, /wrap\.draggable=!n\.settings\.locked/);
});


test('editor includes autosave, crash recovery and revision restore controls', () => {
  assert.match(builder, /scheduleAutosave/);
  assert.match(builder, /saveLocalDraft/);
  assert.match(builder, /recoveryKey/);
  assert.match(builder, /nirmaan-revisions/);
  assert.match(rest, /function save_autosave/);
  assert.match(rest, /function list_revisions/);
  assert.match(rest, /function restore_revision/);
  assert.match(rest, /_nirmaan_builder_revisions/);
  assert.match(rest, /_nirmaan_builder_autosaves/);
});

test('editor supports desktop, tablet and mobile viewport previews', () => {
  assert.match(builder, /previewDevice/);
  assert.match(builder, /applyPreviewDevice/);
  assert.match(builder, /n\.settings\['hide_'\+previewDevice\]/);
  assert.match(builder, /responsiveStyle/);
  const admin = fs.readFileSync('includes/class-nirmaan-builder-admin.php', 'utf8');
  assert.match(admin, /data-preview-device="mobile"/);
  const css = fs.readFileSync('assets/css/editor.css', 'utf8');
  assert.match(css, /data-preview-device=mobile/);
});

test('design system exposes sanitized global tokens and advanced element styles', () => {
  const settings = fs.readFileSync('includes/class-nirmaan-builder-settings.php', 'utf8');
  assert.match(settings, /accent_color/);
  assert.match(settings, /heading_font_family/);
  assert.match(settings, /spacing_scale/);
  assert.match(settings, /--nb-accent/);
  assert.match(settings, /--nb-line-height/);
  assert.match(rest, /fontWeight/);
  assert.match(rest, /borderColor/);
  assert.match(rest, /boxShadow/);
  assert.match(rest, /flexDirection/);
  assert.match(builder, /Letter spacing/);
  assert.match(builder, /Shadow preset/);
  assert.match(builder, /Flex direction/);
  const renderer = fs.readFileSync('includes/class-nirmaan-builder-renderer.php', 'utf8');
  assert.match(renderer, /'boxShadow'=>'box-shadow'/);
  assert.match(renderer, /shadows=array/);
});

test('design system font weight sanitizer safely falls back when omitted', () => {
  const settings = fs.readFileSync('includes/class-nirmaan-builder-settings.php', 'utf8');
  assert.match(settings, /\$weight = \(string\) \( \$input\['font_weight'\] \?\? \$out\['font_weight'\] \)/);
  assert.match(settings, /\? \$weight : '400'/);
});

test('global style presets are persisted, permission-protected and sanitized', () => {
  assert.match(rest, /'\/style-presets'/);
  assert.match(rest, /can_manage_presets/);
  assert.match(rest, /function create_style_preset/);
  assert.match(rest, /function update_style_preset/);
  assert.match(rest, /function delete_style_preset/);
  assert.match(rest, /nirmaan_builder_style_presets/);
  assert.match(rest, /sanitize_preset_payload/);
  assert.match(rest, /Maximum of 200 style presets/);
  assert.match(builder, /Reusable Style Presets/);
  assert.match(builder, /Save current styles as preset/);
  assert.match(builder, /Linked to global style preset/);
  assert.match(builder, /stylePresetsCache/);
});

test('global presets stay linked and synchronize through frontend rendering', () => {
  assert.match(rest, /'style_preset_id'=>sanitize_key/);
  assert.match(rest, /'preset_fallback'=>array/);
  assert.match(rest, /function snapshot_preset_nodes/);
  assert.match(rest, /preserve_deleted_preset_references/);
  assert.match(rest, /references_preserved/);
  assert.match(builder, /function getNodeStyle/);
  assert.match(builder, /function detachPresetReferences/);
  assert.match(builder, /Linked to global style preset/);
  assert.match(builder, /Update selected preset from this element/);
  assert.match(builder, /linked elements will sync/);
  assert.match(builder, /Detach preset/);
  assert.match(builder, /getInheritedNodeStyle\(n,previewDevice\)/);
  const renderer = fs.readFileSync('includes/class-nirmaan-builder-renderer.php', 'utf8');
  assert.match(renderer, /function get_style_preset/);
  assert.match(renderer, /preset\['desktop'\]/);
  assert.match(renderer, /preset\[\$device\]/);
});

test('editor supports responsive style copy, paste and multi-selection extend', () => {
  assert.match(builder, /function captureStyles/);
  assert.match(builder, /function applyStyleSnapshot/);
  assert.match(builder, /function copyStyles/);
  assert.match(builder, /function pasteStyles/);
  assert.match(builder, /function extendStyles/);
  assert.match(builder, /selectNode\(n\.id,e\.ctrlKey\|\|e\.metaKey\)/);
  assert.match(builder, /Styles extended from first selected element/);
  assert.match(builder, /delete node\.settings\.style_preset_id/);
  assert.match(builder, /hide_'\+d/);
});


test('template parts support independent activation and display conditions', () => {
  const admin = fs.readFileSync('includes/class-nirmaan-builder-admin.php', 'utf8');
  assert.match(admin, /nirmaan-save-part-settings/);
  assert.match(admin, /part_active_/);
  assert.match(admin, /part_condition_/);
  assert.match(actions, /nirmaan-save-part-settings/);
  assert.match(rest, /_nirmaan_part_settings/);
  assert.match(rest, /part_settings/);
  assert.match(renderer, /function part_is_active/);
  assert.match(renderer, /part_is_active\(\$id,'body'\)/);
  assert.match(renderer, /part_is_active\(\$id,\$part\)/);
});


test('Theme Builder dashboard supports template search, status, rename and duplicate', () => {
  const admin = fs.readFileSync('includes/class-nirmaan-builder-admin.php', 'utf8');
  assert.match(admin, /nb-template-search/);
  assert.match(admin, /nb-template-status-filter/);
  assert.match(admin, /nb-template-title/);
  assert.match(admin, /nb-template-status/);
  assert.match(admin, /nirmaan-save-template-details/);
  assert.match(admin, /nirmaan-duplicate-template/);
  assert.match(actions, /searchTemplates/);
  assert.match(actions, /duplicate-template/);
  assert.match(rest, /function duplicate_template/);
  assert.match(rest, /invalid_template_status/);
  assert.match(rest, /'status'=>get_post_status\(\$id\)/);
});


test('visual builder supports canvas zoom and fullscreen controls', () => {
  const admin = fs.readFileSync('includes/class-nirmaan-builder-admin.php', 'utf8');
  const css = fs.readFileSync('assets/css/editor.css', 'utf8');
  assert.match(admin, /nb-editor-fullscreen/);
  assert.match(admin, /nb-zoom-in/);
  assert.match(admin, /nb-zoom-out/);
  assert.match(builder, /function setZoom|const setZoom/);
  assert.match(builder, /nb-editor-fullscreen/);
  assert.match(css, /\.nb-editor-fullscreen/);
});

test('visual builder supports element context menu and inline text editing', () => {
  const admin = fs.readFileSync('includes/class-nirmaan-builder-admin.php', 'utf8');
  const css = fs.readFileSync('assets/css/editor.css', 'utf8');
  assert.match(admin, /nb-editor-context-menu/);
  assert.match(builder, /function showContextMenu/);
  assert.match(builder, /function startInlineEdit/);
  assert.match(builder, /oncontextmenu/);
  assert.match(builder, /ondblclick/);
  assert.match(css, /\.nb-editor-context-menu/);
  assert.match(css, /\.nb-inline-editable/);
});


test('layout system supports nested section, row, column and columns structures', () => {
  for (const type of ['section','row','column','columns']) {
    assert.ok(builder.includes("['"+type+"'"), type + ' module missing');
    assert.ok(rest.includes("'"+type+"'"), type + ' is not accepted by REST');
    assert.ok(renderer.includes("case'"+type+"'"), type + ' has no renderer');
  }
  assert.match(builder, /const make=type/);
  assert.match(builder, /Add column/);
  assert.match(builder, /Remove last column/);
  assert.match(builder, /gridTemplateColumns/);
});

test('layout system sanitizes and renders advanced positioning and overflow styles', () => {
  const renderer = fs.readFileSync('includes/class-nirmaan-builder-renderer.php', 'utf8');
  assert.match(rest, /gridTemplateColumns/);
  for (const key of ['position','zIndex','overflow','flexWrap','top','right','bottom','left']) {
    assert.ok(rest.includes("'"+key+"'"), key + ' style is not sanitized');
    assert.ok(renderer.includes("'"+key+"'"), key + ' style is not rendered');
  }
});


test('layer panel supports filtering, expand/collapse, hierarchy navigation and nesting actions', () => {
  for (const feature of ['nb-layer-search','nb-layer-expand-all','nb-layer-collapse-all','nb-layer-navigator','selectedPath','renderLayerNavigator','moveLayer']) {
    assert.ok(builder.includes(feature), feature + ' layer feature missing');
  }
  assert.match(builder, /Indent into previous layout/);
  assert.match(builder, /Outdent layer/);
  assert.match(builder, /containsId\(source\.node,target\.node\.id\)/);
  assert.match(admin, /nb-layer-search/);
  assert.match(admin, /nb-layer-navigator/);
});


test('advanced design controls are editable, sanitized and rendered on frontend', () => {
  assert.ok(builder.includes('linear-gradient'));
  assert.ok(builder.includes('Hover state'));
  assert.ok(builder.includes('Hover transform'));
  assert.ok(builder.includes('Animation duration'));
  assert.ok(rest.includes('backgroundImage'));
  assert.ok(rest.includes("sanitize_style($style['hover'],false)"));
  assert.ok(renderer.includes(':hover{'));
  assert.ok(renderer.includes("'backgroundImage'=>'background-image'"));
  assert.ok(renderer.includes("'animationName'=>'animation-name'"));
  assert.ok(renderer.includes("'filter'=>'filter'"));
  assert.ok(renderer.includes("'transform'=>'transform'"));
});


test('responsive editing supports custom breakpoints and inherited device styles', () => {
  const settings = fs.readFileSync('includes/class-nirmaan-builder-settings.php', 'utf8');
  assert.match(settings, /'breakpoints' => array\( 'tablet' => 1024, 'mobile' => 767 \)/);
  assert.match(settings, /\$mobile >= \$tablet/);
  assert.ok(settings.includes("$out['breakpoints']"));
  assert.match(builder, /function getInheritedNodeStyle/);
  assert.match(builder, /t\('Reset '\)\+device\+' overrides/);
  assert.match(builder, /Save breakpoints/);
  assert.match(builder, /responsiveBreakpoints\.tablet/);
  assert.match(renderer, /Settings::get\(\)\['breakpoints'\]/);
  assert.match(renderer, /min-width:/);
});


test('dynamic content supports protected custom fields and safe node bindings', () => {
  assert.match(rest, /'\/custom-fields\/\(\?P<id>/);
  assert.match(rest, /function get_custom_fields/);
  assert.match(rest, /function save_custom_fields/);
  assert.match(rest, /_nirmaan_builder_custom_fields/);
  assert.match(rest, /sanitize_textarea_field/);
  assert.match(rest, /'post_title','post_excerpt','post_permalink','post_date','post_author','featured_image','custom_field'/);
  assert.match(builder, /Builder Custom Fields/);
  assert.match(builder, /Save custom fields/);
  assert.match(builder, /Dynamic Content/);
  assert.match(renderer, /function dynamic_value/);
  assert.match(renderer, /post_title/);
  assert.match(renderer, /custom_field/);
});

test('Query Loop supports bounded public post queries and nested dynamic templates', () => {
  assert.match(builder, /\['query_loop','Query Loop'\]/);
  assert.match(builder, /Posts per page \(1–24\)/);
  assert.match(rest, /'query_loop'/);
  assert.match(rest, /posts_per_page/);
  assert.match(renderer, /new \\WP_Query/);
  assert.match(renderer, /nb-query-item/);
  assert.match(renderer, /wp_reset_postdata/);
  assert.match(renderer, /render_nodes\(\$n\['children'\]\?\?array\(\),\$depth\+1\)/);
});


test('Gutenberg integration exposes a Builder sidebar and preserves native editor content', () => {
  assert.match(admin, /enqueue_block_editor_assets/);
  assert.match(admin, /wp_enqueue_script\('nirmaan-builder-gutenberg'/);
  assert.match(gutenberg, /wp\.plugins\.registerPlugin/);
  assert.match(gutenberg, /PluginSidebar/);
  assert.match(gutenberg, /getCurrentPostId/);
  assert.match(gutenberg, /_nirmaan_builder_enabled/);
  assert.match(contentTypes, /register_post_meta/);
  assert.match(contentTypes, /'show_in_rest' => true/);
  assert.match(contentTypes, /add_post_type_support\( \$post_type, 'custom-fields' \)/);
  assert.match(renderer, /metadata_exists\('post',get_the_ID\(\),'_nirmaan_builder_enabled'\)/);
  assert.match(rest, /update_post_meta\(\$id,'_nirmaan_builder_enabled',true\)/);
});

test('Classic Editor and public custom post types receive Builder controls safely', () => {
  assert.match(admin, /add_meta_boxes/);
  assert.match(admin, /show_ui'=>true/);
  assert.match(admin, /save_builder_mode/);
  assert.match(admin, /wp_verify_nonce/);
  assert.match(admin, /current_user_can\('edit_post',\$post_id\)/);
  assert.match(admin, /Open Visual Builder/);
  assert.match(contentTypes, /get_post_types\( array\( 'public' => true \), 'names' \)/);
  assert.ok(plugin.includes("register_content_types' ), 5"));
});


test('frontend preview and authenticated inline editing are available for editable posts', () => {
  assert.match(admin, /Open Frontend Preview/);
  assert.match(renderer, /enqueue_frontend_editor/);
  assert.match(renderer, /is_user_logged_in\(\)/);
  assert.match(renderer, /current_user_can\('edit_post',\$post_id\)/);
  assert.match(renderer, /get_post_meta\(\$post_id,'_nirmaan_builder_enabled',true\)/);
  assert.ok(renderer.includes("'1'!==sanitize_text_field(wp_unslash($_GET['nirmaan_frontend_edit']??''))"));
  assert.match(renderer, /add_frontend_edit_link/);
  assert.match(renderer, /admin_bar_menu/);
  assert.match(frontendEditor, /contentEditable = 'true'/);
  assert.match(frontendEditor, /api\('content\/' \+ cfg\.postId/);
  assert.match(frontendEditor, /method: 'POST'/);
  assert.match(frontendEditor, /beforeunload/);
  assert.match(rest, /update_post_meta\(\$id,'_nirmaan_builder_document',\$doc\)/);
  assert.match(rest, /update_post_meta\(\$id,'_nirmaan_builder_enabled',true\)/);
});

test('frontend editor is loaded only for an authorized singular post in explicit edit mode', () => {
  assert.match(renderer, /!is_singular\(\)\|\|!is_user_logged_in\(\)/);
  assert.match(renderer, /wp_create_nonce\('wp_rest'\)/);
  assert.match(renderer, /wp_enqueue_style\('nirmaan-builder-frontend-edit'/);
});


test('WooCommerce builder exposes product detail, catalog, cart and checkout elements', () => {
  for (const type of ['product_breadcrumbs','product_title','product_sale_flash','product_gallery','product_price','product_rating','product_stock','product_sku','product_categories','product_description','product_add_to_cart','product_meta','product_tabs','related_products','shop_products','product_archive','mini_cart','cart','checkout']) {
    assert.ok(builder.includes("['"+type+"'"), type + ' editor module missing');
    assert.ok(rest.includes("'"+type+"'"), type + ' REST allowlist missing');
  }
  assert.match(renderer, /woocommerce_output_product_data_tabs/);
  assert.match(renderer, /woocommerce_template_single_add_to_cart/);
  assert.match(renderer, /woocommerce_template_single_meta/);
  assert.match(renderer, /woocommerce_show_product_images/);
  assert.match(renderer, /woocommerce_breadcrumb/);
  assert.match(renderer, /woocommerce_cart/);
  assert.match(renderer, /woocommerce_checkout/);
});

test('WooCommerce catalog grids have bounded, sanitized display controls', () => {
  assert.match(builder, /Products to show \(1–24\)/);
  assert.match(builder, /Grid columns \(1–6\)/);
  assert.match(builder, /Product category slug/);
  assert.match(builder, /Enable pagination/);
  assert.match(rest, /'woo'=>array\('limit'=>min\(24,max\(1,absint\(\$woo\['limit'\]\?\?8\)\)\)/);
  assert.match(rest, /'popularity','rating','price','rand'/);
  assert.match(renderer, /--nb-woo-columns/);
  assert.match(renderer, /'paginate'=>\$paginate/);
  assert.match(renderer, /'category'=>\$category/);
  assert.match(renderer, /'limit'=>\$limit/);
});


test('Library saves selected reusable sections and typed module presets', () => {
  assert.match(builder, /const selectedNodes=\(\)=>\{/);
  assert.match(builder, /Name this reusable section/);
  assert.match(builder, /module-preset/);
  assert.match(builder, /nodes\.map\(node=>rekey\(clone\(node\)\)\)/);
  assert.match(rest, /sanitize_library_category/);
  assert.match(rest, /invalid_library_title/);
  assert.match(rest, /empty_library_item/);
  assert.match(rest, /unsupported_import_version/);
  assert.match(rest, /_nirmaan_category/);
});


test('History saving serializes autosaves before manual save and preserves edits made during save', () => {
  assert.match(builder, /autosaveInFlight=Promise\.resolve\(\)/);
  assert.match(builder, /await autosaveInFlight\.catch\(\(\)=>\{\}\)/);
  assert.match(builder, /JSON\.stringify\(doc\)===JSON\.stringify\(snapshot\)/);
  assert.match(builder, /checkpoint\(\);doc=restored\.document/);
  assert.match(rest, /Invalid autosave part for this document type/);
  assert.match(rest, /30\*DAY_IN_SECONDS/);
  assert.match(rest, /Invalid revision part for this document type/);
  assert.match(rest, /public function list_revisions/);
});

test('Forms support visual configuration, secure submissions and admin inbox', () => {
  const forms = fs.readFileSync('includes/class-nirmaan-builder-forms.php', 'utf8');
  const formsJs = fs.readFileSync('assets/js/forms.js', 'utf8');
  assert.ok(builder.includes("['form','Form']"));
  assert.ok(builder.includes('Form fields'));
  assert.ok(builder.includes('notification_email'));
  assert.match(rest, /'form_fields'=>\$this->sanitize_form_fields/);
  assert.match(rest, /function sanitize_form_fields/);
  assert.match(renderer, /Forms::render/);
  assert.match(forms, /register_rest_route\( 'nirmaan-builder\/v1', '\/form-submit'/);
  assert.match(forms, /hash_hmac\('sha256'/);
  assert.match(forms, /form_rate_limited/);
  assert.match(forms, /sanitize_email/);
  assert.match(forms, /wp_insert_post/);
  assert.match(forms, /_nb_submission_fields/);
  assert.match(forms, /function render_submissions/);
  assert.match(formsJs, /form\[data-nb-form\]/);
  assert.match(formsJs, /fetch\(form.action/);
});

test('Phase 19 forms include radio fields, conditional visibility and server-side conditional validation', () => {
  const forms = fs.readFileSync('includes/class-nirmaan-builder-forms.php', 'utf8');
  const formsJs = fs.readFileSync('assets/js/forms.js', 'utf8');
  assert.match(rest, /'radio'/);
  assert.match(rest, /'show_if'=>array\('field'=>\$depends_on,'equals'=>\$equals\)/);
  assert.match(forms, /elseif\('radio'===\$type\)/);
  assert.match(forms, /data-nb-depends/);
  assert.match(forms, /\$dependency&&sanitize_text_field/);
  assert.match(formsJs, /data-nb-depends/);
  assert.match(formsJs, /field\.hidden=!visible/);
});

test('Phase 19 forms support configurable success messages, safe webhooks and extension hooks', () => {
  const forms = fs.readFileSync('includes/class-nirmaan-builder-forms.php', 'utf8');
  assert.match(rest, /'success_message'=>sanitize_text_field/);
  assert.match(rest, /'webhook_url'=>esc_url_raw/);
  assert.match(forms, /success_message/);
  assert.match(forms, /wp_http_validate_url/);
  assert.match(forms, /wp_safe_remote_post/);
  assert.match(forms, /redirection'=>0/);
  assert.match(forms, /do_action\('nirmaan_builder_form_submitted'/);
});

test('Phase 19 submissions can be deleted only by an authorized administrator with a nonce', () => {
  const forms = fs.readFileSync('includes/class-nirmaan-builder-forms.php', 'utf8');
  assert.match(forms, /admin_post_nirmaan_delete_submission/);
  assert.match(forms, /function delete_submission/);
  assert.match(forms, /current_user_can\('manage_options'\)/);
  assert.match(forms, /check_admin_referer\('nirmaan_delete_submission_\'/);
  assert.match(forms, /wp_delete_post\(\$id,true\)/);
});

test('Phase 20 registers granular builder roles and a protected role editor', () => {
  const roles = fs.readFileSync('includes/class-nirmaan-builder-roles.php', 'utf8');
  const plugin = fs.readFileSync('includes/class-nirmaan-builder.php', 'utf8');
  const admin = fs.readFileSync('includes/class-nirmaan-builder-admin.php', 'utf8');
  assert.match(plugin, /class-nirmaan-builder-roles\.php/);
  assert.match(plugin, /Roles::register_hooks/);
  for (const capability of ['use_nirmaan_builder','edit_nirmaan_content','use_nirmaan_modules','use_nirmaan_advanced_modules','edit_nirmaan_design','manage_nirmaan_library','manage_nirmaan_templates','manage_nirmaan_settings','manage_nirmaan_roles']) {
    assert.ok(roles.includes(capability), capability + ' capability missing');
  }
  assert.match(roles, /add_role\( 'nirmaan_builder'/);
  assert.match(roles, /check_admin_referer\( 'nirmaan_builder_save_roles'/);
  assert.match(roles, /current_user_can\( 'manage_nirmaan_roles' \)/);
  assert.match(admin, /nirmaan-roles/);
  assert.match(admin, /Roles::render_page/);
});

test('Phase 20 gates REST content, templates, library and design actions by granular capabilities', () => {
  assert.match(rest, /current_user_can\('use_nirmaan_builder'\)/);
  assert.match(rest, /current_user_can\('edit_nirmaan_content'\)/);
  assert.match(rest, /current_user_can\('manage_nirmaan_templates'\)/);
  assert.match(rest, /current_user_can\('manage_nirmaan_library'\)/);
  assert.match(rest, /current_user_can\('manage_nirmaan_settings'\)/);
  assert.match(rest, /current_user_can\('edit_nirmaan_design'\)/);
  assert.match(rest, /use_nirmaan_advanced_modules/);
  assert.match(admin, /manage_nirmaan_settings/);
  assert.match(admin, /manage_nirmaan_templates/);
  assert.match(admin, /manage_nirmaan_library/);
});

test('Phase 20 hides and rejects advanced modules for roles without the advanced-module capability', () => {
  assert.match(builder, /cfg\.canUseAdvancedModules/);
  assert.match(rest, /array_diff\(\$allowed,array\('form','query_loop'/);
  assert.match(rest, /nodes_require_advanced_modules/);
  assert.match(rest, /post_requires_advanced_modules/);
});

test('Phase 20 multilingual integrations register translatable builder content safely', () => {
  const multilingual = fs.readFileSync('includes/class-nirmaan-builder-multilingual.php', 'utf8');
  const plugin = fs.readFileSync('includes/class-nirmaan-builder.php', 'utf8');
  const renderer = fs.readFileSync('includes/class-nirmaan-builder-renderer.php', 'utf8');
  const wpml = fs.readFileSync('wpml-config.xml', 'utf8');
  assert.match(plugin, /class-nirmaan-builder-multilingual\.php/);
  assert.match(multilingual, /pll_get_post_types/);
  assert.match(multilingual, /pll_copy_post_metas/);
  assert.match(multilingual, /wpml_register_single_string/);
  assert.match(multilingual, /wpml_translate_single_string/);
  assert.match(multilingual, /pll_register_string/);
  assert.match(multilingual, /pll__/);
  assert.match(multilingual, /_nirmaan_builder_document/);
  assert.match(renderer, /Multilingual::translate_document/);
  assert.match(wpml, /nirmaan_template/);
  assert.match(wpml, /nirmaan_library/);
  assert.match(wpml, /action="copy"/);
});

test('Phase 20 scripts load WordPress gettext runtime for translations', () => {
  const admin = fs.readFileSync('includes/class-nirmaan-builder-admin.php', 'utf8');
  const forms = fs.readFileSync('includes/class-nirmaan-builder-forms.php', 'utf8');
  for (const file of [admin, forms]) {
    assert.match(file, /wp_set_script_translations/);
    assert.match(file, /wp-i18n/);
  }
  for (const file of [builder, actions, gutenberg, fs.readFileSync('assets/js/forms.js', 'utf8')]) {
    assert.match(file, /wp\.i18n/);
    assert.match(file, /nirmaan-builder/);
  }
});


test('repository is packaged as a WordPress theme, not a plugin', () => {
  const theme = fs.readFileSync('style.css', 'utf8');
  const functions = fs.readFileSync('functions.php', 'utf8');
  const header = fs.readFileSync('header.php', 'utf8');
  const footer = fs.readFileSync('footer.php', 'utf8');
  const bootstrap = fs.readFileSync('includes/class-nirmaan-builder.php', 'utf8');
  assert.match(theme, /Theme Name:\s*Nirmaan Builder/);
  assert.match(functions, /get_template_directory\(\)/);
  assert.match(functions, /Theme::instance\(\)->boot/);
  assert.match(header, /wp_head\(\)/);
  assert.match(header, /wp_body_open\(\)/);
  assert.match(footer, /wp_footer\(\)/);
  assert.match(bootstrap, /class Theme/);
  assert.match(bootstrap, /class-nirmaan-builder-content\.php/);
  assert.match(bootstrap, /load_theme_textdomain/);
  assert.doesNotMatch(bootstrap, /load_plugin_textdomain/);
});

test('Canvas parity modules are allow-listed, persisted and rendered with real frontend output', () => {
  const renderer = fs.readFileSync('includes/class-nirmaan-builder-renderer.php', 'utf8');
  for (const type of ['gallery','video','slider','tabs','accordion','blog','portfolio','fullwidth_section','specialty_section']) {
    assert.ok(builder.includes("['"+type+"'"), type+' missing from editor module palette');
    assert.ok(rest.includes("'"+type+"'"), type+' missing from REST sanitizer');
  }
  for (const type of ['gallery','video','slider','tabs','accordion','blog','portfolio']) assert.ok(renderer.includes("'"+type+"'"),type+' missing from frontend renderer');
  assert.match(renderer, /wp_oembed_get/);
  assert.match(renderer, /nb-tab-radio/);
  assert.match(renderer, /<details/);
  assert.match(renderer, /new \\WP_Query/);
  assert.match(rest, /sanitize_module_items/);
  assert.match(builder, /nb-preview-gallery/);
  assert.match(builder, /nb-preview-panels/);
  assert.match(builder, /query_columns/);
});

test('Canvas interaction supports element copy/paste, resize and root drop targets', () => {
  assert.match(builder, /nodeClipboard/);
  assert.match(builder, /Element pasted/);
  assert.match(builder, /nb-resize-handle/);
  assert.match(builder, /setPointerCapture/);
  assert.ok(builder.includes("canvas.addEventListener('drop'"));
  assert.match(rest, /fullwidth_section','specialty_section/);
});

test('Element settings are separated into Content, Design and Advanced tabs', () => {
  assert.match(builder, /nb-settings-tabs/);
  assert.match(builder, /nb-settings-tab-panel/);
  assert.match(builder, /aria-selected/);
  assert.match(builder, /nb-design-panel/);
  assert.match(builder, /nb-custom-fields/);
});

test('Custom module extension API exposes definitions, sanitization and rendering hooks', () => {
  assert.match(admin, /nirmaan_builder_custom_module_definitions/);
  assert.match(builder, /customModules/);
  assert.match(rest, /nirmaan_builder_sanitize_custom_module_settings/);
  assert.match(rest, /\$custom_types/);
  assert.match(renderer, /nirmaan_builder_render_custom_module/);
});


test('module insertion accepts Fullwidth and Specialty sections as parents', () => {
  assert.match(builder, /\['section','fullwidth_section','specialty_section','row','column','container','columns','query_loop'\]\.includes\(target\.node\.type\)\)target\.node\.children\.push\(make\(type\)\)/);
  assert.match(builder, /\['section','fullwidth_section','specialty_section','row','column','container','columns','query_loop'\]\.includes\(target\.node\.type\)/);
});

test('core module content controls are schema-driven and layout/Woo elements get no generic content field', () => {
  assert.match(builder, /const contentSchemas=/);
  assert.match(builder, /function renderContentSchema\(parent,node\)/);
  for (const type of ['heading','text','image','button','video','spacer']) {
    assert.match(builder, new RegExp(type+':\\['));
  }
  assert.match(builder, /else \{renderContentSchema\(p,n\);\}/);
  assert.doesNotMatch(builder, /else if\(n\.type!=='form'\) \{field\(p,'Content'/);
});

test('Section, Row and Column expose dedicated responsive layout schemas', () => {
  for (const type of ['section','fullwidth_section','specialty_section','row','column']) {
    assert.ok(builder.includes(type+':['), type + ' layout schema missing');
  }
  assert.match(builder, /function renderLayoutSchema/);
  assert.match(builder, /renderLayoutSchema\(responsive,n,style,visibleStyle\)/);
  for (const property of ['width','maxWidth','minHeight','padding','margin','backgroundColor','overflow','flexDirection','flexWrap','justifyContent','alignItems','gap']) {
    assert.ok(rest.includes("'"+property+"'"), property + ' is not accepted by the REST style sanitizer');
    assert.ok(renderer.includes("'"+property+"'"), property + ' is not mapped by the frontend renderer');
  }
});


test('layout Advanced HTML attributes are sanitized, persisted and rendered', () => {
  assert.match(builder, /HTML ID/);
  assert.match(builder, /CSS classes \(space separated\)/);
  assert.match(rest, /'html_id'=>sanitize_html_class/);
  assert.match(rest, /'css_class'=>implode\(' ',array_slice/);
  assert.match(renderer, /\$s\['css_class'\]/);
  assert.match(renderer, /\$s\['html_id'\]/);
  assert.match(renderer, /\$layout_attributes/);
});


test('column widths are pointer-resizable and per-side spacing is persisted and rendered', () => {
  for (const key of ['paddingTop','paddingRight','paddingBottom','paddingLeft','marginTop','marginRight','marginBottom','marginLeft']) {
    assert.ok(builder.includes("key:'"+key+"'"), key + ' control missing');
    assert.ok(rest.includes("'"+key+"'"), key + ' is not sanitized');
  }
  assert.match(builder, /nb-column-width-handle/);
  assert.match(builder, /Drag to resize and balance adjacent column/);
  assert.match(builder, /style\.width=value/);
  for (const property of ['padding-top','padding-right','padding-bottom','padding-left','margin-top','margin-right','margin-bottom','margin-left']) {
    assert.ok(renderer.includes("'"+property+"'"), property + ' is not rendered');
  }
});


test('specialty sections expose asymmetric presets with responsive frontend rendering', () => {
  assert.match(builder, /Specialty layout preset/);
  for (const preset of ['1fr 2fr','2fr 1fr','1fr 1fr 2fr','2fr 1fr 1fr','1fr 2fr 1fr']) {
    assert.ok(builder.includes(preset), 'missing specialty preset '+preset);
  }
  assert.match(builder, /previewDevice==='mobile'\?'1fr'/);
  assert.match(renderer, /'specialty_section'===\$type/);
  assert.ok(renderer.includes("$device_style['gridTemplateColumns']=$device_style['gridTemplateColumns']??('mobile'"));
});

test('row column resizing balances an adjacent column and persists both widths', () => {
  assert.match(builder, /adjacentStart/);
  assert.match(builder, /pairTotal-next/);
  assert.match(builder, /Column widths balanced and saved/);
  assert.match(builder, /targetStyle\.flexBasis=value/);
  assert.ok(rest.includes("'flexBasis'"));
  assert.ok(renderer.includes("'flexBasis'=>'flex-basis'"));
  assert.match(editorCss, /nb-column-width-handle/);
});


test('Akario module palette is integrated into the existing Nirmaan editor canvas', () => {
  const js = fs.readFileSync('assets/js/builder.js', 'utf8');
  const rest = fs.readFileSync('includes/class-nirmaan-builder-rest.php', 'utf8');
  const renderer = fs.readFileSync('includes/class-nirmaan-builder-renderer.php', 'utf8');
  for (const type of ['blurb','cta','testimonial','pricing_tables','number_counter','icon','html','code','search','login','contact_form','email_optin']) {
    assert.ok(js.includes("['"+type+"'"), type + ' missing from existing canvas palette');
    assert.ok(rest.includes("'"+type+"'"), type + ' missing from REST allowlist');
    if (!['contact_form','email_optin'].includes(type)) assert.ok(renderer.includes("case'"+type+"'"), type + ' missing from frontend renderer');
  }
  assert.match(js, /nb-preview-blurb/);
  assert.match(js, /nb-preview-pricing/);
  assert.match(renderer, /Forms::render/);
  assert.match(renderer, /wp_login_form/);
  assert.match(renderer, /wp_kses_post\(\$s\['html'\]/);
});
