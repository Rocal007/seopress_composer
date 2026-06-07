<?php
$remote_url = get_field('remote_page', check_page_id());
	if (isset($remote_url)){
		$response = wp_remote_get($remote_url);
		$all = json_decode(wp_remote_retrieve_body($response));

		if (isset($all->acf->hauptseiten_icon)){
			$icon = $all->acf->hauptseiten_icon;
		}
	}
	else {
			$icon = get_field("hauptseiten_icon", check_page_id());
		};
	
	$top_picture_title = get_the_title();
	$top_picture_description = YoastSEO()->meta->for_current_page()->title;
	$categories = get_the_category();
	

	if ($categories) {
		$cat_id = $categories[0]->cat_ID;
		$category_title = get_cat_name($cat_id);
		$category_link 	= get_category_link($cat_id);
		
			if (has_category() || !is_category() ) {
				$category_name = '<a href="' . esc_url( $category_link ) . '">' . esc_html( $category_title ) . '</a>';
			}
			else {
				$category_name = get_location();
			}
	}

	$image = wp_get_attachment_url(get_post_thumbnail_id(check_page_id()));

	var_dump($categories);
	var_dump($cat_id);
	//var_dump(has_category());
	//var_dump(is_category());
	//var_dump(is_single());
	


//**********
//SUB PAGE DATA -- Page && Hauptseiten Template
//**********	
	if (has_category() && !is_category() && !is_single() ) {
		
		$top_picture = array(
			"top_picture_icon"			=> $icon[0],
			"top_picture_image"			=> $image,
			"top_picture_title" 		=> $top_picture_title,
			"top_picture_location"		=> $category_name,
			"top_picture_sub"			=> $top_picture_description 
		);	
	}


//******************
//Category Page Data - Category Template
//******************	
	elseif (is_category()) {
		
		$top_picture = array(
			"top_picture_icon"			=> 'Entrümpelungen',
			"top_picture_image"			=> $image,
			"top_picture_title" 		=> 'cat' . $top_picture_description,
			"top_picture_location"		=> 'mit Wertausgleich', 
			"top_picture_sub"			=> 'Entrümpelungsfirma für ' . $category_name,
		); 
		
	}


//***************
//Front page Data - Stratseite Template
//***************
	elseif (is_front_page()) {		
		
		$top_picture = array(
			"top_picture_icon"			=> $icon[0],
			"top_picture_image"			=> $image,
			"top_picture_title" 		=> $top_picture_title,
			"top_picture_location"		=> get_options()['bundesland'],
			"top_picture_sub"			=> 'Entrümpelungen, Räumungen, Antiquitäten'		
		);
		
	}


//*********************
//Blog Page Single Data - Single Template
//*********************
	elseif (is_single() && has_category()) {		
		
		$top_picture = array(
			"top_picture_icon"			=> 'Entrümpelungen',
			"top_picture_image"			=> $image,
			"top_picture_title" 		=> get_bloginfo(),
			"top_picture_location"		=> 'Blog',
			"top_picture_sub"			=> 'Entrümpelungen, Räumungen, Antiquitäten'		
		);
		
	}

//************
//Other Pages
//************	
	else {	
		
		$top_picture = array(
			"top_picture_icon"			=> $icon[0],
			"top_picture_image"			=> $image,
			"top_picture_title" 		=> $top_picture_title,
			"top_picture_location"		=> get_options()['bundesland'],
			"top_picture_sub"			=> $top_picture_description,
		);
		
    }