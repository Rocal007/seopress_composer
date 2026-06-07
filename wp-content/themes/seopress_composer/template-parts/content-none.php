<div class="flex flex-col items-center justify-center min-h-[50vh] text-center p-8">
	<div class="max-w-md w-full">
		<h3 class="text-2xl font-bold mb-4 text-base-content/70">
			<?php esc_attr_e('Leider wurden keine Services gefunden', 'seopress'); ?>
		</h3>

		<p class="mb-6 text-base-content/60">
			<?php esc_attr_e('Services suchen?', 'seopress'); ?>
		</p>

		<div class="w-full">
			<?php get_search_form(); ?>
		</div>
	</div>
</div>