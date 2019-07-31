<?php
$GLOBALS['css_set'][] = '/modules/zakupki/css/style.css';
$action = !empty( $this_page->page_parameters[0] ) ? $this_page->page_parameters[0] : '';
switch( true ){
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Закупки : Поставщикам
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    case $action == 'postavschikam':
        $this_page->addBreadcrumbs( 'Поставщикам', 'postavschikam' );
        $module_template = 'postavschikam.html';
        break;
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Закупки : Тендеры
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    case $action == 'tendery':
        $action = !empty( $this_page->page_parameters[1] ) ? $this_page->page_parameters[1] : '';
        $this_page->addBreadcrumbs( 'Тендеры', 'tendery' );
        switch( true ){
            ////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Карточка
            ////////////////////////////////////////////////////////////////////////////////////////////////////////
            case !empty( $action ) && count( $this_page->page_parameters ) == 2:
                
                $item = CommonDb::getItem( 'tendery', $sys_tables['tendery'] . ".chpu_title = '" . $db->real_escape_string( $this_page->page_parameters[1] ) . "'", $sys_tables['tendery'] . '.id' );
                Response::SetArray( 'item', $item );
                
                $h1 = array();
                
                if( !empty( $item ) ){ 
                    
                    //сео параметры
                    $description =  !empty( $item['seo_description'] ) ? $item['seo_description'] : ( empty( $this_page->page_seo_description ) ? $item['content_short'] : $this_page->page_seo_description );
                    $this_page->manageMetadata(
                        array(
                            'title' => !empty( $item['seo_title'] ) ? $item['seo_title'] : ( empty( $this_page->page_seo_title ) ? $item['title'] . ' - продукция завода «Конструктив»' : $this_page->page_seo_title ),
                            'description' =>  $description ,
                        ), true
                    );
                
                    //h1
                    $h1 = empty($this_page->page_seo_h1) ? ( empty( $item['seo_h1'] ) ? $item['title'] : $item['seo_h1'] ) : $this_page->page_seo_h1;;
                    Response::SetString('h1', $h1);  
                    
                } else Host::RedirectLevelUp();
                
                $module_template = 'tendery.item.html';
                break;
            ////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Список
            ////////////////////////////////////////////////////////////////////////////////////////////////////////
            case empty( $action ):
                // формирование списка - актуальные тендеры
                $where = [];
                $where['date_start'] = $sys_tables['tendery'] . '.date_start <= NOW() ';
                $where['date_end'] = $sys_tables['tendery'] . '.date_end >= NOW() ';
                $actual_list = CommonDb::getList( 'tendery', false, implode( " AND ", $where ), 'date_start DESC, date_end DESC', 'id' );
                Response::SetArray( 'actual_list', $actual_list );
                // формирование списка - архивные тендеры
                $where = [];
                $where['date_start'] = $sys_tables['tendery'] . '.date_start < NOW() ';
                $where['date_start'] = $sys_tables['tendery'] . '.date_end < NOW() ';
                $archive_list = CommonDb::getList( 'tendery', false, implode( " AND ", $where ), 'date_start DESC, date_end DESC', 'id' );
                Response::SetArray( 'archive_list', $archive_list );
                
                
                $module_template = 'tendery.list.html';
                break;
        }
        break;
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Главная страница
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    default:
        Host::RedirectLevelUp();
        break;
}
    
?>
