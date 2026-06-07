<?php
/*table of Contents
 
 *1. Ablauf Component
 *2. Bewertungen Component
 *3. top Picture Component
 *4. Contact Component
 *5. Vorteile Component
 *6. Vorteile Contact Component
 *7. Haupttext Leistungen Component
 *8. FAQ Component
 *9. Tipp Component
 *10 Bewertungen Component
 *11 Video Component
 *12 Proven Expert
 *13 Pricing Table
 *14 Bilder Hauptseite
 *15 Leistungen (Schlagworte)
 *16 Timeline
*/







function seopress_shortcodes_init()
{
    //Ablauf Component
    function ablauf_view($bg = "#CCC"){
		include(get_template_directory() .'/models/main_model.php');
		include(get_template_directory() .'/assets/icons.php');?>

        
	<div class="container-fluid ablauf-container" id="ablauf" style="background-color:<?php echo $bg;?>">
		<div class="container">
		<div class="row ablauf">
			<div class="col-md-12">
				<h3>Nutzen Sie jetzt unseren <?php echo do_shortcode('[GRATISWORTE]');?>en Besichtigungsservice und erhalten Sie Ihr Festpreis-Angebot von <?php echo $title;?></h3>

			</div>
		  <div class="col-sm-6 col-md-4">
			<div class="thumbnail ablauf" style="text-align: center">
			 <div class="icon-ablauf">

			<?php echo $besichtigung_icon;?>	
			 </div>
			  <div class="ablauf-caption">
				<div class="ablauf-heading">
					1. Besichtigung
				  </div>
				<p>Vor Ort in <?php echo get_location();?> beraten wir Sie persönlich und verschaffen uns einen Überblick über Räumlichkeiten. Die Besichtigung ist für Sie vollkommen kostenlos und unverbindlich.</p>

			  </div>
			</div>
		  </div>
		  <div class="col-sm-6 col-md-4">
			<div class="thumbnail ablauf" style="text-align: center">
			 <div class="icon-ablauf">
			<?php echo $festpreisangebot_1_icon;?>

			 </div>
			  <div class="ablauf-caption">
				<div class="ablauf-heading">2. Angebot</div>
				<p>Sie erhalten von uns ein Festpreis-Angebot mit Wertausgleich für alle vereinbarten Kosten ihrer Entrümpelung, Abbau & Transport. Es entstehen für Sie keine versteckten Kosten</p>

			  </div>
			</div>
		  </div>
		  <div class="col-sm-6 col-md-4">

			<div class="thumbnail ablauf text-center">
			 <div class="icon-ablauf">
				<?php echo $transport_icon;?>
			 </div>
			  <div class="ablauf-caption">
				<div class="ablauf-heading">3. Durchführung</div>
				<p>pünktlich und mit allen vereinbarten Leistungen aus unserem Angebot. Nach Abschluss unserer Arbeit erhalten Sie ein besenreines Objekt.</p>

			  </div>
			</div>
		  </div>
		</div>
	</div>
	</div><?php
    }
    add_shortcode("ablauf", "ablauf_view");

    //Bewertungen Component
    //
    //
    function bewertungen_view()
    {
        return '
	<div id="custom_html-3" class="widget_text widget_sidebar_main clearfix widget_custom_html"><div class="textwidget custom-html-widget">
			<div class="proven-sidebar">
                <a href="https://www.provenexpert.com/entruempelungen/?utm_source=Widget&amp;utm_medium=Widget&amp;utm_campaign=Widget" title="Kundenbewertungen &amp; Erfahrungen zu Entrümpelungen. Mehr Infos anzeigen." target="_blank" style="text-decoration:none;" rel="noopener">
				<img loading="lazy" alt="Erfahrungen &amp; Bewertungen zu Entrümpelungen" width=180 height=216 style="border:0; margin-bottom: 30px;" data-src="https://images.provenexpert.com/97/bd/57ec96f7d66123a6efeed4cfe325/widget_portrait_180_de_0.png" class=" lazyloaded" src="https://images.provenexpert.com/97/bd/57ec96f7d66123a6efeed4cfe325/widget_portrait_180_de_0.png" loading="lazy">
</a>
				<br>';
    }
    add_shortcode("bewertungen", "bewertungen_view");



   function vorteile_view(){
	    $no_border = TRUE; 
        include(get_template_directory() .'/models/main_model.php');
		include(get_template_directory() .'/assets/icons.php');

        if (!have_rows("schlagworte", $page_id)) {
            $page_id = $home_id;
        }

        if (have_rows("schlagworte", $page_id)) {
            echo '<div class="vorteile hidden-xs">';
            while (have_rows("schlagworte", $page_id)) {
                the_row();
                $schlagwort = get_sub_field("schlagwort");
				$schlagwort = apply_filters( 'the_content', $schlagwort);
				$beschreibung = get_sub_field("beschreibung");
				$beschreibung = apply_filters( 'the_content', $beschreibung);
				$icon = get_sub_field("icon");
				include(get_template_directory() .'/assets/icons_controller.php');
				
				
                echo '<div class="vorteile-icons vorteile-tooltip">
						<span class="icon vorteile-icon">' . $show_icon . '</span>
						<span class="vorteile-schlagwort"> ' . $schlagwort .'</span>
						
						<span class="vorteile-tooltiptext" style="background-color: ' . $akzent_color .';">' . $beschreibung .'</span>
					</div><br>';
            }
            echo "</div>";
        }
    }

    add_shortcode("vorteile", "vorteile_view");
	
	function vorteile_mobile()
    {
        include(get_template_directory() .'/models/main_model.php');

        if (!have_rows("schlagworte", $page_id)) {
            $page_id = $home_id;
        }

        if (have_rows("schlagworte", $page_id)) {
            echo '<div class="vorteile-mobile">';
            while (have_rows("schlagworte", $page_id)) {
                the_row();
                $schlagwort = get_sub_field("schlagwort");
				$schlagwort = apply_filters( 'the_content', $schlagwort);
                $beschreibung = get_sub_field("beschreibung");
				$beschreibung = apply_filters( 'the_content', $beschreibung);
                echo '<li class="vorteile-icons vorteile-tooltip" style="background-color: ' . $akzent_color .';"> ' . $schlagwort .'</li>';
            }
            echo "</div>";
        }
    }
	
	
//top Picture Component
    //
    //
    //
    //
    function top_picture_view(){
       include(get_template_directory() .'/models/main_model.php');
		$claim_text = get_field("claim", false, false); 
		$claim_text = apply_filters( 'the_content', $claim_text);
		$claim_text = wp_strip_all_tags($claim_text);
        $claim = '<h3 class="claim-top">' . $claim_text .'</h3>
			  	  <span class="claim-sub hidden-xs"><a href="#ablauf">Ablauf</a> | <a href="#pricing-table">Kosten</a> |  <a href="#kontakt">Kontakt</a> |  <a href="#faq">Faq</a> | <a href="#tipps">Tipps</a> | <a href="#leistungen">Leistungen</a></span>';

        $url = wp_get_attachment_url(get_post_thumbnail_id($page_id));
		
		if (!empty($page_id)) {
            $url = wp_get_attachment_url(get_post_thumbnail_id($page_id));
			$claim_text = get_field("claim", $page_id, false, false);
			$claim_text = apply_filters( 'the_content', $claim_text);
			$claim_text = wp_strip_all_tags($claim_text);
            $claim = '<h3 class="claim-top">' . $claim_text . '</h3>
					  <span class="claim-sub hidden-xs"><a href="#ablauf">Ablauf</a> | <a href="#pricing-table">Kosten</a> |  <a href="#kontakt">Kontakt</a> |  <a href="#faq">FAQ</a> | <a href="#tipps">Tipps</a> | <a href="#leistungen">Leistungen</a></span>';
			$title = YoastSEO()->meta->for_current_page()->title;
        }

        if (is_category()) {
            $url = wp_get_attachment_url(get_post_thumbnail_id($home_id));
            $claim = '<h3 class="claim-top">Entrümpelungen, Räumungen, Verlassenschaften, Antiquitäten, Wohnungsauflösungen, Betriebsauflösungen <br>kostenlose Besichtigung - professionelle Durchführung</h3>
			 <span class="claim-sub hidden-xs"><a href="#ablauf">Ablauf</a> | <a href="#pricing-table" title>Kosten</a> |  <a href="#kontakt">Kontakt</a> |  <a href="#faq">FAQ</a> | <a href="#tipps">Tipps</a> | <a href="#leistungen">Leistungen</a></span>';
            $title = "Entrümpelungsfirma " . $bezirk;
        }
		if (have_rows("schlagworte", check_page_id())) {
            while (have_rows("schlagworte", check_page_id())) {
                the_row();
				$schlagwort_simgle = get_sub_field("schlagwort");
				$schlagwort_simgle = apply_filters( 'the_content', $schlagwort_simgle);
				$schlagwort_simgle = wp_strip_all_tags($schlagwort_simgle);
                $schlagwort[] = $schlagwort_simgle;
				
				$beschreibung_single = get_sub_field("beschreibung");
				$beschreibung_single = apply_filters( 'the_content', $beschreibung_simgle);
                $beschreibung[] = $beschreibung_simgle;
				 
               }
			//var_dump($beschreibung);
        }
		
		
		
		for($i = 0; $i < 4; ++$i) {
 			$vorteile_list .= '<div class="vorteile-tooltip">
								<div class="vorteil-desktop" style="background-color: ' . $primary_color . '"><i class="fa fa-check-square-o" aria-hidden="true"></i>' . trim($schlagwort[$i]) . '</div>
			<!--<span class="vorteile-tooltiptext" style="background-color: ' . $akzent_color .';">' . apply_filters( 'the_content', $beschreibung[$i] ) .'</span>-->
								</div>'; 
							
		}

        return '<div class="container-fluid top-picture hidden-xs" title="'. $title . '" style="background: url(' . $url . ');">
				<div class="page-image-title">
					<div class="page-image-title-overlay container">
						<a href="/kontakt" title="jetzt Kontakt aufnehmen und gratis Angebot oder Besichtigungstermin einholen">
							<div class="cta-round hidden-xs" style="background-color: ' . $primary_color . '"><span class="gratis">Jetzt</span> Angebot einholen</div>
						</a>
						
						<h1>' . $title . '</h1>
						
						<div class="claim hidden-xs">' . $claim . '</div>
						<div class="vorteile-top">' . $vorteile_list . '</div>
						
					</div>
				</div>
			  </div>';
    }
	add_shortcode("top_picture", "top_picture_view");
 
	
	
	function top_picture_blog(){
       include(get_template_directory() .'/models/main_model.php');

       $url = wp_get_attachment_url(get_post_thumbnail_id());
	   $title = get_the_title();
       $title = apply_filters( 'the_content',$title);
	   $title = wp_strip_all_tags($title);
	   $subtexte = get_field("subtexte");
	   	
	  foreach ($subtexte as $subtext_index) {
				$subtext_link =  preg_replace("/\s+/", "", $subtext_index["index"]);
				$subtext_index_list .= '<li><i class="fa fa-check" aria-hidden="true"></i><a href="#'.$subtext_link.'" >'. $subtext_index["index"] . '</a></li>';
			}
	 
        return '<div class="container-fluid top-picture hidden-xs" title="'. $title . '" style="background: url(' . $url . ');">
				<div class="page-image-title-blog">
					<div class="page-image-title-overlay-blog container">
						<div class="col-md-4">	
						<h1>' . $title . '</h1>
						</div>
						<div class="col-md-8 claim hidden-xs text-left">
							<ul class="content-list-blog">
								' . $subtext_index_list . '

							</ul>
						</div>
						
						
					</div>
				</div>
			  </div>';
    }	

	
	
	
	function top_picture_mobile(){
       include(get_template_directory() .'/models/main_model.php');
		$claim = '<h3 class="claim-top">' . get_field("claim", false, false) .'</h3>
			  	  <p class="claim-sub">für alle Bezirke in ' . $einsatzgebiet . '</p>';

        $url = wp_get_attachment_url(get_post_thumbnail_id($page_id));
		
		if (!empty($page_id)) {
            $url = wp_get_attachment_url(get_post_thumbnail_id($page_id));
            $claim = '<h3 class="claim-top">' . get_field("claim", $page_id, false, false) . '</h3>
					  <p class="claim-sub">für alle Bezirke in ' . $bezirk . '</p>';
			$title = YoastSEO()->meta->for_current_page()->title;
        }

        if (is_category()) {
            $url = wp_get_attachment_url(get_post_thumbnail_id($home_id));
            $claim = '<h3 class="claim-top">kostenlose Besichtigung - professionelle Durchführung</h3>
					  <h5 class="claim-sub hidden-xs">in ganz ' . $bezirk . '</h5>';
            $title = "Entrümpelungsfirma " . $bezirk;
        }
		

        if (have_rows("schlagworte", check_page_id())) {
            while (have_rows("schlagworte", check_page_id())) {
                the_row();
                $schlagwort[] = get_sub_field("schlagwort");
               }
            }
		
		//var_dump($schlagwort);

        return '<div class="container-fluid top-picture" title="'. $title . '" style="background: url(' . $url . ');">
				<div class="page-image-title">
					<div class="container">
						
						<h1>' . $title . '</h1>
						<div class="orange-top">schnell | fachgerecht | günstig</div>
						
							<div class="vorteil-mobile" style="background-color: ' . $akzent_color . '"><i class="fa fa-check-square-o" aria-hidden="true"></i>' . trim($schlagwort[0]) . '</div> 
							<div class="vorteil-mobile" style="background-color: ' . $akzent_color . '"><i class="fa fa-check-square-o" aria-hidden="true"></i>' . trim($schlagwort[1]) . '</div> 
							<div class="vorteil-mobile" style="background-color: ' . $akzent_color . '"><i class="fa fa-check-square-o" aria-hidden="true"></i>' . trim($schlagwort[2]) . '</div> 
							<div class="vorteil-mobile" style="background-color: ' . $akzent_color . '"><i class="fa fa-check-square-o" aria-hidden="true"></i>' . trim($schlagwort[3]) . '</div> 
							
							<a title="Jezt anrufen und Termin mit ' . $title .' vereinbaren" href="tel:' . $phone . '">
                                <div class="phone-button-mobile text-center" style="background-color: ' .$primary_color .'">

   

                                   
									<div class="phone-button-text-mobile">'. $phone_view . '</div>


                                </div>
                            </a>
							<a title="Schreiben Sie uns ' . $title .' " href="kontakt">
                                <div class="phone-button-mobile text-center" style="background-color: black">
    
									<div class="phone-button-text-mobile">Jetzt Termin vereinbaren</div>

                                </div>
                            </a>	
						<div class=""></div>
						<div class=""></div>
					</div>
				</div>
			  </div>';
    }

  

    function contact_vorteile_view() {
        include(get_template_directory() .'/models/main_model.php');
		
        echo '<div class="container-fluid kontakt-vorteile" id="kontakt">
		<div class="inner-container container">
			<div class="row">
				<div class="col-md-5">';
					echo vorteile_view();

					echo '</div>
							<div class="col-md-1 hidden-xs"></div>
							<div class="col-md-6 hidden-xs kontakt-form-vorteile" style="background-color: ' . $bg_light . '"><br>';
					echo do_shortcode('[contact-form-7 id="9511" title="Kontakt Vorteile"]');

					echo '</div>
			</div>
		</div>
	</div>';
    }

    add_shortcode("contact_vorteile", "contact_vorteile_view");

    /*Hauptext mit leistungen
     *
     */
    function main_text_services_view(){
		include(get_template_directory() .'/models/main_model.php');
		
		$headline = get_field("hauptinhalt_uberschrift", $page_id);
		$headline = apply_filters( 'the_content', $headline );
		$headline = replace_sc ($headline);
		$headline = wp_strip_all_tags($headline);
        $haupttext = get_field("hauptinhalt_text", $page_id);
		$haupttext= apply_filters( 'the_content', $haupttext );
		$haupttext = replace_sc ($hauptttext);
        $short_text = get_field("ubersichtstexte_radom", $page_id);
		
       
        if (empty($headline)) {
            $page_id = $ancestors[0];
            $headline = get_field("hauptinhalt_uberschrift", $page_id);
			$headline = apply_filters( 'the_content', $headline );
			$headline = replace_sc ($headline);
			$headline = wp_strip_all_tags($headline);
            $haupttext = get_field("hauptinhalt_text", $page_id);
			$haupttext = replace_sc ($hauptttext);
			$haupttext= apply_filters( 'the_content', $haupttext );
        }

        
		if (have_rows("ubersichtstexte_radom", $page_id)) {
			while (have_rows("ubersichtstexte_radom", $page_id)):
				the_row();
				$ubersichtstexte_radom_data[] = get_row();
			endwhile;
				
			shuffle($ubersichtstexte_radom_data);
		}

		$short_text =	'<p>' . $ubersichtstexte_radom_data[0]["field_6220ef00d9596"] .'</p>
						 <p>' . $ubersichtstexte_radom_data[1]["field_6220ef00d9596"] .'</p>
						 <p>' . $ubersichtstexte_radom_data[2]["field_6220ef00d9596"] .'</p>';
     
		$short_text = apply_filters( 'the_content', $short_text  );
		
        if (is_category()) {
            $page_id = $home_id;
            $headline = get_field("hauptinhalt_uberschrift", check_page_id());
			$headline = apply_filters( 'the_content', $headline );
			$headline = wp_strip_all_tags($headline);
			
			while (have_rows("ubersichtstexte_bezirke", check_page_id())):
				the_row();
				$overview_texte[] = the_row();
			endwhile;
			
			shuffle($overview_texte);
			
			foreach( $overview_texte as $overview_text) {
			
			if ($overview_text["field_628f41c086d01"]){
				$keywordgroup_1_text = $overview_text["field_628be744c5668"];
			}
			if ($overview_text["field_628f4634186be"]){
				$keywordgroup_2_text = $overview_text["field_628be744c5668"];
			}
			if ($overview_text["field_628f46af186bf"]){
				$keywordgroup_3_text = $overview_text["field_628be744c5668"];
			}
			if ($overview_text["field_628f4217ce6fe"]){
				$keywordgroup_4_text = $overview_text["field_628be744c5668"];
			}
			}
			//var_dump ($overview_text);   
			
			$short_text = '<div class="col-md-6">'. $keywordgroup_1_text .'<br><br><br>' . $keywordgroup_2_text .'</div><div class="col-m-6">' . $keywordgroup_3_text .'<br><br><br>' . $keywordgroup_4_text . '</div>';
			$short_text= apply_filters( 'the_content', $short_text );
        }
		
		
        if (empty($ancestors) && !is_category()) {
            $page_id = get_queried_object_id();
            $headline = get_field("hauptinhalt_uberschrift", $page_id);
			$headline = replace_sc ($headline);
			$headline = apply_filters( 'the_content', $headline );
			$headline = wp_strip_all_tags($headline);
            $haupttext = get_field("hauptinhalt_text", $page_id);
			$haupttext= apply_filters( 'the_content', $haupttext );
            
			
			$short_text = $haupttext;
        }

        $args_hauptseite = [
            "post_type" => "page",
            "post_status" => "publish",
            "meta_query" => [
                [
                    "key" => "startseite",
                    "value" => "ja",
                    "compare" => "LIKE",
                ],
            ],
            "orderby" => "menu_order",
            "order" => "ASC",
            "posts_per_page" => 12,
        ];

        $args_cat = [
            "post_type" => "page",
            "cat" => $cat_id,
            "orderby" => "menu_order",
			 "order" => "ASC",
            "posts_per_page" => -1,
			
        ];

        if (is_category()) {
            $args = $args_cat;
			
        } else {
            $args = $args_hauptseite;
        }

        $similar_query = new WP_Query($args);?>
			<div class="container-fluid pdtb-default">
				<div class="container leistungen-menue">
					<div class="row">
					<div class="col-md-12">
							<?php while ($similar_query->have_posts()):
									   $similar_query->the_post();
									   $page_id = get_the_ID();
									   $ancestors = get_ancestors($page_id, "page");
									   $link_title = get_the_title();
									   $wptitle = get_the_title();
									   $categories = get_the_category();
									   $icon = get_field("hauptseiten_icon", $ancestors[0]);
									  
									   $title = title_cleaner($wptitle);
									   //var_dump($title);?>
										
											<div class="col-md-2 col-xs-6">
										      	<a href="<?php the_permalink(); ?>" title="<?php echo $link_title; ?>">
													<div class="leistungen-image icon">
														<?php echo get_icon($icon[0], true); ?>
													</div>
													<p class="menue-title">
														<?php echo $title; ?>
														<span style="display:none"><?php echo get_location(); ?></span>
													</p>
												</a>
											</div>
										
							<?php endwhile; ?>
					</div>
			</div>
		</div>
	</div>
</div>

<?php wp_reset_query();
}
add_shortcode("main_text_services", "main_text_services_view");
	
	
function short_text_images() {
	include(get_template_directory() .'/models/main_model.php');
	if (have_rows("ubersichtstexte_radom", $parent_id)) {
			while (have_rows("ubersichtstexte_radom", $page_id)):
				the_row();
				$ubersichtstexte_radom_data[] = get_row();
			endwhile;
				
			shuffle($ubersichtstexte_radom_data);
		}
			
		$short_text =	'<p>' . $ubersichtstexte_radom_data[0]["field_6220ef00d9596"] .'</p>
						 <p>' . $ubersichtstexte_radom_data[1]["field_6220ef00d9596"] .'</p>
						 <p>' . $ubersichtstexte_radom_data[2]["field_6220ef00d9596"] .'</p>';

		$short_text = str_replace("[ORT]", $bezirk, $short_text);
		echo $current_category;
		?>
<div class="container-fluid short-text-images">
			<div class="container inner-container">
	
	
				<div class="row">
										
										<div class="col-xs-12 col-md-7">
												<?php echo $short_text; ?>
										</div>
										<div class="col-md-5 hidden-xs">
												<?php //echo vorteile_view();?>
										</div>
				</div>
			</div>
		</div>
	
<?php		
}

function main_text_hauptseiten() {
	
		$headline = get_field("hauptinhalt_uberschrift", check_page_id());
		$headline = apply_filters( 'the_content', $headline );
		$headline = replace_sc($headline);
		$headline = wp_strip_all_tags($headline);
       
		$haupttext = get_field("hauptinhalt_text", check_page_id());
		$haupttext = apply_filters( 'the_content', $haupttext );
	    $haupttext = replace_sc($haupttext);?>


<div class="container-fluid main-text-hauptseiten">
			<div class="container inner-container">
	
				<div class="row">
					<div class="col-md-12">
						<h2>
							<?php echo $headline;?>

						</h2>
					</div>
				</div>
				<div class="row">									
					<div class="col-xs-12 col-md-12">
						<?php echo $haupttext;?>
					</div>
				</div>
		
		</div>
</div>
<?php 
}	
	
	
	
function main_text_images() {
	if (have_rows("haupttexte_random", check_page_id())) {
			while (have_rows("haupttexte_random", check_page_id())):
				the_row();
				$haupttext_radom_data[] = get_row();
			endwhile;
				
			shuffle($haupttext_radom_data);
		}
	
		//var_dump($haupttext_radom_data); 
					
		
		
		//var_dump($haupttext_radom_data[0]);
	    //var_dump($haupttext_radom_data);
	if ($ancestors[0]){
		$haupttext_heading = $haupttext_radom_data[0]["field_621dec282f89c"];
		$haupttext_heading = apply_filters( 'the_content', $haupttext_heading );
		$haupttext_heading = replace_sc ($haupttext_heading);
		$haupttext_heading = wp_strip_all_tags($haupttext_heading);
		
		
		$haupttext = $haupttext_radom_data[0]["field_621dec282f8d4"];
		shuffle($haupttext);
		$haupttext = $haupttext[0]["field_621dec2835e86"];
		$haupttext = apply_filters( 'the_content', $haupttext );
		$haupttext = replace_sc($haupttext);
		}
	
		else {
			$haupttext_heading = $haupttext_radom_data[0]["field_621dec282f89c"];
			$haupttext_heading = apply_filters( 'the_content', $haupttext_heading );
			$haupttext_heading = replace_sc ($haupttext_heading);
			$haupttext_heading = wp_strip_all_tags($haupttext_heading);
				
			$haupttext = $haupttext_radom_data[0]["field_621dec282f8d4"];
			shuffle($haupttext);
			$haupttext = $haupttext[0]["field_621dec2835e86"];
			$haupttext = apply_filters( 'the_content', $haupttext );
			$haupttext = replace_sc($haupttext);
		}?>
<div class="container-fluid main-text-hauptseiten">
			<div class="container inner-container">
	
				<div class="row">
					<div class="col-md-12">
						<h2>
							<?php echo $haupttext_heading;?>

						</h2>
					</div>
				</div>
				<div class="row">									
					<div class="col-xs-12 col-md-12">
						<?php echo $haupttext;?>
					</div>
				</div>
		
		</div>
</div>
	
<?php		
} 

    /*FAQ
     *
     */

    function faq_view($index, $faq_data)
    {	
		include(get_template_directory() .'/assets/icons.php');
		
        $faq_title = $faq_data[$index]["field_61e021db32485"];
		$faq_title = apply_filters( 'the_content', $faq_title );

        $faq = $faq_data[$index]["field_61e021fe32486"];
		$faq = replace_sc($faq);
       	$faq = apply_filters( 'the_content', $faq );

        if (!empty($faq_title)) {?>
            <div class="container-fluid faq" id="faq">
			<div class="container faq-container">
	
	
				<div class="row">
											<div class="col-md-1 icon hidden-xs">
												<?php echo $faq_icon;?>
												<!--<p class="icon-caption text-center">FAQ</p>-->
											</div>
											<div class="faq-container col-md-11">
												<div
													  class="faq-heading" 
													  data-toggle="collapse"
													  data-target="#faq-<?php echo $index; ?>"
													  >
														<?php echo wp_strip_all_tags($faq_title); ?>
												</div>
												<div id="faq-<?php echo $index; ?>" class="collapse faq-<?php echo $index; ?>"><?php echo $faq; ?></div>
											</div>
				</div>
			</div>
		</div><?php
        }
    }

    add_shortcode("faq", "faq_view");

	

    /*Tipps
     *
     */

    function tipps_view($index, $tipps_data)
    {
		include(get_template_directory() .'/models/main_model.php');
		include(get_template_directory() .'/assets/icons.php');
        //var_dump($tipps_data[$index]);
		$tip = $tipps_data[$index]["field_60913a7769e86"];
        $tip = apply_filters( 'the_content', $tip );
        //$tip = str_replace("[ORT]", $bezirk, $tip);
		
		$tip_heading = $tipps_data[$index]["field_624192a2d0b23"];
		$tip_heading = apply_filters( 'the_content', $tip_heading );

        if (!empty($tip)) {?>
         <div class="container-fluid tipps">
			<div class="container tip-container">
				<div class="row">
														<div class="tip-icon col-md-1 icon hidden-xs">
															<?php echo $faq_icon;?>
															<!--<p class="icon-caption text-center">FAQ</p>-->
														</div>
														
														<div class="tip-container col-md-11">
															<div
																  class="tip-heading" 
																  data-toggle="collapse"
																  data-target=".collapse.tip-<?php echo $index; ?>"
																  data-text="Collapse"
																 
																  >
																	<?php echo wp_strip_all_tags($tip_heading); ?> 
															</div>
															<div class="tip-content collapse tip-<?php echo $index; ?>"><?php echo $tip; ?></div>

															
														</div>
														
					</div>
				</div>
			</div>
		
<?php }
    }

  
    function bewertungen(){?>
			<?php if (wp_is_mobile()): ?>
			<?php else: ?>
			   <div class="container-fluid reviews hidden-xs">
				<div class="container inner-container">
					<?php echo do_shortcode("[trustindex no-registration=google]"); ?>
				</div>
			</div>	
			<?php endif;
				}?>
	
<?php
/* Video Component
 */

function videos(){
    include(get_template_directory() .'/models/main_model.php');
    $video = get_field("video", check_page_id());
    
	$video_heading = get_field("video_uberschrift", check_page_id());
    $video_heading = apply_filters( 'the_content', $video_heading);
	
	$video_text = get_field("video_text", check_page_id());
	$video_text = apply_filters( 'the_content', $video_text);

        
    if (!empty($video)) {?>
	
			<div class="container-fluid hidden-xs" style="background-color: <?php echo $akzent_color; ?>">
				<div class="container inner-container">
					<div class="row">
					<div class="col-md-5 video-heading">

								<?php echo $video_heading; ?>


						</div>
						<div class="col-md-7 video">

							<iframe class="video" width="500" height="265" loading="lazy" src="https://www.youtube.com/embed/<?php echo $video; ?>" title="<?php echo $title; ?> Player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
						</div>	
						<div class="col-md-12">
								<?php echo $video_text; ?>
						</div>
					</div>	

				</div>
			</div>	
		<?php
			}
		}

/*keywords
 */

function keywords()
{
    include(get_template_directory() .'/models/main_model.php');

    $keywords = get_field("keywords", $page_id);

    

    if (empty($keywords)) {
        $keywords = get_field("keywords", $ancestors[0]);
    }
    ?>
	<div class="container-fluid keywords">
		<div class="container inner-container">
			<div class="keywords row"><?php echo $keywords; ?></div>
		</div>
	</div>
	
<?php
}

/*subtexte
 */

function subtexte($index, $subtext_data)
{
	include(get_template_directory() .'/models/main_model.php');

    $subtext = $subtext_data[$index];
	
    $subtext_heading = $subtext["field_621dec28484fa"];
	$subtext_heading = apply_filters( 'the_content', $subtext_heading );
	
    $subtext_text = $subtext["field_621dec2848532"];
    if ($subtext_text) {
		shuffle($subtext_text);
	}
	$subtext_text = $subtext_text[0]["field_621dec284f4c0"];
	$subtext_text = apply_filters( 'the_content', $subtext_text );

	//var_dump($subtext_data[0]);
	
	if ($subtext_text) {?>
		<div class="container-fluid accent-background-color pdtb-default">
				<div class="container">
					<div class="subtext row">
						<div class="subtext-heading">
							<?php echo $subtext_heading; ?>
						</div>
						<div class="subtext-content">
							<?php echo $subtext_text; ?>
						</div>
					</div>
				</div>
		</div>

<?php
		   }
}
	


function subtexte_hauptseite($index, $subtext_data)
{
    $subtext = $subtext_data[$index];
	$subtext_heading = $subtext ["field_6086c763531f1"];
    $subtext_heading = apply_filters( 'the_content', $subtext_heading );
	$subtext_text = $subtext["field_6086c774531f2"];
	$subtext_text = apply_filters( 'the_content', $subtext_text );
    
	//var_dump($subtext_data[0]);
	
	if ($subtext_text) {?>
		<div class="container-fluid accent-background-color pdtb-default" id="subtext-hauptseite">
			<div class="container">
				<div class="subtext row">
						<div class="subtext-heading text-center" data-toggle="collapse" data-target="#subtext-<?php echo $index; ?>">
							<?php echo $subtext_heading; ?>
						</div>
							<div id="subtext-<?php echo $index; ?>" class="collapse subtext-<?php echo $index; ?>">
								<?php echo $subtext_text; ?>
							</div>
						</div>
				</div>
			</div>
		</div>

<?php }}?>	

	
<?php	
function proven_expert(){?>
<!-- ProvenExpert Bewertungssiegel -->
<style type="text/css">body {-ms-overflow-style: scrollbar;} @media(max-width:800px){.ProvenExpert_widget_container {display:none !important;}}</style>
<span class="ProvenExpert_widget_container" id="provenexpert_circle_widget_hzwcb" style="text-decoration:none;z-index:9999;position:absolute;float:left;line-height:0;right:25px;bottom:100px;"></span><script type="text/javascript" async loading="lazy" src="https://www.provenexpert.com/widget/circlewidget.js?s=140&id=hzwcb&u=2Nmp1LmplRwo14TBlAmZ3LmZ34TZmZGA"></script>
<!-- ProvenExpert Bewertungssiegel -->

<?php }?>

<?php 
function proven_expert_stars(){?>
<a  href="https://www.provenexpert.com/entruempelungen/?utm_source=Widget&amp;utm_medium=Widget&amp;utm_campaign=Widget" title="Kundenbewertungen &amp; Erfahrungen zu Entrümpelungen. Mehr Infos anzeigen." target="_blank" style="text-decoration:none;" rel="noopener noreferrer"><img src="https://images.provenexpert.com/97/bd/57ec96f7d66123a6efeed4cfe325/widget_portrait_160_de_0.png" alt="Erfahrungen &amp; Bewertungen zu Entrümpelungen" width="160" height="192" style="border:0" /></a>
<?php } ?>

<?php
/*Pricing Table
 */

function pricing_table(){
    include(get_template_directory() .'/models/main_model.php');
	

    if (!empty($ancestors)) {
        $page_id = $ancestors[0];
        $pricing_heading = get_field("uberschrift_kosten", $page_id);
		$pricing_heading = apply_filters( 'the_content', $pricing_heading );
    }

    if (empty($pricing_heading)) {
        $page_id = $home_id;
        $pricing_heading = get_field("uberschrift_kosten", $page_id);
		$pricing_heading = apply_filters( 'the_content', $pricing_heading );
    }

    

    if (have_rows("posten_kosten", $page_id)) {
        while (have_rows("posten_kosten", $page_id)):
            the_row();
            $posten_kosten_data[] = get_row();
        endwhile;
        $posten_count = count($posten_kosten_data);
        //var_dump($posten_kosten_data[0]);
    }

    if ($posten_count === 3) {
        $posten_count = 4;
    } elseif ($posten_count === 4) {
        $posten_count = 3;
    }
    ?>

<div id="pricing-table"class="container-fluid" style="background-color:<?php echo $akzent_color; ?>">
<div class="container inner-container">  
	<div class="pricing row">
        
      
        <div><?php echo $pricing_heading; ?></div>
  		<?php if ($posten_count) { ?>
        <?php foreach ($posten_kosten_data as $posten_kosten) { ?>
			<?php if ($posten_kosten["field_6220a0582163b"]) { ?>	 
						<div class="col-md-<?php echo $posten_count; ?>" >
						<div class="pricing-item text-center">
							
								
						  <div class="pricing-divider" style="background-color:<?php echo $secondary_color; ?>">
							  <p class="text-light"><?php echo $posten_kosten["field_6220a0582163b"]; ?></p>
						  </div>
							
						  <div class="card-body bg-white mt-0 shadow">
							<ul class="list-unstyled">
							<?php if ($posten_kosten["field_62209f1eee472"]) { ?>	 
							  <li>wenig Hausrat<br>
								  <span class="h5">ab € <?php echo $posten_kosten["field_62209f1eee472"]; ?>.-</span>
									<hr>
							  </li>
							<?php } ?>
							<?php if ($posten_kosten["field_62209f68ee473"]) { ?>	 
							  <li>normaler Hausrat<br>
								  <span class="h5">ab € <?php echo $posten_kosten["field_62209f68ee473"]; ?>.-</span>
								  <hr>
							  </li>
							<?php } ?>
							<?php if ($posten_kosten["field_62209fbcee474"]) { ?>	
							  <li>viel Hausrat <br>
								  <span class="h5">ab € <?php echo $posten_kosten["field_62209fbcee474"]; ?>.-</span>
									<hr>
							  </li>	
							<?php } ?>
							<?php if ($posten_kosten["field_62209fceee475"]) { ?>	
								
							  <li>Messie <br>
								  <span class="h5">ab € <?php echo $posten_kosten["field_62209fceee475"]; ?>.-</span>
							  </li>		
							</ul>
							<?php } ?>  
							 <a href="/kontakt">
								<div class="pricing-read-more" style="background-color:<?php echo $primary_color; ?>">Anfragen</div>
							 </a>
						  </div>
						</div>
					</div>
		<?php } ?>
		<?php } ?>
		  
		  
				<?php } ?>
  
        
        
	</div>
  </div>
</div>
<?php
}
/*Bilder
 */
function images($index){
include(get_template_directory() .'/models/main_model.php');

    if (!have_rows("bilder_fur_hauptkategorie", $page_id)) {
        $page_id = $home_id;
    }

    
    //var_dump($page_id);

    if (have_rows("bilder_fur_hauptkategorie", $page_id)) {
        while (have_rows("bilder_fur_hauptkategorie", $page_id)):
            the_row();
            $bild_data[] = get_row("bild");
        endwhile;
        shuffle($bild_data);
    }
    $bild_3 = array_chunk($bild_data, 3);
    ?>


<div class="container-fluid images-container" style="background-color:<?php echo $akzent_color; ?>">
	

<div class="container inner-container">
<?php foreach ($bild_3[$index] as $bild) {

    $caption = $bild["bild_beschreibung"];
   	$caption = apply_filters( 'the_content', $caption );
	$caption = wp_strip_all_tags($caption);	
    $index = $index + 1;
    $img_full = $bild["bild"];
    $img_url_1 = preg_replace("/\.[^.]+$/", "", $img_full);
    $img_url_1 = str_replace("-scaled", "", $img_url_1);
    $img_type_1 = end(explode(".", $img_full));

    if (wp_is_mobile()):
        $size = "-300x200";
        $size_w = 400;
        $size_h = 200;
    else:
        $size = "-226x170";
        $size_w = 226;
        $size_h = 170;
    endif;
    ?> 
										
			<div class="col-md-4 main-images">

				<img 
					 width="<?php echo $size_w; ?>" 
					 height="<?php echo $size_h; ?>"
					 alt="<?php echo $caption; ?>"
					 title="<?php echo $caption; ?>"
					 loading="lazy"
					 class="hauptseiten-image center-block" 
					 src="<?php echo $img_url_1 . $size . "." . $img_type_1; ?>"
					 >


				<div class="caption text-center"><?php echo $caption; ?></div>
			</div>

											 
	<?php
} ?>

	</div>
	
</div>
<?php
}

function leistungen()
{
    if (have_rows("leistungen_startseite")) {
        while (have_rows("leistungen_startseite")):
            the_row();
            $leistungen_data[] = get_row();
        endwhile;
        shuffle($leistungen_data);
        var_dump($leistungen_data);
    }
}
}

