<?php
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
add_action( 'term_link', function ( $termlink, $term, $taxonomy ) {
	if ( is_admin() ) {
		return $termlink;
	}

	$wanted_taxonomies = array( 'type', 'promotion', 'niveau' );

	if ( empty( $wanted_taxonomies ) || ! in_array( $taxonomy, $wanted_taxonomies ) ) {
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
					$taxonomies[ $taxonomy ][] = $term->slug;
				} else {
					$taxonomies = $g_taxonomies;
				}
			} elseif ( ! in_array( $term->slug, $g_taxonomies[ $taxonomy ] ) ) {
				// Add the current slug into the taxonomy's get args
				$taxonomies                = $g_taxonomies;
				$taxonomies[ $taxonomy ][] = $term->slug;
			} else {
				// Current term already exists, so take it off to "deselect" it
				$terms = $g_taxonomies[ $taxonomy ];

				$key_find = array_search( $term->slug, $terms );
				unset( $terms[ $key_find ] );

				if ( empty( $terms ) ) {
					unset( $g_taxonomies[ $taxonomy ] );
				} else {
					$g_taxonomies[ $taxonomy ] = array_values( $terms );
				}

				$taxonomies = $g_taxonomies;
			}
		} else {
			// As no terms from current taxonomy, add to existing get args the current taxonomy term
			$taxonomies                = $g_taxonomies;
			$taxonomies[ $taxonomy ][] = $term->slug;
		}
	} else {
		// Default, get current taxonomy term's slug
		$taxonomies[ $taxonomy ][] = $term->slug;
	}

	return esc_url( add_query_arg( array( 'bea_taxonomy' => $taxonomies ), get_post_type_archive_link( 'question' ) ) );
}, 20, 3 );

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

add_action( 'init', function () {
	$labels = array(
		"name"               => __( 'Quiz', '' ),
		"singular_name"      => __( 'Quiz', '' ),
		"menu_name"          => __( 'Mes quiz', '' ),
		"all_items"          => __( 'Tous les quiz', '' ),
		"add_new"            => __( 'Ajouter un quiz', '' ),
		"add_new_item"       => __( 'Ajouter un quiz', '' ),
		"edit_item"          => __( 'Modifier le quiz', '' ),
		"new_item"           => __( 'Nouveau quiz', '' ),
		"view_item"          => __( 'Voir le quiz', '' ),
		"search_items"       => __( 'Chercher un quiz', '' ),
		"not_found"          => __( 'Pas de quiz trouvé', '' ),
		"not_found_in_trash" => __( 'Pas de quiz dans la poubelle', '' ),
		"parent_item_colon"  => __( 'Quiz parent', '' ),
		"parent_item_colon"  => __( 'Quiz parent', '' ),
	);

	$args = array(
		"label"               => __( 'Quiz', '' ),
		"labels"              => $labels,
		"description"         => "",
		"public"              => true,
		"publicly_queryable"  => true,
		"show_ui"             => true,
		"show_in_rest"        => false,
		"rest_base"           => "",
		"has_archive"         => false,
		"show_in_menu"        => true,
		"exclude_from_search" => false,
		"capability_type"     => "post",
		"map_meta_cap"        => true,
		"hierarchical"        => false,
		"rewrite"             => array( "slug" => "quiz", "with_front" => true ),
		"query_var"           => true,

		"supports"   => array( "title", "editor" ),
		"taxonomies" => array( "type", "promotion", "niveau" ),
	);
	register_post_type( "quiz", $args );
} );

/**
 * Parse search/archive query for excluding terms
 *
 * @author Maxime CULEA
 *
 * @return \WP_Query
 */
add_action( 'parse_query', function ( \WP_Query $query ) {
	if ( ! $query->is_search() && ! $query->is_archive() ) {
		return $query;
	}

	$query->set( 'orderby', 'rand' );

	if ( ! isset( $_GET['bea_taxonomy'] ) ) {
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

/**
 * Customize the current selected tags
 *
 * @author Maxime CULEA
 */
add_filter( 'wp_generate_tag_cloud_data', function ( $tags_cloud ) {
	if ( ! isset( $_GET['bea_taxonomy'] ) ) {
		return $tags_cloud;
	}

	$g_taxonomies = $_GET['bea_taxonomy'];

	foreach ( $tags_cloud as $tag => $tag_cloud ) {
		$term = get_term( $tag_cloud['id'] );
		if ( isset( $g_taxonomies[ $term->taxonomy ] ) && in_array( $term->slug, $g_taxonomies[ $term->taxonomy ] ) ) {
			$tags_cloud[ $tag ]['class'] = $tag_cloud['class'] . ' ignore';
		}
	}

	return $tags_cloud;
} );

/**
 * Create Quiz Widget
 */
class quiz_widget extends WP_Widget {
	function __construct() {
		parent::__construct( 'save_quiz', 'Save Quiz', array( 'description' => 'Allow to save a quiz' ) );
	}

	public function widget( $args, $instance ) {
		global $wp_query;

		if ( ! $wp_query->have_posts() ) {
			return;
		}

		echo '<section class="widget widget_text">';
		echo '<h2 class="widget-title">Enregistrer ces questions</h2>';
		echo '<div class="textwidget"><div class="tagcloud">';
		printf( '<a href="%s">Générer le quiz</a>', add_query_arg( array(
			'save_quiz' => true,
			'post_ids'  => wp_list_pluck( $wp_query->posts, 'ID' )
		), site_url() ) );
		echo '</div></div>';
		echo '</section>';
	}
}

add_action( 'widgets_init', function () {
	register_widget( 'quiz_widget' );
} );