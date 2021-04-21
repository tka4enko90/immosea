<?php get_header();
the_post();
?>
    <main class="main">
        <div class="container">
            <div class="post-single">
                <div class="post-single-content">
                    <h2 class="text-primary"><?php echo get_the_title(); ?></h2>
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
    </main>
<?php get_footer(); ?>