<?php
    if( !empty( $this_page->page_parameters[0] ) ) Host::RedirectLevelUp();
    Response::SetBoolean( 'about', true );
    Response::SetBoolean( 'no_sticky_actions', true );
    $module_template = 'templates/about.html';
    $GLOBALS['css_set'][] = '/css/about.css';
    $get_parameters = Request::GetParameters( METHOD_GET );
    if( !empty( $get_parameters['v'] ) && $get_parameters['v'] == '2' ) {
        $module_template =  'templates/about.ab.html';
        $GLOBALS['css_set'][] = '/css/ab/about.css';
    }

?>
