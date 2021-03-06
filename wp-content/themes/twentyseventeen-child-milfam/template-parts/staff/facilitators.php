<div class="individual-person">
  <div class="col_one">
    <div class="individual-person-thumb-wrapper">
    <div class="individual-person-thumb">
      <?php if (has_post_thumbnail( $post->ID ) ){
        $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ) );
      ?>
        <img src="<?php echo $image[0]; ?>" alt="profile image">
      <?php } ?>
    </div>
    </div>
    <h3 class="person-name"><?php the_title(); ?><?php echo do_shortcode("[credentials]"); ?></h3>
  </div>
  <div class="col_two">
    <?php the_content(); ?>

    <?php if ( get_post_meta( get_the_ID(), 'presentations', true ) ): ?>
      <h5 class="individual-person-presentations">Presentations</h5>
      <?php echo html_entity_decode(get_post_meta( get_the_ID(), 'presentations', true )); ?>
    <?php endif; ?>
  </div>
</div>
