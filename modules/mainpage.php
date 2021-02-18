<?php
    if( !empty( $this_page->page_parameters[0] ) ) Host::RedirectLevelUp();
    Response::SetBoolean( 'mainpage', true );
    Response::SetBoolean( 'no_sticky_actions', true );
    $module_template =  'templates/mainpage.html';
    $GLOBALS['css_set'][] = '/css/mainpage.css';
    $GLOBALS['css_set'][] = '/css/mainpage.header.css';


?>
