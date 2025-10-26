<?php
/**
 * Template Name: 醫師單頁面
 * Template Post Type: team
 * Description: 醫師單頁面
 */

get_header();
?>

<!-- 共用影片背景 -->
<?php echo do_shortcode("[astra_custom_layout id=3990]"); ?>


<main class="single-team">
  <div class="single-team-warp left-block-bg">
    <?php 
    if ( have_posts() ) : while ( have_posts() ) : the_post();

      // 1) 精選圖（可能為 false）
      $img_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );

      // 2) 標題
      $title = get_the_title();

      // 3) ACF 欄位（job_title、name_en）— 沒值回空字串
      $job_title = function_exists('get_field') ? ( get_field('job_title') ?: '' ) : ( get_post_meta(get_the_ID(), 'job_title', true) ?: '' );
      $name_en   = function_exists('get_field') ? ( get_field('name_en')   ?: '' ) : ( get_post_meta(get_the_ID(), 'name_en',   true) ?: '' );

      // 4) 內容（走 the_content 濾器，讓短碼/嵌入生效）
      $content = apply_filters( 'the_content', get_the_content() );
    ?>

      <article id="post-<?php the_ID(); ?>" class="cont">
        <div class="left">
          <?php if ( $img_url ) : ?>
            <figure class="team-thumb">
              <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($title); ?>">
            </figure>
          <?php endif; ?>
          <header class="team-header">
            <h1 class="team-title">
              <span class="name"><?php echo esc_html($title); ?></span>
              <span class="job-title"><?php echo esc_html($job_title); ?></span>
            </h1>
          </header>
          <p class="name-en"><?php echo esc_html($name_en); ?></p>
        </div>

        <div class="right">
          <div class="team-content">
            <?php echo $content; ?>
          </div>
        </div>

      </article>

    <?php endwhile; endif; ?>
  </div>

  <!-- 專業證照 -->
  <?php echo do_shortcode("[astra_custom_layout id=4054]"); ?>

</main>






<?php get_footer(); ?>
