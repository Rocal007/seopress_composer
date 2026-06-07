<?php get_header(); ?>
		<?php

		echo top_picture_blog();
		contact_line();
		//yoast_breadcrumb( '<div class="container-fluid breadcrumb-container hidden-xs"> <p id="breadcrumbs">','</p></div>' );
		
		
		while( have_posts() ) : the_post();
		
			//get_template_part( 'content', get_post_format() );
			$title = get_the_title();
			$title = apply_filters( 'the_content', $title);
			$subtexte = get_field("subtexte");
			//var_dump($subtexte);
			$haupttext = get_field("haupttext");
			apply_filters( 'the_content', $haupttext);
			?>
			<div class="container">
				<div class="row">
					<?php echo $haupttext;?> 
					
				</div>	
			
				<?php videos();?>
						<?php foreach ($subtexte as $subtext) {
						$subtext_bild = $subtext["bild"]["sizes"]["medium"];
						//var_dump($subtext_bild);
						$subtext_link =  preg_replace("/\s+/", "", $subtext["index"]);
						$subtext_heading = apply_filters('the_content', $subtext["uberschrift"]);
						$subtext_heading = wp_strip_all_tags($subtext_heading);
						
						echo '<div class="row">
								<h2 id="'.$subtext_link.'">'. $subtext_heading . '</h2>';
								if ($subtext_bild){
									echo '<img class="blog-picture-left" alt="'.$subtext_heading.'"  src="'.$subtext_bild.'">';
								}
									echo '<div class="subtext-text">'. $subtext["text"] . '</div>
							</div>';

					}?>

			</div>
		<?php endwhile;?>

<?php if ( wp_is_mobile() ) :
	
 else : 
	echo do_shortcode('[trustindex no-registration=google]');
endif;	?>	
<?php get_footer(); ?>