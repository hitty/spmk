<?php
$GLOBALS['js_set'][] = '/modules/news/js/script.js';

$get_parameters = Request::GetParameters( METHOD_GET );
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
            $count = !empty( $get_parameters['count'] ) ? $get_parameters['count'] : 30;
            $list = CommonDb::getList('news', '0,' . $count, $sys_tables['news'] . '.published = 1 AND ' . $sys_tables['news'] . '.date <= CURDATE()', 'date DESC', 'id');
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
            
            //сео параметры
            $description =  !empty( $item['seo_description'] ) ? $item['seo_description'] : ( empty( $this_page->page_seo_description ) ? $item['content_short'] : $this_page->page_seo_description );
            $this_page->manageMetadata(
                array(
                    'title' => !empty( $item['seo_title'] ) ? $item['seo_title'] : ( empty( $this_page->page_seo_title ) ? $item['title']  : $this_page->page_seo_title ),
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
                    'url' => '/news/' . $item['chpu_title'] . '/'
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
        $conditions['date'] = $sys_tables['news'] . ".`date` <= CURDATE()";
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
//хлебные крошки
$this_page->clearBreadcrumbs();
$this_page->addBreadcrumbs( 'Новости', 'news' );
if( !empty( $category ) ) {
    $this_page->addBreadcrumbs( $category['title'], $category['chpu_title'] );
    if( !empty( $item ) ) $this_page->addBreadcrumbs( $item['title'], $item['chpu_title'] );
}

?>
