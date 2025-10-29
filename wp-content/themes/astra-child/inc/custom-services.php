<?php 

/* =================================

  服務項目頁面 > 下方服務項目列表
  "<?php require get_theme_file_path( 'inc/custom-services.php' ); ?>"
  "[astra_custom_layout id=4558]"

 * ================================== */


// 列表
$args_column = array(
    'post_type' => 'service',
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
<ul class="services-list">
	<?php 	
	foreach ($column as $item): 
		$title = $item->post_title;
		$name = $item->post_name;

		// --- ACF 欄位：職稱 / 英文名 ---
		$title_en = get_field('title_en', $item->ID) ?: '';

	?>
		<li class="item">
			<div class="cont">
				<div class="head">
					<h4 class="title"><?php echo $title; ?></h4>
					<h5 class="title-en"><?php echo $title_en; ?></h5>
				</div>
				
				<div class="read-more btn-plrimary-l">
					<a href="<?php echo esc_url( home_url( 'service/'.$name ) ); ?>">了解更多</a>
				</div>

			</div>
		</li>		

	<?php endforeach; ?>
</ul>
<?php endif; ?>
	