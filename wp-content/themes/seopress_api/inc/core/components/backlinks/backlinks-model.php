<?php 
$remote_url = get_field('remote_page', check_page_id());
		if (isset($remote_url)){			
				//var_dump($remote_url);
				$response = wp_remote_get($remote_url);
				$all = json_decode(wp_remote_retrieve_body($response));
				$backlinks_acfs = $all->acf->backlink_seiten;
				//var_dump($backlinks_acfs);
				
			if (isset($backlinks_acfs)){
				foreach ($backlinks_acfs as $backlink_acf) {
				$backlinks[] = array(
					"link_text" => $backlink_acf->link_name,
					"link_url" => $backlink_acf->webadresse_url
					); 
				}
				//var_dump($backlinks);
			}
		}