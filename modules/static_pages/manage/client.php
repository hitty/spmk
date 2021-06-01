<?php
require_once('includes/class.paginator.php');

$GLOBALS['css_set'][] = '/modules/static_pages/manage/css/style.css';
$GLOBALS['js_set'][] = '/modules/static_pages/manage/js/script.js';

// мэппинги модуля
$mapping = include( dirname( __FILE__ ) . '/mapping.php' );

// добавление title
$this_page->manageMetadata( array( 'title' => 'ЛК - Страницы - СПМК' ) );

// собираем GET-параметры
$get_parameters = array();
$filters = array( 'name', 'lastname', 'email');
foreach( $filters as $index ){
    $filters[ $index ] = Request::GetString( $index, METHOD_GET );
    if(!empty($filters[ $index ])) {
        $filters[ $index ] = urldecode($filters[ $index ]);
        $get_parameters[ $index ] = $filters[ $index ];
    }
}
$page = Request::GetInteger('page',METHOD_GET);
if(empty($page)) $page = 1;
else $get_parameters['page'] = $page;

// определяем запрошенный экшн
$action = empty($this_page->page_parameters[0]) ? "" : $this_page->page_parameters[0];

// обработка action-ов
switch($action){
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // фотогалерея проекта
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    case 'photos':
        if ($ajax_mode) {
            // свойства папок для загрузки и формата фотографий
            Photos::$__folder_options = array(
                'sm' => array(130, 130, '', 90),
                'med' => array(560, 360, 'cut', 99),
                'big' => array(2000, 2000, '', 90),
                'hero' => array(2000, 666, 'cut', 90)
            );

            $ajax_result['error'] = '';
            // переопределяем экшн
            $action = empty($this_page->page_parameters[1]) ? "" : $this_page->page_parameters[1];
            switch ($action) {
                case 'list':
                    $id = Request::GetInteger('id', METHOD_POST);
                    //получение списка фотографий
                    if (!empty($id)) {
                        $list = Photos::getList('static_pages', $id);
                        if (!empty($list)) {
                            $ajax_result['ok'] = true;
                            $ajax_result['list'] = $list;
                            $ajax_result['folder'] = Config::$values['img_folders'];
                        } else $ajax_result['error'] = 'Невозможно построить список фотографий';
                    } else $ajax_result['error'] = 'Неверные входные параметры';
                    break;
                case 'add':
                    //загрузка фотографий
                    $id = Request::GetInteger('id', METHOD_POST);
                    if (!empty($id)) {
                        $res = Photos::Add('static_pages', $id);
                        if (!empty($res)) {
                            if (gettype($res) == 'string') $ajax_result['error'] = $res;
                            else {
                                if (gettype($res) == 'string') $ajax_result['error'] = $res;
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
                    if (!empty($id)) {
                        $res = Photos::Delete('static_pages', $id);
                        if (!empty($res)) {
                            $ajax_result['ok'] = true;
                        } else $ajax_result['error'] = 'Невозможно выполнить удаление фото';
                    } else $ajax_result['error'] = 'Неверные входные параметры';
                    break;
                case 'setMain':
                    // установка флага "главное фото" для объекта
                    //id текущей услуги
                    $id = Request::GetInteger('id', METHOD_POST);
                    //id фотки
                    $id_photo = Request::GetInteger('id_photo', METHOD_POST);
                    if (!empty($id_photo)) {
                        $res = Photos::setMain('static_pages', $id, $id_photo);
                        if (!empty($res)) {
                            $ajax_result['ok'] = true;
                        } else $ajax_result['error'] = 'Невозможно установить статус';
                    } else $ajax_result['error'] = 'Неверные входные параметры';
                    break;
                case 'rotate':
                    //поворачиваем на 90 по часовой стрелке
                    $id = Request::GetInteger('id', METHOD_POST);
                    //id фотки
                    $id_photo = Request::GetInteger('id_photo', METHOD_POST);
                    if (!empty($id_photo)) {
                        $res = Photos::rotatePhoto('static_pages', $id_photo);
                        if (!empty($res)) {
                            $ajax_result['ok'] = true;
                        } else $ajax_result['error'] = 'Невозможно повернуть картинку';
                    } else $ajax_result['error'] = 'Неверные входные параметры';
                    break;
                case 'setTitle':
                    //добавление названия
                    $id = Request::GetInteger('id_photo', METHOD_POST);
                    $title = Request::GetString('title', METHOD_POST);
                    if (!empty($id)) {
                        $res = Photos::setTitle('static_pages', $id, $title);
                        $ajax_result['last_query'] = '';
                        if (!empty($res)) $ajax_result['ok'] = true;
                        else $ajax_result['error'] = 'Невозможно выполнить обновление названия фото';
                    } else $ajax_result['error'] = 'Неверные входные параметры';
                    break;
            }
        }
        break;    
    /****************************\
    |* Редактирование           *|
    \***************************/        
    case 'add':
    case 'edit':

        $module_template = '/modules/static_pages/manage/templates/edit.html';
        $id = empty($this_page->page_parameters[1]) ? 0 : $this_page->page_parameters[1];
        
        if($action=='add'){
            // создание болванки новой записи
            $info = $db->prepareNewRecord($sys_tables['static_pages']);
            $info['date'] = date('d.m.Y');
            //временный ID для связанных таблиц
            if( empty( Session::GetInteger( 'common_static_pages_add' ) ) ) {
                $common_static_pages_add = mt_rand( 800000000, 1000000000 );
                Session::SetInteger( 'common_static_pages_add', $common_static_pages_add );
            }
            Response::SetInteger( 'common_edit', Session::GetInteger( 'common_static_pages_add' ) );
            
        } else {
            // получение данных из БД
            $info = $db->fetch("SELECT 
                                    *
                                FROM ".$sys_tables['static_pages']." 
                                WHERE id=?", $id) ;        
            if( empty( $info ) ) Host::Redirect( '/manage/static_pages/add/' );
            Response::SetInteger( 'id_static_pages', $id );
        }
        // перенос дефолтных (считанных из базы) значений в мэппинг формы
        foreach( $info as $key=>$field ){
            if( !empty( $mapping['static_pages'][$key] ) ) $mapping['static_pages'][$key]['value'] = $info[$key];
        }

        // получение данных, отправленных из формы
        $post_parameters = Request::GetParameters( METHOD_POST );
        
        // если была отправка формы - начинаем обработку
        if(!empty($post_parameters['submit'])){
            Response::SetBoolean('form_submit', true); // признак того, что форма была обработана
            // перенос полученных значений в мэппинг формы для последующего отображения (подмена дефолотных)
            foreach($post_parameters as $key=>$field){
                if(!empty($mapping['static_pages'][$key])) $mapping['static_pages'][$key]['value'] = $post_parameters[$key];
            }
            // проверка значений из формы
            $errors = Validate::validateParams($post_parameters,$mapping['static_pages']);
            // выписывание ошибок в мэппинг формы (для отображения ошибочных полей)
            foreach( $errors as $key=>$value ){
                if( !empty( $mapping['static_pages'][$key] ) ) $mapping['static_pages'][$key]['error'] = $value;
            }
            //если почта непуста, проверяем ее
            if(!empty($post_parameters['email'])){
                if( empty($errors['email'] ) && !Validate::isEmail( $mapping['static_pages']['email']['value'] ) ) $errors['email'] = 'Некорректный email';
                else{
                    // дубликаты мейла
                    $res = $db->fetch("SELECT id FROM ".$sys_tables['static_pages']." WHERE email=? AND id<>?", $mapping['static_pages']['email']['value'], $id );
                    if(!empty($res)) $errors['email'] = $mapping['static_pages']['email']['error'] = 'Такой email уже есть в базе данных пользователей';
                }
            }
            //проверяем пароли
            if( 
                ( !empty( $post_parameters['passwd1'] ) && empty( $post_parameters['passwd2'] ) ) ||
                ( !empty( $post_parameters['passwd2'] ) && empty( $post_parameters['passwd1'] ) )
            ) $errors['passwd1'] = $mapping['static_pages']['passwd1']['error'] = 'Заполните оба поля «Пароль»';
            else if ( !empty( $post_parameters['passwd1'] ) && !empty( $post_parameters['passwd2'] ) ){
                if( $post_parameters['passwd1'] != $post_parameters['passwd2'] ) $errors['passwd1'] = $mapping['static_pages']['passwd1']['error'] = 'Пароли отличаются';   
                else $info['passwd'] = sha1(sha1( $post_parameters['passwd1'] ) );
            }
            
            // если ошибок не было - готовимся к сохранению данных в БД и производим попытку сохранения
            if(empty($errors)) {
                // подготовка всех значений для сохранения
                foreach($info as $key=>$field){
                    if( isset( $mapping['static_pages'][$key]['value'] ) ) $info[$key] = $mapping['static_pages'][$key]['value'];
                }
                // сохранение в БД
                if($action=='edit'){
                    $res = $db->updateFromArray($sys_tables['static_pages'], $info, 'id') or die($db->error);
                } else {
                    $res = $db->insertFromArray( $sys_tables['static_pages'], $info, 'id' );
                    if( !empty( $res ) ) {
                        $new_id = $db->insert_id;
                        
                        Session::SetInteger( 'common_static_pages_add', 0 );
                        // редирект на редактирование свеженькой страницы
                        if(!empty($res)) {
                            header('Location: '.Host::getWebPath('/manage/static_pages/edit/'.$new_id.'/'));
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
        Response::SetArray( 'data_mapping', $mapping['static_pages'] );
        break;
    default:
        $module_template = '/modules/static_pages/manage/templates/list.html';
        // формирование списка
        $conditions = array( );
        if( !empty( $filters ) ){
            foreach( $filters as $index ) if(!empty( $filters[ $index ] ) ) $conditions[ $index ] = $sys_tables['static_pages'].".`" . $index . "` LIKE '%".$db->real_escape_string( $filters[ $index ] )."%'";
        }
        $condition = implode(" AND ",$conditions);        
        $sortby = Request::GetInteger( 'sort', METHOD_GET );

        // формирование списка
        $list = CommonDb::getList( $sys_tables['static_pages'], false, $condition );
        Response::SetArray('list', $list);
        break;


}
// запоминаем для шаблона GET - параметры
Response::SetArray('get_array', $get_parameters);
foreach($get_parameters as $gk=>$gv) $get_parameters[$gk] = $gv;
Response::SetString('get_string', implode('&',$get_parameters));


?>
