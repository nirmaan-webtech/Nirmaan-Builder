(function (wp, config) {
 'use strict';
 if (!wp || !wp.plugins || !wp.editPost || !wp.element || !wp.data || !config) return;
 const el = wp.element.createElement;
 const PluginSidebar = wp.editPost.PluginSidebar;
 const PanelBody = wp.components.PanelBody;
 const ToggleControl = wp.components.ToggleControl;
 const Button = wp.components.Button;
 const Notice = wp.components.Notice;
 function Sidebar() {
  const meta = wp.data.useSelect(function (select) {
   return select('core/editor').getEditedPostAttribute('meta') || {};
  }, []);
  const dispatch = wp.data.useDispatch('core/editor');
  const enabled = !!meta._nirmaan_builder_enabled;
  const postId = wp.data.useSelect(function (select) {
   return select('core/editor').getCurrentPostId();
  }, []);
  const builderUrl = config.builderUrl + '&post_id=' + encodeURIComponent(postId);
  return el(PluginSidebar, { name: 'nirmaan-builder-sidebar', title: config.label, icon: 'layout' },
   el(PanelBody, { title: 'Frontend output', initialOpen: true },
    el(ToggleControl, {
     label: 'Use Nirmaan Builder output',
     checked: enabled,
     help: enabled ? 'The Builder layout replaces native post content on the frontend.' : 'The native Gutenberg content remains the frontend output.',
     onChange: function (value) {
      dispatch.editPost({ meta: Object.assign({}, meta, { _nirmaan_builder_enabled: !!value }) });
     }
    }),
    el(Notice, { status: 'info', isDismissible: false }, 'Gutenberg content is preserved separately. Save the post after changing this setting.'),
    el(Button, { variant: 'primary', disabled: !postId, onClick: function () { window.location.href = builderUrl; } }, 'Open Visual Builder')
   )
  );
 }
 wp.plugins.registerPlugin('nirmaan-builder-sidebar', { render: Sidebar, icon: 'layout' });
})(window.wp, window.NirmaanBuilderGutenbergConfig);
