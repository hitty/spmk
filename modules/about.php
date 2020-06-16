<?php
    if( !empty( $this_page->page_parameters[0] ) ) Host::RedirectLevelUp();
    Response::SetBoolean( 'about', true );
    Response::SetBoolean( 'no_sticky_actions', true );
    $module_template = 'templates/about.html';
    $GLOBALS['css_set'][] = '/css/about.css';
    $GLOBALS['css_set'][] = '/css/ab/about.css';
    $get_parameters = Request::GetParameters( METHOD_GET );

?>
