<?php function logo_clean() {
    include(get_template_directory() .'/inc/core/components/logo/logo-model.php');?> 
    <div class="logo-icon icon">
		<a href="<?php echo esc_url( home_url( '/' ) );?>" rel="home" title="<?php esc_attr_e(get_bloginfo('name')); ?>">
			<?php echo get_icon($logo["logo_icon"], true);?>
		</a>
    </div>
    <div class="header-title secondary-border-color">
		<h2>
			<strong class="header-title-background">
				<?php esc_html_e($logo['logo_title']); ?>
			</strong>
			<sup>
				<?php echo $logo['logo_location']; ?>
			</sup>
		</h2>
	</div>
	<div class="header-description secondary-background-color secondary-border-color">
		<h3 class="header-description-text">
			 <?php echo $logo['logo_sub']; ?>
			 <?php //echo $logo['logo_location']; ?>
		</h3>
	</div>
<?php }