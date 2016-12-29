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
		$taxonomies[ $taxonomy ] = $term->slug;
	}

	return esc_url( add_query_arg( array( 'bea_taxonomy' => $taxonomies ), get_post_type_archive_link( $p_type ) ) );
}