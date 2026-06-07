<?php 
wp_reset_query();

if (have_rows("timeline", check_page_id())){
$i = 0; 
$timelines_title = get_the_title( get_the_ID() );
		while (have_rows("timeline", check_page_id())) : the_row();

					//get relevant fields, apply filters, push in array 
						$timeline_title_single = get_sub_field("timeline_titel");
						$timeline_title_single = apply_filters( 'the_content', $timeline_title_single );
						$timeline_title_single = wp_strip_all_tags($timeline_title_single);
						$timeline_title[] = $timeline_title_single;


						$timeline_text_single = get_sub_field("timeline_text");
						$timeline_text_single = apply_filters( 'the_content', $timeline_text_single );;
						$timeline_text[] = $timeline_text_single;

						$timeline_icon[] = get_sub_field("icon");

					 //set classes for left and right
						$timeline_index = get_row_index();
						if ( $timeline_index & 1 ) {
							$timeline_title_class = 'timeline-title-right';
							$timeline_text_class = 'timeline-text-right';
							$timeline_icon_class = 'timeline-icon-right';
							$timeline_offset_class = 'col-sm-offset-6';

						} else {
							$timeline_title_class = 'timeline-title';
							$timeline_text_class = 'timeline-text';
							$timeline_icon_class = 'timeline-icon';
							$timeline_offset_class ='';
							}     

						//push classes in array
						$timeline_title_classes[] = $timeline_title_class; 
						$timeline_text_classes[] = $timeline_text_class;
						$timeline_icon_classes[] = $timeline_icon_class;
						$timeline_offset_classes[] = $timeline_offset_class;


				//set timeline model array for view use
					$timelines[] = array(
						"timeline_title" 	    => $timeline_title[$i],
						"timeline_title_class"  => $timeline_title_classes[$i],
						"timeline_text"		    => $timeline_text[$i],
						"timeline_text_class"   => $timeline_text_classes[$i],
						"timeline_icon"         => $timeline_icon[$i],
						"timeline_icon_class"   => $timeline_icon_classes[$i], 
						"timeline_offset_class" => $timeline_offset_classes[$i]
					);
				$i++;

			endwhile;
    //var_dump($timelines);
}
else {
	$timelines_title = get_the_title( get_the_ID() );
    $remote_url = get_field('remote_page', check_page_id());
    if (isset($remote_url)){
        //var_dump($remote_url);
        $response = wp_remote_get($remote_url);
        $all = json_decode(wp_remote_retrieve_body($response));
        $timeline_acfs = $all->acf->timeline;
    }

    if (isset($timeline_acfs)){
		

        foreach ($timeline_acfs as $key=>$timeline_acf) {
            if ( $key & 1 ) {
                $timeline_title_class =     'timeline-title-right';
                $timeline_text_class =      'timeline-text-right';
                $timeline_icon_class =      'timeline-icon-right';
                $timeline_offset_class =    'col-sm-offset-6';
                                    
            } else {
                $timeline_title_class =     'timeline-title';
                $timeline_text_class =      'timeline-text';
                $timeline_icon_class =      'timeline-icon';
                $timeline_offset_class =    '';
                }     
			
			$timeline_title = apply_filters( 'the_content', $timeline_acf->timeline_titel); 
			
			
            $timelines[] = array(
                "timeline_title"		=> wp_strip_all_tags($timeline_title),
                "timeline_title_class"  => $timeline_title_class,
                "timeline_text" 		=> apply_filters( 'the_content', $timeline_acf->timeline_text),
                "timeline_text_class"   => $timeline_text_class,
                "timeline_icon" 		=> $timeline_acf->icon,
                "timeline_icon_class"   => $timeline_icon_class, 
                "timeline_offset_class" => $timeline_offset_class
            ); 
        }
    }
}
//var_dump($timelines);?>