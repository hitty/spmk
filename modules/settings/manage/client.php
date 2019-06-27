<?php
//меппинг модуля и меппинг для левого меню
$mapping = include( dirname( __FILE__ ) . '/mapping.php' );

$this_page->manageMetadata( array( 'title' => 'ЛК - Настройки - СПМК' ) );
// определяем запрошенный экшн
$action = empty( $this_page->page_parameters[0] ) ? "" : $this_page->page_parameters[0];

switch ( true ){
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // фотогалерея медиа
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    case $action == 'photos':
        if($ajax_mode){
            // свойства папок для загрузки и формата фотографий
            Photos::$__folder_options =  array(
                'sm'   =>  array(150,50,'',90),
                'big'  =>  array(1700,600,'',90)
            );                 

            $ajax_result['error'] = '';
            // переопределяем экшн
            $action = empty($this_page->page_parameters[1]) ? "" : $this_page->page_parameters[1];
            switch($action){
                case 'list':
                    $name = Request::GetString( 'name', METHOD_POST );
                    //получение фотографи
                    if( !empty( $name ) ) {
                        $item = $db->fetch( " SELECT * FROM " . $sys_tables['settings'] ." WHERE title = ?", $name );
                        if( !empty( $item ) ){
                            $ajax_result['ok'] = true;
                            $ajax_result['list'] = array(
                                array(
                                    'id' => $item['id'],
                                    'name' => $item['value'],
                                    'subfolder' => substr( $item['value'], 0, 2 ),
                                )
                            );
                            $ajax_result['folder'] = Config::$values['img_folders'];
                        } else $ajax_result['error'] = 'Невозможно построить список фотографий';
                    } else $ajax_result['error'] = 'Неверные входные параметры';
                    break;
                case 'add':
                    //загрузка фотографий
                    $name = Request::GetString( 'name', METHOD_POST );
                    if( !empty( $name ) ) {
                        $min_width = $name != 'photogallery_mainpage' ? false : 1700;
                        $res = Photos::Add( 'settings', $name, false, false, false, false, false, $min_width, false, false, false, false, true, false );
                        if(!empty($res)){
                            if( gettype( $res ) == 'string') $ajax_result['error'] = $res;  
                            else {
                                $db->query(" INSERT IGNORE INTO " . $sys_tables['settings'] . " SET title = ?, value = ? 
                                                         ON DUPLICATE KEY UPDATE value = ?
                                            ", $name, $res['filename'], $res['filename'] );
                                $ajax_result['ok'] = true;
                                $ajax_result['list'] = $res;
                            }
                        } else $ajax_result['error'] = 'Невозможно выполнить добавление фото';
                    } else $ajax_result['error'] = 'Неверные входные параметры';
                    break;
                case 'del':
                    //удаление фото
                    $name = Request::GetString( 'name', METHOD_POST );
                    if( !empty( $name ) ) {
                        $photo = $db->fetch( " SELECT * FROM " . $sys_tables['settings'] . " WHERE title = ?", $name );
                        if( !empty( $photo ) ) {
                            $res = true;
                            foreach( Photos::$__folder_options as $_folder=>$_options )    {
                                $filename = ROOT_PATH . '/' . Config::$values['img_folders'] . "/" . $_folder . "/" . substr( $photo['value'], 0, 2 ) . "/" . $photo['value'];
                                if( !file_exists( $filename ) || !unlink( $filename ) ) { $res = false; break; }
                            }
                            if( $res ) $res = $db->query( " DELETE FROM " . $sys_tables['settings'] ." WHERE title = ?", $name );
                            if( !empty( $res ) ) {
                                $ajax_result['ok'] = true;
                            } else $ajax_result['error'] = 'Невозможно выполнить удаление фото';
                        } else $ajax_result['error'] = 'Неверные входные параметры';
                    } else $ajax_result['error'] = 'Неверные входные параметры';
                    break;
                case 'setMain':
                    // установка флага "главное фото" для объекта
                    //id текущей записи
                    $id = Request::GetInteger('id', METHOD_POST);
                    //id фотки
                    $id_file = Request::GetInteger('id_file', METHOD_POST);                
                    if(!empty($id_file)){
                        $res = Photos::setMain( 'settings', $id, $id_file);
                        if(!empty($res)){
                            $ajax_result['ok'] = true;
                        } else $ajax_result['error'] = 'Невозможно установить статус';
                    } else $ajax_result['error'] = 'Неверные входные параметры';
                    break;   
                case 'rotate':
                    //поворачиваем на 90 по часовой стрелке
                    $id = Request::GetInteger('id', METHOD_POST);
                    //id фотки
                    $id_file = Request::GetInteger( 'id_file', METHOD_POST );                
                    if(!empty($id_file)){
                        $res = Photos::rotatePhoto( 'settings', $id_file );
                        if(!empty($res)){
                            $ajax_result['ok'] = true;
                        } else $ajax_result['error'] = 'Невозможно повернуть картинку';
                    } else $ajax_result['error'] = 'Неверные входные параметры';
                    break;
                                         
            }
        }
        break;
    default:
        // обработка action-ов
        $module_template = '/modules/settings/manage/templates/edit.html';

        // получение данных из БД
        $info = $db->fetchall("SELECT * FROM ".$sys_tables['settings'] ) ;        

        // перенос дефолтных (считанных из базы) значений в мэппинг формы
        foreach( $info as $key=>$field ){
            if( !empty( $mapping['settings'][$field['title']] ) ) $mapping['settings'][$field['title']]['value'] = $field['value'];
        }

        // получение данных, отправленных из формы
        $post_parameters = Request::GetParameters( METHOD_POST );

        // если была отправка формы - начинаем обработку
        if(!empty($post_parameters['submit'])){
            Response::SetBoolean('form_submit', true); // признак того, что форма была обработана
            // перенос полученных значений в мэппинг формы для последующего отображения (подмена дефолотных)
            foreach($post_parameters as $key=>$field){
                if(!empty($mapping['settings'][$key])) $mapping['settings'][$key]['value'] = $post_parameters[$key];
            }
            // проверка значений из формы
            $errors = Validate::validateParams( $post_parameters, $mapping['settings'] );
            //проверка списка emailов
            if( !empty( $mapping['settings']['admin_emails']['value'] ) ){
                $mapping['settings']['admin_emails']['value'] = trim( $mapping['settings']['admin_emails']['value'], ';' )    ;
                $mapping['settings']['admin_emails']['value'] = str_replace( ' ', '', $mapping['settings']['admin_emails']['value'] )    ;
                $emails = explode( ';', $mapping['settings']['admin_emails']['value'] );
                foreach( $emails as $email ) if( !empty( $email ) && !Validate::isEmail( $email ) ) $errors['admin_emails']  = "Один или несколько email некорекктны";
            }
            // выписывание ошибок в мэппинг формы (для отображения ошибочных полей)
            foreach($errors as $key=>$value){
                if(!empty($mapping['settings'][$key])) $mapping['settings'][$key]['error'] = $value;
            }
            // если ошибок не было - готовимся к сохранению данных в БД и производим попытку сохранения
            if(empty($errors)) {
                // подготовка всех значений для сохранения
                foreach($mapping['settings'] as $key=>$field)
                    if( !empty( $key ) && strstr( $key, 'photogallery' ) == '' ) 
                        $db->query(" INSERT IGNORE INTO " . $sys_tables['settings'] . " SET title = ?, value = ? 
                                     ON DUPLICATE KEY UPDATE value = ?
                        ", $key, !empty( $field['value'] ) ? $field['value'] : '' , !empty( $field['value'] ) ? $field['value'] : '' );

                Response::SetBoolean( 'saved', true ); // результат сохранения
            } else Response::SetBoolean('errors', true); // признак наличия ошибок 
        }

        // запись данных для отображения на странице
        Response::SetArray( 'data_mapping', $mapping['settings'] );
}
?>
