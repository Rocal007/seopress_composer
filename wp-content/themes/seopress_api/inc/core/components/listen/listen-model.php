<?php 
		$remote_url = get_field('remote_page', check_page_id());
		$leistungen_listen_acfs = false;
		if (isset($remote_url)){
				//var_dump($remote_url);
				$response = wp_remote_get($remote_url);
				$all = json_decode(wp_remote_retrieve_body($response));
				if (isset($all->acf->leistungen_listen)){
				$leistungen_listen_acfs = $all->acf->leistungen_listen;
				}
			
			//var_dump($leistungen_listen_acfs);
			if ($leistungen_listen_acfs){
				foreach ($leistungen_listen_acfs as $liste_acf) {
				$listen_heading = apply_filters( 'the_content', $liste_acf->uberschrift);	
				$listen[] = array(
					"listen_heading"			=> wp_strip_all_tags($listen_heading),
					"listen_block"		 		=> $liste_acf->leistungsblock
					); 
				}
			}
		}