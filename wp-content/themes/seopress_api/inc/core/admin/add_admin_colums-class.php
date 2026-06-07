<?php
// Add custom column to admin pages overview
function custom_template_column($columns)
{
  $columns['template'] = 'Template';
  return $columns;
}
add_filter('manage_pages_columns', 'custom_template_column');

// Display template in the custom column
function custom_template_column_content($column_name, $post_id)
{
  if ($column_name == 'template') {
    $template = get_post_meta($post_id, '_wp_page_template', true);
    echo $template ? $template : 'Standard';
  }
}
add_action('manage_pages_custom_column', 'custom_template_column_content', 10, 2);

// Make the column sortable
function custom_template_column_sortable($columns)
{
  $columns['template'] = 'template';
  return $columns;
}
add_filter('manage_edit-page_sortable_columns', 'custom_template_column_sortable');

// Modify query to sort by template
function custom_template_column_orderby($query)
{
  if (!is_admin() || !$query->is_main_query()) {
    return;
  }

  if ($query->get('orderby') === 'template') {
    $query->set('meta_key', '_wp_page_template');
    $query->set('orderby', 'meta_value');
  }
}
add_action('pre_get_posts', 'custom_template_column_orderby');
