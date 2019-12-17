<?php
    Response::SetBoolean( 'about', true );
    Response::SetBoolean( 'no_sticky_actions', true );
    $GLOBALS['css_set'][] = '/css/about.css';
    $module_template =  'templates/about.ab.html';
    $GLOBALS['css_set'][] = '/js/carousel.flickity/styles.css';
    $GLOBALS['js_set'][] = '/js/carousel.flickity/script.js';
    $GLOBALS['css_set'][] = '/css/ab/about.css';
    Session::SetString( 'main_url', '/lndng/' );
?>
