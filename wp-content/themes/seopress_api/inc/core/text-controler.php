<?php

/**
 * Assign automatic Text Changes
 */


/*$shortcodes = ['DL','UNDWORTE', 'SOWIEWORTE', 'ISTWORTE', 'AUFLOESUNGWORTE', 'RAEUMUNGWORTE', 'ENTRUEMPELUNGWORTE', 'DL-ENTRUMPELUNG' , 'DASDERUNTERNEHMENWORTE', 'DIEFIRMAWORTE', 'SCHNELLWORTE', 'PROFIWORTE', 'ORDENTLICHWORTE', 'PARTNERWORTE', 'HELFENWORTE', 'MACHTWORTE', 'BRAUCHWORTE', 'ALLEINWORTE', 'RAUMWORTE', 'SCHAFFENWORTE', 'HANDWERKSLEISTUNGENLISTE', 'KUNDENLISTE', 'ANTIKLISTE']; 
*/

function zeig_textelement($textelemente) {
   //var_dump($textelemente);
    $textelemente[] = $textelemente;
    shuffle($textelemente);
    if (is_string($textelemente[0])) {
        $t = $textelemente[0];
    } else {
        $t = $textelemente[1];
    }

    return $t;
}

function zeig_liste(array $listenpunkte) {

    shuffle($listenpunkte);
    $liste_txt  = implode(", ", $listenpunkte);
   
	return $liste_txt;
}

function get_dl_felder($page_id) {
    if (have_rows('dienstleistungen', $page_id) ) {
        while ( have_rows('dienstleistungen', $page_id) ) : the_row();
        	$sub_value[] = get_sub_field('dienstleistung');
        endwhile;        
		
	return $sub_value;
    }
}

/*
 * SHORT-CODES
 */

 function checkdienstleistung () {
	foreach(get_main_page_ids() as $id){
		if (check_page_id() == $id) {
				$dl = get_dl_felder($id);
				shuffle($dl);
			}	
	}
	return $dl[0];
}
add_shortcode('DL', 'checkdienstleistung');

function ort() {
	return get_location();
}
add_shortcode('ORT', 'ort');

function einsatzgebiet() {
	$bl = get_field('einsatzgebiet', get_option('page_on_front'));
	return $bl;
}
add_shortcode('BL', 'einsatzgebiet');

function gratisworte() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_textelement($gratiswo);
}
add_shortcode('GRATISWORTE', 'gratisworte');

function undworte() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_textelement($undwo);
}
add_shortcode('UNDWORTE', 'undworte');

function sowieworte() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_textelement($sowiewo);
}
add_shortcode('SOWIEWORTE', 'sowieworte');

function istworte() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_textelement($istworte);
}
add_shortcode('ISTWORTE', 'istworte');

function aufloesungworte() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_textelement($aufloesungwo);
}
add_shortcode('AUFLOESUNGWORTE', 'aufloesungworte');

function raeumungsworte(){
	require(get_template_directory() .'/models/text_model.php');
	return zeig_textelement($raeumungswo);
}
add_shortcode('RAEUMUNGWORTE', 'raeumungsworte');

function entruempelungworte() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_textelement($entruempelungwo);
}
add_shortcode('ENTRUEMPELUNGWORTE', 'entruempelungworte');

function dasderunternehmenworte() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_textelement($unternehmenwo);
}
add_shortcode('DASDERUNTERNEHMENWORTE', 'dasderunternehmenworte');

function derdasunternehmenworte() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_textelement($unternehmenwo);
}
add_shortcode('DERDASUNTERNEHMENWORTE', 'derdasunternehmenworte');

function diefirmaworte() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_textelement($firmawo);
}
add_shortcode('DIEFIRMAWORTE', 'diefirmaworte');

function schnellworte() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_textelement($schnellwo);
}
add_shortcode('SCHNELLWORTE', 'schnellworte');

function profiworte() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_textelement($profiwo);
}
add_shortcode('PROFIWORTE', 'profiworte');

function ordentlichworte() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_textelement($ordentlichwo);
}
add_shortcode('ORDENTLICHWORTE', 'ordentlichworte');

function partnerworte() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_textelement($partnerwo);
}
add_shortcode('PARTNERWORTE', 'partnerworte');

function raumworte() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_textelement($raumwo);
}
add_shortcode('RAUMWORTE', 'raumworte');

function expertenworte() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_textelement($expertenwo);
}
add_shortcode('EXPERTENWORTE', 'expertenworte');

