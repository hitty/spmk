<?php
    if( !empty( $this_page->page_parameters[0] ) ) Host::RedirectLevelUp();
    Response::SetBoolean( 'calculator', true );
    $module_template = 'templates/calculator.html';
    $GLOBALS['css_set'][] = '/css/calculator.css';
    $GLOBALS['js_set'][] = '/js/jquery.calculator.js';
    $get_parameters = Request::GetParameters( METHOD_GET );
    Response::SetBoolean( 'no_sticky_actions', true );
?>
