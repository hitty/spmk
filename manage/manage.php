<?
if( $this_page->first_instance ){
    $GLOBALS['css_set'][] = '/manage/css/controls.css';
    $GLOBALS['css_set'][] = '/manage/css/style.css';
    $GLOBALS['css_set'][] = '/manage/css/content.css';
    $GLOBALS['css_set'][] = '/manage/css/jquery-ui.min.css';
    $GLOBALS['js_set'][] = '/js/jquery-ui.min.js';
    $GLOBALS['js_set'][] = '/manage/js/jquery.list.js';
    $GLOBALS['js_set'][] = '/js/file_upload/jquery.uploadifive.js';
    $GLOBALS['css_set'][] = '/js/file_upload/uploadify.css';            
    $GLOBALS['js_set'][] = '/js/photo_upload/jquery.uploadifive.js';
    $GLOBALS['css_set'][] = '/js/photo_upload/uploadify.css';            
    $GLOBALS['css_set'][] = '/manage/css/popup.window.css';
    $GLOBALS['js_set'][] = '/manage/js/script.js';
    $GLOBALS['js_set'][] = '/js/tinymce/tinymce.min.js';
    $GLOBALS['css_set'][] = '/js/datepicker/jquery-ui-1.8.16.custom.css';
    $GLOBALS['js_set'][] = '/js/datepicker/jquery-ui.min.js';
    $GLOBALS['js_set'][] = '/manage/js/jquery.form.edit.js';
    
}         
$action = empty( $this_page->page_parameters[0] ) ? false : $this_page->page_parameters[0];
if( ! ( $auth->id_group == 101 )  ) Host::Redirect( '/' );

if( empty( $action ) && ! ( $auth->id_group == 101 ) ) Host::Redirect( '/' );
//меню
$this_page->menuClear( 1 );
$this_page->menuAdd( 'Объекты', 'manage/objects', 1, false, false, false );
$this_page->menuAdd( 'Ассортимент', 'manage/assortment', 1, false, false, false );
$this_page->menuAdd( 'Тендеры', 'manage/tendery', 1, false, false, false );
$this_page->menuAdd( 'Новости', 'manage/news', 1, false, false, false );
if( $auth->id_group == 101 ){
    $this_page->menuAdd( 'URLы', 'manage/pages', 1, false, false, false );
    $this_page->menuAdd( 'Сео', 'manage/seo', 1, false, false, false );
}

//меппинг модуля и меппинг для левого меню
$menu_mapping = $admin_mapping = include(dirname(__FILE__).'/mapping.php');

$_SESSION['CKFinder_UserRole'] = "admin";


