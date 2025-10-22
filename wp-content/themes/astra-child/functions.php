<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {
	wp_enqueue_style( 'slick-css', get_stylesheet_directory_uri() . '/assets/css/slick.css', array(), CHILD_THEME_ASTRA_CHILD_VERSION );
	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );
	
	wp_enqueue_script( 'slick-js', get_stylesheet_directory_uri() . '/assets/js/slick.min.js', array(), '', true );
	wp_enqueue_script('main', get_stylesheet_directory_uri().'/assets/js/main.js', array(), '', true);
}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );


/*新增Google Analytic追蹤碼*/
function insert_google_analytics() {
    ?>
    <!-- Google tag (gtag.js) -->
	<!-- <script async src="https://www.googletagmanager.com/gtag/js?id=G-H3E04MDSH5"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', 'G-H3E04MDSH5');
	</script> -->
    <?php
}
add_action('wp_head', 'insert_google_analytics');

/*只給能管理站台的人上傳 SVG*/
add_filter('upload_mimes', function ($mimes) {
  if (current_user_can('manage_options')) {
    $mimes['svg'] = 'image/svg+xml';
  }
  return $mimes;
});

// footer copyright
add_shortcode('copyright', function () {
    $year = date_i18n('Y');
    $name = get_bloginfo('name');

    return
        '<small class="site-copyright">' .
            '<span class="copyright-prefix">' . esc_html("Copyright © {$year} {$name}") . '</span> ' .
            '<span class="line">|</span> ' .
            '<span class="copyright-powered">Powered by Eos</span>' .
        '</small>';
});


// 客製化 post type
function create_post_type() {
	register_post_type( 'team',
		array(
			'labels' 				=> array(
				'name' 				=> __( '醫師介紹' ),
				'singular_name' 	=> __( '醫師介紹' )
			),
			'public' 				=> true,
			'has_archive' 			=> true,
			'menu_icon' 			=> 'dashicons-groups',
			'supports' 				=> array('title', 'editor', 'thumbnail', 'excerpt', 'revisions'),
			'taxonomies' 			=> array('team-type', 'team-tag'),
			'capability_type' 		=> 'page',
			'map_meta_cap'			=> true,
			// 'show_in_rest'      	=> true, // To use Gutenberg editor.
		)
	);

	register_post_type( 'case',
		array(
			'labels' 				=> array(
				'name' 				=> __( '實績案例' ),
				'singular_name' 	=> __( '實績案例' )
			),
			'public' 				=> true,
			'has_archive' 			=> true,
			'menu_icon' 			=> 'dashicons-welcome-write-blog',
			'supports' 				=> array('title', 'editor', 'thumbnail', 'excerpt', 'revisions'),
			'taxonomies' 			=> array('case-type', 'case-tag'),
			'capability_type' 		=> 'page',
			'map_meta_cap'			=> true,
			// 'show_in_rest'      	=> true, // To use Gutenberg editor.
			// 'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'taxonomies'  ),
		)
	);

	flush_rewrite_rules();
}
add_action( 'init', 'create_post_type' );

// 新增 Taxonomy 給客製化的 post type
function create_custom_taxonomy() {
	  
	// 分類
	register_taxonomy('team-type', array('team'), 
		array(
			'labels' 				=> array(
			'name' 				=> __( '醫師介紹分類' ),
			'singular_name' 	=> __( '醫師介紹分類' )
		),
		'show_ui' 				=> true,
		'show_admin_column' 	=> true,
		'query_var' 			=> true,
		'hierarchical' 			=> true,
		'rewrite' 				=> array( 'slug' => 'team-type' ),
		)
	);


	register_taxonomy('case-type',array('case'), 
		array(
			'labels' 				=> array(
			'name' 				=> __( '實績案例分類' ),
			'singular_name' 	=> __( '實績案例分類' )
		),
		'show_ui' 				=> true,
		'show_admin_column' 	=> true,
		'query_var' 			=> true,
		'hierarchical' 			=> true,
		'rewrite' 				=> array( 'slug' => 'case-type' ),
		)
	);

	// 標籤
	register_taxonomy('case-tag',array('case'), 
		array(
			'labels' 				=> array(
			'name' 				=> __( '實績案例標籤' ),
			'singular_name' 	=> __( '實績案例標籤' )
		),
		'show_ui' 				=> true,
		'show_admin_column' 	=> true,
		'query_var' 			=> true,
		'hierarchical' 			=> true,
		'rewrite' 				=> array( 'tag' => 'case-tag' ),
		)
	);

}
add_action( 'init', 'create_custom_taxonomy', 0 );


