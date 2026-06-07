<?php 
$remote_url = get_field('remote_page', check_page_id());
if (isset($remote_url)){
	
	$response = wp_remote_get($remote_url);
	$all = json_decode(wp_remote_retrieve_body($response));
    if (isset($all->acf->ablauf_blocke)){
        $ablaufe = $all->acf->ablauf_blocke;
    }
    $page_id = get_queried_object_id();
   
    if (is_category()){
		$ablauf_boxes_title = single_cat_title( '', false );
	}
	else {
		$ablauf_boxes_title = get_the_title($page_id);
	}


	//var_dump($ablaufe);
        if (isset($ablaufe)){
            $index = 1;
            foreach($ablaufe as $ablauf_single){

                $ablauf_heading = $ablauf_single->uberschrift;
                $ablauf_heading = apply_filters( 'the_content', $ablauf_heading);
                $ablauf_content = $ablauf_single->text;
                $ablauf_content = apply_filters( 'the_content', $ablauf_content);
                $ablauf_icon = $ablauf_single->icon;
                $ablauf_link = $ablauf_single->link;
                $ablauf_button_text = $ablauf_single->button_text;
                $ablauf_button_link_text = $ablauf_single->button_link_text;
                $ablauf_button_link_text = apply_filters( 'the_content', $ablauf_button_link_text);
                
               


                $ablauf_boxes[] = array(
                    "index"                     => $index,
                    "ablauf_heading"			=> wp_strip_all_tags($ablauf_heading),
                    "ablauf_content"            => $ablauf_content,
                    "ablauf_icon"               => $ablauf_icon,
                    "ablauf_link"               => $ablauf_link,
                    "ablauf_button_text"        => $ablauf_button_text,
                    "ablauf_button_link_text"   => wp_strip_all_tags($ablauf_button_link_text),
                ); 
                //var_dump($ablauf);
                $index++;
            }
            //var_dump($ablauf_boxes);
        }
}