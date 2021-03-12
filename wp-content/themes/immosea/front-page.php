<?php
/* Template Name: Home */

get_header();
?>
<main>
    <form action="">
        <input id="loadFile" type="file" onchange="readAsBase64()">
    </form>
    <my-app></my-app>
</main>
<?php
get_footer();

