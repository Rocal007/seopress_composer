<?php
//Main Pages Menue
foreach (get_main_page_ids(false) as $id) {
    $title 		= get_the_title($id);
    $link   	= get_the_permalink($id);
	$image  	= get_the_post_thumbnail_url($id, 'category-thumb');
    $icon   	= get_field("hauptseiten_icon", $id);
	$remote_url	= get_field('remote_page', $id);

    $main_menu[] = array(
        'menu_title'            => title_cleaner($title), 
        'menu_linked_title'     => $title, 
        'menu_link'             => $link,
        'menu_icon'             => $icon[0],
        'menu_location'         => get_location(),
		'menu_image_url'		=> $image,
		'remote_url'			=> $remote_url
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