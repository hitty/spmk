<?php
require_once('includes/class.photos.php');

$GLOBALS['css_set'][] = '/modules/profile/css/style.css';
$GLOBALS['js_set'][] = '/modules/profile/js/script.js';

// мэппинги модуля
$mapping = include( dirname( __FILE__ ) . '/mapping.php' );

// добавление title
$this_page->manageMetadata( array( 'title' => 'Профиль' ) );

// собираем GET-параметры
$get_parameters = array();
// определяем запрошенный экшн
$action = empty($this_page->page_parameters[1]) ? "" : $this_page->page_parameters[1];

// обработка action-ов
switch(true){
        
    /****************************\
    |* Редактирование профиля   *|
    \***************************/        
    default:
        $id = $auth->id;
        // получение данных из БД
        $info = $db->fetch("SELECT *
                            FROM ".$sys_tables['users']." 
                            WHERE id=?", $id) ;
        if( empty( $info ) ) Host::Redirect( '/' );
            
        // перенос дефолтных (считанных из базы) значений в мэппинг формы
        foreach( $info as $key=>$field ){
            if( !empty( $mapping['profile'][$key] ) ) $mapping['profile'][$key]['value'] = $info[$key];
        }

        // получение данных, отправленных из формы
        $post_parameters = Request::GetParameters( METHOD_POST );
        
        // если была отправка формы - начинаем обработку
        if(!empty($post_parameters['submit'])){
            Response::SetBoolean('form_submit', true); // признак того, что форма была обработана
            // перенос полученных значений в мэппинг формы для последующего отображения (подмена дефолотных)
            foreach($post_parameters as $key=>$field){
                if(!empty($mapping['profile'][$key])) $mapping['profile'][$key]['value'] = $post_parameters[$key];
            }
            // проверка значений из формы
            $errors = Validate::validateParams($post_parameters,$mapping['profile']);
            
            //если почта непуста, проверяем ее
            if(!empty($post_parameters['email'])){
                if( empty($errors['email'] ) && !Validate::isEmail( $mapping['profile']['email']['value'] ) ) $errors['email'] = 'Некорректный email';
                else{
                    // дубликаты мейла
                    $res = $db->fetch("SELECT id FROM ".$sys_tables['users']." WHERE email=? AND id<>? ", $mapping['profile']['email']['value'], $id );
                    if(!empty($res)) $errors['email'] = $mapping['profile']['email']['error'] = 'Такой email уже есть в базе данных пользователей';
                }
            }
            //проверяем пароли
            if( 
                ( !empty( $post_parameters['passwd1'] ) && empty( $post_parameters['passwd2'] ) ) ||
                ( !empty( $post_parameters['passwd2'] ) && empty( $post_parameters['passwd1'] ) )
            ) $errors['passwd1'] = $mapping['profile']['passwd1']['error'] = 'Заполните оба поля «Пароль»';
            else if ( !empty( $post_parameters['passwd1'] ) && !empty( $post_parameters['passwd2'] ) ){
                if( $post_parameters['passwd1'] != $post_parameters['passwd2'] ) $errors['passwd1'] = $mapping['profile']['passwd1']['error'] = 'Пароли отличаются';   
                else $info['passwd'] = sha1(sha1( $post_parameters['passwd1'] ) );
            }
            
            // выписывание ошибок в мэппинг формы (для отображения ошибочных полей)
            foreach($errors as $key=>$value){
                if(!empty($mapping['profile'][$key])) $mapping['profile'][$key]['error'] = $value;
            }
            // если ошибок не было - готовимся к сохранению данных в БД и производим попытку сохранения
            if(empty($errors)) {
                // подготовка всех значений для сохранения
                foreach($info as $key=>$field){
                    if(isset($mapping['profile'][$key]['value'])) $info[$key] = $mapping['profile'][$key]['value'];
                }
                $info['id'] = $auth->id;
                // сохранение в БД
                $res = $db->updateFromArray( $sys_tables['users'], $info, 'id' ) or die($db->error);
                
                Response::SetBoolean('saved', $res); // результат сохранения
            } else Response::SetBoolean('errors', true); // признак наличия ошибок
            
            Response::SetBoolean('form_submit', true);
            Response::SetBoolean('saved', true);
        } 

        // запись данных для отображения на странице
        Response::SetArray( 'data_mapping', $mapping['profile'] );

        if( ! ( !empty( $ajax_mode ) && !empty( $post_parameters['submit'] ) ) )  $module_template = '/modules/profile/templates/edit.html';
        
        //попап добавление
        if( $ajax_mode ) $ajax_result['ok'] = true;
        break;
}
?>
