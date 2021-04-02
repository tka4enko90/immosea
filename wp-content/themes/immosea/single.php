<?php get_header(); ?>

<main class="main">
    <div class="container">
        <div class="post-single">
            <div class="post-single-img">
                <?php the_post_thumbnail('post-width'); ?>
            </div>
            <div class="post-single-content">
                <h2 class="text-primary"><?php echo get_the_title(); ?></h2>
                <?php the_content(); ?>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>
