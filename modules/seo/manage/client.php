<?php
require_once('includes/class.paginator.php');

// добавление title
$this_page->manageMetadata(array('title'=>'СЕО для страниц'));
// мэппинги модуля
$mapping = include(dirname(__FILE__).'/mapping.php');

// собираем GET-параметры
$get_parameters = array();
$filters = array();
$filters['url'] = Request::GetString('f_url',METHOD_GET);
$filters['pretty_url'] = Request::GetString('f_pretty_url',METHOD_GET);
$filters['match'] = Request::GetString('f_match',METHOD_GET);
if(!empty($filters['url'])) {
    $filters['url'] = trim($filters['url'],'/');
    $filters['url'] = urldecode($filters['url']);
    $get_parameters['f_url'] = $filters['url'];
}
if(!empty($filters['pretty_url'])) {
    $filters['pretty_url'] = trim($filters['pretty_url'],'/');
    $filters['pretty_url'] = urldecode($filters['pretty_url']);
    $get_parameters['f_pretty_url'] = $filters['pretty_url'];
}
//фильтр по статусу страницы - для ловца
$filter_status = Request::GetString('f_status',METHOD_GET);
if(!empty($filter_status)) {
    $filter_status = urldecode($filter_status);
    $get_parameters['f_status'] = $filter_status;
}
if(!empty($filters['match'])) $get_parameters['f_match'] = $filters['match'];
$page = Request::GetInteger('page',METHOD_GET);
if(empty($page)) $page = 1;
else $get_parameters['page'] = $page;
// определяем запрошенный экшн
$action = empty($this_page->page_parameters[0]) ? "" : $this_page->page_parameters[0];
$GLOBALS['js_set'][] = '/modules/seo/manage/js/script.js';
$GLOBALS['css_set'][] = '/modules/seo/manage/css/style.css';

