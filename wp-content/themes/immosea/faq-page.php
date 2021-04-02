<?php
/* Template Name: FAQ Page */
get_header();

$posts = new WP_Query(array(
    'post_type' => 'faq',
    'post_status' => 'publish'
));

?>

<main class="main">
    <div class="section">
        <div class="container">
            <h2>FAQ</h2>
            <div class="accordion">
                <?php if ($posts->have_posts()) : ?>
                    <?php while ($posts->have_posts() ) : $posts->the_post(); ?>
                        <div class="accordion__item-title" data-accordion-title>
                            <?php echo the_title(); ?>
                        </div>
                        <div class="accordion__item-content" data-accordion-content>
                            <?php echo get_the_content(); ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div>No Posts</div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</main>

<?php get_footer();

