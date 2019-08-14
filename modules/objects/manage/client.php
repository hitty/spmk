<?php
require_once('includes/class.paginator.php');
require_once('includes/class.photos.php');
$GLOBALS['css_set'][] = '/modules/objects/manage/css/style.css';
$GLOBALS['js_set'][] = '/modules/objects/manage/js/script.js';
// мэппинги модуля
$mapping = include( dirname( __FILE__ ) . '/mapping.php' );

// добавление title
$this_page->manageMetadata( array( 'title' => 'Объекты' ) );

// собираем GET-параметры
$get_parameters = array();
$filters = array();
$filters['title'] = Request::GetString( 'title', METHOD_GET );
$filters['published'] = Request::GetInteger( 'published', METHOD_GET );
if(!empty($filters['title'])) {
    $filters['title'] = urldecode($filters['title']);
    $get_parameters['title'] = $filters['title'];
}
if(!empty($filters['published'])) $get_parameters['published'] = $filters['published']; 
$page = Request::GetInteger('page',METHOD_GET);

if(empty($page)) $page = 1;
else $get_parameters['page'] = $page;

// определяем запрошенный экшн
$action = empty($this_page->page_parameters[0]) ? "" : $this_page->page_parameters[0];
// обработка action-ов
switch( $action ){
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // типы объектов
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    case 'types':
        $action = empty($this_page->page_parameters[1]) ? "" : $this_page->page_parameters[1];
        // обработка action-ов
        switch( $action ){
            ////////////////////////////////////////////////////////////////////////////////////////////////////////
            // фотогалерея объекта
            ////////////////////////////////////////////////////////////////////////////////////////////////////////
            case 'photos':
                if($ajax_mode){
                    // свойства папок для загрузки и формата фотографий
                    Photos::$__folder_options =  array(
                        'sm'   =>  array(130,130,'cut',90),
                        'med'  =>  array(260,190,'cut',90),
                        'big'  =>  array(2000,2000,'',90)
                    );                 

                    $ajax_result['error'] = '';
                    // переопределяем экшн
                    $action = empty($this_page->page_parameters[2]) ? "" : $this_page->page_parameters[2];
                    switch($action){
                        case 'list':
                            $id = Request::GetInteger('id', METHOD_POST);
                            //получение списка фотографий
                            if(!empty($id)){
                                $list = Photos::getList( 'objects_types', $id );
                                if(!empty($list)){
                                    $ajax_result['ok'] = true;
                                    $ajax_result['list'] = $list;
                                    $ajax_result['folder'] = Config::$values['img_folders'];
                                } else $ajax_result['error'] = 'Невозможно построить список фотографий';
                            } else $ajax_result['error'] = 'Неверные входные параметры';
                            break;
                        case 'add':
                            //загрузка фотографий
                            $id = Request::GetInteger('id', METHOD_POST);  
                            if(!empty($id)){
                                $res = Photos::Add( 'objects_types', $id );
                                if(!empty($res)){
                                    if(gettype($res) == 'string') $ajax_result['error'] = $res;  
                                    else {
                                        if(gettype($res) == 'string') $ajax_result['error'] = $res;  
                                        else {
                                            $ajax_result['ok'] = true;
                                            $ajax_result['list'] = $res;
                                        }
                                    }
                                } else $ajax_result['error'] = 'Невозможно выполнить добавление фото';
                            } else $ajax_result['error'] = 'Неверные входные параметры';
                            break;
                        case 'del':
                            //удаление фото
                            $id = Request::GetInteger('id_photo', METHOD_POST);  
                            if( !empty( $id ) ) {
                                $res = Photos::Delete( 'objects_types', $id );
                                if( !empty( $res ) ) {
                                    $ajax_result['ok'] = true;
                                } else $ajax_result['error'] = 'Невозможно выполнить удаление фото';
                            } else $ajax_result['error'] = 'Неверные входные параметры';
                            break;
                        case 'setMain':
                            // установка флага "главное фото" для объекта
                            //id текущей новости
                            $id = Request::GetInteger('id', METHOD_POST);
                            //id фотки
                            $id_photo = Request::GetInteger('id_photo', METHOD_POST);                
                            if(!empty($id_photo)){
                                $res = Photos::setMain( 'objects_types', $id, $id_photo);
                                if(!empty($res)){
                                    $ajax_result['ok'] = true;
                                } else $ajax_result['error'] = 'Невозможно установить статус';
                            } else $ajax_result['error'] = 'Неверные входные параметры';
                            break;   
                    }
                }
                break;
               
            /****************************\
            |* Редактирование объектов  *|
            \***************************/        
            case 'add':
            case 'edit':

                $id = empty($this_page->page_parameters[2]) ? 0 : $this_page->page_parameters[2];
                
                if($action=='add'){
                    // создание болванки новой записи
                    $info = $db->prepareNewRecord($sys_tables['objects_types']);
                    //временный ID для связанных таблиц
                    if( empty( Session::GetInteger( 'common_objects_types_add' ) ) ) {
                        $common_objects_types_add = mt_rand( 800000000, 1000000000 );
                        Session::SetInteger( 'common_objects_types_add', $common_objects_types_add );
                    }
                    Response::SetInteger( 'common_edit', Session::GetInteger( 'common_objects_types_add' ) );
                    
                } else {
                    // получение данных из БД
                    $info = $db->fetch("SELECT *
                                        FROM ".$sys_tables['objects_types']." 
                                        WHERE id=?", $id) ;
                    if( empty( $info ) ) Host::Redirect( '/manage/objects/types/add/' );
                    Response::SetInteger( 'id_objects', $id );
                }
                // перенос дефолтных (считанных из базы) значений в мэппинг формы
                foreach( $info as $key=>$field ){
                    if( !empty( $mapping['objects_types'][$key] ) ) $mapping['objects_types'][$key]['value'] = $info[$key];
                }

                // получение данных, отправленных из формы
                $post_parameters = Request::GetParameters( METHOD_POST );
                
                // если была отправка формы - начинаем обработку
                if( !empty( $post_parameters['submit'] ) ){
                    Response::SetBoolean( 'form_submit', true ); // признак того, что форма была обработана
                    // перенос полученных значений в мэппинг формы для последующего отображения (подмена дефолотных)
                    foreach( $post_parameters as $key => $field ){
                        if( !empty( $mapping['objects_types'][$key] ) ) $mapping['objects_types'][$key]['value'] = $post_parameters[$key];
                    }
                    // проверка значений из формы
                    $errors = Validate::validateParams( $post_parameters, $mapping['objects_types'] );
                    // выписывание ошибок в мэппинг формы (для отображения ошибочных полей)
                    foreach( $errors as $key => $value ){
                        if( !empty( $mapping['objects_types'][$key] ) ) $mapping['objects_types'][$key]['error'] = $value;
                    }
                    // если ошибок не было - готовимся к сохранению данных в БД и производим попытку сохранения
                    if( empty( $errors ) ) {
                        // подготовка всех значений для сохранения
                        foreach($info as $key=>$field){
                            if(isset($mapping['objects_types'][$key]['value'])) $info[$key] = $mapping['objects_types'][$key]['value'];
                        }
                        // сохранение в БД
                        if($action=='edit'){
                            $res = $db->updateFromArray($sys_tables['objects_types'], $info, 'id') or die($db->error);
                        } else {
                            $res = $db->insertFromArray($sys_tables['objects_types'], $info, 'id');
                            if(!empty($res)){
                                $new_id = $db->insert_id;
                                
                                //обновление временных данных
                                //перенос фотографий
                                Photos::setMain( 'objects_types', $new_id, false, 'id_parent',  Session::GetInteger( 'common_objects_types_add' ) );
                                Session::SetInteger( 'common_objects_types_add', 0 );
                                // редирект на редактирование свеженькой страницы
                                if(!empty($res) && empty( $ajax_mode ) ) {
                                    header('Location: '.Host::getWebPath('/manage/objects/types/edit/'.$new_id.'/'));
                                    exit(0);
                                }
                            }
                        }
                        Response::SetBoolean('saved', $res); // результат сохранения
                    } else Response::SetBoolean('errors', true); // признак наличия ошибок
                } else {
                    if( $action == 'add' ) {
                        //удаление временных данных
                        Photos::DeleteAll( 'objects_types', false, Session::GetInteger( 'common_objects_types_add' ) );
                    }
                }
                // если мы попали на страницу редактирования путем редиректа с добавления, 
                // значит мы успешно создали новый объект, нужно об этом сообщить в шаблон
                $referer = Host::getRefererURL();
                if($action=='edit' && !empty($referer) && substr($referer,-5)=='/add/') {
                    Response::SetBoolean('form_submit', true);
                    Response::SetBoolean('saved', true);
                }
                // запись данных для отображения на странице
                Response::SetArray( 'data_mapping', $mapping['objects_types'] );

                if( ! ( !empty( $ajax_mode ) && !empty( $post_parameters['submit'] ) ) )  $module_template = '/modules/objects/manage/templates/types.edit.html';
                
                //попап добавление
                if( $ajax_mode ) {
                    $ajax_result['ok'] = true;
                    $ajax_result['title'] = $info['title'];
                    $ajax_result['id'] = !empty( $new_id ) ? $new_id : 0;
                }
                break;
            case 'delete':
                if( $ajax_mode ){
                    $post_parameters = Request::GetParameters( METHOD_POST );
                    if( empty( $post_parameters['submit'] ) ) {
                        //попап
                        Response::SetString( 'url', '/' . $this_page->real_url . '/' );
                        $module_template = '/templates/types.popup.delete.html';
                        Response::SetString( 'title', 'Объекты' );
                        $ajax_result = array('ok' => true );
                    } else {
                        //удаление
                        $id = empty($this_page->page_parameters[2]) ? 0 : $this_page->page_parameters[2];
                        
                        $res = $db->query("DELETE FROM ".$sys_tables['objects_types']." WHERE id=?", $id);
                        $results['delete'] = ($res && $db->affected_rows) ? $id : -1;
                        $ajax_result = array('ok' => $results['delete']>0, 'ids'=>array($id));
                    }                                       
                    
                }
                break;
            default:
                $module_template = '/modules/objects/manage/templates/types.list.html';
                // формирование списка
                $conditions = array( );
                if(!empty($filters)){
                    if(!empty($filters['title'])) $conditions['title'] = $sys_tables['objects_types'].".`title` LIKE '%".$db->real_escape_string($filters['title'])."%'";
                    if(!empty($filters['published'])) $conditions['published'] = $sys_tables['objects_types'].".`published` = ".$db->real_escape_string($filters['published'])."";
                }
                // формирование списка для фильтра
                $condition = implode(" AND ",$conditions);        
                // создаем пагинатор для списка
                $paginator = new Paginator( $sys_tables['objects_types'], 30, $condition );
                // get-параметры для ссылок пагинатора
                $get_in_paginator = array();
                foreach($get_parameters as $gk=>$gv){
                    if($gk!='page') $get_in_paginator[] = $gk.'='.$gv;
                }
                // ссылка пагинатора
                $paginator->link_prefix = '/manage/objects'                // модуль
                                          ."/?"                                       // конечный слеш и начало GET-строки
                                          .implode('&',$get_in_paginator)             // GET-строка
                                          .(empty($get_in_paginator)?"":'&')."page="; // параметр для номера страницы
                if($paginator->pages_count>0 && $paginator->pages_count<$page){
                    Header('Location: '.Host::getWebPath($paginator->link_prefix.$paginator->pages_count));
                    exit(0);
                }
                // формирование списка
                $list = CommonDb::getList( 'objects_types', $paginator->getLimitString($page), $condition, 'position DESC', 'id' );
                //подсчет курсов
                Response::SetArray( 'list', $list );
                Response::SetArray( 'paginator', $paginator->Get($page) );
                break;
        }        
        break;
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // фотогалерея объекта
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    case 'photos':
        if($ajax_mode){
            // свойства папок для загрузки и формата фотографий
            Photos::$__folder_options =  array(
                'sm'   =>  array(130,130,'cut',90),
                'med'  =>  array(260,190,'cut',90),
                'big'  =>  array(2000,2000,'',90)
            );                 

            $ajax_result['error'] = '';
            // переопределяем экшн
            $action = empty($this_page->page_parameters[1]) ? "" : $this_page->page_parameters[1];
            switch($action){
                case 'list':
                    $id = Request::GetInteger('id', METHOD_POST);
                    //получение списка фотографий
                    if(!empty($id)){
                        $list = Photos::getList( 'objects', $id );
                        if(!empty($list)){
                            $ajax_result['ok'] = true;
                            $ajax_result['list'] = $list;
                            $ajax_result['folder'] = Config::$values['img_folders'];
                        } else $ajax_result['error'] = 'Невозможно построить список фотографий';
                    } else $ajax_result['error'] = 'Неверные входные параметры';
                    break;
                case 'add':
                    //загрузка фотографий
                    $id = Request::GetInteger('id', METHOD_POST);  
                    if(!empty($id)){
                        $res = Photos::Add( 'objects', $id );
                        if(!empty($res)){
                            if(gettype($res) == 'string') $ajax_result['error'] = $res;  
                            else {
                                if(gettype($res) == 'string') $ajax_result['error'] = $res;  
                                else {
                                    $ajax_result['ok'] = true;
                                    $ajax_result['list'] = $res;
                                }
                            }
                        } else $ajax_result['error'] = 'Невозможно выполнить добавление фото';
                    } else $ajax_result['error'] = 'Неверные входные параметры';
                    break;
                case 'del':
                    //удаление фото
                    $id = Request::GetInteger('id_photo', METHOD_POST);  
                    if( !empty( $id ) ) {
                        $res = Photos::Delete( 'objects', $id );
                        if( !empty( $res ) ) {
                            $ajax_result['ok'] = true;
                        } else $ajax_result['error'] = 'Невозможно выполнить удаление фото';
                    } else $ajax_result['error'] = 'Неверные входные параметры';
                    break;
                case 'setMain':
                    // установка флага "главное фото" для объекта
                    //id текущей новости
                    $id = Request::GetInteger('id', METHOD_POST);
                    //id фотки
                    $id_photo = Request::GetInteger('id_photo', METHOD_POST);                
                    if(!empty($id_photo)){
                        $res = Photos::setMain( 'objects', $id, $id_photo);
                        if(!empty($res)){
                            $ajax_result['ok'] = true;
                        } else $ajax_result['error'] = 'Невозможно установить статус';
                    } else $ajax_result['error'] = 'Неверные входные параметры';
                    break;   
            }
        }
        break;
       
    /****************************\
    |* Редактирование объектов  *|
    \***************************/        
    case 'add':
    case 'edit':

        $id = empty($this_page->page_parameters[1]) ? 0 : $this_page->page_parameters[1];
        
        if($action=='add'){
            // создание болванки новой записи
            $info = $db->prepareNewRecord($sys_tables['objects']);
            //временный ID для связанных таблиц
            if( empty( Session::GetInteger( 'common_objects_add' ) ) ) {
                $common_objects_add = mt_rand( 800000000, 1000000000 );
                Session::SetInteger( 'common_objects_add', $common_objects_add );
            }
            Response::SetInteger( 'common_edit', Session::GetInteger( 'common_objects_add' ) );
            
        } else {
            // получение данных из БД
            $info = $db->fetch("SELECT *
                                FROM ".$sys_tables['objects']." 
                                WHERE id=?", $id) ;
            if( empty( $info ) ) Host::Redirect( '/manage/objects/add/' );
            Response::SetInteger( 'id_objects', $id );
        }
        // перенос дефолтных (считанных из базы) значений в мэппинг формы
        foreach( $info as $key=>$field ){
            if( !empty( $mapping['objects'][$key] ) ) $mapping['objects'][$key]['value'] = $info[$key];
        }
        $sprav_list = array(
            'id_type' => 'objects_types'
        );
        foreach( $sprav_list as $field => $table ){
            $list = $db->fetchall( "SELECT * FROM ".$sys_tables[ $table ] . " ORDER BY title DESC, id DESC" );
            foreach( $list as $key=>$val ) $mapping['objects'][$field]['values'][] = $val;
        }

        // получение данных, отправленных из формы
        $post_parameters = Request::GetParameters( METHOD_POST );
        
        // если была отправка формы - начинаем обработку
        if( !empty( $post_parameters['submit'] ) ){
            Response::SetBoolean( 'form_submit', true ); // признак того, что форма была обработана
            // перенос полученных значений в мэппинг формы для последующего отображения (подмена дефолотных)
            foreach( $post_parameters as $key => $field ){
                if( !empty( $mapping['objects'][$key] ) ) $mapping['objects'][$key]['value'] = $post_parameters[$key];
            }
            // проверка значений из формы
            $errors = Validate::validateParams( $post_parameters, $mapping['objects'] );
            // выписывание ошибок в мэппинг формы (для отображения ошибочных полей)
            foreach( $errors as $key => $value ){
                if( !empty( $mapping['objects'][$key] ) ) $mapping['objects'][$key]['error'] = $value;
            }
            // если ошибок не было - готовимся к сохранению данных в БД и производим попытку сохранения
            if( empty( $errors ) ) {
                // подготовка всех значений для сохранения
                foreach($info as $key=>$field){
                    if(isset($mapping['objects'][$key]['value'])) $info[$key] = $mapping['objects'][$key]['value'];
                }
                $info['id_user'] = $auth->id;
                if( empty( $info['chpu_title'] ) ) $info['chpu_title'] = Convert::chpuTitle( $info['title'] );
                
                // сохранение в БД
                if($action=='edit'){
                    $res = $db->updateFromArray($sys_tables['objects'], $info, 'id') or die($db->error);
                } else {
                    $res = $db->insertFromArray($sys_tables['objects'], $info, 'id');
                    if(!empty($res)){
                        $new_id = $db->insert_id;
                        
                        //обновление временных данных
                        //перенос фотографий
                        Photos::setMain( 'objects', $new_id, false, 'id_parent',  Session::GetInteger( 'common_objects_add' ) );
                        Session::SetInteger( 'common_objects_add', 0 );
                        // редирект на редактирование свеженькой страницы
                        if(!empty($res) && empty( $ajax_mode ) ) {
                            header('Location: '.Host::getWebPath('/manage/objects/edit/'.$new_id.'/'));
                            exit(0);
                        }
                    }
                }
                Response::SetBoolean('saved', $res); // результат сохранения
            } else Response::SetBoolean('errors', true); // признак наличия ошибок
        } else {
            if( $action == 'add' ) {
                //удаление временных данных
                Photos::DeleteAll( 'objects', false, Session::GetInteger( 'common_objects_add' ) );
            }
        }
        // если мы попали на страницу редактирования путем редиректа с добавления, 
        // значит мы успешно создали новый объект, нужно об этом сообщить в шаблон
        $referer = Host::getRefererURL();
        if($action=='edit' && !empty($referer) && substr($referer,-5)=='/add/') {
            Response::SetBoolean('form_submit', true);
            Response::SetBoolean('saved', true);
        }
        // запись данных для отображения на странице
        Response::SetArray( 'data_mapping', $mapping['objects'] );

        if( ! ( !empty( $ajax_mode ) && !empty( $post_parameters['submit'] ) ) )  $module_template = '/modules/objects/manage/templates/edit.html';
        
        //попап добавление
        if( $ajax_mode ) {
            $ajax_result['ok'] = true;
            $ajax_result['title'] = $info['title'];
            $ajax_result['id'] = !empty( $new_id ) ? $new_id : 0;
        }
        break;
    case 'delete':
        if( $ajax_mode ){
            $post_parameters = Request::GetParameters( METHOD_POST );
            if( empty( $post_parameters['submit'] ) ) {
                //попап
                Response::SetString( 'url', '/' . $this_page->real_url . '/' );
                $module_template = '/templates/popup.delete.html';
                Response::SetString( 'title', 'Объекты' );
                $ajax_result = array('ok' => true );
            } else {
                //удаление
                $id = empty($this_page->page_parameters[1]) ? 0 : $this_page->page_parameters[1];
                
                $res = $db->query("DELETE FROM ".$sys_tables['objects']." WHERE id=?", $id);
                $results['delete'] = ($res && $db->affected_rows) ? $id : -1;
                $ajax_result = array('ok' => $results['delete']>0, 'ids'=>array($id));
            }                                       
            
        }
        break;
    default:
        $module_template = '/modules/objects/manage/templates/list.html';
        // формирование списка
        $conditions = array( );
        if(!empty($filters)){
            if(!empty($filters['title'])) $conditions['title'] = $sys_tables['objects'].".`title` LIKE '%".$db->real_escape_string($filters['title'])."%'";
            if(!empty($filters['published'])) $conditions['published'] = $sys_tables['objects'].".`published` = ".$db->real_escape_string($filters['published'])."";
        }
        // формирование списка для фильтра
        $condition = implode(" AND ",$conditions);        
        // создаем пагинатор для списка
        $paginator = new Paginator( $sys_tables['objects'], 30, $condition );
        // get-параметры для ссылок пагинатора
        $get_in_paginator = array();
        foreach($get_parameters as $gk=>$gv){
            if($gk!='page') $get_in_paginator[] = $gk.'='.$gv;
        }
        // ссылка пагинатора
        $paginator->link_prefix = '/manage/objects'                // модуль
                                  ."/?"                                       // конечный слеш и начало GET-строки
                                  .implode('&',$get_in_paginator)             // GET-строка
                                  .(empty($get_in_paginator)?"":'&')."page="; // параметр для номера страницы
        if($paginator->pages_count>0 && $paginator->pages_count<$page){
            Header('Location: '.Host::getWebPath($paginator->link_prefix.$paginator->pages_count));
            exit(0);
        }
        // формирование списка
        $list = CommonDb::getList( 'objects', $paginator->getLimitString($page), $condition, 'position DESC', 'id' );
        //подсчет курсов
        Response::SetArray( 'list', $list );
        Response::SetArray( 'paginator', $paginator->Get($page) );
        break;
}
// запоминаем для шаблона GET - параметры
Response::SetArray('get_array', $get_parameters);
foreach($get_parameters as $gk=>$gv) $get_parameters[$gk] = $gv;
Response::SetString('get_string', implode('&',$get_parameters));

?>
