<?php 

/* =================================

  服務項目單面 > 上半部(回到特色醫療、標題、英文標題、精選圖)
  "<?php require get_theme_file_path( 'inc/custom-service-head.php' ); ?>"
  "[astra_custom_layout id=4622]"

 * ================================== */


?>

<div class="service-header">
  <?php 
    $post_id = get_the_ID();

    // ACF 英文標題（可選）
    $title_en = get_field('title_en', $post_id) ?: '';

  ?>

	<div class="left">
		<!-- 返回列表 -->
		<div class="back-btn btn-text-html left">
			<a href="<?php echo esc_url( home_url( 'services') ); ?>" aria-label="回到特色醫療">回到特色醫療</a>
		</div>

		<!-- 精選圖 手機版 -->
		<div class="img mobile">
			<?php echo get_the_post_thumbnail($post_id, 'large', ['alt' => esc_attr(get_the_title($post_id))]); ?>
		</div>

		<!-- 英文標題 -->
		<div class="title-warp">
			<h4 class="title-en"><?php echo esc_html($title_en); ?></h4>
			<!-- 標題（中文） -->
			<h1 class="title">
				<?php echo esc_html(get_the_title($post_id)); ?>
			</h1>
		</div>
		
	</div>

	<!-- 精選圖 電腦版 -->
	<?php if (has_post_thumbnail($post_id)): ?>
		<div class="img desk">
			<?php echo get_the_post_thumbnail($post_id, 'large', ['alt' => esc_attr(get_the_title($post_id))]); ?>
		</div>
	<?php endif; ?>

</div>
