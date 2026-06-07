<?php 
		$remote_url = get_field('remote_page', check_page_id());
		if (isset($remote_url)){
			
			
				//var_dump($remote_url);
				$response = wp_remote_get($remote_url);
				$all = json_decode(wp_remote_retrieve_body($response));
				$images_acfs = $all->acf->inhaltsbilder;
			}

			var_dump($image_acfs);
			if (isset($images_acfs)){
				foreach ($images_acfs as $image_acf) {
				$images[] = array(
					"image_url"			=> apply_filters( 'the_content', $image_acf->bild),
					"image_caption" 	=> apply_filters( 'the_content', $image_acf->bild_beschreibung),
					); 
				}
			}
			//shuffle($tips);
		
		else {
			if ( have_rows('tipps', check_page_id()) ) {
				$i=0;
				while( have_rows('tipps', check_page_id()) ): the_row();
									
					$tip_heading_single = get_sub_field('tipp_headline');
					$tip_heading_single = apply_filters( 'the_content', $tip_heading_single);
					$tip_heading_single = strip_tags($tip_heading_single, '<strong>');
	
					$tip_heading[] = $tip_heading_single;
				
					$tip_content_single = get_sub_field('tip');
					$tip_content_single = apply_filters( 'the_content', $tip_content_single);
					
					
					if (amp_is_request()){
					$tip_content_single = wp_strip_all_tags($tip_content_single);
					}
				
					$tip_content[] = $tip_content_single;
					
				  
					$tip_extension[] = get_sub_field('tip-extension');
	
					$tips[] = array(
						"tip_heading"		=> $tip_heading[$i],
						"tip_content" 		=> $tip_content[$i],
						"tip_extension"		=> $tip_extension[$i]
					);
					shuffle($tips);
					$i++;
				endwhile;
			}
		}
		?>