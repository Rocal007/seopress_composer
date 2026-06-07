<!DOCTYPE html>
<html <?php language_attributes(); ?> data-theme="seopress" style="scroll-behavior:smooth">

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
	<?php
	$settings_data = seopress_container()->get(\SeopressComposer\Models\SiteSettings_Data::class)->get_data();
	if (!empty($settings_data['google_site_verification'])) {
		$verification_code = $settings_data['google_site_verification'];
		// If user pasted the full tag, try to extract the content
		if (strpos($verification_code, '<meta') !== false && preg_match('/content=["\']([^"\']+)["\']/', $verification_code, $matches)) {
			$verification_code = $matches[1];
		}
		echo '<meta name="google-site-verification" content="' . esc_attr($verification_code) . '" />' . "\n";
	}
	?>

</head>

<body id="top" <?php body_class(); ?>>
	<?php
	if (function_exists('wp_body_open')) {
		wp_body_open();
	} ?>

	<!-- Skip to Content Link - WCAG 2.4.1 Bypass Blocks -->
	<a href="#main-content" class="skip-to-content">
		<?php esc_html_e('Zum Hauptinhalt springen', 'seopress_composer'); ?>
	</a>

	<?php get_template_part('template-parts/header', 'topbar'); ?>
	<?php get_template_part('template-parts/header', 'main'); ?>