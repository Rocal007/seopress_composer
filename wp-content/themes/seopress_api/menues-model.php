<?php
$site_title = get_bloginfo( 'name' );

//All categories menu
$categories = get_categories(array(
    'orderby' => 'name',
    'order' => 'DESC',
    'parent'   => 0,
    'hide_empty' => 0,
    'exclude' => 1,
));


foreach ($categories as $category) {
    $thumbnail = get_field('karte', $category->taxonomy . '_' . $category->term_id);
	if (!empty($thumbnail['sizes']['map-thumb'])) {
		$thumbnail = $thumbnail['sizes']['map-thumb'];
	}
	else {
		$thumbnail = '';
	}
    //var_dump($thumbnail['sizes']['map-thumb']) . '<br>';
    $category_url = esc_url(get_category_link($category->term_id));

    if (!empty($thumbnail)) {
        $categories_menu[] = array(
            'name'                  => $category->name,
            'term_id'               => $category->term_id,
            'taxonomy'              => $category->taxonomy,
            'thumbnail'             => $thumbnail,
            'category_url'          => $category_url,
        );
    }
	
}

$cat = get_field('einsatzgebiet', 'option');

//Main Pages Menue
foreach (get_main_page_ids(false) as $id) {
    $link_title		= get_the_title($id);
	$title  		= title_cleaner($link_title);
    $link   		= get_the_permalink($id);
	$image  		= get_the_post_thumbnail_url($id, 'category-thumb');
	$tooltip_image 	= get_the_post_thumbnail_url($id, 'thumbnail');
	$claim			= '';
    $icon   		= get_field("hauptseiten_icon", $id);
	$remote_url		= get_field('remote_page', $id);
	
	if (empty($icon[0])) {
		$icon[0] = 'Entrümpelungen';
	}


    $main_menu[] = array(
        'menu_title'            => $title,
        'menu_linked_title'     => $link_title,
        'menu_link'             => $link,
        'menu_icon'             => $icon[0],
        'menu_location'         => get_location(),
		'menu_image_url'		=> $image,
		'menu_tooltip_image_url'=> $tooltip_image,
		'remote_url'			=> $remote_url,
		'tooltip_claim'			=> $claim
    );
}

//Cat Menue
if (is_category()){
		$category = single_term_title("", false);
		$cat_id = get_cat_ID( $category );

		$args_cat = [
			"post_type"         => "page",
			"cat"               => $cat_id,
			"orderby"           => "menu_order",
			"order"             => "ASC",
			"posts_per_page"    => -1,
		];

		$cat_query = new WP_Query($args_cat);

		while ($cat_query->have_posts()):
			$cat_query->the_post();
			$page_id 		= get_the_ID();
			$ancestors 		= get_ancestors($page_id, "page");
			$remote_url		= get_field('remote_page', $ancestors[0]);
			$link_title 	= get_the_title();
			$categories 	= get_the_category();
			$icon 			= get_field("hauptseiten_icon", $ancestors[0]);
			$title 			= title_cleaner($link_title);
			$link 			= get_the_permalink();
			$image  		= get_the_post_thumbnail_url($ancestors[0], 'category-thumb');

			$cat_menu[] = array(
				'cat_menu_title'            => $title, 
				'cat_menu_linked_title'     => $link_title, 
				'cat_menu_link'             => $link,
				'cat_menu_icon'             => $icon[0],
				'cat_menu_location'        	=> get_location(),
				'cat_menu_image_url'		=> $image,
				'remote_url'				=> $remote_url
			);

		endwhile;
		wp_reset_query();
}