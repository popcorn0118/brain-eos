<?php
/**
 * 通用 Archive Template
 * 支援一般文章和自定義文章類型的 Archive 頁面
 * 支援篩選功能：年度、分類、關鍵字搜尋
 * 
 * @package parkonehealth-beauty
 */

get_header();

// 自動判斷當前的文章類型
$current_post_type = get_post_type() ?: get_query_var('post_type');
if (empty($current_post_type)) {
    $current_post_type = 'post'; // 預設為一般文章
}

// 根據文章類型設定相關的分類法（taxonomy）
$taxonomy_map = array(
    'post' => 'category',      // 一般文章使用 category
    'case' => 'case-category',     // 案例使用 case-type
    'product' => 'product-category', // 產品使用 product-category（範例）
    // 可以根據需要添加更多文章類型和對應的分類法
);

$current_taxonomy = isset($taxonomy_map[$current_post_type]) ? $taxonomy_map[$current_post_type] : 'category';

// 根據文章類型設定標籤分類法
$tag_taxonomy_map = array(
    'post' => 'post_tag',      // 一般文章使用 post_tag
    'case' => 'case-tag',      // 案例使用 case-tag
    'product' => 'product-tag', // 產品使用 product-tag（範例）
);

$current_tag_taxonomy = isset($tag_taxonomy_map[$current_post_type]) ? $tag_taxonomy_map[$current_post_type] : 'post_tag';

// 根據文章類型設定 URL 基礎路徑
$base_url_map = array(
    'post' => 'blog',          // 一般文章
    'case' => 'cases',         // 案例
    'product' => 'products',   // 產品（範例）
);

$base_url = isset($base_url_map[$current_post_type]) ? $base_url_map[$current_post_type] : 'blog';

// 獲取 URL 參數（根據文章類型調整參數名稱）
$category_param = $current_post_type === 'case' ? 'case-type' : 'category';
$category_query = isset($_GET[$category_param]) ? sanitize_text_field($_GET[$category_param]) : '';
$page_query = isset($_GET['paged']) ? absint($_GET['paged']) : (isset($_GET['page-query']) ? absint($_GET['page-query']) : 1);
$search_query = isset($_GET['search-query']) ? sanitize_text_field($_GET['search-query']) : (isset($_GET['s']) ? get_search_query() : '');
$year_query = isset($_GET['year-query']) ? sanitize_text_field($_GET['year-query']) : '';

// 處理分類查詢條件
$is_category_query = (empty($category_query) || ($category_query == 'all')) ? null : $category_query;
$is_year_query = (empty($year_query) || ($year_query == 'all')) ? null : $year_query;

// 構建 WP_Query 參數
$query_args = array(
    'post_type' => $current_post_type,
    'post_status' => 'publish',
    'posts_per_page' => 10,
    'paged' => $page_query,
    'orderby' => 'date',
    'order' => 'DESC'
);

// 添加搜尋條件
if (!empty($search_query)) {
    $query_args['s'] = $search_query;
}

// 添加分類篩選
if ($is_category_query) {
    $query_args['tax_query'] = array(
        array(
            'taxonomy' => $current_taxonomy,
            'field' => 'slug',
            'terms' => $is_category_query,
        )
    );
}

// 添加年份篩選
if ($is_year_query) {
    $query_args['date_query'] = array(
        array('year' => $is_year_query)
    );
}

// 執行查詢
$archive_query = new WP_Query($query_args);
$total_count = $archive_query->found_posts;
$max_page = $archive_query->max_num_pages;

// 分頁顯示設定
$showPage = 5;

// 根據文章類型設定顯示文字
$type_labels = array(
    'post' => array(
        'name' => '文章',
        'category_label' => '分類',
        'year_label' => '年度'
    ),
    'case' => array(
        'name' => '案例',
        'category_label' => '案例分類',
        'year_label' => '年度'
    ),
    'product' => array(
        'name' => '產品',
        'category_label' => '產品分類',
        'year_label' => '年度'
    )
);

$current_labels = isset($type_labels[$current_post_type]) ? $type_labels[$current_post_type] : $type_labels['post'];

