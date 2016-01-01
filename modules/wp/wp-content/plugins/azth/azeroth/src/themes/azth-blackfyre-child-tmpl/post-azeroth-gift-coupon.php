<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 */

?>

<?php get_header();?>
<?php  global $wpClanWars, $wpdb, $post, $refresh, $report, $submitted, $submittedalready; $submittedalready = ''; $submitted = ''; $report = ''; $refresh = ''; ?>
<!-- Page content
    ================================================== -->
<!-- Wrap the rest of the page in another container to center all the content. -->

<div class="container blog blog-ind">

  <div class="row">

 <div class="col-lg-8 col-md-8 ">
     <h1>TEST</h1>
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

        <div class="blog-post">

        <div class="blog-image right">
             <?php
                $key_1_value = get_post_meta($post->ID, '_smartmeta_my-awesome-field77', true);
                if($key_1_value != '') {
                 $blackfyre_allowed['iframe'] = array(
                            'src'             => array(),
                            'height'          => array(),
                            'width'           => array(),
                            'frameborder'     => array(),
                            'allowfullscreen' => array(),
                        );
                 echo wp_kses($key_1_value, $blackfyre_allowed,array('http', 'https'));
                }elseif ( has_post_thumbnail() ) { ?>
                  <?php
                   $thumb = get_post_thumbnail_id();
                   $img_url = wp_get_attachment_url( $thumb,'full'); //get img URL
                   $image = blackfyre_aq_resize( $img_url, 817, 320, true, '', true ); //resize & crop img
                   ?><img alt="img" src="<?php echo esc_url($image[0]); ?>" />
             <?php }else{ ?>
                 <img alt="img" src="<?php echo esc_url(get_template_directory_uri()); ?>/img/defaults/default-banner.jpg" />
             <?php } ?>

             <div class="blog-date">
                <span class="date"><?php the_time('M'); ?><br /><?php the_time('d'); ?></span>
                <div class="plove"><?php if( function_exists('heart_love') ) heart_love(); ?></div>
             </div>

                    <div class="blog-rating">
                    <?php
                    // overall stars
                    $overall_rating_1 = get_post_meta(get_the_ID(), 'overall_rating', true);
                    if($overall_rating_1!="0" && $overall_rating_1=="0.5"){ ?>
                    <div class="overall-score"><div class="rating r-05"></div></div>
                    <?php } ?>

                    <?php $overall_rating_2 = get_post_meta(get_the_ID(), 'overall_rating', true);
                    if($overall_rating_2!="0" && $overall_rating_2=="1"){ ?>
                    <div class="overall-score"><div class="rating r-1"></div></div>
                    <?php } ?>

                    <?php $overall_rating_3 = get_post_meta(get_the_ID(), 'overall_rating', true);
                    if($overall_rating_3!="0" && $overall_rating_3=="1.5"){ ?>
                    <div class="overall-score"><div class="rating r-15"></div></div>
                    <?php } ?>

                    <?php $overall_rating_4 = get_post_meta(get_the_ID(), 'overall_rating', true);
                    if($overall_rating_4!="0" && $overall_rating_4=="2"){ ?>
                    <div class="overall-score"><div class="rating r-2"></div></div>
                    <?php } ?>

                    <?php $overall_rating_5 = get_post_meta(get_the_ID(), 'overall_rating', true);
                    if($overall_rating_5!="0" && $overall_rating_5=="2.5"){ ?>
                    <div class="overall-score"><div class="rating r-25"></div></div>
                    <?php } ?>

                    <?php $overall_rating_6 = get_post_meta(get_the_ID(), 'overall_rating', true);
                    if($overall_rating_6!="0" && $overall_rating_6=="3"){ ?>
                    <div class="overall-score"><div class="rating r-3"></div></div>
                    <?php } ?>

                    <?php $overall_rating_7 = get_post_meta(get_the_ID(), 'overall_rating', true);
                    if($overall_rating_7!="0" && $overall_rating_7=="3.5"){ ?>
                    <div class="overall-score"><div class="rating r-35"></div></div>
                    <?php } ?>

                    <?php $overall_rating_8 = get_post_meta(get_the_ID(), 'overall_rating', true);
                    if($overall_rating_8!="0" && $overall_rating_8=="4"){ ?>
                    <div class="overall-score"><div class="rating r-4"></div></div>
                    <?php } ?>

                    <?php $overall_rating_9 = get_post_meta(get_the_ID(), 'overall_rating', true);
                    if($overall_rating_9!="0" && $overall_rating_9=="4.5"){ ?>
                    <div class="overall-score"><div class="rating r-45"></div></div>
                    <?php } ?>

                    <?php $overall_rating_10 = get_post_meta(get_the_ID(), 'overall_rating', true);
                    if($overall_rating_10!="0" && $overall_rating_10=="5"){ ?>
                    <div class="overall-score"><div class="rating r-5"></div></div>

                    <?php } ?>
                     </div><!-- blog-rating -->

        </div><!-- blog-image -->


             <div class="blog-info">
                    <div class="post-pinfo">
                        <span class="fa fa-user"></span> <a data-original-title="<?php esc_html_e("View all posts by", 'blackfyre'); ?> <?php echo esc_attr(get_the_author()); ?>" data-toggle="tooltip" href="<?php echo esc_url(get_author_posts_url( get_the_author_meta( 'ID' ) )); ?>"><?php echo esc_attr(get_the_author()); ?></a> &nbsp;
                        <?php $posttags = get_the_tags();if ($posttags) {?>  <span class="fa fa-tags"></span>  <?php $i = 0; $len = count($posttags); foreach($posttags as $tag) { ?>  <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>"> <?php echo esc_attr($tag->name); if($i != $len - 1) echo ', '; ?> </a><?php  $i++; } }?></div>
                    <?php if(of_get_option('share_this')){ ?>
                    <div class="sharepost">
                        <span class='st_sharethis_hcount' displayText='ShareThis'></span>
                        <span class='st_facebook_hcount' displayText='Facebook'></span>
                        <span class='st_twitter_hcount' displayText='Tweet'></span>
                        <span class='st_reddit_hcount' displayText='Reddit'></span>
                        <span class='st_email_hcount' displayText='Email'></span>
                    </div>
                    <?php } ?>
                    <div class="clear"></div>
         </div>

              <!-- post ratings -->
            <?php
                $overall_rating = get_post_meta($post->ID, 'overall_rating', true);
                $rating_one = get_post_meta($post->ID, 'creteria_1', true);
                $rating_two = get_post_meta($post->ID, 'creteria_2', true);
                $rating_three = get_post_meta($post->ID, 'creteria_3', true);
                $rating_four = get_post_meta($post->ID, 'creteria_4', true);
                $rating_five = get_post_meta($post->ID, 'creteria_5', true);

                if($overall_rating== NULL or $rating_one== NULL && $rating_two== NULL && $rating_three== NULL && $rating_four== NULL && $rating_five== NULL ){}else{

                    ?>

            <?php include('post-rating.php') ?>

          <?php } ?>
            <!-- /post ratings -->

            <div class="blog-content wcontainer">
                <?php the_content(); ?>
            </div> <!-- /.blog-content -->


            <div class="clear"></div>
        </div><!-- /.blog-post -->
        <?php endwhile; endif; ?>
        <div class="clear"></div>

     <?php if(of_get_option('authorsingle')){ ?>
    <div class="block-divider"></div>
    <div class="author-block wcontainer">
                <?php echo get_avatar( get_the_author_meta('ID'), 250 ); ?>
                <div class="author-content">
                    <h3><?php esc_html_e("About ", 'blackfyre'); ?> <?php echo esc_attr(get_the_author()); ?></h3>
                   <?php the_author_meta('description'); ?>
                </div>
                <div class="clear"></div>
    </div><!-- /author-block -->
    <?php } ?>
    <?php wp_link_pages(); ?>
     <?php if(comments_open()){?>
          <div id="comments"  class="block-divider"></div>
           <?php comments_template('/short-comments-blog.php'); ?>

    <?php } ?>

    </div> <!-- /.span8 -->

    <div class="col-lg-4 col-md-4  ">
            <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Blog sidebar') ) : ?>
                <?php dynamic_sidebar('three'); ?>
           <?php endif; ?>
    </div><!-- /.span4 -->

  </div>  <!-- /container -->
 </div>  <!-- /row -->


<?php if($report == 'reported'){ ?>
<script>
jQuery( document ).ready(function() {
    NotifyMe(settingsNoty.reported, "information");
});
</script>
<?php $report = '';
} ?>

<?php if($submitted == 'submitted'){ ?>
<script>
jQuery(document).ready(function(){
    NotifyMe(settingsNoty.submitted, "information");
});
</script>
<?php $submitted = '';
} ?>

<?php if($submittedalready == 'submittedalready'){ ?>
<script>
jQuery(document).ready(function(){
    NotifyMe(settingsNoty.already_submitted, "information");
});
</script>
<?php $submittedalready = '';
} ?>

<?php if($refresh == 'go'){
	 if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
        	 	 header('Location: http://' . $_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI]);
        	 }else{
        	 	 header('Location: https://' . $_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI]);
        	 }

die; $refresh = ''; }  ?>
<?php get_footer(); ?>