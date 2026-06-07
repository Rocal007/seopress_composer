<?php 
$remote_url = get_field('remote_page', check_page_id());

if (isset($remote_url) && empty(get_field("video",check_page_id()))){
		$response = wp_remote_get($remote_url);
		$all = json_decode(wp_remote_retrieve_body($response));
	if (isset($video_acfs)){
	
		$video_acfs = $all->acf;
		$video_heading = apply_filters( 'the_content', $video_acfs->video_uberschrift);
		$video_text = apply_filters( 'the_content', $video_acfs->video_text);
		
		$video = array(
			"video_cat"			=> get_the_title(check_page_id()),
			"video_heading"		=> wp_strip_all_tags($video_heading),
			"video_text" 		=> wp_strip_all_tags($video_text),
			"video"				=> $video_acfs->video
			); 
		}
		var_dump($video_acfs);
}
	
else {
	$video = get_field("video",check_page_id());
				
	$video_heading = get_field("video_uberschrift", check_page_id());
	$video_heading = apply_filters( 'the_content', $video_heading);

	$video_text = get_field("video_text", check_page_id());
	$video_text = apply_filters( 'the_content', $video_text);
	
		$video = array(
			"video_cat"				=> get_the_title(check_page_id()),
			"video"					=> $video,
			"video_heading"	 		=> wp_strip_all_tags($video_heading),
			"video_text" 			=> $video_text,
		);					
}

function videos_all() {
	foreach(get_main_page_ids() as $id){
		$remote_url = get_field('remote_page', $id);
		$response = wp_remote_get($remote_url);
		$all = json_decode(wp_remote_retrieve_body($response));
		$video_acfs = $all->acf;

		$videos[] = array(
			"video_cat"			=> get_the_title($id),
			"video_heading"		=> apply_filters( 'the_content', $video_acfs->video_uberschrift),
			"video_text" 		=> apply_filters( 'the_content', $video_acfs->video_text),
			"video"				=> $video_acfs->video
			); 
		}

	return $videos;
}