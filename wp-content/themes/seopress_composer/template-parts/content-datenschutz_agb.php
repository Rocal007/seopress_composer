<?php
$components = seopress_components();
?>
<section class="py-24 lg:py-32 bg-base-100">
	<div class="container mx-auto px-4">
		<h1 class="text-4xl font-bold mb-8 text-center text-primary"><?= get_the_title() ?></h1>
		<div class="prose max-w-none prose-lg">
			<?php the_content(); ?>
		</div>
	</div>
</section>