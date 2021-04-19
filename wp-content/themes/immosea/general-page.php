<?php
/* Template Name: General Page */
get_header(); ?>


<?php
    $hero      = get_field('hero');
    $content   = get_field('content');
    $features  = get_field('features');
    $prices    = get_field('prices');
    $banner    = get_field('banner_image', 'option');
    $logo      = get_field('logo', 'option');
?>

    <main class="main">
        <?php if($hero) : ?>
            <div class="intro inverse <?php if($hero['image']) { echo 'intro--image'; } ?>">
                <div class="container">
                    <div class="intro__content">
                        <?php if(!empty($hero['title'])) : ?>
                            <h2><?php echo $hero['title']; ?></h2>
                        <?php endif ;?>
                        <p><?php echo $hero['text']; ?></p>
                        <?php if($hero['button']): ?>
                            <div class="intro__button">
                                <a class="button button--primary" href="<?php echo $hero['button']['url'] ?>"><?php
                                    echo $hero['button']['title'];?></a>
                            </div>
                        <?php endif;?>
                    </div>
                </div>
                <?php if($hero['image']) : ?>
                    <div class="intro__image">
                        <img src="<?php echo $hero['image']['url'] ?>">
                    </div>
                <?php endif ;?>

                <?php if($hero['show_menu']) : ?>
                    <div class="intro__bar">
                        <div class="container">
                            <?php
                                $args = array(
                                    'theme_location'=>'services',
                                    'container'=>'',
                                    'menu_class'=>'intro__bar-nav',
                                );
                                wp_nav_menu($args);
                            ?>
                        </div>
                    </div>
                <?php endif ;?>
            </div>
        <?php endif ;?>

        <?php if($prices['items']) : ?>
            <div class="container">
                <div class="prices">
                    <div class="prices__row prices__row--head">
                        <div>Leistung</div>
                        <div>Leistungsbeschreibung</div>
                        <div>Preis</div>
                    </div>
                    <div>
                        <?php foreach($prices['items'] as $row) { ?>
                            <div class="prices__row">
                                <div><?php echo $row['name'] ?></div>
                                <div><?php echo $row['description'] ?></div>
                                <div><?php echo $row['price'] ?></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php endif ;?>

        <?php if($content['title']) : ?>
            <div class="container">
                <div class="main-content">
                    <div class="main-content__title">
                        <h2><?php echo $content['title'] ?></h2>
                    </div>
                    <div class="main-content__text">
                        <?php echo $content['text'] ?>
                    </div>
                </div>
            </div>
        <?php endif ;?>

        <?php if($features['items']) : ?>
            <div class="cases">
                <div class="container">
                    <div class="cases__title">
                        <h2><?php echo $features['title']; ?></h2>
                    </div>
                    <?php if(isset($features['items'])): ?>
                        <div class="cases__list">
                            <?php foreach($features['items'] as $row) { ?>
                                <div>
                                    <div class="cases__list-icon">
                                        <img src="<?php echo $row['icon']['url'] ?>">
                                    </div>
                                    <div class="cases__list-content">
                                        <h3><?php echo $row['title']; ?></h3>
                                        <?php echo $row['text']; ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif ;?>

        <?php if($banner['url']) : ?>
            <div class="container">
                <div class="banner">
                    <div class="banner__image">
                        <img src="<?php echo $banner['url'] ?>">
                    </div>
                    <div class="banner__content">
                        <h3>Deine Vorteile</h3>
                        <div class="banner__logo">
                            <img src="<?php echo $logo['url'] ?>" alt="immosea">
                        </div>
                        <div class="banner__holder">
                            <div class="banner__line">
                                <strong>S</strong>
                                chnell deine Werbetexte <br/>erhalten
                            </div>
                            <div class="banner__line">
                                von
                                <strong>E</strong>
                                xperten erstellt
                            </div>
                            <div class="banner__line">
                                <div>auf dich und<br/>deine Immobillie</div>
                                <strong>A</strong>
                                <div>ngepasst</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif ;?>

    </main>

<?php get_footer();

