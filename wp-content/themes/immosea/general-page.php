<?php
/* Template Name: General Page */
get_header(); ?>


<main class="main">
    <?php if( have_rows('flexible_content') ):
        while ( have_rows('flexible_content') ) : the_row();

        if( get_row_layout() == 'hero' ): ?>
            <?php
                $title  = get_sub_field('title');
                $text   = get_sub_field('text');
                $button = get_sub_field('button');
                $image  = get_sub_field('image');
                $show_menu = get_sub_field('show_menu');
            ?>

            <div class="intro inverse <?php if($image) { echo 'intro--image'; } ?>">
                <div class="container">
                    <div class="intro__content">
                        <?php if(!empty($title)) : ?>
                            <h2><?php echo $title; ?></h2>
                        <?php endif ;?>
                        <p><?php echo $text; ?></p>
                        <?php if($button): ?>
                            <div class="intro__button">
                                <a class="button button--primary"
                                   href="<?php echo $button['url'] ?>"
                                   target="<?php echo $button['target'] ?>">
                                    <?php echo $button['title'];?>
                                </a>
                            </div>
                        <?php endif;?>
                    </div>
                </div>
                <?php if($image) : ?>
                    <div class="intro__image">
                        <img src="<?php echo $image['url'] ?>">
                    </div>
                <?php endif ;?>

                <?php if($show_menu) : ?>
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

        <?php elseif( get_row_layout() == 'prices' ): ?>
            <?php
                $pricesItems = get_sub_field('items');
            ?>
            <?php if($pricesItems) : ?>
                <div class="container">
                    <div class="prices">
                        <div class="prices__row prices__row--head">
                            <div>Leistung</div>
                            <div>Leistungsbeschreibung</div>
                            <div>Preis</div>
                        </div>
                        <div>
                            <?php foreach($pricesItems as $row) { ?>
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

        <?php elseif( get_row_layout() == 'columns_content' ): ?>
            <?php
                $title = get_sub_field('title');
                $text = get_sub_field('text');
            ?>
            <?php if($title) : ?>
                <div class="container">
                    <div class="main-content">
                        <div class="main-content__title">
                            <h2><?php echo $title ?></h2>
                        </div>
                        <div class="main-content__text">
                            <?php echo $text ?>
                        </div>
                    </div>
                </div>
            <?php endif ;?>

        <?php elseif( get_row_layout() == 'content' ): ?>
            <?php
                $content = get_sub_field('wysiwyg');
            ?>
            <?php if($content) : ?>
                <div class="container">
                    <?php echo $content ?>
                </div>
            <?php endif ;?>

        <?php elseif( get_row_layout() == 'features' ): ?>
            <?php
                $title         = get_sub_field('title');
                $featuresItems = get_sub_field('items');
            ?>
            <?php if($featuresItems) : ?>
                <div class="cases">
                    <div class="container">
                        <div class="cases__title">
                            <h2><?php echo $title; ?></h2>
                        </div>
                        <?php if(isset($featuresItems)): ?>
                            <div class="cases__list">
                                <?php foreach($featuresItems as $row) { ?>
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

        <?php elseif( get_row_layout() == 'banner' ): ?>
            <?php
                $banner = get_sub_field('banner_image');
                $logo   = get_field('logo', 'option');
            ?>
            <?php if($banner) : ?>
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

        <?php endif; ?>
    <?php endwhile; ?>
    <?php endif; ?>
</main>

<?php get_footer();

