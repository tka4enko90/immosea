<?php
/* Template Name: Home */
get_header(); ?>
    <form action="">
        <input type="file" id="myInputID">
    </form>

<?php
    $hero      = get_field('hero');
    $features  = get_field('features');
    $package   = get_field('package');
    $questions = get_field('questions');
    $faq       = get_field('faq');
    $button    = get_field('link_to_form', 'option');
    $social = get_field('social_links', 'option');
?>

<main class="main">
    <?php if($hero) : ?>
        <div class="hero">
            <?php if($social): ?>
                <ul class="social-links">
                    <?php foreach($social as $row) { ?>
                        <li>
                            <a href="<?php echo $row['link']['url'] ?>" target="_blank">
                                <img src="<?php echo $row['icon']['url'] ?>" class="style-svg">
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            <?php endif; ?>

            <div class="container">
                <div class="hero__content">
                    <?php if(!empty($hero['title'])) : ?>
                        <h1 class="text-primary"><?php echo $hero['title']; ?></h1>
                    <?php endif ;?>
                    <?php if($button): ?>
                        <div class="hero__button">
                            <a class="button button--primary" href="<?php echo $button['url'] ?>"><?php echo $hero['title_of_button'];?></a>
                        </div>
                    <?php endif;?>
                </div>
            </div>
            <?php if(isset($hero['gallery'])): ?>
                <div class="hero__slider">
                    <?php foreach($hero['gallery'] as $key => $row) { ?>
                        <div>
                            <img src="<?php echo $row['url'] ?>">
                            <span class="hero__slider-number">0<?php echo $key+1 ?></span>
                        </div>
                    <?php } ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif ;?>

    <div class="features">
        <div class="container">
            <?php if(!empty($features['title'])) : ?>
            <div class="features__title">
                <h2><?php echo $features['title']; ?></h2>
            </div>
            <?php endif ;?>


            <?php if(isset($features['items'])): ?>
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
    <?php if(!empty($package['items'])) : ?>
        <div class="package">
            <div class="container">
                <div class="package__content">
                    <?php if(isset($package['title'])) : ?>
                        <h2><?php echo $package['title']; ?></h2>
                    <?php endif ;?>
                    <div class="package__list">
                        <?php foreach($package['items'] as $row) { ?>
                            <a class="package__list-link" href=<?php echo $row['link']['url'] ?>>
                                <div class="package__list-title">
                                    <?php if(isset($row['icon'])) : ?>
                                        <div class="package__list-icon">
                                            <img src="<?php echo $row['icon']['url'] ?>">
                                        </div>
                                    <?php endif ;?>
                                    <?php if($row['link']) : ?>
                                        <h3><?php echo $row['link']['title'] ?></h3>
                                    <?php endif ;?>

                                </div>
                                <?php if(!empty($row['text'])) : ?>
                                    <?php echo $row['text']; ?>
                                <?php endif ;?>
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
    <?php endif ;?>
    <?php if(!empty($questions)) : ?>
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
    <?php endif ;?>
    <?php if($faq) : ?>
        <div class="faq-section">
            <div class="container">
                <div class="faq-section__content">
                    <h2><?php echo $faq['title']; ?></h2>
                    <div class="accordion">
                        <?php foreach($faq['items'] as $row) { ?>
                            <div class="accordion__item-title" data-accordion-title>
                                <?php echo get_the_title($row); ?>
                            </div>
                            <div class="accordion__item-content" data-accordion-content>
                                <?php echo get_the_content(null, false, $row); ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>

            <?php if($faq['image']): ?>
                <div class="faq-section__image">
                    <img src="<?php echo $faq['image']['url'] ?>">
                </div>
            <?php endif; ?>
        </div>
    <?php endif ;?>
</main>

<?php get_footer();

