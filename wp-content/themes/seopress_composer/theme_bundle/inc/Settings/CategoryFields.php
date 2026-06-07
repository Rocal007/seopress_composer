<?php
namespace SeopressComposer\Settings;

class CategoryFields
{
  public function __construct()
  {
    add_action('acf/init', [$this, 'register_field_group']);
    add_action('category_add_form_fields', [$this, 'add_category_image_field']);
    add_action('category_edit_form_fields', [$this, 'edit_category_image_field']);
    add_action('created_category', [$this, 'save_category_image']);
    add_action('edited_category', [$this, 'save_category_image']);
    add_action('admin_footer', [$this, 'category_image_script']);
  }

  public function register_field_group()
  {
    if (function_exists('acf_add_local_field_group')) {
      acf_add_local_field_group(array(
        'key' => 'group_606eee6aed902',
        'title' => 'cat bilder',
        'fields' => array(
          array(
            'key' => 'field_606eee84c5d3a',
            'label' => 'wappen',
            'name' => 'wappen',
            'type' => 'image',
            'return_format' => 'array',
            'preview_size' => 'medium',
            'library' => 'all',
          ),
          array(
            'key' => 'field_606eeea3c5d3b',
            'label' => 'Orte Detail',
            'name' => 'Orte_detail',
            'type' => 'repeater',
            'layout' => 'table',
            'sub_fields' => array(
              array(
                'key' => 'field_608fd877634b2',
                'label' => 'plz',
                'name' => 'plz',
                'type' => 'text',
                'wrapper' => array(
                  'width' => '50',
                  'class' => '',
                  'id' => '',
                ),
              ),
              array(
                'key' => 'field_608fd884634b3',
                'label' => 'Ortsname',
                'name' => 'ortsname',
                'type' => 'text',
                'wrapper' => array('width' => '50'),
              ),
              array(
                'key' => 'field_608fd884634b4_link', // Generating a unique key for safety
                'label' => 'Link',
                'name' => 'link',
                'type' => 'text', // Assuming text or url type based on assumption of functionality
                'wrapper' => array('width' => '100'),
              )
            ),
          ),
          array(
            'key' => 'field_608fd89cab0ef',
            'label' => 'Karte',
            'name' => 'karte',
            'type' => 'image',
            'return_format' => 'array',
            'preview_size' => 'medium',
            'library' => 'all',
          ),
        ),
        'location' => array(
          array(
            array(
              'param' => 'taxonomy',
              'operator' => '==',
              'value' => 'category', // Changed from 'all' to 'category' to be more specific if desired, matching code usually implies category taxonomy
            ),
          ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'active' => true,
      ));
    }
  }

  public function add_category_image_field($taxonomy)
  {
    ?>
    <div class="form-field term-group">
      <label for="category-image-id"><?php _e('Featured Image', 'seopress_composer'); ?></label>
      <input type="hidden" id="category-image-id" name="category-image-id" value="">
      <div id="category-image-wrapper"></div>
      <input type="button" class="button button-secondary" id="category-image-upload"
        value="<?php _e('Add Image', 'seopress_composer'); ?>" />
    </div>
    <?php
  }

  public function edit_category_image_field($term)
  {
    $image_id = get_term_meta($term->term_id, 'category-image-id', true);
    ?>
    <tr class="form-field term-group-wrap">
      <th scope="row">
        <label for="category-image-id"><?php _e('Featured Image', 'seopress_composer'); ?></label>
      </th>
      <td>
        <input type="hidden" id="category-image-id" name="category-image-id" value="<?php echo esc_attr($image_id); ?>">
        <div id="category-image-wrapper">
          <?php if ($image_id)
            echo wp_get_attachment_image($image_id, 'thumbnail'); ?>
        </div>
        <input type="button" class="button button-secondary" id="category-image-upload"
          value="<?php _e('Add Image', 'seopress_composer'); ?>" />
        <input type="button" class="button button-secondary" id="category-image-remove"
          value="<?php _e('Remove Image', 'seopress_composer'); ?>" />
      </td>
    </tr>
    <?php
  }

  public function save_category_image($term_id)
  {
    if (isset($_POST['category-image-id'])) {
      update_term_meta($term_id, 'category-image-id', sanitize_text_field($_POST['category-image-id']));
    }
  }

  public function category_image_script()
  {
    if (!isset($_GET['taxonomy']) || $_GET['taxonomy'] != 'category')
      return;
    ?>
    <script>
      jQuery(document).ready(function ($) {
        var mediaUploader;
        $('#category-image-upload').click(function (e) {
          e.preventDefault();
          if (mediaUploader) {
            mediaUploader.open();
            return;
          }
          mediaUploader = wp.media.frames.file_frame = wp.media({
            title: '<?php _e('Select Image', 'seopress_composer'); ?>',
            button: {
              text: '<?php _e('Use this image', 'seopress_composer'); ?>'
            },
            multiple: false
          });
          mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#category-image-id').val(attachment.id);
            $('#category-image-wrapper').html('<img src="' + attachment.sizes.thumbnail.url + '" />');
          });
          mediaUploader.open();
        });

        $('#category-image-remove').click(function () {
          $('#category-image-id').val('');
          $('#category-image-wrapper').html('');
        });
      });
    </script>
    <?php
  }
}
