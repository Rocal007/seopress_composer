<?php 
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
				
				
				$tip_extension_single = get_sub_field('tip_extension');
				$tip_extension_single = apply_filters( 'the_content', $tip_extension_single);					
				$tip_extension[] = $tip_extension_single;
				
				$tips[] = array(
					"tip_heading"		=> $tip_heading[$i],
					"tip_content" 		=> $tip_content[$i],
					"tip_extension"		=> $tip_extension[$i]
				);
                shuffle($tips);
				
				$i++;
			endwhile;
		}?>