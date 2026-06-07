<?php 
$remote_url = get_field('remote_page', check_page_id());
if (isset($remote_url)){
	//var_dump($remote_url);
	$response = wp_remote_get($remote_url);
	$all = json_decode(wp_remote_retrieve_body($response));
	$all_array = json_decode(wp_remote_retrieve_body($response), true);
	
	$banner_all = $all_array["acf"];

    //var_dump($banner_all['banner_picture']['sizes']['medium_large']);

		$banner = array(
			"link" 		=> $banner_all['banner_link'],
            "img_url"	=> $banner_all['banner_picture']['sizes']['medium_large'],
			"img_title" => $banner_all['add_text'],
            "img_alt" 	=> $banner_all['add_text'],
		);
	}