<?php get_header();?>
<?php
$params = array(
    'parent'        =>  get_queried_object_id(), 
    'orderby'       => 'name',
    'order'         => 'ASC',
    'hide_empty'    =>  false 
);
if ( count( get_categories( $params ) ) ) {
    wp_list_categories( $params );
}  
?>
<?php get_footer();