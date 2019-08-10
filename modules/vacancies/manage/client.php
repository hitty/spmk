<?php
require_once('includes/class.paginator.php');
$GLOBALS['css_set'][] = '/modules/vacancies/manage/css/style.css';
$GLOBALS['js_set'][] = '/modules/vacancies/manage/js/script.js';
// мэппинги модуля
$mapping = include( dirname( __FILE__ ) . '/mapping.php' );

// добавление title
$this_page->manageMetadata( array( 'title' => 'Вакансии' ) );

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
        
    /****************************\
    |* Редактирование  *|
    \***************************/        
    case 'add':
    case 'edit':

        $id = empty($this_page->page_parameters[1]) ? 0 : $this_page->page_parameters[1];
        
        if($action=='add'){
            // создание болванки новой записи
            $info = $db->prepareNewRecord($sys_tables['vacancies']);
            //временный ID для связанных таблиц
            if( empty( Session::GetInteger( 'common_vacancies_add' ) ) ) {
                $common_vacancies_add = mt_rand( 800000000, 1000000000 );
                Session::SetInteger( 'common_vacancies_add', $common_vacancies_add );
            }
            Response::SetInteger( 'common_edit', Session::GetInteger( 'common_vacancies_add' ) );
            
        } else {
            // получение данных из БД
            $info = $db->fetch("SELECT *
                                FROM ".$sys_tables['vacancies']." 
                                WHERE id=?", $id) ;
            if( empty( $info ) ) Host::Redirect( '/manage/vacancies/add/' );
            Response::SetInteger( 'id_vacancies', $id );
        }
        // перенос дефолтных (считанных из базы) значений в мэппинг формы
        foreach( $info as $key=>$field ){
            if( !empty( $mapping['vacancies'][$key] ) ) $mapping['vacancies'][$key]['value'] = $info[$key];
        }

        // получение данных, отправленных из формы
        $post_parameters = Request::GetParameters( METHOD_POST );
        
        // если была отправка формы - начинаем обработку
        if( !empty( $post_parameters['submit'] ) ){
            Response::SetBoolean( 'form_submit', true ); // признак того, что форма была обработана
            // перенос полученных значений в мэппинг формы для последующего отображения (подмена дефолотных)
            foreach( $post_parameters as $key => $field ){
                if( !empty( $mapping['vacancies'][$key] ) ) $mapping['vacancies'][$key]['value'] = $post_parameters[$key];
            }
            // проверка значений из формы
            $errors = Validate::validateParams( $post_parameters, $mapping['vacancies'] );
            // выписывание ошибок в мэппинг формы (для отображения ошибочных полей)
            foreach( $errors as $key => $value ){
                if( !empty( $mapping['vacancies'][$key] ) ) $mapping['vacancies'][$key]['error'] = $value;
            }
            // если ошибок не было - готовимся к сохранению данных в БД и производим попытку сохранения
            if( empty( $errors ) ) {
                // подготовка всех значений для сохранения
                foreach($info as $key=>$field){
                    if(isset($mapping['vacancies'][$key]['value'])) $info[$key] = $mapping['vacancies'][$key]['value'];
                }
                $info['id_user'] = $auth->id;
                if( empty( $info['chpu_title'] ) ) $info['chpu_title'] = Convert::chpuTitle( $info['title'] );
                // сохранение в БД
                if($action=='edit'){
                    $res = $db->updateFromArray($sys_tables['vacancies'], $info, 'id') or die($db->error);
                } else {
                    $res = $db->insertFromArray($sys_tables['vacancies'], $info, 'id');
                    if(!empty($res)){
                        $new_id = $db->insert_id;
                        
                        //обновление временных данных
                        //перенос фотографий
                        Session::SetInteger( 'common_vacancies_add', 0 );
                        // редирект на редактирование свеженькой страницы
                        if(!empty($res) && empty( $ajax_mode ) ) {
                            header('Location: '.Host::getWebPath('/manage/vacancies/edit/'.$new_id.'/'));
                            exit(0);
                        }
                    }
                }
                Response::SetBoolean('saved', $res); // результат сохранения
            } else Response::SetBoolean('errors', true); // признак наличия ошибок
        }
        // если мы попали на страницу редактирования путем редиректа с добавления, 
        // значит мы успешно создали новый объект, нужно об этом сообщить в шаблон
        $referer = Host::getRefererURL();
        if($action=='edit' && !empty($referer) && substr($referer,-5)=='/add/') {
            Response::SetBoolean('form_submit', true);
            Response::SetBoolean('saved', true);
        }
        // запись данных для отображения на странице
        Response::SetArray( 'data_mapping', $mapping['vacancies'] );

        if( ! ( !empty( $ajax_mode ) && !empty( $post_parameters['submit'] ) ) )  $module_template = '/modules/vacancies/manage/templates/edit.html';
        
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
                Response::SetString( 'title', 'Вакансии' );
                $ajax_result = array('ok' => true );
            } else {
                //удаление
                $id = empty($this_page->page_parameters[1]) ? 0 : $this_page->page_parameters[1];
                
                $res = $db->query("DELETE FROM ".$sys_tables['vacancies']." WHERE id=?", $id);
                $results['delete'] = ($res && $db->affected_rows) ? $id : -1;
                $ajax_result = array('ok' => $results['delete']>0, 'ids'=>array($id));
            }                                       
            
        }
        break;
    default:
        $module_template = '/modules/vacancies/manage/templates/list.html';
        // формирование списка
        $conditions = array( );
        if(!empty($filters)){
            if(!empty($filters['title'])) $conditions['title'] = $sys_tables['vacancies'].".`title` LIKE '%".$db->real_escape_string($filters['title'])."%'";
            if(!empty($filters['published'])) $conditions['published'] = $sys_tables['vacancies'].".`published` = ".$db->real_escape_string($filters['published'])."";
        }
        // формирование списка для фильтра
        $condition = implode(" AND ",$conditions);        
        // создаем пагинатор для списка
        $paginator = new Paginator( $sys_tables['vacancies'], 30, $condition );
        // get-параметры для ссылок пагинатора
        $get_in_paginator = array();
        foreach($get_parameters as $gk=>$gv){
            if($gk!='page') $get_in_paginator[] = $gk.'='.$gv;
        }
        // ссылка пагинатора
        $paginator->link_prefix = '/manage/vacancies'                // модуль
                                  ."/?"                                       // конечный слеш и начало GET-строки
                                  .implode('&',$get_in_paginator)             // GET-строка
                                  .(empty($get_in_paginator)?"":'&')."page="; // параметр для номера страницы
        if($paginator->pages_count>0 && $paginator->pages_count<$page){
            Header('Location: '.Host::getWebPath($paginator->link_prefix.$paginator->pages_count));
            exit(0);
        }
        // формирование списка
        $list = CommonDb::getList( 'vacancies', $paginator->getLimitString($page), $condition, 'position DESC, id DESC', 'id' );
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