add_action("widgets_init", "seopress_shortcodes_init"); 



function punsh_line($index) {
include(get_template_directory() .'/models/main_model.php');	


$punshline =  get_field("punsh_lines", check_page_id());
	if ($punshline){
		$punshline = $punshline[$index]["punsh_line"];
		$punshline = apply_filters( 'the_content', $punshline );
		$punshline = wp_strip_all_tags($punshline);
	};?>
	
<div class="container-fluid punsh-line text-center" style="background-color:<?php echo $akzent_color;?>">
	<div class="container inner-container">
		<div class="row punshline">
			<?php echo $punshline;?>
		</div>
	</div>
</div>

<?php	
}
/*
function contact_line($contact_title = false) {
$invert = false; 
include(get_template_directory() .'/models/main_model.php');
include(get_template_directory() .'/assets/icons.php');

if ($contact_title === 1) {
$contact_line_title = '<div class="col-md-12 contact-line-heading">
							 Kontaktieren Sie uns per Email, Kontaktformular oder gerne telefonisch für eine persönliche Beratung. <br>Fotoservice mit Email und Whatts App - ein Besichtigungstermin ist kostenlos &amp; unverbindlich.
			 			</div>';
}?>

<div class="container-fluid contact-line text-center hidden-xs" style="background-color:<?php echo $akzent_color;?>">
	<div class="container inner-container">
		<div class="row">
			
			 <div class="col-md-4 button-group">
                            <a title="Jezt anrufen und Termin mit <?php the_title(); ?> vereinbaren" href="tel:<?php echo $phone;?>">
                                <div class="phone-button text-center" style="border-color: <?php echo $primary_color;?>">

   

                                    <div class="phone-button-text"><?php echo $phone_view;?></div>


                                </div>
                            </a>
                        </div>
					
						 <div class="col-md-4 button-group">
                            <a title="Schreiben Sie, schicken Sie uns Fotos oder vereinbaren Sie einen Termin mit <?php the_title(); ?>" href="/kontakt">
                                <div class="phone-button text-center" style="border-color: <?php echo $primary_color;?>">

   

                                    
									<div class="phone-button-text">Kontakt</div>



                                </div>
                            </a>
                        </div>
						<div class="col-md-4 button-group">
                            <a title="Schreiben Sie, schicken Sie uns Fotos oder vereinbaren Sie einen Termin mit <?php the_title(); ?>" href="mailto:' . get_options()['email'] . '">
                                <div class="phone-button text-center" style="border-color: <?php echo $primary_color;?>">

   

                                  
									<div class="phone-button-text">E-Mail</div>



                                </div>
                            </a>
                        </div>
			 <?php echo $contact_line_title; ?>
		</div>
	</div>
</div>

<?php	
}
*/
 function timeline(){
		include(get_template_directory() .'/assets/icons.php');
	 	include(get_template_directory() .'/models/main_model.php');
	 	
        if (!have_rows("timeline", $page_id)) {
            $page_id = $home_id;
        }
	 
	 	if (is_category()) {
		 	$page_id = $home_id;
		}
	
        if (have_rows("timeline", $page_id)) {?>
			<div class="container inner-container hidden-xs hidden-sm">
    			<div class="page-header">
					<!--<h3 id="timeline-heading">Ablauf <?php echo $title;?></h3>-->
				</div>
					<div id="timeline">
						<div class="row timeline-movement timeline-movement-top">
								<div class="timeline-future-movement icon"><?php echo $transport_icon;?></div>
						</div>
		<?php
			
         
            while (have_rows("timeline", $page_id)) {
				$timeline_index = get_row_index();
				if ( $timeline_index & 1 ) {
					$importo = 'importo-right';
					$causale = 'causale-right';
					$icon_class = 'timeline-icon-right';
					$offset= 'col-sm-offset-6';
										
				} else {
					$importo = 'importo';
					$causale = 'causale';
					$icon_class = 'timeline-icon';
					$offset ='';
					}
				
                the_row();
                $timeline_title = get_sub_field("timeline_titel");
				$timeline_title = apply_filters( 'the_content', $timeline_title );

                $timeline_text = get_sub_field("timeline_text");
				$timeline_text = apply_filters( 'the_content', $timeline_text );
				
				$icon = get_sub_field("icon");
				  include(get_template_directory() .'/assets/icons_controller.php');?>
					
					<div class="row timeline-movement">

							<div class="timeline-badge"></div>


							   <div class="<?php echo $offset;?> col-md-6 timeline-item">
									<div class="row">
										<div class="col-sm-11">
											<div class="timeline-panel credits">
												<div class="timeline-panel-ul">
													<div class="<?php echo $icon_class;?> icon col-md-3"><?php echo $show_icon;?></div>
													<div class="<?php echo $importo;?>" style="border-color:<?php echo $primary_color;?>">
														<?php echo $timeline_title;?>
													</div>
													<div class="<?php echo $causale;?>"><?php echo  $timeline_text;?></div>
												

												</div>
											</div>

										</div>
									</div>
								</div>


					</div>
<?php            
            }
        }?>
				</div></div>
<?php }