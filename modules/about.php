<?php
    Response::SetBoolean( 'about', true );
    Response::SetBoolean( 'no_sticky_actions', true );
    $module_template = 'templates/about.html';
    $GLOBALS['css_set'][] = '/css/about.css';
?>
