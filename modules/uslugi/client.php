<?php
$GLOBALS['js_set'][] = '/modules/uslugi/js/script.js';

$post_parameters = Request::GetParameters( METHOD_POST );
Response::SetArray( 'parameters', $post_parameters );

$action = empty( $this_page->page_parameters[0] ) ? false : $this_page->page_parameters[0];
switch( true ){
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // карточка услуги
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    case !empty( $action ) && count( $this_page->page_parameters ) == 1:

        
        $item = CommonDb::getItem( 'uslugi', $sys_tables['uslugi'] . ".chpu_title = '" . $db->real_escape_string( $this_page->page_parameters[0] ) . "'", $sys_tables['uslugi'] . '.id' );
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
            
            //метаданные для шаринга
            Response::SetArray('open_graph', array(
                    'title' => $item['title'],
                    'description' => $description,
                    'image' => '/img/uploads/big/' . ( !empty( $item['subfolder'] ) ? $item['subfolder'] : ( !empty( $photos[0] ) ? $photos[0]['subfolder'] : '' ) ) . '/' . ( !empty( $item['photo'] ) ? $item['photo'] : ( !empty( $photos[0] ) ? $photos[0]['name'] : '' ) ),
                    'url' => '/uslugi/' . $item['chpu_title'] . '/'
                )
            );        
            
        } else Host::RedirectLevelUp();
        
        $photos = Photos::getList( 'uslugi', $item['id'] );
        if( !empty( $photos[0] ) ) array_shift( $photos );
        Response::SetArray( 'photos', $photos );
        
        $module_template = 'item.html';
        break;
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // список услуг
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    case 
        empty( $action ) :
        //категории
        // фильтры
        $condition = '';
        $conditions = [];
        $sortby = Request::GetInteger( 'sort', METHOD_GET );
        $conditions['published'] = $sys_tables['uslugi'] . ".`published` = 1";
        $condition = implode(" AND ",$conditions);        
        $count = Config::Get( 'string_per_page/uslugi' );
        // создаем пагинатор для списка
        $paginator_where = $condition;
        $paginator = new Paginator( $sys_tables['uslugi'], $count, $paginator_where, false, false, $this_page->real_url );

        $list = CommonDb::getList( 'uslugi', $paginator->getFromString( $paginator->current_page ) . ',' . $count, $condition, 'date DESC', 'id' );
        Response::SetArray( 'list', $list );
        $module_template = 'list.html';
        break;
}
//хлебные крошки
$this_page->clearBreadcrumbs();
$this_page->addBreadcrumbs( 'Услуги', 'uslugi' );
if( !empty( $item ) ) $this_page->addBreadcrumbs( $item['title'], $item['chpu_title'] );

?>
