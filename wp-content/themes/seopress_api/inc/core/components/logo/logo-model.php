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
	
	$logo_title = get_the_title(check_page_id());
	$sub_title 	= str_replace(get_options()['bundesland'],"", $logo_title);	
	$logo_title = title_cleaner($logo_title);
	
	$sub_description = YoastSEO()->meta->for_current_page()->title;
	$sub_description = title_cleaner($sub_description);
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

	//var_dump($categories);
	//var_dump($cat_id);
	//var_dump(has_category());
	//var_dump(is_category());
	//var_dump(is_single());
	
	if (has_category() && !is_category() && !is_single() ) {
		
		$logo = array(
			"logo_icon"			=> $icon[0],
			"logo_title" 		=> $logo_title,
			"logo_location"		=> $category_name,
			"logo_sub"			=> $sub_title 
		);	
	}
	
	elseif (is_category()) {
		
		$logo = array(
			"logo_icon"			=> 'Entrümpelungen',
			"logo_title" 		=> $sub_title,
			"logo_location"		=> 'mit Wertausgleich', 
			"logo_sub"			=> 'Entrümpelungsfirma für ' . $category_name,
		); 
		
	}
	
	elseif (is_front_page()) {		
		
		$logo = array(
			"logo_icon"			=> $icon[0],
			"logo_title" 		=> $logo_title,
			"logo_location"		=> get_options()['bundesland'],
			"logo_sub"			=> 'Entrümpelungen, Räumungen, Antiquitäten'		
		);
		
	}

	elseif (is_single() && has_category()) {		
		
		$logo = array(
			"logo_icon"			=> 'Entrümpelungen',
			"logo_title" 		=> get_bloginfo(),
			"logo_location"		=> 'Blog',
			"logo_sub"			=> 'Entrümpelungen, Räumungen, Antiquitäten'		
		);
		
	}
	
	else {	
		
		$logo = array(
			"logo_icon"			=> $icon[0],
			"logo_title" 		=> $logo_title,
			"logo_location"		=> get_options()['bundesland'],
			"logo_sub"			=> $sub_description,
		);
		
    }