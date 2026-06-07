<?php
get_header(); ?>
<div id="primary" class="container-fluid">
    <main id="main" class="container">
        <?php if ( have_posts() ) : ?>
            <header class="page-header">
                <h1 class="page-title">
                    <?php printf( esc_html__( 'Suchergebnisse für: %s', 'seopress' ), '<span>' . get_search_query() . '</span>' ); ?>
                </h1>
            </header><!-- .page-header -->
            <?php
            
            while ( have_posts() ) :
                the_post();
                
                get_template_part( 'template-parts/content', 'search' );
            endwhile;
            
            the_posts_navigation();
        else :
            get_template_part( 'template-parts/content', 'none' );
        endif;
        ?>
    </main><!-- #main -->
</div><!-- #primary -->
<?php
get_footer();