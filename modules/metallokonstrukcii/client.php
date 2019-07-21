<?php
$GLOBALS['css_set'][] = '/modules/metallokonstrukcii/css/style.css';
$GLOBALS['js_set'][] = '/modules/metallokonstrukcii/js/script.js';

$post_parameters = Request::GetParameters( METHOD_POST );
Response::SetArray( 'parameters', $post_parameters );

$action = empty( $this_page->page_parameters[0] ) ? false : $this_page->page_parameters[0];
switch( true ){
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // блоки
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    case !empty( $action ) && $action == 'block':    
        ////////////////////////////////////////////////////////////////////////////////////////////////////////
        // блок
        ////////////////////////////////////////////////////////////////////////////////////////////////////////
        case $action == 'block':
            $list = CommonDb::getList( 'metallokonstrukcii', false, $sys_tables['metallokonstrukcii'] . '.published = 1 AND ' . $sys_tables['metallokonstrukcii'] . '.parent_id = 0', 'position DESC', 'id' );
            Response::SetArray( 'list', $list );
            $module_template = 'block.html';
            if( $ajax_mode ) $ajax_result['ok'] = true;
            break;
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // карточка металлоконструкции
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    case !empty( $action ) && count( $this_page->page_parameters ) == 1:
        $item = CommonDb::getItem( 'metallokonstrukcii', "chpu_title = '" . $db->real_escape_string( $action ) . "'", $sys_tables['metallokonstrukcii'] . '.id' );
        Response::SetArray( 'item', $item );
        
        $h1 = array();
        
        if( !empty( $item ) ){ 
            //хлебные крошки
            $this_page->addBreadcrumbs( $item['title'], $item['chpu_title'] );
            
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
            
            //метаданные для шаринга
            Response::SetArray('open_graph', array(
                    'title' => $item['title'],
                    'description' => $description,
                    'image' => '/img/uploads/big/' . ( !empty( $item['subfolder'] ) ? $item['subfolder'] : ( !empty( $photos[0] ) ? $photos[0]['subfolder'] : '' ) ) . '/' . ( !empty( $item['photo'] ) ? $item['photo'] : ( !empty( $photos[0] ) ? $photos[0]['name'] : '' ) ),
                    'url' => '/metallokonstrukcii/' . $item['chpu_title'] . '/'
                )
            );        
            
        } else Host::RedirectLevelUp();
        
        $photos = Photos::getList( 'metallokonstrukcii', $item['id'] );
        Response::SetArray( 'photos', $photos );
        
        $module_template = 'item.html';
        break;
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // список металлоконструкций
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    case empty( $action ):
        $list = CommonDb::getList( 'metallokonstrukcii', false, $sys_tables['metallokonstrukcii'] . '.published = 1 AND ' . $sys_tables['metallokonstrukcii'] . '.parent_id = 0', 'position DESC', 'id' );
        Response::SetArray( 'list', $list );
        $module_template = 'list.html';
        break;
}
?>