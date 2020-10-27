<?php
$GLOBALS['css_set'][] = '/css/objects.css';
$GLOBALS['css_set'][] = '/modules/objects/css/style.css';
$GLOBALS['js_set'][] = '/modules/objects/js/script.js';
$GLOBALS['js_set'][] = '/js/gallery/script.js';
$GLOBALS['css_set'][] = '/js/gallery/style.css';          

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
            //похожие объекты
            $similar = empty( $this_page->page_parameters[1] ) ? false : true;
            $count = !empty( $similar ) ? 6 : false;
            Response::SetBoolean( 'similar', $similar );
            //id карточки
            $id = empty( $this_page->page_parameters[2] ) ? false : $this_page->page_parameters[2];
            //ловия
            $clauses = [];
            $clauses['published'] = $sys_tables['objects'] . '.published = 1';
            if( !empty( $id ) ) $clauses['id'] = $sys_tables['objects'] . '.id != ' . $id;
            
            $list = CommonDb::getList( 'objects', $count, implode( " AND ", $clauses ), 'position DESC', 'id' );
            Response::SetArray( 'list', $list );
            $module_template = 'block.html';
            if( $ajax_mode ) $ajax_result['ok'] = true;
            $get_parameters = Request::GetParameters( METHOD_GET );
            break;
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // карточка объектов
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    case !empty( $action ) && count( $this_page->page_parameters ) == 1:
        $item = CommonDb::getItem( 'objects', "chpu_title = '" . $db->real_escape_string( $action ) . "'", $sys_tables['objects'] . '.id' );
        Response::SetArray( 'item', $item );
        
        $h1 = array();
        
        if( !empty( $item ) ){ 
            //хлебные крошки
            $this_page->addBreadcrumbs( $item['title'], $item['chpu_title'] );
            
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
                    'url' => '/objekty/' . $item['chpu_title'] . '/'
                )
            );        
            
        } else Host::RedirectLevelUp();
        
        $photos = Photos::getList( 'objects', $item['id'] );
        Response::SetArray( 'photos', $photos );
        
        $module_template = 'item.html';
        break;
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // список объектов
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    case empty( $action ):
        $GLOBALS['css_set'][] = '/css/objects.css';
        if( $this_page->first_instance ){
            $types = CommonDb::getList( 'objects_types', false, $sys_tables['objects_types'] . ".id NOT IN (4,6) " );
            Response::SetArray( 'types', $types );
        }
        $parameters = Request::GetParameters();
        //условия
        $conditions = [];
        $conditions[] = $sys_tables['objects'] . '.published = 1';

        Response::SetString( 'service', '' );
        if( !empty( $parameters['service'] ) ) {
            if (in_array($parameters['service'], ['proektirovanie', 'izgotovlenie', 'dostavka', 'montazh', 'podkluch', 'komplektaciya'])) {
                Response::SetString( 'service', $parameters['service'] );
                $conditions['service'] = $sys_tables['objects']. '.' . $parameters['service'] . ' = 1';
            }
            else Host::Redirect( '/objekty/');
            
        }
        if( !empty( $parameters['type'] ) ) {
            $type = CommonDb::getItem( 'objects_types', $sys_tables['objects_types'] . '.id = ' . $db->real_escape_string( $parameters['type'] ));
            if( empty( $type ) ) Host::Redirect( '/objekty/' );
            Response::SetInteger( 'type', $parameters['type'] );
            $conditions[] = $sys_tables['objects'] . '.id_type = ' . $type['id'];
            $this_page->manageMetadata(
                [
                    'title' => !empty($this_page->page_seo_title) ? $this_page->page_seo_title : $type['title'] . ' - объекты компании «СПМК». Изготовление металлоконструкций от 200 тонн',
                    'description' => !empty($this_page->page_seo_description) ? $this_page->page_seo_description : $type['title'] . ' - объекты компании «СПМК».'
                ], true
            );
            Response::SetString( 'h1', $type['title'] );
        } else Response::SetInteger( 'type', 0 );
        
        
        $list = CommonDb::getList( 'objects', false, implode( " AND ", $conditions ) , 'id DESC', 'id' );
        foreach( $list as $k => $item ) $list[$k]['photos'] = Photos::getList( 'objects', $item['id'], 5 );
        Response::SetArray( 'list', $list );
        $module_template = 'list.html';
        break;
}
?>