function wertausgleichworte() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_textelement($wertausgleichwo);
}
add_shortcode('WERTAUSGLEICHWORTE', 'wertausgleichworte');

function kompetentzworte() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_textelement($kompetentwo);
}
add_shortcode('KOMPETENZWORTE', 'kompetentzworte');


function kompetentworte() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_textelement($kompetentwo);
}
add_shortcode('KOMPETENTWORTE', 'kompetentworte');

function site_title() {
	$page_id = get_queried_object_id();
	if (is_category()){
		$title = single_cat_title( '', false );
	}
	else {
		$title = get_the_title($page_id);
	}
	return $title;
}
add_shortcode('SITE_TITLE', 'site_title');

function handwerksleistungenliste() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_liste($handwleistungenli);
}
add_shortcode('HANDWERKLEISTUNGENLISTE', 'handwerksleistungenliste');

function kundenliste() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_liste($kundenli);
}
add_shortcode('KUNDENLISTE', 'kundenliste');

function antikliste() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_liste($antikli);
}
add_shortcode('ANTIKLISTE', 'antikliste');

function antikliste_2() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_liste($antikli_2);
}
add_shortcode('ANTIKLISTE2', 'antikliste_2');

function immoliste() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_liste($immobilienli);
}
add_shortcode('IMMOLISTE', 'immoliste');

function immoliste_2() {
	require(get_template_directory() .'/models/text_model.php');
	return zeig_liste($immobilienli_2);
}
add_shortcode('IMMOLISTE2', 'immoliste_2');

//(add Yoast Vars)
function retrieve_ort_replacement( $var1 ) {
         return get_location();
  }
function retrieve_experten_replacement( $var2 ) {
         return expertenworte();
  }
function retrieve_profi_replacement( $var3 ) {
         return profiworte();
  }

function retrieve_gratis_replacement( $var4 ) {
         return gratisworte();
  }
 


//Title Cleaner

function title_cleaner($title){
	$location = get_location();
	$title = str_replace($location, '', $title);
	$title = str_replace(get_options()['bundesland'], '', $title);
	$replacements = [
		'in' => '',
		'Haushaltsauflösungen und Wohnungsauflösungen' => 'Wohnungsauflösungen',
		'& Geschäftsauflösungen' => '',
		'Hotelräumungen' => 'Hotelauflösungen',
		'Büroräumungen' => 'Büroauflösungen',
		'Umzüge' => 'Umzüge & Übersiedlungen',
		'#038;' => '',
		'Betriebs- &' => '',
		'Geschäfts- &' => '',
		'Altwaren und ' => '',
		'& Altwaren' => '',
		'-' => '',
		'Dachbodenentrümpelungen' => 'Dachböden',
		'Dachbodenentrümpelung' => 'Dachböden'
	];
	
	foreach($replacements as $key => $value){
		$title = str_replace($key, $value, $title);
	}
	
    return $title;
}

function show_bl() {
	$page_id = get_queried_object_id();
	$bl = get_field('einsatzgebiet', get_option('page_on_front'));
	if (isset($ancestors)) {
		$bl = $categories[0]->name;
	}
	if (is_category()) {
		$bl = $category;
	}
	if ( is_page_template( 'faq.php' ) ) {
    	$bl = get_field('einsatzgebiet', get_option('page_on_front'));
	}
	return $bl;
}

function show_bez() {
     $cats = get_the_category();
     $bez = $cats[0]->name;
     $bez = substr($bez, 5);
	//echo('bez: '.$bez.'<br><br>');
	return $bez;
}
function get_dl_fields($page_id) {
    if (have_rows('dienstleistungen', $page_id) ) {
        while ( have_rows('dienstleistungen', $page_id) ) : the_row();
        	$sub_value[] = get_sub_field('dienstleistung');
        endwhile;
    }
	return $sub_value;
}

function init_faqs($faq_cat='all', $mixed=True, $how_many_faq_cats=10, $how_many_faqs=1, $extra=False) {
    $faq_cats = ['ableben' ,'anwesenheit' ,'dauer' ,'haftung' ,'kosten' ,'uebergabe' ,'verwertung' ,'wertausgleich' ,'wohlgefuehl' ,'zusatzleistungen'];
    $i = 0;
    if ($mixed) {
        $faq_cats[] = shuffle($faq_cats);
    }

    if ($faq_cat == 'all') {
        foreach ($faq_cats as $faq_cats_element) {
            if ($i < $how_many_faq_cats) {
                get_questions_build_faq($faq_cats_element, $how_many_faqs, $extra);
                $i = $i+1;
            }
        }
    } else {
        if ($i < $how_many_faq_cats) {
            get_questions_build_faq($faq_cat, $how_many_faqs);
            $i = $i+1;
        }
    }
}

