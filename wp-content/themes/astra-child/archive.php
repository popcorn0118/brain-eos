<?php
/**
 * Archive (Category/Tag/Tax/CPT Archive)
 * 與 page-list.php 結構一致；僅「加上」archive 相關 class 與 Banner 邏輯。
 */

get_header();

/** ─────────────────────────────────────────────────────────
 * 小工具：將 ACF 圖片欄位值（ID / array / URL）解析為 URL
 * ───────────────────────────────────────────────────────── */
$resolve_img_url = function ($src) {
  if (!$src) return '';
  if (is_array($src))       return $src['url'] ?? '';
  if (is_numeric($src))     return wp_get_attachment_image_url($src, 'full') ?: '';
  return esc_url_raw($src);
};

/** ─────────────────────────────────────────────────────────
 * 基本物件與 post_type 判定（先決定 post_type，後面才用得到）
 * ───────────────────────────────────────────────────────── */
$qo = get_queried_object();
$post_type = 'post';

if (is_post_type_archive()) {
  $pt = get_query_var('post_type');
  $post_type = is_array($pt) ? reset($pt) : ($pt ?: 'post');
} elseif (is_tax() || is_category() || is_tag()) {
  if (!empty($qo->taxonomy)) {
    $tx = get_taxonomy($qo->taxonomy);
    if ($tx && !empty($tx->object_type)) {
      $post_type = is_array($tx->object_type) ? reset($tx->object_type) : 'post';
    }
  }
}

/** ─────────────────────────────────────────────────────────
 * 對齊 page-list.php 的代稱（slug）與 taxonomy 設定
 * ───────────────────────────────────────────────────────── */
$slug_map = [
  'post' => 'article',
  'case' => 'cases',
];
$slug = $slug_map[$post_type] ?? sanitize_html_class($post_type);

$tax_map = [
  'post' => ['tax_cat' => 'category',  'tax_tag' => 'post_tag'],
  'case' => ['tax_cat' => 'case-type', 'tax_tag' => 'case-tag'],
];
$cfg_tax = $tax_map[$post_type] ?? $tax_map['post'];

/** ─────────────────────────────────────────────────────────
 * Banner：優先用 Term ACF 的 img_bg；沒有就回退到同代稱 Page 的 img_bg
 * ───────────────────────────────────────────────────────── */
$bg_url = '';
if ($qo && isset($qo->taxonomy, $qo->term_id)) {
  $bg_url = $resolve_img_url(get_field('img_bg', $qo));
}
if (!$bg_url && ($p = get_page_by_path($slug))) {
  $bg_url = $resolve_img_url(get_field('img_bg', $p->ID));
}

/** ─────────────────────────────────────────────────────────
 * Archive Title 與前綴（分類 / 標籤）
 * ───────────────────────────────────────────────────────── */
if (is_category()) {
  $archive_title = single_cat_title('', false);
} elseif (is_tag()) {
  $archive_title = single_tag_title('', false);
} elseif (is_tax()) {
  $archive_title = single_term_title('', false);
} elseif (is_post_type_archive()) {
  $archive_title = post_type_archive_title('', false);
} elseif (is_author()) {
  $archive_title = get_the_author();
} elseif (is_date()) {
  $archive_title = get_the_date(get_option('date_format'));
} else {
  $archive_title = wp_strip_all_tags(get_the_archive_title());
}

$prefix = '';
if (is_category() || (is_tax() && is_tax($cfg_tax['tax_cat']))) {
  $prefix = '分類：';
} elseif (is_tag() || (is_tax() && is_tax($cfg_tax['tax_tag']))) {
  $prefix = '標籤：';
}

/** ─────────────────────────────────────────────────────────
 * 組裝 archive class（給版型/樣式掛鉤）
 * ───────────────────────────────────────────────────────── */
$archive_classes = ['is-archive', 'post-type-' . sanitize_html_class($post_type)];

if (is_category())              $archive_classes[] = 'is-category';
elseif (is_tag())               $archive_classes[] = 'is-tag';
elseif (is_tax())               $archive_classes[] = 'is-tax';
elseif (is_author())            $archive_classes[] = 'is-author';
elseif (is_date())              $archive_classes[] = 'is-date';
elseif (is_post_type_archive()) $archive_classes[] = 'is-post-type-archive';

if (!empty($qo->taxonomy)) $archive_classes[] = 'tax-' . sanitize_html_class($qo->taxonomy);
if (!empty($qo->slug))     $archive_classes[] = 'term-' . sanitize_html_class($qo->slug);

