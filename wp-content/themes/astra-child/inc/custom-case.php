<?php 

/* =================================

  首頁案例見證
  "<?php require get_theme_file_path( 'inc/custom-case.php' ); ?>"
  "[astra_custom_layout id=3509]"

 * ================================== */


// 文章列表
$args_column = array(
    'post_type' => 'case',
    'post_status'		=> 'publish',
    'posts_per_page' => 3,
    'paged'         => 1,
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
<ul class="case-carousel">
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
		$category = get_the_terms($item->ID, 'case-type'); // get category
		$tags = get_the_terms($item->ID, 'case-tag'); // get tag
		$img = wp_get_attachment_image_src( get_post_thumbnail_id( $item ), 'full' );
		$img_default = '';
		// $img_default = get_stylesheet_directory_uri() . '/assets/img/img-default.jpg';

	?>
	<li class="item">
		<div class="info">
			
			<div class="img" style="background-image: url(<?php echo !empty($img[0]) ? $img[0] : $img_default; ?>);"></div>
			<div class="cont">
				<h4 class="title"><?php echo $title; ?></h4>
				<div class="meta">
				<?php if (!empty($category[0]->name)) : ?>
					<div class="category"><?php echo esc_html($category[0]->name); ?></div>
				<?php endif; ?>

				<?php if (!empty($tags[0]->name)) : ?>
					<div class="tag"><?php echo esc_html($tags[0]->name); ?></div>
				<?php endif; ?>

				</div>
				<div class="desc">
					<?php echo $desc; ?>
				</div>
				<!-- <hr> -->
				<div class="read-more">
					<a href="<?php echo esc_url( home_url( 'case/'.$name ) ); ?>">閱讀更多</a>
				</div>

			</div>
			
			
		</div>
	</li>		

	<?php endforeach; ?>
</ul>
<?php endif; ?>
	