$anchor_i = 0;
function get_questions_build_faq($faq_cat, $how_many_faqs, $extra=False) {
    global $anchor_i;
    $i = 0;
    $page_id = 10947; //page-id der faq-seite
    // $page_id = 11145; //page-id der entruempelung-faq-seite
    if (have_rows('fragen-'.$faq_cat, $page_id)) {
        while (have_rows('fragen-'.$faq_cat, $page_id)) : the_row();
        	$question = get_sub_field('frage-'.$faq_cat);
        	$page_id_dl = check_page_id();
        	$dl = get_dl_fields($page_id_dl);
        	$bl = get_location();
        	$bez = get_location(); //funkt mit dem echten bezirk erst, wenn es in einer kategorie ist
        	$question = str_replace('_DL_', show_txt_element($dl), $question);
        	$question = str_replace('_BL_', $bl, $question);
        	$question = str_replace('_BEZ_', $bez, $question);
        	$question = replace_sc ($question);

        	$answer = get_answer($faq_cat, $page_id);

            if ($i < $how_many_faqs && $extra == False) {
        	    $faq = '<strong>'.$question.'</strong>'.'<br>'.$answer;
        	    echo($faq.'<br><br>');
        	    $i = $i+1;
        	}


            if ($i < $how_many_faqs && $extra) {
				include(get_template_directory() .'/assets/icons.php');
                $anchor_i = $anchor_i+1;
				echo '<div class="row"><div class="col-md-1 icon hidden-xs">' . $faq_icon . '</div>';
				echo '<div class="col-md-11">';
                echo '<div class="faq-heading" data-toggle="collapse" data-target="#collapseFaq-'. $anchor_i . '" aria-expanded="false" aria-controls="collapseFaq">' . $question . '</div>';
				echo '<div id="collapseFaq-'. $anchor_i . '" class="faq-content collapse">' . $answer . '</div>';
				echo '</div></div>';
        	    $i = $i+1;
        	}
        endwhile;
    }
	//return $faq;
}

function get_answer($faq_cat, $page_id){
    require(get_template_directory() .'/models/text_model.php');
    $answers = array();
    if (have_rows('antworten_'.$faq_cat, $page_id) ) {
        while ( have_rows('antworten_'.$faq_cat, $page_id) ) : the_row();
        	$answer = get_sub_field('antwort_'.$faq_cat);
        	$page_id_dl = check_page_id();
        	$dl = get_dl_fields($page_id_dl);
        	$bl = get_location();
        	$bez = get_location();
        	$answer = str_replace('_DL_', show_txt_element($dl), $answer);
        	$answer = str_replace('_BL_', $bl, $answer);
        	$answer = str_replace('_BEZ_', $bez, $answer);
        	$answer = replace_sc ($answer);
        	array_push($answers, $answer);
        endwhile;
    }
    shuffle($answers);
    return $answers[0];
}

function replace_sc($txt){
    require(get_template_directory() .'/models/text_model.php');
	$sc = array();
    $sc = ['_SOWIE-WO_','_UND-WO_','_PROFIS-WO_','_SCHNELL-WO_','_KOMPETENT-WO_','_RAUM-WO_','_PARTNER-WO_','_AUFLOESUNG-WO_','_ENTRUEMPELUNG-WO_','_RAEUMUNG-WO_', '_FIRMA-WO_', '_UNTERNEHMEN-WO_', '_KUNDEN-LI_', '_ANKAUF-LI_', '_HANDWLEISTUNGEN-LI_'];
    foreach ($sc as $sc_element) {
        $arr_name = strtolower($sc_element);
		$arr_name = substr($arr_name, 1, -1);
        $arr_name = str_replace('-', '', $arr_name);
		
        if (substr($arr_name, -2) == 'li') {
            $txt = str_replace($sc_element, show_list($$arr_name), $txt);
        } else {
            $txt = str_replace($sc_element, show_txt_element($$arr_name), $txt);
		}
    }
    return $txt;
}

function show_txt_element($txt_elements) {
    $txt_elements[] = $txt_elements;
    shuffle($txt_elements);
    if (is_string($txt_elements[0])) {
        $t = $txt_elements[0];
    } else {
        $t = $txt_elements[1];
    }
    return $t;
}

function show_list(array $li) {
    shuffle($li);
    $li_txt  = implode(", ", $li);
	return $li_txt;}