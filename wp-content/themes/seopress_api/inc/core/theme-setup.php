<?php
/**
 * Assign the seopress version to a var
 */
$seopress_theme_arr		= wp_get_theme( 'seopress' );
$seopress_version		= $seopress_theme_arr['Version'];
$seopress_name			= $seopress_theme_arr['Name'];

function seopress_setup()
{

	global $content_width;
	if( !isset( $content_width ) )
	{
		$content_width = 730;
	}
	
	load_theme_textdomain( 'seopress', get_template_directory() . '/languages' );
	
	add_theme_support( 'automatic-feed-links' );
	
	add_theme_support( 'title-tag' );

	add_theme_support( 'align-wide' );
	
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 730, 300, true );

	// This theme uses wp_nav_menu() in two locations.
	register_nav_menus( array(
		'primary'	=> __( 'Top Main Menu', 'seopress' ),
		'sidebar'	=> __( 'Sidebar Menu', 'seopress' ),
	) );
	
	add_theme_support( 'html5', array(
		'comment-list', 'gallery', 'caption'
	) );
	
	add_theme_support( 'post-formats', array( 'quote' ) );

	add_theme_support( 'custom-background', array(
		'default-color'      => '#ffffff',
		'default-attachment' => 'fixed',
	) );


	add_theme_support( 'custom-header', array(
	'width'         => 1140,
	'height'        => 260,
	'flex-width'    => true,
	'flex-height'   => true,
	'uploads'       => true,
	'header-text'	=> false,
	) );

	
	add_theme_support( 'custom-logo', array(
		'height'		=> 100,
		'width'			=> 400,
		'flex-height'	=> true,
		'flex-width'	=> true,
	) );
}
add_action( 'after_setup_theme', 'seopress_setup' );