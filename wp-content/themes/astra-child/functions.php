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

	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

/*移除會員中心下載區塊*/
add_filter( 'woocommerce_account_menu_items', 'remove_my_account_downloads', 999 );

function remove_my_account_downloads( $items ) {
    unset($items['downloads']);
    return $items;
}
/*新增Google Analytic追蹤碼*/
function insert_google_analytics() {
    ?>
    <!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-H3E04MDSH5"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', 'G-H3E04MDSH5');
	</script>
    <?php
}
add_action('wp_head', 'insert_google_analytics');

add_action( 'init', function() {

    // 防止重複註冊 "實績案例分類" (Taxonomy)
    if ( !taxonomy_exists( 'case-category' ) ) {
        register_taxonomy( 'case-category', array( 'case' ), array(
            'labels' => array(
                'name' => '實績案例分類',
                'singular_name' => '實績案例分類',
                'menu_name' => '實績案例分類',
                'all_items' => '所有實績案例分類',
                'edit_item' => '編輯實績案例分類',
                'view_item' => '檢視實績案例分類',
                'update_item' => '更新實績案例分類',
                'add_new_item' => '新增實績案例分類',
                'new_item_name' => '新增實績案例分類',
                'search_items' => '搜尋實績案例分類',
                'popular_items' => '熱門實績案例分類',
                'separate_items_with_commas' => '用逗號分隔實績案例分類',
                'add_or_remove_items' => '新增或移除實績案例分類',
                'choose_from_most_used' => '從最常用的實績案例分類中選擇',
                'not_found' => '未找到實績案例分類',
                'no_terms' => '沒有實績案例分類',
                'items_list_navigation' => '實績案例分類列表導覽',
                'items_list' => '實績案例分類列表',
                'back_to_items' => '← 返回實績案例分類',
                'item_link' => '實績案例分類連結',
                'item_link_description' => '實績案例分類連結敘述',
            ),
            'public' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
        ));
    }

    // 防止重複註冊 "實績案例" (Custom Post Type)
    if ( !post_type_exists( 'case' ) ) {
        register_post_type( 'case', array(
            'labels' => array(
                'name' => '實績案例',
                'singular_name' => '實績案例',
                'menu_name' => '實績案例',
                'all_items' => '所有實績案例',
                'edit_item' => '編輯實績案例',
                'view_item' => '檢視實績案例',
                'view_items' => '檢視實績案例',
                'add_new_item' => '新增實績案例',
                'add_new' => '新增實績案例',
                'new_item' => '新實績案例',
                'parent_item_colon' => '上層實績案例：',
                'search_items' => '搜尋實績案例',
                'not_found' => '未找到實績案例',
                'not_found_in_trash' => '垃圾桶中未找到實績案例',
                'archives' => '實績案例列表',
                'attributes' => '實績案例屬性',
                'insert_into_item' => '插入實績案例',
                'uploaded_to_this_item' => '上傳至此實績案例',
                'filter_items_list' => '篩選實績案例列表',
                'filter_by_date' => '按日期篩選實績案例',
                'items_list_navigation' => '實績案例列表導覽',
                'items_list' => '實績案例列表',
                'item_published' => '實績案例已發佈。',
                'item_published_privately' => '實績案例已私人發佈。',
                'item_reverted_to_draft' => '實績案例已回復至草稿。',
                'item_scheduled' => '實績案例已排程。',
                'item_updated' => '實績案例已更新。',
                'item_link' => '實績案例連結',
                'item_link_description' => '實績案例的連結。',
            ),
            'public' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-admin-post',
            'supports' => array(
                'title',
                'editor',
                'excerpt',
                'revisions',
                'page-attributes',
                'thumbnail',
                'custom-fields',
            ),
            'taxonomies' => array( 'case-category' ),
            'has_archive' => 'case',
            'delete_with_user' => false,
        ));
    }
    
});
