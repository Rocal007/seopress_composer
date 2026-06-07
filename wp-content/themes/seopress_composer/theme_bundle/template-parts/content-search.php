<article id="post-<?php the_ID(); ?>" <?php post_class('card bg-base-100 shadow-lg hover:shadow-xl transition-all mb-6'); ?>>
    <div class="card-body">
        <header class="card-title">
            <h2 class="text-2xl font-bold">
                <a href="<?php the_permalink(); ?>" rel="bookmark" class="hover:text-primary transition-colors">
                    <?php the_title(); ?>
                </a>
            </h2>
        </header>

        <div class="prose max-w-none mb-4">
            <?php the_excerpt(); ?>
        </div>

        <div class="card-actions justify-end">
            <a class="btn btn-secondary btn-sm" href="<?php the_permalink(); ?>">
                <?php _e('mehr erfahren', 'seopress'); ?>
            </a>
        </div>
    </div>
</article>