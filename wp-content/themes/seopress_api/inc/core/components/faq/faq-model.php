<?php 
if ( have_rows('faq_eintrag', check_page_id())) {
			$i=0;
			while( have_rows('faq_eintrag', check_page_id())): the_row();
								
				$faq_heading_single = get_sub_field('titel');
				$faq_heading_single = apply_filters( 'the_content', $faq_heading_single);
				$faq_heading_single = strip_tags($faq_heading_single, '<strong>');

				$faq_heading[] = $faq_heading_single;
				
				$faq_content_single = get_sub_field('faq_inhalt');
                $faq_content_single = apply_filters( 'the_content', $faq_content_single);
				                
                if (amp_is_request()){
                $faq_content_single = wp_strip_all_tags($faq_content_single);
                }
			
                $faq_content[] = $faq_content_single;
				
			    $faq_extension_single = get_sub_field('faq_extension');
				$faq_extension_single = apply_filters( 'the_content', $faq_extension_single);


				$faq_extension[] = $faq_extension_single;

				$faqs[] = array(
					"faq_heading"		=> $faq_heading[$i],
					"faq_content" 		=> $faq_content[$i],
					"faq_extension"		=> $faq_extension[$i]
				);
                shuffle($faqs);
				$i++;
			endwhile;
		}
/*
$remote_url = get_field('remote_page', check_page_id());
if (isset($remote_url)){
	//var_dump($remote_url);
		$response = wp_remote_get($remote_url);
		$all = json_decode(wp_remote_retrieve_body($response));
		//var_dump($all);
		$faqs_acfs = $all->acf->faq_eintrag;
	}

	//var_dump($faqps_acfs);
	if (isset($faqs_acfs)){
		foreach ($faqs_acfs as $faq_acf) {
		$faqs[] = array(
			"faq_heading"		=> apply_filters( 'the_content', $faq_acf->title),
			"faq_content" 		=> apply_filters( 'the_content', $faq_acf->faq_inhalt),
			"faq_extension"		=> apply_filters( 'the_content', $faq_acf->faq_extension)
		); 
		}
		shuffle($faqs);
	}
else {

	}
?>*/