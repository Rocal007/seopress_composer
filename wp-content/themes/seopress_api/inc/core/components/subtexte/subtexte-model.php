<?php 
	if ( have_rows('subtexte') ) {
        $i=0; 
            while( have_rows('subtexte') ): the_row();
                
                $subtext_index_single = get_sub_field('subtext_index');
                $subtext_index_hs[] = $subtext_index_single; 
                $subtext_heading_single_hs = get_sub_field('uberschrift');
                $subtext_heading_single_hs = apply_filters( 'the_content', $subtext_heading_single_hs);
                $subtext_heading_single_hs = strip_tags($subtext_heading_single_hs, '<strong>');

                $subtext_heading_hs[] =  $subtext_heading_single_hs;
                
                $subtext_content_hs_single = get_sub_field('subtext');
                $subtext_content_hs[] = $subtext_content_hs_single;

            $subtexte_hs[] = array(
            "subtext_heading"		=> $subtext_heading_hs[$i],
            "subtext_content" 		=> $subtext_content_hs[$i],
            "subtext_index"		    => $subtext_index_hs[$i]
        );
        $i++;  
        endwhile;
        //var_dump($subtexte_hs);
    }

    if ( have_rows('subtexte_radom', check_page_id()) ) {
            $i=0; 
				while( have_rows('subtexte_radom', check_page_id()) ): the_row();
                    $subtext_heading_single = get_sub_field('subtext_uberschrift_radom');
                    $subtext_heading_single = apply_filters( 'the_content', $subtext_heading_single);
				    $subtext_heading_single = strip_tags($subtext_heading_single, '<strong>');

                    $subtext_heading[] =  $subtext_heading_single;
                    
                    //var_dump($subtext_heading[$i]);
                    
                    if ( have_rows('subtext_radom') ) {    
                        while( have_rows('subtext_radom') ): the_row();
                             $subtext_content[$i][] = get_sub_field('subtext_radom_absatz');
                        endwhile;
                        
                        shuffle($subtext_content);
                        //var_dump($subtext_content[$i]);   
                    }        

            $subtexte[] = array(
                "subtext_heading"		=> $subtext_heading[$i],
                "subtext_content" 		=> $subtext_content[$i][0],
                "subtext_extension"		=> $subtext_content[$i][1]
            );
            $i++;  
            endwhile;
            shuffle($subtexte);
            //var_dump($subtexte);
        }?>