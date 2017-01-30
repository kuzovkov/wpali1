<?php

/**
 * Kirki Advanced Customizer
 * @package responsive-magazine
 */
 
// Early exit if Kirki is not installed
if ( !class_exists( 'Kirki' ) ) {
	return;
}

Kirki::add_field( 'first_mag_settings', array(
	'type'			 => 'switch',
	'settings'		 => 'responsive-magazine-get-featured',
	'label'			 => __( 'Carousel', 'responsive-magazine' ),
	'description'	 => __( 'Enable or disable carousel', 'responsive-magazine' ),
	'section'		 => 'layout_section',
	'default'		 => 0,
	'priority'		 => 10,
) );
Kirki::add_field( 'first_mag_settings', array(
	'type'			 => 'select',
	'settings'		 => 'responsive-magazine-featured-categories',
	'label'			 => __( 'Carousel category', 'responsive-magazine' ),
	'description'	 => __( 'Select category for the carousel', 'responsive-magazine' ),
	'section'		 => 'layout_section',
	'default'		 => '',
	'priority'		 => 10,
	'choices'		 => responsive_magazine_get_cats(),
	'required'		 => array(
		array(
			'setting'	 => 'responsive-magazine-get-featured',
			'operator'	 => '==',
			'value'		 => 1,
		),
	)
) );

function responsive_magazine_get_cats() {
	/* GET LIST OF CATEGORIES */
	$layercats		 = get_categories();
	$newList		 = array();
	$newList[ '0' ]	 = __( 'All categories', 'responsive-magazine' );
	foreach ( $layercats as $category ) {
		$newList[ $category->term_id ] = $category->cat_name;
	}
	return $newList;
}
