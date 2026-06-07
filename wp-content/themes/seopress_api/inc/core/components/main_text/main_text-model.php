<?php 
$remote_url = get_field('remote_page', check_page_id());
if (isset($remote_url)){
	//var_dump($remote_url);
	$response = wp_remote_get($remote_url);
	$all = json_decode(wp_remote_retrieve_body($response));
	$all_array = json_decode(wp_remote_retrieve_body($response), true);
	

//Categories and Start Page
	if (is_category() || is_front_page()){
		$overview_text_blocks = $all_array["acf"]["ubersichtstexte_bezirke"];
		$overview_headlines = $all_array["acf"]["uberschriften"];
		foreach ($overview_headlines as $overview_headline) { 
			$overview_headlines_clean[] = apply_filters( 'the_content', $overview_headline["uberschrift"]);
		}
		shuffle($overview_headlines_clean);
		
		foreach ($overview_text_blocks as $overview_text_block) { 	
			if ($overview_text_block['keywords_1']) {
					$keywords_1[]= apply_filters( 'the_content', $overview_text_block['ubersichtstext_bezirke']);
				}

			if ($overview_text_block['keywords_2']) {
					$keywords_2[]= apply_filters( 'the_content', $overview_text_block['ubersichtstext_bezirke']);
				}

			if ($overview_text_block['keywords_3']) {
					$keywords_3[]= apply_filters( 'the_content', $overview_text_block['ubersichtstext_bezirke']);
				}

			if ($overview_text_block['keywords_4']) {
					$keywords_4[]= apply_filters( 'the_content', $overview_text_block['ubersichtstext_bezirke']);
				}
		}
		shuffle($keywords_1);
		shuffle($keywords_2);
		shuffle($keywords_3);
		shuffle($keywords_4);
		
		
		
		$haupttext_heading = $overview_headlines_clean[0];
		$haupttext_heading = apply_filters( 'the_content', $haupttext_heading);	
		
		$haupttext_category = array(
			"haupttext-heading" => wp_strip_all_tags($haupttext_heading),
			"p_1" 				=> $keywords_1[0],
			"p_2" 				=> $keywords_2[0],
			"p_3" 				=> $keywords_3[0],
			"p_4" 				=> $keywords_4[0]
		);
		//var_dump($haupttext_category);
	}

//Hauptseiten	
	$haupttext_heading = $all->acf->hauptinhalt_uberschrift;
	$haupttext_content = $all->acf->hauptinhalt_text;
	
	if (isset($haupttext_heading)){
		$haupttext_heading = $all->acf->hauptinhalt_uberschrift;
		$haupttext_heading = apply_filters( 'the_content', $haupttext_heading);
		$haupttext_content = $all->acf->hauptinhalt_text;

		$haupttext = array(
			"haupttext_heading"			=> wp_strip_all_tags($haupttext_heading),
			"haupttext_content" 		=> apply_filters( 'the_content', $haupttext_content),
		); 
	}

//Subseiten
if (!is_category() && !is_front_page()){
		$haupttexte_sub_all = $all_array["acf"]["haupttexte_random"];
		
		shuffle($haupttexte_sub_all);
		$haupttext_random_heading = $haupttexte_sub_all[0]["haupttext_uberschrift_radom"];
		$haupttext_random_heading = apply_filters( 'the_content', $haupttext_random_heading);
		
		$haupttext_random_paragraphs = $haupttexte_sub_all[0]['haupttext_radom'];
		shuffle($haupttext_random_paragraphs);
	
	
		foreach($haupttext_random_paragraphs as $haupttext_random_paragraph){
			$haupttext_random_paragraph_single = apply_filters( 'the_content', $haupttext_random_paragraph["haupttext_absatz_radom"]);	
			$haupttext_random_content[] = $haupttext_random_paragraph_single;
		}
	
		
		$hauptext_subpage = array(
			"haupttext_heading"			=> wp_strip_all_tags($haupttext_random_heading),
			"haupttext_content" 		=> $haupttext_random_content,
		);
	}
}