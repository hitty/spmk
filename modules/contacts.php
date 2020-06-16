<?php
    if( !empty( $this_page->page_parameters[0] ) ) Host::RedirectLevelUp();
    Response::SetBoolean( 'contacts_page', true );
    $module_template = 'templates/includes/contacts.html';
?>
