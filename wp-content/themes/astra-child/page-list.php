<?php
/**
 * Template Name: 共用列表
 * Description: 共用列表頁，依頁面 slug 切換 post_type 與參數
 */

get_header();

// 1) 依頁面 slug 決定 post_type/每頁數/排序…（你可自行擴充）
$slug = get_post_field('post_name', get_queried_object_id());
$map  = [
  'article' => ['post_type' => 'post',  'per_page' => 12],
  'cases'   => ['post_type' => 'case',  'per_page' => 12],
];

$cfg   = $map[$slug] ?? ['post_type' => 'post', 'per_page' => 12];
$paged = max(1, get_query_var('paged'), get_query_var('page'));

$q = new WP_Query([
  'post_type'      => $cfg['post_type'],
  'posts_per_page' => $cfg['per_page'],
  'paged'          => $paged,
  'orderby'        => 'date',
  'order'          => 'DESC',
  // 需要時可加 taxonomy 過濾：'tax_query' => [...]
]);

?>
<main id="primary" class="site-main list-<?= esc_attr($slug) ?>">
  <div class="ph-container">
    <?php if ($q->have_posts()) : ?>
      <ul class="list">
        <?php while ($q->have_posts()) : $q->the_post(); ?>
          <li class="item">
            <a href="<?php the_permalink(); ?>">
              <h3 class="title"><?php the_title(); ?></h3>
              <!-- 放你共用卡片樣式；摘要/圖等 -->
            </a>
          </li>
        <?php endwhile; ?>
      </ul>

      <?php
      // 2) 分頁（Page 模板要手動指定 base）
      echo paginate_links([
        'total'   => $q->max_num_pages,
        'current' => $paged,
        'base'    => trailingslashit(get_permalink()) . 'page/%#%/',
        'format'  => '',
        'mid_size'=> 2,
        'prev_text' => '«',
        'next_text' => '»',
      ]);
      wp_reset_postdata();
      ?>
    <?php else: ?>
      <p>目前沒有內容。</p>
    <?php endif; ?>
  </div>
</main>
<?php get_footer(); ?>
