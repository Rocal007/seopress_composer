<?php
$favorite_blog_cats = get_field ('blog_kategorie', check_page_id());
if ($favorite_blog_cats) {
    foreach($favorite_blog_cats as $fav_blog_cat){
        $fav_blog_cat_id[] = intval($fav_blog_cat);
    }
    //var_dump($fav_blog_cat_id);
}

$post_args = array(
    'posts_per_page' => 5,
    'cat' => $fav_blog_cat_id
);
$favorite_post_query = new WP_Query($post_args);
$i=0;

if ($favorite_post_query->have_posts()) :
    while ($favorite_post_query->have_posts()) : $favorite_post_query->the_post(); 
  
            $title_single = get_the_title(get_the_ID());
			$title_single = apply_filters( 'the_content', $title_single);
			$title[] = $title_single;
            
            $blog_picture_single = get_the_post_thumbnail_url(get_the_ID(),'blog-thumb');
            $blog_picture[] = $blog_picture_single;

			$blog_url_single = get_permalink(get_the_ID());
            $blog_url[] = $blog_url_single;
			//var_dump($blog_url);            

            $haupttext_single = get_field("haupttext");
			//apply_filters( 'the_content', $haupttext_single);
            $haupttext[] = $haupttext_single; 

            if ( have_rows('subtexte') ) {    
                while( have_rows('subtexte') ): the_row();
                     $blog_index[$i][] = get_sub_field('index');
                endwhile;
            } 
            
            $blog_index_single = get_field("subtexte");
			
            $blogs[] = array(
                "blog_heading"		=> $title[$i],
                "blog_main_text" 	=> $haupttext[$i],
                "blog_index" 		=> $blog_index[$i],
                "blog_picture"	    => $blog_picture[$i],
				"blog_url"			=> $blog_url[$i]
            );
        $i++;  
  endwhile;
endif; 

wp_reset_query();

shuffle($blogs)?>