<?php
$GLOBALS['css_set'][] = '/modules/news/css/style.css';
$GLOBALS['js_set'][] = '/modules/news/js/script.js';

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
            $list = CommonDb::getList( 'news', false, $sys_tables['news'] . '.published = 1', 'position date DESC', 'id' );
            Response::SetArray( 'list', $list );
            $module_template = 'block.html';
            if( $ajax_mode ) $ajax_result['ok'] = true;
            break;
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // карточка новости
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    case !empty( $action ) && count( $this_page->page_parameters ) == 2:
        $category = CommonDb::getItem( 'news_categories', "`chpu_title` = '" . $db->real_escape_string( $action ) . "'" );
        if( empty( $category ) ) Host::RedirectLevelUp();

        
        $item = CommonDb::getItem( 'news', $sys_tables['news'] . ".chpu_title = '" . $db->real_escape_string( $this_page->page_parameters[1] ) . "'", $sys_tables['news'] . '.id' );
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
                    'url' => '/proekty/' . $item['chpu_title'] . '/'
                )
            );        
            
        } else Host::RedirectLevelUp();
        
        $photos = Photos::getList( 'news', $item['id'] );
        Response::SetArray( 'photos', $photos );
        
        $module_template = 'item.html';
        break;
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // список новостей
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    case 
        empty( $action ) ||
        ( !empty( $action ) && count( $this_page->page_parameters ) == 1 ):
        //категории
        $categories = CommonDb::getList( 'news_categories');
        Response::SetArray( 'categories', $categories );
        // фильтры
        $condition = '';
        $conditions = [];
        $sortby = Request::GetInteger( 'sort', METHOD_GET );
        $conditions['published'] = $sys_tables['news'] . ".`published` = 1";
        if( !empty( $action ) ) {
            $category = CommonDb::getItem( 'news_categories', "`chpu_title` = '" . $db->real_escape_string( $action ) . "'" );
            if( empty( $category ) ) Host::RedirectLevelUp();
            $conditions['category_chpu_title'] = $sys_tables['news'] . ".`id_category` = " . $category['id'];
            Response::SetString( 'category_title', $category['title'] );
        }
            
        $condition = implode(" AND ",$conditions);        
        $count = Config::Get( 'string_per_page/news' );
        // создаем пагинатор для списка
        $paginator_where = $condition;
        $paginator = new Paginator( $sys_tables['news'], $count, $paginator_where, false, false, $this_page->real_url );

        $list = CommonDb::getList( 'news', $paginator->getFromString( $paginator->current_page ) . ',' . $count, $condition, 'date DESC', 'id' );
        Response::SetArray( 'list', $list );
        $module_template = 'list.html';
        break;
}
?>