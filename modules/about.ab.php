<?php
    Response::SetBoolean( 'about', true );
    Response::SetBoolean( 'no_sticky_actions', true );
    $module_template =  'templates/about.ab.html';
    $GLOBALS['css_set'][] = '/css/about.css';
    $GLOBALS['css_set'][] = '/css/ab/about.css';
    Session::SetString( 'main_url', '/lndng/' );
?>