if( !empty( $admin_mapping[ $action ] ) || ( !empty( $this_page->page_parameters[1] ) && !empty( $admin_mapping[ $this_page->page_parameters[1] ] ) ) ) {
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //стандартные action's : загрузка видео-, аудио-, текстовых файлов, фотографий, обновление значений полей
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $actions_array = array( 'video_files', 'edit_params', 'delete' );
    if( 
        ( !empty( $this_page->page_parameters[1] ) && in_array( $this_page->page_parameters[1], $actions_array ) ) || 
        ( !empty( $this_page->page_parameters[2] ) && in_array( $this_page->page_parameters[2], $actions_array ) )
    ) {
        //определение таблицы
        switch( true ){
            case !empty( $this_page->page_parameters[2] ) && !empty( $sys_tables[ $this_page->page_parameters[0] . '_' . $this_page->page_parameters[1] ] ):
                $table = $sys_tables[ $this_page->page_parameters[0] . '_' . $this_page->page_parameters[1] ]; 
                break;
            case !empty( $this_page->page_parameters[1] ) && !empty( $sys_tables[ $this_page->page_parameters[0] ] ):
                $table = $sys_tables[ $this_page->page_parameters[0] ]; 
                break;
            case !empty( $parameters['table'] ) && !empty( $sys_tables[ $parameters['table'] ] ):
                $table = $sys_tables[$parameters['table']];
                break;
        }
        if( !empty( $table ) ){
            $action = in_array( $this_page->page_parameters[2], $actions_array ) ? $this_page->page_parameters[2] : $this_page->page_parameters[1];

            switch( true ){
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                // ссылки с ютьба
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                case $action == 'video_files':
                    if($ajax_mode){
                        $ajax_result['error'] = '';
                        // переопределяем экшн
                        $action = empty($this_page->page_parameters[2]) ? "" : $this_page->page_parameters[2];
                        switch($action){
                            case 'list':
                                $id = Request::GetInteger('id', METHOD_POST);
                                //получение списка фотографий
                                if(!empty($id)){
                                    $list = Video::getList( $table, $id );
                                    if(!empty($list)){
                                        $ajax_result['ok'] = true;
                                        $ajax_result['list'] = $list;
                                    } else $ajax_result['error'] = 'Невозможно построить список файлов';
                                } else $ajax_result['error'] = 'Неверные входные параметры';
                                break;
                            case 'add':
                                //загрузка фотографий
                                $id = Request::GetInteger('id', METHOD_POST);  
                                $link = Request::GetString('link', METHOD_POST);  
                                if( !empty( $id ) &&  !empty( $link ) && strstr( $link, 'youtube' ) != '' ) {
                                    $res = Video::Add( $table, $id, $link );
                                    if(!empty($res)){
                                        if(gettype($res) == 'string') $ajax_result['error'] = $res;  
                                        else {
                                            if(gettype($res) == 'string') $ajax_result['error'] = $res;  
                                            else {
                                                $ajax_result['ok'] = true;
                                                $ajax_result['list'] = $res;
                                            }
                                        }
                                    } else $ajax_result['error'] = 'Невозможно выполнить добавление файла';
                                } else $ajax_result['error'] = 'Неверная ссылка';
                                break;
                            case 'del':
                                //удаление фото
                                $id = Request::GetInteger('id', METHOD_POST);  
                                if( !empty( $id ) ) {
                                    $res = Video::Delete( $table, $id );
                                    if( !empty( $res ) ) {
                                        $ajax_result['ok'] = true;
                                    } else $ajax_result['error'] = 'Невозможно выполнить удаление файла';
                                } else $ajax_result['error'] = 'Неверные входные параметры';
                                break;
                            case 'setTitle':
                                //добавление названия
                                $id = Request::GetInteger('id', METHOD_POST);                
                                $title = Request::GetString('title', METHOD_POST);                
                                if(!empty($id)){
                                    $res = Video::setTitle( $table ,$id, $title);
                                    $ajax_result['last_query'] = '';
                                    if(!empty($res)) $ajax_result['ok'] = true;
                                    else $ajax_result['error'] = 'Невозможно выполнить обновление названия фото';
                                } else $ajax_result['error'] = 'Неверные входные параметры';
                                break;
                                
                        }
                    }
                    break; 
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                // редактирование отдельных полей
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                case $action == 'edit_params':
                    if($ajax_mode){
                        $parameters = Request::GetParameters( METHOD_POST );
                        $db->updateFromArray( $table, $parameters, 'id' );
                    }
                    break;
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                // удаление записи
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                case $action == 'delete':
                        if( $ajax_mode ){
                            $post_parameters = Request::GetParameters( METHOD_POST );
                            $id = !empty( $this_page->page_parameters[3] ) && Validate::isDigit( $this_page->page_parameters[3] ) ? $this_page->page_parameters[3] : ( !empty( $this_page->page_parameters[2] ) && Validate::isDigit( $this_page->page_parameters[2] ) ? $this_page->page_parameters[2] : 0 );
                            if( empty( $post_parameters['submit'] ) ) {
                                //попап
                                Response::SetString( 'url', '/' . $this_page->real_url . '/' );
                                $module_template = '/templates/popup.delete.html';
                                Response::SetString( 'title', ' запись ' );
                                $ajax_result = array('ok' => true );
                            } else {
                                $res = $db->query("DELETE FROM ".$sys_tables[ $table ]." WHERE id=?", $id );
                                $results['delete'] = ($res && $db->affected_rows) ? $id : -1;
                                $ajax_result = array('ok' => $results['delete']>0, 'ids'=>array($id));
                            }                                       
                            
                        }
                        break;                    
            }   
        }   
    } else {

        $admin_module = !empty( $admin_mapping[ $action ] ) ? $admin_mapping[ $action ] : $admin_mapping[ $this_page->page_parameters[1] ];
        if(!empty($admin_module['module'])){
            $GLOBALS['css_set'][] = '/manage/css/form.css';
            // сдвигаем URL
            if( sizeof( $this_page->page_parameters )>1 ) {
                $backup_parameter_from_pageURL = array();
                $backup_parameter_from_pageURL[] = array_shift( $this_page->page_parameters );
                $admin_mapping = $admin_mapping[$backup_parameter_from_pageURL[0]];
            }


            $module_file = Host::$root_path.'/modules/'.$admin_module['module'].'/manage/client.php';
            if(file_exists($module_file)) {
                // запомненные в сессии состояния элементов админки
                $admin_modules_runtime_settings = Session::GetArray('admin_modules_runtime_settings');
                // состояние модуля
                $module_settings = empty($admin_modules_runtime_settings[$admin_module['module']])?array():$admin_modules_runtime_settings[$admin_module['module']];
                            
                // запуск модуля
                include($module_file);
                
                if($ajax_mode){
                    header("Content-type: application/json; charset=utf-8");
                        if(!empty($module_template)){
                            $tpl = new Template($module_template, '/modules/'.$admin_module['module']);
                            $module_content = $tpl->Processing();
                            $ajax_result['html'] = $module_content;
                            $ajax_result['module'] = $module_template;
                        } 
                        echo Convert::json_encode($ajax_result);
                        exit(0);    
                }
                $tpl = new Template($module_template, '/modules/'.$admin_module['module'] . '/manage');
                $module_content = $tpl->Processing();                        
                // сохранение состояния модуля в сессию
                $admin_modules_runtime_settings[$admin_module['module']] = $module_settings;
                Session::SetArray('admin_modules_runtime_settings',$admin_modules_runtime_settings);
            }
        }
    }
} else {
    switch(true){
        
        ////////////////////////////////////////////////////////////////////////////////////////////////////////
        // главная страница
        ////////////////////////////////////////////////////////////////////////////////////////////////////////
        default:
            $module_template = 'item.html';
            break;
    }    
}

// если был сдвиг URL для псевдомодуля, восстанавливаем URL обратно
if(!empty($backup_parameter_from_pageURL)){
    array_unshift($this_page->page_parameters, $backup_parameter_from_pageURL);
}


//на заглавной странице отображаем список изменений проектов
if(empty($module_content) && !empty( $module_template ) ) {
    $tpl = new Template($module_template, '/manage');
    $module_content = $tpl->Processing();                        
    Response::SetString('module_content',$module_content);
}                                       

?>