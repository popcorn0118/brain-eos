<?php 

/* =================================

  關於我們頁面 > 專業醫療團隊 輪播區塊
  "<?php require get_theme_file_path( 'inc/custom-team.php' ); ?>"
  "[astra_custom_layout id=3943]"

 * ================================== */


// 文章列表
$args_column = array(
    'post_type' => 'team',
    'post_status'		=> 'publish',
    'posts_per_page' => -1,
    // 'paged'         => 1,
    "order"     => "desc",
    // 's' => !empty($search_query) ? $search_query : "",
	// 'tax_query'       => array(
	// 	'relation' => 'AND',
	// 	  array(
	// 		'taxonomy' => 'post_tag',
	// 		'field' => 'slug',
	// 		'terms' => array( '首頁精選' ),
	// 	  ),
	// ),
);

$column = get_posts($args_column);

?>

<?php if (!empty($column)): ?>
<ul class="team-carousel">
	<?php 	
	foreach ($column as $item): 
		$title = $item->post_title;
		$name = $item->post_name;
		$date = strtotime($item->post_date);
		$desc = trim( $item->post_excerpt );
		if ( $desc === '' ) {
			$desc = $item->post_content;
		}
		$desc = wp_strip_all_tags( strip_shortcodes( $desc ) );
		// $category = get_the_terms($item->ID, 'team-type'); // get category
		$img = wp_get_attachment_image_src( get_post_thumbnail_id( $item ), 'full' );
		$img_default = '';
		// $img_default = get_stylesheet_directory_uri() . '/assets/img/img-default.jpg';

		// --- ACF 欄位：職稱 / 英文名 ---
		$job_title = get_field('job_title', $item->ID) ?: '';
		$name_en   = get_field('name_en',   $item->ID) ?: '';

	?>
	<li class="item">
		<div class="info">
			
			<div class="img" style="background-image: url(<?php echo !empty($img[0]) ? $img[0] : $img_default; ?>);"></div>
			<div class="cont">
				<div class="head">
					<h3 class="name"><?php echo $title; ?> <?php echo $job_title; ?></h3>
					<h5 class="name_en"><?php echo $name_en; ?></h5>
				</div>
				
				<div class="meta">
					<!-- <?php //if (!empty($category[0]->name)) : ?>
						<div class="category"><?php //echo esc_html($category[0]->name); ?></div>
					<?php //endif; ?> -->

				</div>

				<div class="desc">
					<?php echo $desc; ?>
				</div>

				<div class="read-more btn-text-html">
					<a href="<?php echo esc_url( home_url( 'team/'.$name ) ); ?>">閱讀更多</a>
				</div>

			</div>
			
			
		</div>
	</li>		

	<?php endforeach; ?>
</ul>
<?php endif; ?>
	