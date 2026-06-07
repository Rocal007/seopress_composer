<?php
$components = seopress_components();
// $ancestors = get_ancestors($post->ID, 'page');
// $parent_id = $ancestors ? $ancestors[0] : 0;
// Note: This template seems to be a partial used in a loop or specific context.
// Ideally, this logic should be moved to a View component (e.g. Card.php variant).
// For now, we keep the logic but update the markup to Tailwind/DaisyUI.
?>
<div class="card card-side bg-base-100 shadow-xl mb-8 hover:shadow-2xl transition-all">
	<figure class="w-full md:w-1/2">
		<a href="<?php the_permalink(); ?>" class="w-full h-full block">
			<?php if (has_post_thumbnail()): ?>
				<?php the_post_thumbnail('large', ['class' => 'w-full h-full object-cover']); ?>
			<?php else: ?>
				<div class="w-full h-64 bg-base-200 flex items-center justify-center text-base-content/30">Kein Bild</div>
			<?php endif; ?>
		</a>
	</figure>
	<div class="card-body w-full md:w-1/2">
		<h2 class="card-title text-2xl font-bold text-primary">
			<a href="<?php the_permalink(); ?>" class="hover:underline">
				<?php the_title(); ?>
			</a>
		</h2>
		<div class="prose mb-4">
			<?php
			// Legacy teaser logic usually fetched a custom field. We'll fall back to standard excerpt if fields are missing.
			// If we had the data helper here we would use it. For a pure template part replacement inside a loop:
			the_excerpt();
			?>
		</div>
		<div class="card-actions justify-end">
			<a href="<?php the_permalink(); ?>" class="btn btn-primary">Mehr erfahren</a>
		</div>
	</div>
</div>