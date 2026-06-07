<?php 
if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_62e6282ac0301',
	'title' => 'Dienstleistungen',
	'fields' => array(
		array(
			'key' => 'field_62e6283de29fa',
			'label' => 'Dienstleistungen',
			'name' => 'dienstleistungen',
			'type' => 'repeater',
			'instructions' => 'Hier die Dienstleistung und Ihre Synonyme eintragen',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '50',
				'class' => 'editor',
				'id' => '',
			),
			'acfe_repeater_stylised_button' => 0,
			'collapsed' => '',
			'min' => 1,
			'max' => 0,
			'layout' => 'table',
			'button_label' => 'Dienstleistung hinzufügen',
			'sub_fields' => array(
				array(
					'key' => 'field_62e6287ae29fb',
					'label' => 'Dienstleistung',
					'name' => 'dienstleistung',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '90',
						'class' => '',
						'id' => '',
					),
					'default_value' => 'Entrümpelung',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				),
			),
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
		array(
			array(
				'param' => 'page_template',
				'operator' => '==',
				'value' => 'template-startseite.php',
			),
		),
		array(
			array(
				'param' => 'page_template',
				'operator' => '==',
				'value' => 'template-kontakt.php',
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

endif;