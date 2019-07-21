<?php
require_once('includes/class.paginator.php');
require_once('includes/class.sendpulse.php');

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
                'name' => !empty( $parameters['emanameil'] ) ? $parameters['name'] : '',
                'phone' => !empty( $parameters['phone'] ) ? $parameters['phone'] : '',
                'email' => !empty( $parameters['email'] ) ? $parameters['email'] : '',
                'comment' => !empty( $parameters['comment'] ) ? $parameters['comment'] : '',
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
            // инициализация шаблонизатора
            //отправка письма спамерам
            if( !empty( $parameters['comment'] ) && preg_match( '#url|http|vk.cc#msiU', $parameters['comment'] ) ) {
                $eml_tpl = new Template('send.email.spam.html', 'modules/applications/');
                $html = $eml_tpl->Processing();
                $mailer_title = 'Интересный запрос от спамеров' ;

                $emails = [
                    [
                        'name' => '',
                        'email'=> 'kya82@mail.ru'
                    ],
                    [
                        'name' => '',
                        'email'=> $parameters['email']
                    ]
                ];
                
                $sendpulse = new Sendpulse( );
                $result = $sendpulse->sendMail(
                    $mailer_title,
                    $html,
                    false,
                    false,
                    $mailer_title .' через сайт ' . Host::$host,
                    'no-reply@' . Host::$host,
                    $emails,
                    !empty( $files ) ? $files : false
                );
                
            } else {
                $eml_tpl = new Template('send.email.html', 'modules/applications/');
                $html = $eml_tpl->Processing();
                // параметры письма
                if( empty( $mailer_title ) ) $mailer_title = 'Запрос КП #' . $id . ' - '.date('d.m.Y');
                Response::SetString( 'mailer_title', $mailer_title );
                $emails = [
                    [
                        'name' => '',
                        'email'=> 'kya82@mail.ru'
                    ]
                ];
                
                //if( !DEBUG_MODE ) $emails[] = ['Отдел продаж',  "market@constr62.ru" ];
                //if( !DEBUG_MODE ) $emails[] = ['Новицкая Лилия',  "nla@constr62.ru" ];
                
                $sendpulse = new Sendpulse( );
                $result = $sendpulse->sendMail(
                    $mailer_title,
                    $html,
                    false,
                    false,
                    $mailer_title .' через сайт ' . Host::$host,
                    'no-reply@spmk.group',
                    $emails,
                    !empty( $files ) ? $files : false
                );

                
                $ajax_result['result'] = $result;
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