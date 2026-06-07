<?php
if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_636b919bc9825',
	'title' => 'Blog Kategories',
	'fields' => array(
		array(
			'key' => 'field_636b91ab9f5c6',
			'label' => 'Blog Kategorie auswählen',
			'name' => 'blog_kategorie',
			'type' => 'checkbox',
			'instructions' => 'Beträge aus diesen Kategorien werden angezeigt, 3-4 auswählen',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '30',
				'class' => '',
				'id' => '',
			),
			'choices' => array(
				305 => 'Entrümpelungen',
				309 => 'Räumungen',
				306 => 'Verlassenschaften',
				310 => 'Altwaren und Antiquitäten',
				311 => 'Messie Entrümpelung',
				312 => 'Umzüge & Übersiedlungen',
				314 => 'Kellerräumungen',
				313 => 'Dachbodenentrümpelungen',
				308 => 'Wohnungsräumungen',
				315 => 'Geschäftsauflösungen',
				326 => 'Hotelauflösungen',
				317 => 'Büroauflösungen',
			),
			'allow_custom' => 0,
			'default_value' => array(
			),
			'layout' => 'vertical',
			'toggle' => 0,
			'return_format' => 'value',
			'save_custom' => 0,
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'page_template',
				'operator' => '==',
				'value' => 'template-hauptseiten.php',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'left',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'acfe_display_title' => '',
	'acfe_autosync' => '',
	'acfe_form' => 0,
	'acfe_meta' => '',
	'acfe_note' => '',
));

endif;?>