// обработка action-ов
switch($action){
    case 'autocomplete':
        if($ajax_mode){
            $ajax_result['error'] = '';
            // переопределяем экшн
            $action = empty($this_page->page_parameters[1]) ? "" : $this_page->page_parameters[1];
            switch($action){
                case 'list':
                    $search_str = Request::GetString('search_string', METHOD_POST);
                    $list = $db->fetchall("SELECT url FROM ".$sys_tables['common_pages']." WHERE block_page!=1 AND url LIKE ? ORDER BY url LIMIT 10", false, $search_str.'%');
                    $ajax_result['ok'] = true;
                    $ajax_result['list'] = $list;
                    break;
            }
        }
        break;
    case 'add':
    case 'edit':
        $GLOBALS['js_set'][] = '/js/jquery.typewatch.js';
        
        $module_template = '/modules/seo/manage/templates/edit.html';
        $id = empty($this_page->page_parameters[1]) ? 0 : $this_page->page_parameters[1];
	        
		if($action=='add'){
            // создание болванки новой записи
            $info = $db->prepareNewRecord($sys_tables['common_pages_seo']);
            $info['keywords'] = $info['description'] = $info['seo_text_top'] = $info['seo_text_bottom'] = "";
        } else {
            // получение данных из БД
            $info = $db->fetch("SELECT *
                                FROM ".$sys_tables['common_pages_seo']." 
                                WHERE id=?", $id);
            if(empty($info)) Host::Redirect('/manage/seo/add/');
        }
        // перенос дефолтных (считанных из базы) значений в мэппинг формы
        foreach($info as $key=>$field){
            if(!empty($mapping['seo'][$key])) $mapping['seo'][$key]['value'] = $info[$key];
        }
        // получение данных, отправленных из формы
        $post_parameters = Request::GetParameters(METHOD_POST);
	
        // если была отправка формы - начинаем обработку
        if(!empty($post_parameters['submit'])){
            Response::SetBoolean('form_submit', true); // признак того, что форма была обработана
            // перенос полученных значений в мэппинг формы для последующего отображения (подмена дефолотных)
            foreach($post_parameters as $key=>$field){
                if(!empty($mapping['seo'][$key])) $mapping['seo'][$key]['value'] = $post_parameters[$key];
            }
            // проверка значений из формы
            $errors = Validate::validateParams($post_parameters,$mapping['seo']);
            // выписывание ошибок в мэппинг формы (для отображения ошибочных полей)
            foreach($errors as $key=>$value){
                if(!empty($mapping['seo'][$key])) $mapping['seo'][$key]['error'] = $value;
            }
            // пустой pretty_url может быть только у пустого url (заполняем копией url)
            if(!empty($mapping['seo']['url']) && empty($mapping['seo']['pretty_url'])) $mapping['seo']['pretty_url'] = $mapping['seo']['url'];
            // если ошибок не было - готовимся к сохранению данных в БД и производим попытку сохранения
            if(empty($errors)) {
                // подготовка всех значений для сохранения
                foreach($info as $key=>$field){
                    if(isset($mapping['seo'][$key]['value'])) $info[$key] = $mapping['seo'][$key]['value'];
                }
                if(!empty($info['pretty_url'])) {
                    $info['pretty_url'] = trim($info['pretty_url'],'/');
                }
                if(!empty($info['url'])) {
                    $info['url'] = trim($info['url'],'/');
                }
                if(empty($info['pretty_url']) && !empty($info['url'])) $info['pretty_url'] = 404;
                // сохранение в БД
                if($action=='edit'){
                    $res = $db->updateFromArray($sys_tables['common_pages_seo'], $info, 'id');
                } else {
                    $res = $db->insertFromArray($sys_tables['common_pages_seo'], $info, 'id');
                    if(!empty($res)){
                        $new_id = $db->insert_id;
                        // редирект на редактирование свеженькой страницы
                        if(!empty($res)) {
                            header('Location: '.Host::getWebPath('/manage/seo/edit/'.$new_id.'/'));
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
        Response::SetArray('data_mapping',$mapping['seo']);
        break;
    default:
        $module_template = '/modules/seo/manage/templates/list.html';
        // формирование списка
        $conditions = array();
        if(!empty($filters)){
            if(!empty($filters['url'])) $conditions[] = "`url` LIKE '%".$db->real_escape_string($filters['url'])."%'";
            if(!empty($filters['pretty_url'])) {
                if(empty($filters['match']) || $filters['match'] == 1) $conditions[] = "`pretty_url` LIKE '%".$db->real_escape_string($filters['pretty_url'])."%'";
                else $conditions[] = "`pretty_url` = '".$db->real_escape_string($filters['pretty_url'])."'";
            }
        }
        if(!empty($conditions)) $condition = implode(' AND ', $conditions);
        else $condition = '';
        // создаем пагинатор для списка
        $paginator = new Paginator($sys_tables['common_pages_seo'], 30, $condition);
        // get-параметры для ссылок пагинатора
        $get_in_paginator = array();
        foreach($get_parameters as $gk=>$gv){
            if($gk!='page') $get_in_paginator[] = $gk.'='.$gv;
        }
        // ссылка пагинатора
        $paginator->link_prefix = '/manage/seo'                                  // модуль
                                  ."/?"                                         // конечный слеш и начало GET-строки
                                  .implode('&',$get_in_paginator)               // GET-строка
                                  .(empty($get_in_paginator)?"":'&')."page=";   // параметр для номера страницы
        if($paginator->pages_count>0 && $paginator->pages_count<$page){
            Header('Location: '.Host::getWebPath($paginator->link_prefix.$paginator->pages_count));
            exit(0);
        }

        $sql = "SELECT id,url,pretty_url,title,h1_title FROM ".$sys_tables['common_pages_seo'];
        if(!empty($condition)) $sql .= " WHERE ".$condition;
        $sql .= " ORDER BY `url` ASC";
        $sql .= " LIMIT ".$paginator->getLimitString($page); 
        $list = $db->fetchall($sql);
        Response::SetArray('list', $list);
        if($paginator->pages_count>1){
            Response::SetArray('paginator', $paginator->Get($page));
        }
}

// запоминаем для шаблона GET - параметры
Response::SetArray('get_array', $get_parameters);
foreach($get_parameters as $gk=>$gv) $get_parameters[$gk] = $gk.'='.$gv;
Response::SetString('get_string', implode('&',$get_parameters));


?>