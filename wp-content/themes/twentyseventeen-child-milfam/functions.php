<?php

function remove_footer_admin () {
  echo 'Thank you for creating with <a href="https://wordpress.org/">WordPress</a> | <a href="https://extension.org/terms-of-use/" target="_blank">Terms of Use</a>';
}
add_filter('admin_footer_text', 'remove_footer_admin');

add_filter( 'body_class','my_body_classes' );
function my_body_classes( $classes ) {
    $classes[] = 'has-sidebar';
    return $classes;
}


function show_learn_widget( $atts ) {
  $a = shortcode_atts( array(
    'key' => '',
    'tags' => '',
    'limit' => '5',
    'match_all_tags' => false,
    'event_type' => 'upcoming'
  ), $atts );
  $a['operator'] = ($a['match_all_tags'] == "true" ? "and" : '');
  ob_start();
  include(locate_template('learn-widget.php'));
  return ob_get_clean();
}

add_shortcode( 'learn_widget', 'show_learn_widget' );

add_shortcode( 'sign_up_section', 'show_sign_up_module' );
function show_sign_up_module() {
  $page = get_posts( array( 'name' => 'sign-up', 'post_type' => 'page' ) );
  if ( $page ) {
    $signup = '<div class="sign-up-module">';
    $signup .= $page[0]->post_content;
    $signup .= '</div>';
    return $signup;
  }
}


function show_aweber_widget( $atts ) {
  $a = shortcode_atts( array(
    'key' => ''
  ), $atts );
  ob_start();
  include(locate_template('aweber-widget.php'));
  return ob_get_clean();
}

add_shortcode( 'aweber_signup', 'show_aweber_widget' );

add_action( 'widgets_init', 'register_custom_sidebars' );
function  register_custom_sidebars() {
	register_sidebar(
		array(
      'name' => 'CA Landing Page Sidebar ',
      'id' => 'sidebar-ca',
			'description' => 'Displays in in the sidebar on the CA landing page.',
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h2 class="widget-title">',
      'after_title' => '</h2>'
		)
	);

  register_sidebar(
		array(
      'name' => 'General Sidebar ',
      'id' => 'sidebar-general',
			'description' => 'Displays in in the sidebar on the general MFLN pages, about About.',
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h2 class="widget-title">',
      'after_title' => '</h2>'
		)
	);
}

add_action( 'init', 'categorize_page_settings' );
function categorize_page_settings() {
  // Add category metabox to page
  register_taxonomy_for_object_type('category', 'page');
}

add_action( 'init', 'my_add_excerpts_to_pages' );
function my_add_excerpts_to_pages() {
  add_post_type_support( 'page', 'excerpt' );
}

add_action( 'init', 'ssp_add_categories_to_podcast' );
function ssp_add_categories_to_podcast () {
  register_taxonomy_for_object_type( 'category', 'podcast' );
}


add_filter('pre_get_posts', 'query_post_type');
function query_post_type($query) {
  if( is_category() && $query->is_main_query() ) {
    $post_type = get_query_var('post_type');
    if($post_type)
        $post_type = $post_type;
    else
        $post_type = array('post', 'podcast');
        $query->set('post_type',$post_type);
        return $query;
    }
}


function twentyseventeen_entry_footer() {

	/* translators: used between list items, there is a space after the comma */
	$separate_meta = __( ', ', 'twentyseventeen' );

	// Get Categories for posts.
	$categories_list = get_the_category_list( $separate_meta );

	// Get Tags for posts.
	$tags_list = get_the_tag_list( '', $separate_meta );

	// We don't want to output .entry-footer if it will be empty, so make sure its not.
	if ( ( ( twentyseventeen_categorized_blog() && $categories_list ) || $tags_list ) || get_edit_post_link() ) {

		echo '<footer class="entry-footer">';

			if ( 'post' === get_post_type() || 'podcast' === get_post_type() ) {
				if ( ( $categories_list && twentyseventeen_categorized_blog() ) || $tags_list ) {
					echo '<span class="cat-tags-links">';

						// Make sure there's more than one category before displaying.
						if ( $categories_list && twentyseventeen_categorized_blog() ) {
							echo '<span class="cat-links">' . twentyseventeen_get_svg( array( 'icon' => 'folder-open' ) ) . '<span class="screen-reader-text">' . __( 'Categories', 'twentyseventeen' ) . '</span>' . $categories_list . '</span>';
						}

						if ( $tags_list ) {
							echo '<span class="tags-links">' . twentyseventeen_get_svg( array( 'icon' => 'hashtag' ) ) . '<span class="screen-reader-text">' . __( 'Tags', 'twentyseventeen' ) . '</span>' . $tags_list . '</span>';
						}

					echo '</span>';
				}
			}

			twentyseventeen_edit_link();

		echo '</footer> <!-- .entry-footer -->';
	}
}



/**
 * Create a shortcode to insert content of a page of specified slug
 */
