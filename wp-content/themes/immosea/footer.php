<?php
    $info   = get_field('info', 'option');
    $copy   = get_field('copy', 'option');
    $social = get_field('social_links', 'option');
?>

<footer class="footer">
    <div class="container">
        <div class="footer__holder">
            <div class="footer__info">
                <div class="footer__logo">Logo</div>
                <?php echo $info; ?>

                <?php if($social): ?>
                    <ul class="social-links social-links--inline">
                        <?php foreach($social as $row) { ?>
                            <li>
                                <a href="<?php echo $row['link']['url'] ?>" target="_blank">
                                    <img src="<?php echo $row['icon']['url'] ?>" class="style-svg">
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php endif; ?>
            </div>
            <?php
                $args = array(
                    'theme_location'=>'footer',
                    'container'=>'',
                    'menu_class'=>'footer__nav',
                );
                wp_nav_menu($args);
            ?>
        </div>
        <div class="footer__copy">
            <?php echo $copy; ?>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>

<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<!--<script src="--><?php //echo get_template_directory_uri() ?><!--/app/dist/my-app.js"></script>-->

</div>
</body>
</html>
