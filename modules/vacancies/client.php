<?php
$GLOBALS['css_set'][] = '/modules/vacancies/css/style.css';
$GLOBALS['js_set'][] = '/modules/vacancies/js/script.js';
$action = !empty( $this_page->page_parameters[0] ) ? $this_page->page_parameters[0] : '';

switch( true ){
            
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Список
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    case empty( $action ):
        // формирование списка - актуальные Вакансии
        $where = [];
        $where['published'] = $sys_tables['vacancies'] . '.published = 1';

        $list = CommonDb::getList( 'vacancies', false, implode( " AND ", $where ), 'position DESC, id DESC', 'id' );
        Response::SetArray( 'list', $list );

        //сео параметры
        $description =  empty( $this_page->page_seo_description ) ? "" : $this_page->page_seo_description;
        $this_page->manageMetadata(
            [
                'title' => empty( $this_page->page_seo_title ) ? 'Вакансии компании «СПМК»' : $this_page->page_seo_title,
                'description' =>  $description ,
            ], true
        );
        Response::SetString( 'h1', !empty( $this_page->page_seo_h1 ) ? $this_page->page_seo_h1 : 'Вакансии');
        $module_template = 'mainpage.html';
        break;
}
    
?>
