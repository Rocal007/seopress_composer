<?php 
function get_overview_text($remote_url) {

    if (isset($remote_url)){
        $response = wp_remote_get($remote_url);
        $all = json_decode(wp_remote_retrieve_body($response));
		$all_array = json_decode(wp_remote_retrieve_body($response), true);
        
		$overview_texte_acfs = $all->acf->ubersichtstexte_radom;
    
        if (isset($overview_texte_acfs)){
            
            foreach ($overview_texte_acfs as $key=>$overview_text_acf) {
                    $overview_texte[] = array(
                        //"overview_text_content"		=> wp_strip_all_tags( $overview_text_acf -> ubersichtstext_radom),
                        "overview_text_content"		=> apply_filters( 'the_content', $overview_text_acf -> ubersichtstext_radom ),
                );
            }
        
            shuffle($overview_texte);
            
        }
    }
    //var_dump($overview_texte);
    return $overview_texte[0]["overview_text_content"];
}