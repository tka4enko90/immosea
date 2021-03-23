<?php
/* Template Name: Form Page */
get_header(); ?>

    <main class="main">

        <!--    <form action="">-->
        <!--        <input id="loadFile" type="file" onchange="readAsBase64()">-->
        <!--    </form>-->
        <div class="container container--expand">
            <my-app></my-app>
        </div>

    </main>
    <script src="https://unpkg.com/vue"></script>
    <script src="<?php echo get_template_directory_uri() ?>/app/dist/my-app.js"></script>
<?php get_footer();

