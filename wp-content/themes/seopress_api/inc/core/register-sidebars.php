<?php

function seopress_widgets_init()
{
	
	/* register widgets / sidebar
	* @ https://codex.wordpress.org/Function_Reference/register_sidebar
	* Usages register_sidebar( $args );
	*/
	register_sidebar( array(
		'name'			=> __( 'Blog Sidebar', 'seopress' ),
		'id'			=> 'sidebar-1',
		'description'	=> __( 'Widgets for Blog sidebar. If you are using Full Width Layout in customize, then this sidebar will not display.', 'seopress' ),
		'before_widget'	=> '<div id="%1$s" class="widget_sidebar_main clearfix %2$s">',
		'after_widget'	=> '</div>',
		'before_title'	=> '<h3 class="right-widget-title">',
		'after_title'	=> '</h3>',
	) );
	
	register_sidebar( array(
		'name'			=> __( 'Page Sidebar', 'seopress' ),
		'id'			=> 'seopress_sidebar_page',
		'description'	=> __( 'Widgets for Page sidebar. If you are using Full Width No Sidebar Template, then this sidebar will not display.', 'seopress' ),
		'before_widget'	=> '<div id="%1$s" class="widget_sidebar_main clearfix %2$s">',
		'after_widget'	=> '</div>',
		'before_title'	=> '<h3 class="right-widget-title">',
		'after_title'	=> '</h3>',
	) );
	
	register_sidebar( array(
		'name'			=> __( 'Entrümplungen', 'seopress' ),
		'id'			=> 'seopress_sidebar_entrumpelungen',
		'description'	=> __( 'Widgets for Page sidebar. If you are using Full Width No Sidebar Template, then this sidebar will not display.', 'seopress' ),
		'before_widget'	=> '<div id="%1$s" class="widget_sidebar_main clearfix %2$s">',
		'after_widget'	=> '</div>',
		'before_title'	=> '<h3 class="right-widget-title">',
		'after_title'	=> '</h3>',
	) );
    
    register_sidebar( array(
		'name'			=> __( 'Verlassenschaften', 'seopress' ),
		'id'			=> 'seopress_sidebar_verlassenschaften',
		'description'	=> __( 'Widgets for Page sidebar. If you are using Full Width No Sidebar Template, then this sidebar will not display.', 'seopress' ),
		'before_widget'	=> '<div id="%1$s" class="widget_sidebar_main clearfix %2$s">',
		'after_widget'	=> '</div>',
		'before_title'	=> '<h3 class="right-widget-title">',
		'after_title'	=> '</h3>',
	) );
	
    register_sidebar( array(
		'name'			=> __( 'Räumungen', 'seopress' ),
		'id'			=> 'seopress_sidebar_raumungen',
		'description'	=> __( 'Widgets for Page sidebar. If you are using Full Width No Sidebar Template, then this sidebar will not display.', 'seopress' ),
		'before_widget'	=> '<div id="%1$s" class="widget_sidebar_main clearfix %2$s">',
		'after_widget'	=> '</div>',
		'before_title'	=> '<h3 class="right-widget-title">',
		'after_title'	=> '</h3>',
	) );
    
     register_sidebar( array(
		'name'			=> __( 'Antiquitäten', 'seopress' ),
		'id'			=> 'seopress_sidebar_antiquitaeten',
		'description'	=> __( 'Widgets for Page sidebar. If you are using Full Width No Sidebar Template, then this sidebar will not display.', 'seopress' ),
		'before_widget'	=> '<div id="%1$s" class="widget_sidebar_main clearfix %2$s">',
		'after_widget'	=> '</div>',
		'before_title'	=> '<h3 class="right-widget-title">',
		'after_title'	=> '</h3>',
	) );
    
    
     register_sidebar( array(
		'name'			=> __( 'Messie Service', 'seopress' ),
		'id'			=> 'seopress_sidebar_messie',
		'description'	=> __( 'Widgets for Page sidebar. If you are using Full Width No Sidebar Template, then this sidebar will not display.', 'seopress' ),
		'before_widget'	=> '<div id="%1$s" class="widget_sidebar_main clearfix %2$s">',
		'after_widget'	=> '</div>',
		'before_title'	=> '<h3 class="right-widget-title">',
		'after_title'	=> '</h3>',
	) );
    
    
    
	if( class_exists( 'WooCommerce' ) )
	{
		register_sidebar( array(
			'name'			=> __( 'Woocommerce Sidebar', 'seopress' ),
			'id'			=> 'seopress_sidebar_woo',
			'description'	=> __( 'Widgets for Woocommerce Pages (For:- Product Loop, Product Search and Product Single Page). If you are using Full Width Layout in customize, then this sidebar will not display.', 'seopress' ),
			'before_widget'	=> '<div id="%1$s" class="widget_sidebar_main clearfix %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="right-widget-title">',
			'after_title'	=> '</h3>',
		) );
	}

	register_sidebar( array(
		'name'			=> __( 'Top Header right', 'seopress' ),
		'id'			=> 'seopress_sidebar_header',
		'description'	=> __( 'Widgets for top header right. You can select header layout accordingly here: Dashboard > Appearance > Customize > SEOPress Options > Header Layout Options.', 'seopress' ),
		'before_widget'	=> '<div id="%1$s" class="widgets_header fl_right_header_spsl clearboth %2$s">',
		'after_widget'	=> '</div>',
		'before_title'	=> '',
		'after_title'	=> '',
	) );
	
	register_sidebar( array(
		'name'			=> __( 'Top Header Left', 'seopress' ),
		'id'			=> 'seopress_sidebar_header_left',
		'description'	=> __( 'Widgets for top header left. You can select header layout accordingly here: Dashboard > Appearance > Customize > SEOPress Options > Header Layout Options.', 'seopress' ),
		'before_widget'	=> '<div id="%1$s" class="widgets_header_left clearboth %2$s">',
		'after_widget'	=> '</div>',
		'before_title'	=> '',
		'after_title'	=> '',
	) );

	$seopress_number_of_footer_widgets = absint( get_theme_mod( 'seopress_number_of_footer_widgets', '0' ) );
	if( $seopress_number_of_footer_widgets != 0 )
	{
		for( $i = 1; $i <= $seopress_number_of_footer_widgets; $i++ )
		{
			register_sidebar( array(
				'name' 			=> __( 'Footer Widget ', 'seopress' ) . $i,
				'id'			=> 'seopress_footer_' . $i,
				'description'	=> __( 'Widgets for footer ', 'seopress' ) . $i,
				'before_widget'	=> '<div id="%1$s" class="widgets_footer clearfix %2$s">',
				'after_widget'	=> '</div>',
				'before_title'	=> '<h3 class="widgets_footer_title">',
				'after_title'	=> '</h3>',
			) );
		}
	}
}
add_action( 'widgets_init', 'seopress_widgets_init' );