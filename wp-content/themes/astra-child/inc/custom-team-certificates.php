<?php 

/* =================================

  關於我們頁面 > 專業醫療團隊 > 專業證照區塊
  "<?php require get_theme_file_path( 'inc/custom-team-certificates.php' ); ?>"
  "[astra_custom_layout id=4136]"

 * ================================== */


?>

<ul class="team-certificates">
	<?php 	
		$post_id = get_the_ID();
		$certificates = get_field('certificates', $post_id) ? : '';
		foreach ($certificates as $img):
	?>
		<li class="item">
			<img src="<?php echo $img['link']; ?>" class="" alt="<?php echo $img['alt'] ? $img['alt'] : $img['title']; ?>">
		</li>		
	<?php endforeach?>
	
</ul>


	