$archive_class_str = implode(' ', array_unique($archive_classes));

/** ─────────────────────────────────────────────────────────
 * 代稱 Page（提供「返回 XXX」用）
 * ───────────────────────────────────────────────────────── */
$slug_page = get_page_by_path($slug);
?>

<section class="page-hero" <?php echo $bg_url ? 'style="background-image:url(' . esc_url($bg_url) . ')"' : ''; ?>>
  <div class="ph-container">
    <h1 class="page-title"><?php echo esc_html($prefix . $archive_title); ?></h1>

    <h4 class="page-subtitle">
      <?php if ($slug_page): ?>
        <a href="<?php echo esc_url(get_permalink($slug_page->ID)); ?>">
          返回<?php echo esc_html(get_the_title($slug_page->ID)); ?>
        </a>
      <?php endif; ?>
    </h4>
  </div>
</section>

<main class="page-main list-<?php echo esc_attr($slug); ?> <?php echo esc_attr($archive_class_str); ?>">
  <div class="ph-container list-layout">
    <div class="content-col">
      <?php if (have_posts()): ?>
        <ul class="list">
          <?php while (have_posts()): the_post(); ?>
            <?php
              // 分類／標籤（有才顯示）
              $cats = get_the_terms(get_the_ID(), $cfg_tax['tax_cat']);
              $tags = get_the_terms(get_the_ID(), $cfg_tax['tax_tag']);

              // 摘要（不在 PHP 截字；交給 CSS ellipsis）
              $excerpt = get_post_field('post_excerpt', get_the_ID());
              if (!$excerpt) {
                $content = get_post_field('post_content', get_the_ID());
                $content = strip_shortcodes($content);
                $excerpt = wp_strip_all_tags($content, true);
              }

              // 精選圖（若需預設圖可在此加 fallback）
              $thumb_html = get_the_post_thumbnail(get_the_ID(), 'full');
            ?>
            <li class="item">
              <article class="card">
                <a class="thumb" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
                  <?php echo $thumb_html; ?>
                </a>

                <div class="meta">
                  <time class="date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                    <?php echo esc_html(get_the_date('Y.m.d')); ?>
                  </time>

                  <span class="line"></span>

                  <?php if (!is_wp_error($cats) && !empty($cats)): ?>
                    <div class="cats">
                      <?php foreach ($cats as $t): ?>
                        <a href="<?php echo esc_url(get_term_link($t)); ?>" class="cat"><?php echo esc_html($t->name); ?></a>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>

                  <?php if (!is_wp_error($tags) && !empty($tags)): ?>
                    <div class="tags">
                      <?php foreach ($tags as $t): ?>
                        <a href="<?php echo esc_url(get_term_link($t)); ?>" class="tag"><?php echo esc_html($t->name); ?></a>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>

                <h3 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <p class="desc"><?php echo esc_html($excerpt); ?></p>

                <div class="more">
                  <a class="btn btn-readmore" href="<?php the_permalink(); ?>">閱讀更多</a>
                </div>
              </article>
            </li>
          <?php endwhile; ?>
        </ul>

        <?php
        // 分頁
        echo '<nav class="pagination">';
        echo paginate_links([
          'total'     => $GLOBALS['wp_query']->max_num_pages,
          'mid_size'  => 2,
          'prev_text'  => '<svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.2036 0.868601C10.0678 1.0038 9.99097 1.18723 9.98985 1.37886C9.98872 1.5705 10.0634 1.75481 10.1976 1.8916L13.5766 5.2766H0.71664C0.525022 5.2766 0.341251 5.35272 0.205756 5.48822C0.0702612 5.62371 -0.00585938 5.80748 -0.00585938 5.9991C-0.00585938 6.19072 0.0702612 6.37449 0.205756 6.50999C0.341251 6.64548 0.525022 6.7216 0.71664 6.7216H13.5706L10.1916 10.1066C10.0586 10.244 9.98475 10.4281 9.98587 10.6193C9.98699 10.8106 10.063 10.9938 10.1976 11.1296C10.3334 11.2635 10.5167 11.338 10.7074 11.3369C10.898 11.3357 11.0805 11.259 11.2146 11.1236L15.7936 6.5126C15.8562 6.44562 15.9069 6.36853 15.9436 6.2846C15.9814 6.19681 16.0004 6.10214 15.9996 6.0066C15.9997 5.8175 15.9258 5.63589 15.7936 5.5006L11.2146 0.8856C11.1499 0.817265 11.0721 0.762538 10.9859 0.72462C10.8997 0.686702 10.8069 0.666356 10.7127 0.664773C10.6186 0.66319 10.5251 0.680402 10.4376 0.715401C10.3502 0.750399 10.2707 0.802482 10.2036 0.868601Z" /></svg>',
          'next_text'  => '<svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.2036 0.868601C10.0678 1.0038 9.99097 1.18723 9.98985 1.37886C9.98872 1.5705 10.0634 1.75481 10.1976 1.8916L13.5766 5.2766H0.71664C0.525022 5.2766 0.341251 5.35272 0.205756 5.48822C0.0702612 5.62371 -0.00585938 5.80748 -0.00585938 5.9991C-0.00585938 6.19072 0.0702612 6.37449 0.205756 6.50999C0.341251 6.64548 0.525022 6.7216 0.71664 6.7216H13.5706L10.1916 10.1066C10.0586 10.244 9.98475 10.4281 9.98587 10.6193C9.98699 10.8106 10.063 10.9938 10.1976 11.1296C10.3334 11.2635 10.5167 11.338 10.7074 11.3369C10.898 11.3357 11.0805 11.259 11.2146 11.1236L15.7936 6.5126C15.8562 6.44562 15.9069 6.36853 15.9436 6.2846C15.9814 6.19681 16.0004 6.10214 15.9996 6.0066C15.9997 5.8175 15.9258 5.63589 15.7936 5.5006L11.2146 0.8856C11.1499 0.817265 11.0721 0.762538 10.9859 0.72462C10.8997 0.686702 10.8069 0.666356 10.7127 0.664773C10.6186 0.66319 10.5251 0.680402 10.4376 0.715401C10.3502 0.750399 10.2707 0.802482 10.2036 0.868601Z" /></svg>',
        ]);
        echo '</nav>';
        ?>

      <?php else: ?>
        <p>目前沒有內容。</p>
      <?php endif; ?>
    </div>

    <!-- 側欄 -->
    <aside class="sidebar-col">
      <section class="widget widget-search">
        <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
          <label class="screen-reader-text" for="search-field">搜尋關鍵字</label>
          <input
            type="search"
            id="search-field"
            class="search-field"
            name="s"
            placeholder="請輸入搜尋關鍵詞..."
            value="<?php echo esc_attr(get_search_query()); ?>"
            autocomplete="off"
            required
          >
          <button type="submit" class="search-submit" aria-label="開始搜尋">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 8C15 9.38447 14.5895 10.7378 13.8203 11.889C13.0511 13.0401 11.9579 13.9373 10.6788 14.4672C9.3997 14.997 7.99224 15.1356 6.63437 14.8655C5.2765 14.5954 4.02922 13.9287 3.05026 12.9497C2.07129 11.9708 1.4046 10.7235 1.13451 9.36563C0.86441 8.00777 1.00303 6.6003 1.53285 5.32122C2.06266 4.04213 2.95987 2.94888 4.11101 2.17971C5.26216 1.41054 6.61553 1 8 1C9.85652 1 11.637 1.7375 12.9497 3.05025C14.2625 4.36301 15 6.14348 15 8Z" stroke="#434343" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M17.0004 16.9999L12.6504 12.6499" stroke="#434343" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
        </form>
      </section>

      <section class="widget widget-recent">
        <h3 class="widget-title">近期文章</h3>
        <ul>
          <?php
          $recent = new WP_Query([
            'post_type'           => $post_type,
            'posts_per_page'      => 5,
            'no_found_rows'       => true,
            'ignore_sticky_posts' => true,
          ]);
          if ($recent->have_posts()):
            while ($recent->have_posts()): $recent->the_post(); ?>
              <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
            <?php endwhile; wp_reset_postdata();
          else:
            echo '<li>尚無內容</li>';
          endif; ?>
        </ul>
      </section>

      <section class="widget widget-cats">
        <h3 class="widget-title">文章分類</h3>
        <ul>
          <?php
          $terms = get_terms([
            'taxonomy'   => $cfg_tax['tax_cat'],
            'hide_empty' => true,
          ]);
          if (!is_wp_error($terms) && $terms):
            foreach ($terms as $t) {
              echo '<li><a href="' . esc_url(get_term_link($t)) . '">' . esc_html($t->name) . '</a> <span class="count">(' . intval($t->count) . ')</span></li>';
            }
          else:
            echo '<li>尚無分類</li>';
          endif;
          ?>
        </ul>
      </section>
    </aside>
  </div>
</main>

<?php get_footer(); ?>
