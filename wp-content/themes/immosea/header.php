<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8"/>
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri() ?>/favicons//apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_template_directory_uri() ?>/favicons//favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_template_directory_uri() ?>/favicons//favicon-16x16.png">
    <link rel="manifest" href="<?php echo get_template_directory_uri() ?>/favicons//site.webmanifest">
    <link rel="mask-icon" href="<?php echo get_template_directory_uri() ?>/favicons//safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link
        rel="preload"
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;400;500;700;900&family=Open+Sans:wght@300;400;600;700;800&family=Yeseva+One&display=swap"
        as="style"
        onload="this.onload=null;this.rel='stylesheet'"
    />
    <noscript>
        <link
            href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;400;500;700;900&family=Open+Sans:wght@300;400;600;700;800&family=Yeseva+One&display=swap"
            rel="stylesheet"
            type="text/css"
        />
    </noscript>
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#fe0524">
    <?php wp_head(); ?>
</head>
<body <?php body_class()?>>

<div class="wrapper">
    <header class="header">
        <div class="header__logo">
            <?php
                $logo = get_field('logo', 'option');
            ?>
            <a href="<?= esc_url(home_url('/')); ?>">
                <img src="<?php echo $logo['url'] ?>" alt="immosea">
            </a>
        </div>
        <button class="header__toggle" data-toggle><span>Menu</span></button>
        <div class="header__content">
            <?php
                $args = array(
                    'theme_location'=>'menu',
                    'container'=>'',
                    'menu_class'=>'header__nav',
                );
                wp_nav_menu($args);
            ?>

            <?php
                $social = get_field('social_links', 'option');
            ?>
            <?php if($social): ?>
                <ul class="social-links tablet-visible">
                    <?php foreach($social as $row) { ?>
                        <li>
                            <a href="<?php echo $row['link']['url'] ?>" target="_blank">
                                <img src="<?php echo $row['icon']['url'] ?>" class="style-svg">
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            <?php endif; ?>

            <?php
                $button = get_field('link_to_form', 'option');
                $header_phone = get_field('header_phone', 'option');
                $vowels = array("(", ")", " ", "-");
                $n_phone = str_replace($vowels, "", $header_phone);
            ?>
            <?php if (!empty($header_phone)) { ?>
            <a href="tel:<?php echo $n_phone; ?>" class="header__nav-link">
                <?php echo $header_phone; ?>
            </a>
            <?php } ?>

            <?php
                $body_classes = get_body_class();
                if(in_array('page-template-page-wizard', $body_classes)) {}
                else { ?>
                    <?php if($button): ?>
                        <a href="<?php echo $button['url'] ?>"
                           class="button button--outline button--small header__button"
                        >
                            <?php echo $button['title'] ?>
                        </a>
                    <?php endif; ?>
                <?php }
            ?>
        </div>
    </header>
