<?php
require_once('includes/class.paginator.php');
require_once('includes/class.email.php');

Response::SetString('img_folder',Config::$values['img_folders']);
$action = empty($this_page->page_parameters[0]) ? "" : $this_page->page_parameters[0];
//записей на страницу
$count = 10;
//от какой записи вести отчет
$from=0;        
switch(true){
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // отправка заявки
    ////////////////////////////////////////////////////////////////////////////////////////////////////////    
    case $action == 'add':
        $parameters = Request::GetParameters( METHOD_POST );
        
        if( !empty( $parameters['phone'] ) ){
            
            
            if(!empty($_FILES)){
                $filenames = $files = array();
                for ($i=0; $i < count( $_FILES['file_upload'] ); $i++ ) {
                    if( !empty( $_FILES['file_upload']['name'][$i] ) ) 
                        $filenames[] = array(
                            'name' =>       $_FILES['file_upload']['name'][$i],
                            'type' =>       $_FILES['file_upload']['type'][$i],
                            'tmp_name' =>   $_FILES['file_upload']['tmp_name'][$i],
                            'error' =>      $_FILES['file_upload']['error'][$i],
                            'size' =>       $_FILES['file_upload']['size'][$i],
                        );    
                }
                
                foreach( $filenames as $f =>$file ){
                    if ($file['error']==0) {
                        $_temp_folder = '/img/docs/'; // папка для файлов
                        $fileTypes = array('docx','doc','txt','xls','xlsx','cdr','dwg','pdf','jpeg','png','gif','bmp','jpg'); // допустимые расширения файлов
                        $fileParts = pathinfo($file['name']);
                        $targetExt = $fileParts['extension'];
                        $_targetFile = md5( microtime() ) . '.' . $targetExt; // конечное имя файла
                        if ( in_array( strtolower( $targetExt ), $fileTypes ) ) {
                            move_uploaded_file( $file['tmp_name'], Host::$root_path . $_temp_folder . $_targetFile );
                            $files[] = Host::$protocol . '://' . Host::$host . $_temp_folder . $_targetFile;
                        }
                    }
                }
            }
           
            $city = Cookie::GetArray( 'city' );
            $ip = !empty( Host::getUserIp(true) ) ? Host::getUserIp(true) : Host::getUserIp();
            if( $ip == '31.204.181.238' ) die();
            $data = array(
                'name' => $parameters['name'],
                'phone' => $parameters['phone'],
                'email' => $parameters['email'],
                'comment' => $parameters['comment'],
                'files' => !empty( $files ) ? $files : '',
                'files' => !empty( $files ) ? $files : '',
                'ip' => $ip,
                'ref' => !empty( Host::getRefererURL() ) ? Host::getRefererURL() : '',
                'browser' => !empty( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '',
                'city' => !empty( $city ) ? $city ['city']: '',
                'type' => 2
            );
            Response::SetArray( 'data', $data );
            $db->insertFromArray( $sys_tables['applications'], $data );
            $id = $db->insert_id;
            Response::SetInteger( 'id', $id );
            $mailer_title = ( ( empty( $parameters['reference'] ) ? 'Запрос КП #' . $id : 'Запрос референс-листа' ) . ' - '.date('d.m.Y') );
            Response::SetString( 'mailer_title', $mailer_title );
            $mailer = new EMailer('mail');
            // инициализация шаблонизатора
            //отправка письма спамерам
            if( !empty( $parameters['comment'] ) && preg_match( '#url|http|vk.cc#msiU', $parameters['comment'] ) ) {
                $eml_tpl = new Template('send.email.spam.html', 'modules/applications/');
                $html = $eml_tpl->Processing();
                $html = iconv('UTF-8', $mailer->CharSet.'//IGNORE', $html);
                // параметры письма
                $mailer->Body = $html;
                $mailer->Subject = iconv('UTF-8', $mailer->CharSet, 'Интересный запрос от спамеров' );
                $mailer->IsHTML(true);
                $mailer->AddAddress("kya82@mail.ru");
                $mailer->AddAddress( $parameters['email'] );
                $mailer->From = 'no-reply@' . Host::$host;
                $mailer->FromName = iconv('UTF-8', $mailer->CharSet,  Host::$host );
                // попытка отправить
                $mailer->Send();
                $ajax_result['ok'] = false;
            } else {
                $eml_tpl = new Template('send.email.html', 'modules/applications/');
                $html = $eml_tpl->Processing();
                $html = iconv('UTF-8', $mailer->CharSet.'//IGNORE', $html);
                // параметры письма
                $mailer->Body = $html;
                if( empty( $mailer_title ) ) $mailer_title = 'Запрос КП #' . $id . ' - '.date('d.m.Y');
                Response::SetString( 'mailer_title', $mailer_title );
                $mailer->Subject = iconv('UTF-8', $mailer->CharSet, $mailer_title .' через сайт ' . Host::$host );
                $mailer->IsHTML(true);
                $mailer->AddAddress("kya82@mail.ru");
                if( !DEBUG_MODE ) $mailer->AddAddress("market@constr62.ru");
                if( !DEBUG_MODE ) $mailer->AddAddress("nla@constr62.ru");
                $mailer->From = 'no-reply@' . Host::$host;
                $mailer->FromName = iconv('UTF-8', $mailer->CharSet,  Host::$host );
                // попытка отправить
                $mailer->Send();
                $ajax_result['ok'] = true;
                $module_template = "/templates/popup.success.html";    
            }
        }
        break;
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // форма заявки
    ////////////////////////////////////////////////////////////////////////////////////////////////////////    
    case $action == 'popup':
        $get_parameters = Request::GetParameters( METHOD_GET );
        if( !empty( $get_parameters['g'] ) ) Response::SetString( 'goal', $get_parameters['g'] );

        $ajax_result['ok'] = true;
        $module_template = 'templates/includes/form.html';
        break;
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // отправка заявки
    ////////////////////////////////////////////////////////////////////////////////////////////////////////    
    case $action == 'success':
        $ajax_result['ok'] = true;
        Response::SetString('popup_redirect','true');
        $module_template = "/templates/popup.success.html";
        break;
    default:
        Host::RedirectLevelUp();
        break;
}
?>