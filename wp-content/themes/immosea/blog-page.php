<?php
    /* Template Name: Blog */
    get_header();

    $allPosts = new WP_Query(array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => -1
    ));
?>

<main class="main">
    <div class="container">
        <div class="posts-list">
            <?php if ($allPosts->have_posts()) : ?>
                <?php while ($allPosts->have_posts() ) : $allPosts->the_post(); ?>
                    <div class="post">
                        <div class="post-inner">
                            <div class="post-img">
                                <?php the_post_thumbnail('post-width'); ?>
                            </div>

                            <div class="post-body">
                                <div class="post-info">
                                    <div class="post-date">
                                        <?php $post_date = get_the_date( 'M j, Y' ); ?>
                                        <?php echo $post_date; ?>
                                    </div>
                                </div>
                                <h3 class="post-title">
                                    <?php the_title(); ?>
                                </h3>
                                <div class="post-desc text-truncate">
                                    <?php echo apply_filters( 'the_content', wp_trim_words( get_the_content(), 10, '&hellip;' ) ); ?>
                                </div>
                                <a href="<?php echo get_the_permalink(); ?>" class="button button--small
                                button--outline">Read More</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
                <?php else: ?>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php get_footer();

