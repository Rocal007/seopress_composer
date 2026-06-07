<?php 
$remote_url = get_field('remote_page', check_page_id());
		if (isset($remote_url)){			
				//var_dump($remote_url);
				$response = wp_remote_get($remote_url);
				$all = json_decode(wp_remote_retrieve_body($response));
				$claims_acfs = $all->acf->claim;
				//var_dump($claims_acfs);
				$claim = apply_filters( 'the_content', $claims_acfs);
				$claim = wp_strip_all_tags($claim);

			if (isset($claims_acfs)){
				//foreach ($claims_acfs as $claim_acf) {
				$claims[] = array(
					"claim_content" => $claim,
					); 
				//}
				//var_dump($claims);
			}
		}