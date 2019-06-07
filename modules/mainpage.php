<?php
    Response::SetBoolean( 'mainpage', true );
    $module_template = 'templates/mainpage.html';
    $this_page->manageMetadata(
        array( 'title' => 'Премия города Москва в области архитектуры и градостроительства' ),
        true
    );
?>
