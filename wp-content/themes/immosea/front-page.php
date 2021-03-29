<?php
/* Template Name: Home */
get_header(); ?>


<?php
    $hero      = get_field('hero');
    $features  = get_field('features');
    $package   = get_field('package');
    $questions = get_field('questions');
    $faq       = get_field('faq');
    $button    = get_field('link_to_form', 'option');
?>

<main class="main">
    <div class="hero">
        <div class="container">
            <div class="hero__content">
                <h1 class="text-primary"><?php echo $hero['title']; ?></h1>
                <?php if($button): ?>
                    <div class="hero__button">
                        <a class="button button--primary" href="<?php echo $button['url'] ?>"><?php echo $hero['title_of_button'];
                        ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php if($hero['gallery']): ?>
            <div class="hero__slider">

                <?php $index = 1 ?>
                <?php foreach($hero['gallery'] as $row) { ?>
                    <div>
                        <img src="<?php echo $row['url'] ?>">
                        <span class="hero__slider-number">0<?php echo $index ?></span>
                    </div>
                    <?php $index++ ?>
                <?php } ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="features">
        <div class="container">
            <div class="features__title">
                <h2><?php echo $features['title']; ?></h2>
            </div>

            <?php if($features['items']): ?>
                <div class="features__list">
                    <?php foreach($features['items'] as $row) { ?>
                        <div>
                            <div class="features__list-icon">
                                <img src="<?php echo $row['icon']['url'] ?>">
                            </div>

                            <div class="features__list-holder">
                                <h3 class="features__list-title"><?php echo $row['title']; ?></h3>
                                <?php echo $row['text']; ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <div class="package">
        <div class="container">
            <div class="package__content">
                <h2><?php echo $package['title']; ?></h2>

                <div class="package__list">
                    <?php foreach($package['items'] as $row) { ?>
                        <a class="package__list-link" href=<?php echo $row['link']['url'] ?>>
                            <div class="package__list-title">
                                <div class="package__list-icon">
                                    <img src="<?php echo $row['icon']['url'] ?>">
                                </div>
                                <h3><?php echo $row['link']['title'] ?></h3>
                            </div>
                            <?php echo $row['text']; ?>
                        </a>
                    <?php } ?>
                </div>

                <?php if($button): ?>
                    <div class="package__button">
                        <a class="button button--outline button--outline-inverse" href="<?php echo $button['url'] ?>"><?php
                            echo $package['title_of_button']; ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php if($package['image']): ?>
            <div class="package__image">
                <img src="<?php echo $package['image']['url'] ?>">
            </div>
        <?php endif; ?>
    </div>

    <div class="questions">
        <div class="container">
            <div class="questions__content">
                <h2><?php echo $questions['title']; ?></h2>
                <div class="questions__list">
                    <div>
                        <div class="questions__list-step"><span>1</span></div>
                        <div class="questions__list-text"><?php echo $questions['step_first']; ?></div>
                    </div>
                    <div>
                        <div class="questions__list-step"><span>2</span></div>
                        <div class="questions__list-text"><?php echo $questions['step_second']; ?></div>
                    </div>
                    <div>
                        <div class="questions__list-step"><span>3</span></div>
                        <div class="questions__list-text"><?php echo $questions['step_third']; ?></div>
                    </div>
                </div>
                <?php if($button): ?>
                    <div class="questions__button">
                        <a class="button button--primary" href="<?php echo $button['url'] ?>"><?php
                            echo $questions['title_of_button']; ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if($questions['image']): ?>
            <div class="questions__image">
                <img src="<?php echo $questions['image']['url'] ?>">
            </div>
        <?php endif; ?>
    </div>

    <div class="faq-section">
        <div class="container">
            <div class="faq-section__content">
                <h2><?php echo $faq['title']; ?></h2>
                <div class="accordion">
                        <div class="accordion__item-title" data-accordion-title>
                            ddd
                        </div>
                        <div class="accordion__item-content" data-accordion-content>
                            dddddd
                        </div>
                        <div class="accordion__item-title" data-accordion-title>
                            ddd
                        </div>
                        <div class="accordion__item-content" data-accordion-content>
                            dddddd
                        </div>
                        <div class="accordion__item-title" data-accordion-title>
                            ddd
                        </div>
                        <div class="accordion__item-content" data-accordion-content>
                            dddddd
                        </div>
                </div>
            </div>
        </div>

        <?php if($faq['image']): ?>
            <div class="faq-section__image">
                <img src="<?php echo $faq['image']['url'] ?>">
            </div>
        <?php endif; ?>
    </div>
</main>

<?php get_footer();

