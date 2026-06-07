<?php 
$remote_url = get_field('remote_page', check_page_id());
		if (isset($remote_url)){			
				//var_dump($remote_url);
				$response = wp_remote_get($remote_url);
				$all = json_decode(wp_remote_retrieve_body($response));
				$punshline_acfs = $all->acf->punsh_lines;
				//var_dump($punshline_acfs);
			}

			if (isset($punshline_acfs)){
				foreach ($punshline_acfs as $punshline_acf) {
				$punshlines[] = array(
					"punshline_content" => apply_filters( 'the_content', $punshline_acf-> punsh_line),
					); 
				}
			}



/*$punshline =  get_field("punsh_lines", check_page_id());
	if ($punshline){
		$punshline = $punshline[$index]["punsh_line"];
		$punshline = apply_filters( 'the_content', $punshline );
		$punshline = wp_strip_all_tags($punshline);
	};*/?>