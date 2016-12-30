<?php add_filter( 'term_link', 'bea_term_link', 20, 3 );


/**
 * Rewrite the taxonomy term's link
 * This allow to filter the archive page
 *
 * @author Maxime CULEA
 *
 * @param $termlink
 * @param $term
 * @param $taxonomy
 *
 * @return string
 */
function bea_term_link( $termlink, $term, $taxonomy ) {
	if ( is_admin() ) {
		return $termlink;
	}

	$wanted_taxonomies = array( 'type', 'promotion', 'niveau' );

	if ( empty( $wanted_taxonomies ) || ! in_array( $taxonomy, $wanted_taxonomies ) ) {
		return $termlink;
	}

	$p_type = get_post_type();
	if ( ! post_type_exists( $p_type ) ) {
		return $termlink;
	}

	// has already taxonomies in get args
	if ( isset( $_GET['bea_taxonomy'] ) ) {
		$g_taxonomies = $_GET['bea_taxonomy'];
		if ( isset( $g_taxonomies[ $taxonomy ] ) ) {
			// Checking if is an array
			if ( ! is_array( $g_taxonomies[ $taxonomy ] ) ) {
				// If not already in args
				if ( $term->slug !== $g_taxonomies[ $taxonomy ] ) {
					// Add the current slug to the existing slug
					$taxonomies[ $taxonomy ] = array( $g_taxonomies[ $taxonomy ], $term->slug );
				} else {
					$taxonomies = $g_taxonomies;
				}
			} elseif ( ! in_array( $term->slug, $g_taxonomies[ $taxonomy ] ) ) {
				// Add the current slug into the taxonomy's get args
				$taxonomies[ $taxonomy ] = array( $g_taxonomies[ $taxonomy ], $term->slug );
			} else {
				// No matching, then returns the get args as it
				$taxonomies = $g_taxonomies;
			}
		} else {
			// As no terms from current taxonomy, add to existing get args the current taxonomy term
			$taxonomies              = $g_taxonomies;
			$taxonomies[ $taxonomy ] = array( $term->slug );
		}
	} else {
		// Default, get current taxonomy term's slug
		$taxonomies[ $taxonomy ][] = $term->slug;
	}

	return esc_url( add_query_arg( array( 'bea_taxonomy' => $taxonomies ), get_post_type_archive_link( $p_type ) ) );
}

if ( ! post_type_exists( 'question' ) ) {
	$labels = array(
		"name"          => __( 'Questions', '' ),
		"singular_name" => __( 'Question', '' ),
	);

	$args = array(
		"label"               => __( 'Questions', '' ),
		"labels"              => $labels,
		"description"         => "",
		"public"              => true,
		"publicly_queryable"  => true,
		"show_ui"             => true,
		"show_in_rest"        => false,
		"rest_base"           => "",
		"has_archive"         => true,
		"show_in_menu"        => true,
		"exclude_from_search" => false,
		"capability_type"     => "post",
		"map_meta_cap"        => true,
		"hierarchical"        => false,
		"rewrite"             => array( "slug" => "question", "with_front" => true ),
		"query_var"           => true,
		"menu_icon"           => "dashicons-editor-help",
		"supports"            => array( "title", "editor" ),
		"taxonomies"          => array( "type", "promotion", "niveau" ),
	);
	register_post_type( "question", $args );
}

if ( post_type_exists( 'question' ) ) {
	if ( ! taxonomy_exists( 'type' ) ) {
		$labels = array(
			"name"          => __( 'Types', '' ),
			"singular_name" => __( 'Type', '' ),
		);

		$args = array(
			"label"              => __( 'Types', '' ),
			"labels"             => $labels,
			"public"             => true,
			"hierarchical"       => false,
			"show_ui"            => true,
			"show_in_menu"       => true,
			"show_in_nav_menus"  => true,
			"query_var"          => true,
			"rewrite"            => array( 'slug' => 'type', 'with_front' => true, ),
			"show_admin_column"  => false,
			"show_in_rest"       => false,
			"rest_base"          => "",
			"show_in_quick_edit" => false,
		);
		register_taxonomy( "type", array( "question" ), $args );
	}

	if ( ! taxonomy_exists( 'promotion' ) ) {
		$labels = array(
			"name"          => __( 'Promotions', '' ),
			"singular_name" => __( 'Promotion', '' ),
		);

		$args = array(
			"label"              => __( 'Promotions', '' ),
			"labels"             => $labels,
			"public"             => true,
			"hierarchical"       => false,
			"show_ui"            => true,
			"show_in_menu"       => true,
			"show_in_nav_menus"  => true,
			"query_var"          => true,
			"rewrite"            => array( 'slug' => 'promotion', 'with_front' => true, ),
			"show_admin_column"  => false,
			"show_in_rest"       => false,
			"rest_base"          => "",
			"show_in_quick_edit" => false,
		);
		register_taxonomy( "promotion", array( "question" ), $args );
	}

	if ( ! taxonomy_exists( 'niveau' ) ) {
		$labels = array(
			"name"          => __( 'Niveaux', '' ),
			"singular_name" => __( 'niveau', '' ),
		);

		$args = array(
			"label"              => __( 'Niveaux', '' ),
			"labels"             => $labels,
			"public"             => true,
			"hierarchical"       => false,
			"show_ui"            => true,
			"show_in_menu"       => true,
			"show_in_nav_menus"  => true,
			"query_var"          => true,
			"rewrite"            => array( 'slug' => 'niveau', 'with_front' => true, ),
			"show_admin_column"  => false,
			"show_in_rest"       => false,
			"rest_base"          => "",
			"show_in_quick_edit" => false,
		);
		register_taxonomy( "niveau", array( "question" ), $args );
	}
}

add_action( 'parse_query', function ( \WP_Query $query ) {
	if ( ! $query->is_search() && ! $query->is_archive() || ! isset( $_GET['bea_taxonomy'] ) ) {
		return $query;
	}

	// Get all taxonomies
	$taxononomies = $_GET['bea_taxonomy'];
	foreach ( $taxononomies as $taxonomy => $terms ) {
		$tax_query[] = array(
			'taxonomy' => $taxonomy,
			'field'    => 'slug',
			'terms'    => is_array( $terms ) ? $terms : (array) $terms,
			'operator' => 'NOT IN'
		);
	}

	// Anyway add AND relation for multiple tax query
	$tax_query['relation'] = 'AND';

	$query->set( 'tax_query', array( $tax_query ) );

	return $query;
} );