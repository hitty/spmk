<?php
 require_once('includes/class.production.php'); // управление проектами

$GLOBALS['css_set'][] = '/modules/production/css/style.css';
$GLOBALS['js_set'][] = '/modules/production/js/script.js';

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
            $parent_id = empty( $this_page->page_parameters[1] ) ? 0 : $this_page->page_parameters[1];
            $where = array( $sys_tables['production'] . '.published = 1' );
            $where[] = $sys_tables['production'] . '.parent_id = ' . Convert::ToInt( $parent_id );
            $list = CommonDb::getList( 'production', false,  implode( ' AND ', $where ), 'position DESC', 'id' );
            Response::SetArray( 'list', $list );
            $module_template = 'block.html';
            if( $ajax_mode ) $ajax_result['ok'] = true;
            break;
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // карточка продукции
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    case !empty( $action ) && count( $this_page->page_parameters ) <= 2:
        $item = CommonDb::getItem( 'production', "chpu_title = '" . $db->real_escape_string( $action ) . "' AND parent_id = 0 ", $sys_tables['production'] . '.id' );
        Response::SetArray( 'item', $item );
        $h1 = array();
        
        if( !empty( $item ) ){ 
            //хлебные крошки
            $this_page->addBreadcrumbs( $item['title'], $item['chpu_title'] );
            
            if( count( $this_page->page_parameters ) == 1 ) Response::SetBoolean( 'show_sub_production_list', true );

            $sub_production_list = CommonDb::getList( 'production', false, $sys_tables['production'] . '.published = 1 AND ' . $sys_tables['production'] . '.parent_id = ' . $item['id'], 'position DESC', 'id' );
            Response::SetArray( 'sub_production_list', $sub_production_list );

            if( count( $this_page->page_parameters ) == 2 ) {
                $item = CommonDb::getItem( 'production', "chpu_title = '" . $db->real_escape_string( $this_page->page_parameters[1] ) . "' AND parent_id = " . $item['id'], $sys_tables['production'] . '.id' );
                $this_page->addBreadcrumbs( $item['title'], $item['chpu_title'] );
                Response::SetArray( 'content_item', $item );
                $action = empty( $this_page->page_parameters[1] ) ? false : $this_page->page_parameters[1];
            }
            if( !empty( $item ) ){ 
                
                Response::SetArray( 'content_item', $item );
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
                        'url' => '/produktsiya/' . $item['chpu_title'] . '/'
                    )
                );        
            } else Host::RedirectLevelUp();    
        } else Host::RedirectLevelUp();
        
        $photos = Photos::getList( 'production', $item['id'] );
        Response::SetArray( 'photos', $photos );
        
        $module_template = 'item.html';
        break;
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // список продукции
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    case empty( $action ):
        $list = CommonDb::getList( 'production', false, $sys_tables['production'] . '.published = 1 AND ' . $sys_tables['production'] . '.parent_id = 0', 'position DESC', 'id' );
        Response::SetArray( 'list', $list );
        $module_template = 'list.html';
        break;
}
Response::SetString( 'action', $action );
?>