function insertPersonProfile($atts, $content = null) {
  // Default output if no pageid given
  $output = NULL;

  // extract atts and assign to array
  extract(shortcode_atts(array(
    "template" => 'list', // default value could be placed here
    "name" => '',
    "category_name" => ''
  ), $atts));


  // make sure we aren't calling both id and cat at the same time
		if ( isset( $name ) && $name != '' && isset( $category_name ) && $category_name != '' ) {
			return "<p>People Directory error: You cannot set both a single person's name and a category name. Please choose one or the other.</p>";
		}

		$query_args = array(
			'post_type'      => 'staff',
			'posts_per_page' => - 1
		);

		// check if it's a single staff member first, since single members won't be ordered
		if ( ( isset( $name ) && $name != '' ) && ( ! isset( $category_name ) || $category_name == '' ) ) {
			$query_args['name'] = $name;
		}
		// ends single staff

		// check if we're returning a staff category
		if ( ( isset( $category_name ) && $category_name != '' ) && ( ! isset( $name ) || $name == '' ) ) {
            $cats_query = array();

            $cats = explode( ',', $category_name );

            if (count($cats) > 1) {
                $cats_query['relation'] = $params['cat_relation'];
            }

            foreach ($cats as $cat) {
                $cats_query[] = array(
                    'taxonomy' => 'staff_category',
                    'terms'    => $cat,
                    'field'    => "slug"
                );
            }

            $query_args['tax_query'] = $cats_query;
		}

		if ( isset( $orderby ) && $orderby != '' ) {
			$query_args['orderby'] = $orderby;
		}
		if ( isset( $order ) && $order != '' ) {
			$query_args['order'] = $order;
		}

    // echo "<p>" . print_r($query_args) . "</p>";

    $customQuery = new WP_query( $query_args );
    // echo "<p>" . $pageContent->request . "</p>";

    $output .= "<div class='people_directory-" . $template . "'>";
    ob_start();
    while ($customQuery->have_posts()) : $customQuery->the_post();
      get_template_part( 'template-parts/staff/' . $template );
      // $output = get_the_content();
    endwhile;
    $output .= ob_get_clean();
    $output .= "</div>";
    wp_reset_postdata();
    return $output;
}
add_shortcode('people_directory', 'insertPersonProfile');


add_shortcode("mutliline", "convert_multiline");
function convert_multiline($atts) {
    $field = $atts["field"];
    $newContent = "";

    if(!empty($field)) {
        $newContent = nl2br($field);
    }

    return $newContent;
}

add_shortcode( 'gallery_start', 'galleryStart' );
function galleryStart() {
  return '<div class="person-directory-gallery-wrapper">';
}

add_shortcode( 'gallery_end', 'galleryEnd' );
function galleryEnd() {
  return '</div>';
}

add_action( 'pre_get_posts', 'blog_page_size', 1 );
function blog_page_size( $query ) {
    if ( is_admin() || ! $query->is_main_query() )
        return;
    if ( $query->is_home() && $query->is_main_query() ) {
        $query->set( 'posts_per_page', 2 );
        return;
    }
}



/**
 * Count our number of active panels.
 *
 * Primarily used to see if we have any panels active, duh.
 */
function twentyseventeen_banner_count() {

	$panel_count = 0;
	$num_sections = apply_filters( 'twentyseventeen_front_page_banner', 1 );

	// Create a setting and control for each of the sections available in the theme.
	for ( $i = 1; $i < ( 1 + $num_sections ); $i++ ) {
		if ( get_theme_mod( 'banner_' . $i ) ) {
			$panel_count++;
		}
	}

	return $panel_count;
}

/**
 * Display a front page section.
 *
 * @param WP_Customize_Partial $partial Partial associated with a selective refresh request.
 * @param integer              $id Front page section to display.
 */
function twentyseventeen_front_page_banner( $partial = null, $id = 0 ) {
	if ( is_a( $partial, 'WP_Customize_Partial' ) ) {
		// Find out the id and set it up during a selective refresh.
		global $twentyseventeencounter;
		$id = str_replace( 'banner_', '', $partial->id );
		$twentyseventeencounter = $id;
	}

	global $post; // Modify the global post object before setting up post data.
	if ( get_theme_mod( 'banner_' . $id ) ) {
		$post = get_post( get_theme_mod( 'banner_' . $id ) );
		setup_postdata( $post );
		set_query_var( 'banner', $id );

		get_template_part( 'template-parts/page/content', 'front-page-banner' );

		wp_reset_postdata();
	} elseif ( is_customize_preview() ) {
		// The output placeholder anchor.
		echo '<article class="panel-placeholder panel twentyseventeen-panel twentyseventeen-panel' . $id . '" id="banner' . $id . '"><span class="twentyseventeen-panel-title">' . sprintf( __( 'Front Page Banner Placeholder', 'twentyseventeen' ), $id ) . '</span></article>';
	}
}



function milfam_customize_register( $wp_customize ) {

	$num_sections = apply_filters( 'twentyseventeen_front_page_banner', 1 );

	// Create a setting and control for each of the sections available in the theme.
	for ( $i = 1; $i < ( 1 + $num_sections ); $i++ ) {
		$wp_customize->add_setting( 'banner_' . $i, array(
			'default'           => false,
			'sanitize_callback' => 'absint',
			'transport'         => 'postMessage',
		) );

		$wp_customize->add_control( 'banner_' . $i, array(
			/* translators: %d is the front page section number */
			'label'          => sprintf( __( 'Front Page Banner', 'twentyseventeen' ), $i ),
			'description'    => ( 1 !== $i ? '' : __( 'Select pages to display as a banner. Empty section will not be displayed.', 'twentyseventeen' ) ),
			'section'        => 'theme_options',
			'type'           => 'dropdown-pages',
			'allow_addition' => true,
			'active_callback' => 'twentyseventeen_is_static_front_page',
		) );

		$wp_customize->selective_refresh->add_partial( 'banner_' . $i, array(
			'selector'            => '#banner' . $i,
			'render_callback'     => 'twentyseventeen_front_page_banner',
			'container_inclusive' => true,
		) );
	}
}
add_action( 'customize_register', 'milfam_customize_register' );

function disable_website_field_in_comments($fields)
{
  if(isset($fields['url']))
    unset($fields['url']);
    return $fields;
}
add_filter('comment_form_default_fields', 'disable_website_field_in_comments');
