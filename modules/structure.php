<?php
    if( !empty( $this_page->page_parameters[0] ) ) Host::RedirectLevelUp();
    Response::SetBoolean( 'structure', true );
    $module_template = 'templates/structure.html';
    $GLOBALS['css_set'][] = '/css/structure.css';
    
    //photos
    //montazh
    $montazh_photos = Photos::getList( 'uslugi', 2, 4 );
    if( !empty($montazh_photos) ){
        array_shift($montazh_photos);
        Response::SetArray( 'montazh_photos', $montazh_photos );
    }
    
?>
