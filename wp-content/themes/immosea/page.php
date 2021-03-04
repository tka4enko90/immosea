<?php
use Timber\Timber;
if (class_exists('Timber')) {
    $context['options'] = Timber::get_context();
    if (class_exists('CustomMultipagePlugin')) {
        $CustomMultipagePlugin = new CustomMultipagePlugin($context);
    }

    get_header();
    if (class_exists('Timber')) {
        if ($CustomMultipagePlugin) {
            $CustomMultipagePlugin->renderPage($context);
        }
    }
}
get_footer();
