<?php
    if( !empty( $this_page->page_parameters[0] ) ) Host::RedirectLevelUp();
    Response::SetBoolean( 'zavod', true );
    $module_template = 'templates/zavod.html';
    $GLOBALS['css_set'][] = '/css/about.css';
?>