/**
 * 生成預設特色圖片的函數
 * @param string $title 文章標題
 * @param int $width 圖片寬度
 * @param int $height 圖片高度
 * @return string SVG 圖片的 data URI
 */
function generate_default_featured_image($title = '', $width = 600, $height = 400) {
    // 取得標題的第一個字符作為顯示
    $first_char = mb_substr($title, 0, 1, 'UTF-8');
    if (empty($first_char)) {
        $first_char = '文';
    }
    
    // 創建 SVG
    $svg = '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">
        <rect width="100%" height="100%" fill="#cccccc"/>
        <text x="50%" y="50%" font-family="Arial, sans-serif" font-size="64" font-weight="bold" 
              text-anchor="middle" dominant-baseline="middle" fill="#999999">' . 
              htmlspecialchars($first_char, ENT_QUOTES, 'UTF-8') . 
        '</text>
    </svg>';
    
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

/**
 * 獲取文章摘要的函數
 * @param int $post_id 文章 ID
 * @param int $length 摘要長度（字數）
 * @return string 文章摘要
 */
function get_post_excerpt_or_content($post_id, $length = 50) {
    // 首先檢查是否有手動設定的摘要
    $excerpt = get_the_excerpt($post_id);
    
    if (!empty($excerpt)) {
        return $excerpt;
    }
    
    // 如果沒有摘要，從內容中擷取
    $content = get_post_field('post_content', $post_id);
    
    if (!empty($content)) {
        // 移除所有 HTML 標籤和短代碼
        $content = wp_strip_all_tags(strip_shortcodes($content));
        
        // 移除多餘的空白字符
        $content = preg_replace('/\s+/', ' ', trim($content));
        
        // 擷取指定長度的文字
        if (mb_strlen($content, 'UTF-8') > $length) {
            $excerpt = mb_substr($content, 0, $length, 'UTF-8') . '...';
        } else {
            $excerpt = $content;
        }
        
        return $excerpt;
    }
    
    return ''; // 如果都沒有內容，返回空字串
}
?>

<div class="ast-container primary-container">
    <div id="" class="content-area primary">
        
        
        <main id="main" class="site-main article-list <?php echo esc_attr($current_post_type); ?> animated-slow animated fadeInUp">
            
            <?php astra_content_top(); ?>

            <!-- 篩選搜尋區域 -->
            <div class="archive-filters">
                <div class="filter-row">
                    
                    <!-- 年度選擇下拉選單 -->
                    <div class="filter-item">
                        <select class="filter-select year-filter" data-param="year-query">
                            <option value="">選擇<?php echo esc_html($current_labels['year_label']); ?></option>
                            <option value="all" <?php selected($year_query, 'all'); ?>>全部<?php echo esc_html($current_labels['year_label']); ?></option>
                            <?php 
                                // 使用 WordPress 標準方式獲取年份列表
                                global $wpdb;
                                $years = $wpdb->get_col($wpdb->prepare("
                                    SELECT DISTINCT YEAR(post_date) 
                                    FROM {$wpdb->posts} 
                                    WHERE post_status = 'publish' 
                                    AND post_type = %s 
                                    ORDER BY post_date DESC
                                ", $current_post_type));
                                
                                foreach ($years as $year): 
                            ?>
                                <option value="<?php echo esc_attr($year); ?>" <?php selected($year_query, $year); ?>>
                                    <?php echo esc_html($year); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 分類選擇下拉選單 -->
                    <div class="filter-item">
                        <select class="filter-select category-filter" data-param="<?php echo esc_attr($category_param); ?>">
                            <option value="">選擇<?php echo esc_html($current_labels['category_label']); ?></option>
                            <option value="all" <?php selected($category_query, 'all'); ?>>全部<?php echo esc_html($current_labels['category_label']); ?></option>
                            <?php
                                // 獲取當前文章類型的分類列表
                                $categories = get_terms(array(
                                    'taxonomy' => $current_taxonomy,
                                    'parent' => 0,
                                    'hide_empty' => true
                                ));
                                
                                if (!empty($categories) && !is_wp_error($categories)):
                                    foreach ($categories as $term): 
                            ?>
                                <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($category_query, $term->slug); ?>>
                                    <?php echo esc_html($term->name); ?>
                                </option>
                            <?php 
                                    endforeach;
                                endif; 
                            ?>
                        </select>
                    </div>
                    
                    <!-- 關鍵字搜尋 -->
                    <div class="filter-item search-item">
                        <input type="text" 
                               class="search-input" 
                               placeholder="搜尋關鍵字..." 
                               value="<?php echo esc_attr($search_query); ?>">
                        <button class="search-btn" type="button">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M21 21L16.514 16.506L21 21ZM19 10.5C19 15.194 15.194 19 10.5 19C5.806 19 2 15.194 2 10.5C2 5.806 5.806 2 10.5 2C15.194 2 19 5.806 19 10.5Z" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 搜尋結果資訊顯示 -->
            <div class="posts-count">
                <?php
                    // 顯示搜尋結果統計
                    if (!empty($search_query)) {
                        printf('共有 %d 筆「%s」的搜尋結果', $total_count, esc_html($search_query));
                    } else {
                        printf('共有 %d 筆%s', $total_count, esc_html($current_labels['name']));
                    }
                ?>
                
                <?php if (!empty($category_query) || !empty($search_query) || !empty($year_query)): ?>
                    <!-- 清除所有篩選條件連結 -->
                    <a class="clear-all" href="<?php echo esc_url(home_url($base_url)); ?>">清除全部篩選詞</a>
                <?php endif; ?>
            </div>

            <!-- 文章列表內容區域 -->
            <?php if ($archive_query->have_posts()): ?>
                <div class="archive-posts-grid">
                    <?php while ($archive_query->have_posts()): $archive_query->the_post(); ?>
                        <?php
                            // 獲取文章相關資訊
                            $post_id = get_the_ID();
                            $title = get_the_title();
                            $permalink = get_permalink();
                            $date = get_the_date('Y.m.d');
                            
                            // 使用新的摘要函數
                            $excerpt = get_post_excerpt_or_content($post_id, 30);
                            
                            // 根據文章類型獲取對應的分類和標籤
                            $categories = get_the_terms($post_id, $current_taxonomy);
                            $tags = get_the_terms($post_id, $current_tag_taxonomy);
                            
                            // 獲取特色圖片，如果沒有則使用預設圖片
                            $thumbnail_url = get_the_post_thumbnail_url($post_id, 'medium');
                            $has_thumbnail = !empty($thumbnail_url);
                            
                            // 如果沒有特色圖片，生成預設圖片
                            if (!$has_thumbnail) {
                                $thumbnail_url = generate_default_featured_image($title, 600, 400);
                            }
                        ?>
                        
                        <article <?php post_class('archive-post-item'); ?>>
                            <!-- 特色圖片區域 -->
                            <div class="post-thumbnail">
                                <a href="<?php echo esc_url($permalink); ?>" 
                                   class="post-image-link <?php echo !$has_thumbnail ? 'default-image' : ''; ?>"
                                   style="background-image: url('<?php echo esc_url($thumbnail_url); ?>');"
                                   title="<?php echo esc_attr($title); ?>">
                                </a>
                            </div>
                            
                            <!-- 文章內容區域 -->
                            <div class="post-content">
                                <!-- 文章資訊（日期、分類、標籤） -->
                                <div class="post-meta">
                                    <span class="post-date"><?php echo esc_html($date); ?></span>
                                    <span class="meta-separator">|</span>
                                    
                                    <!-- 顯示分類 -->
                                    <?php if ($categories && !is_wp_error($categories)): ?>
                                        <span class="post-categories">
                                            <?php 
                                                $cat_names = array();
                                                foreach ($categories as $category) {
                                                    $cat_names[] = $category->name;
                                                }
                                                echo esc_html(implode('、', $cat_names));
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <!-- 顯示標籤 -->
                                    <?php if ($tags && !is_wp_error($tags)): ?>
                                        <span class="meta-separator">、</span>
                                        <span class="post-tags">
                                            <?php 
                                                $tag_names = array();
                                                foreach ($tags as $tag) {
                                                    $tag_names[] = $tag->name;
                                                }
                                                echo esc_html(implode('、', $tag_names));
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- 文章標題 -->
                                <h2 class="post-title">
                                    <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
                                </h2>
                                
                                <!-- 文章摘要 -->
                                <?php if (!empty($excerpt)): ?>
                                    <div class="post-excerpt">
                                        <?php echo wp_kses_post($excerpt); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- 閱讀更多按鈕 -->
                                <div class="read-more-wrapper">
                                    <a class="ast-button ast-button-primary" href="<?php echo esc_url($permalink); ?>">
                                        <?php echo $current_post_type === 'case' ? '了解更多' : '閱讀更多'; ?>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <!-- 分頁導航 -->
                <?php if ($total_count > 0 && $max_page > 1): ?>
                    <div class="archive-pagination">
                        <?php
                            // 使用 WordPress 內建分頁函式，並保持篩選參數
                            $pagination_args = array(
                                'total' => $max_page,
                                'current' => $page_query,
                                'prev_text' => '‹',
                                'next_text' => '›',
                                'type' => 'list',
                                'end_size' => 2,
                                'mid_size' => 1,
                                'add_args' => array(
                                    $category_param => $category_query,
                                    'search-query' => $search_query,
                                    'year-query' => $year_query
                                )
                            );
                            
                            // 移除空值參數
                            $pagination_args['add_args'] = array_filter($pagination_args['add_args']);
                            
                            echo paginate_links($pagination_args);
                        ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- 沒有找到文章時的提示 -->
                <div class="no-posts">
                    <p>沒有找到符合條件的<?php echo esc_html($current_labels['name']); ?>。</p>
                </div>
            <?php endif; ?>
            
            <?php 
            // 重置查詢，避免影響其他部分
            wp_reset_postdata(); 
            ?>
            
            <?php astra_content_bottom(); ?>
            
        </main>
        
    </div>
  
</div>

<!-- 樣式和 JavaScript -->
<style>
/* 篩選區域樣式 */
.archive-filters {
	margin-top: 50px;
    margin-bottom: 2rem;
    padding: 2rem;
    background: rgba(255, 255, 255, 0.5);
}

.filter-row {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: center;
}

.filter-item {
    position: relative;
}

.filter-select {
    appearance: none;
    background: white url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="8" viewBox="0 0 12 8"><path fill="%23666" d="M1.41 0L6 4.59 10.59 0 12 1.41 6 7.41 0 1.41z"/></svg>') no-repeat right 1rem center;
    border: 1px solid #e0e0e0;
    padding: 0.75rem 2.5rem 0.75rem 1.5rem;
    font-size: 1rem;
    color: #666;
    min-width: 180px;
	height: 56px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: #4CAF50;
    box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
}

.search-item {
    position: relative;
    flex: 1;
    min-width: 300px;
}

.search-input {
    width: 100%;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 25px;
    padding: 0.75rem 3.5rem 0.75rem 1.5rem;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #4CAF50;
    box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
}

.search-btn {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #666;
    padding: 0.25rem;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.search-btn:hover {
    color: #4CAF50;
    background: rgba(76, 175, 80, 0.1);
}

/* 文章計數和清除篩選 */
.posts-count {
    margin: 2rem 0 1rem;
    font-size: 1.1rem;
    color: #333;
    font-weight: 500;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.clear-all {
    color: #4CAF50;
    text-decoration: none;
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    border: 1px solid #4CAF50;
    border-radius: 20px;
    transition: all 0.3s ease;
}

.clear-all:hover {
    background: #4CAF50;
    color: white;
}

/* 文章網格布局 */
.archive-posts-grid {
    display: grid;
    gap: 2rem;
    margin-bottom: 3rem;
	grid-template-columns: 1fr;
}

/* 桌面版：每行兩個項目 */
@media (min-width: 769px) {
   
}

/* 手機版：每行一個項目 */
@media (max-width: 768px) {
   
    
    .filter-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-item {
        width: 100%;
    }
    
    .filter-select,
    .search-item {
        min-width: 100%;
    }
    
    .posts-count {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
}

/* 文章項目樣式 */
	
.primary-container{
	width: 100%;
}	
.primary{
	width: 100%;
}	
.archive-post-item {
    display: flex;
    background: transparent;
/*     border-radius: 15px; */
    overflow: hidden;
/*     box-shadow: 0 2px 10px rgba(0,0,0,0.1); */
    transition: all 0.3s ease;
    border: none;
/* 	padding-bottom: 0px !important; */
}

.archive-post-item:hover {
	background: transparent;
    transform: translateY(-5px);
	
/*     box-shadow: 0 8px 25px rgba(0,0,0,0.15); */
}
	
.post-thumbnail {
    overflow: hidden;
    aspect-ratio: 600 / 400; /* 維持 600x400 比例 */
}

.post-image-link {
    display: block;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    transition: transform 0.3s ease;
    text-decoration: none;
}

/* 預設圖片的特殊樣式 */
.post-image-link.default-image {
    background-size: contain; /* 讓預設的 SVG 圖片不會被裁切 */
    background-color: #cccccc; /* 確保背景色一致 */
}

.archive-post-item:hover .post-image-link {
    transform: scale(1.05);
}

.post-content {
    flex: 1;
    padding: 0 1.5rem 2.5rem 1.5rem;
    display: flex;
    flex-direction: column;
}

.post-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
    color: #666;
    flex-wrap: wrap;
}

.meta-separator {
    color: #ccc;
}

.post-title {
    margin: 0 0 1rem 0;
    font-size: 1.25rem;
    font-weight: 600;
    line-height: 1.4;
}

.post-title a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.post-title a:hover {
    color: #4CAF50;
}

.post-excerpt {
    color: #666;
    line-height: 1.6;
    margin-bottom: 1.5rem;
    flex-grow: 1;
}

.read-more-wrapper {
    margin-top: auto;
}

/* 手機版文章項目調整 */
@media (max-width: 768px) {
    .archive-post-item {
        flex-direction: column;
    }
    
    .post-thumbnail {
        flex: none;
        height: 200px;
    }
    
    .post-content {
        padding: 1.25rem;
    }
}

/* 分頁樣式 */
.archive-pagination {
    display: flex;
    justify-content: center;
    margin-top: 3rem;
}

.archive-pagination .page-numbers {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    gap: 0.5rem;
}

.archive-pagination .page-numbers li {
    display: inline;
}

.archive-pagination .page-numbers a,
.archive-pagination .page-numbers span {
    display: inline-block;
    padding: 0.75rem 1rem;
    color: #666;
    text-decoration: none;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    transition: all 0.3s ease;
    min-width: 45px;
    text-align: center;
}

.archive-pagination .page-numbers a:hover {
    background: #4CAF50;
    color: white;
    border-color: #4CAF50;
}

.archive-pagination .page-numbers .current {
    background: #4CAF50;
    color: white;
    border-color: #4CAF50;
}

/* 無文章提示 */
.no-posts {
    text-align: center;
    padding: 4rem 2rem;
    color: #666;
}

.no-posts p {
    font-size: 1.1rem;
    margin: 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 獲取當前頁面的基礎 URL
    const baseUrl = '<?php echo esc_js(home_url($base_url)); ?>';
    
    // 篩選功能
    const yearFilter = document.querySelector('.year-filter');
    const categoryFilter = document.querySelector('.category-filter');
    const searchInput = document.querySelector('.search-input');
    const searchBtn = document.querySelector('.search-btn');
    
    function performFilter() {
        const year = yearFilter?.value || '';
        const category = categoryFilter?.value || '';
        const search = searchInput?.value || '';
        
        let url = baseUrl;
        let params = new URLSearchParams();
        
        // 根據文章類型設定正確的參數名稱
        const categoryParam = categoryFilter?.getAttribute('data-param') || 'category';
        
        if (year && year !== '') params.append('year-query', year);
        if (category && category !== '') params.append(categoryParam, category);
        if (search && search !== '') params.append('search-query', search);
        
        if (params.toString()) {
            url += '?' + params.toString();
        }
        
        window.location.href = url;
    }
    
    // 綁定事件
    if (yearFilter) yearFilter.addEventListener('change', performFilter);
    if (categoryFilter) categoryFilter.addEventListener('change', performFilter);
    if (searchBtn) searchBtn.addEventListener('click', performFilter);
    
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performFilter();
            }
        });
    }
});
</script>

<?php get_footer(); ?>