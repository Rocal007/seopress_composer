<?php 
$remote_url = get_field('remote_page', check_page_id());
if (isset($remote_url)){
	
		$response = wp_remote_get($remote_url);
		$all = json_decode(wp_remote_retrieve_body($response));
		//var_dump($all);
		$vorteile_acfs = $all->acf->schlagworte;
	}

	if (isset($vorteile_acfs)){
		foreach ($vorteile_acfs as $vorteil_acf) {
		$vorteile[] = array(
			"vorteil_heading"		=> apply_filters( 'the_content', $vorteil_acf->schlagwort),
			"vorteil_content" 		=> apply_filters( 'the_content', $vorteil_acf->beschreibung),
			"vorteil_icon"			=> $vorteil_acf->icon,
		); 
		}
		//shuffle($vorteile);
		//var_dump($vorteile);
	}
	
else {
if ( have_rows('schlagworte', check_page_id())) {
			$i=0;
			while( have_rows('schlagworte', check_page_id())): the_row();
				//heading get and push				
				$vorteil_heading_single = get_sub_field('schlagwort');
				$vorteil_heading_single = apply_filters( 'the_content', $vorteil_heading_single);
				$vorteil_heading_single = strip_tags($vorteil_heading_single, '<strong>');

				$vorteil_heading[] = $vorteil_heading_single;
				
				
				//content get and push
				$vorteil_content_single = get_sub_field('beschreibung');
                $vorteil_content_single = apply_filters( 'the_content', $vorteil_content_single);
				          
					if (amp_is_request()){
					$vorteil_content_single = wp_strip_all_tags($vorteil_content_single);
					}
			
                $vorteil_content[] = $vorteil_content_single;
				
				//icon get and push
			    $vorteil_icon_single = get_sub_field('icon');

				$vorteil_icon[] = $vorteil_icon_single;
				
				//set view array
				$vorteile[] = array(
					"vorteil_heading"		=> $vorteil_heading[$i],
					"vorteil_content" 		=> $vorteil_content[$i],
					"vorteil_icon"			=> $vorteil_icon[$i]
				);
                //shuffle($vorteile);
				$i++;
			endwhile;
		}
	}
?>