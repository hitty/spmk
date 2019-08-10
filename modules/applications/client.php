<?php
require_once('includes/class.paginator.php');
require_once('includes/class.sendpulse.php');

Response::SetString('img_folder',Config::$values['img_folders']);
$action = empty($this_page->page_parameters[0]) ? "" : $this_page->page_parameters[0];
//записей на страницу
$count = 10;
//от какой записи вести отчет
$from=0;   

$parameters = Request::GetParameters( METHOD_POST );
$get_parameters = Request::GetParameters( METHOD_GET );
$application_type = !empty( $parameters['application_type'] ) ? $parameters['application_type'] : ( !empty( $get_parameters['application_type'] ) ? $get_parameters['application_type'] : '' );
if( !empty( $application_type ) ) {
    $forms = Config::Get('forms');
    foreach( $forms as $k => $form ){
        if( strstr( $application_type, $k ) ) {
            $application = $form;
            break;
        }
    }
}
   
switch(true){
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // отправка заявки
    ////////////////////////////////////////////////////////////////////////////////////////////////////////    
    case $action == 'add':
        Response::SetString( 'application_type', $application_type );
        if( 
            $application_type == 'vacancies' ||
            $application_type == 'postavschikam' ||
            $application_type == 'tendery' ||
            !empty( $parameters['phone'] ) 
        ){
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
            // заголовок письма
            if( !empty( $application['mailer_title'] ) ) $mailer_title = $application['mailer_title'];
            // отбивка при успешной отправке
            if( !empty( $application['success_text'] ) ) Response::SetString( 'success_text', $application['success_text'] );
            
            switch( true ){
                ///////////////////////////////////////////////////////////////
                // Вакансии
                ///////////////////////////////////////////////////////////////
                case $application_type == 'vacancies':
                    break;
                ///////////////////////////////////////////////////////////////
                // Поставщикам и тендеры
                ///////////////////////////////////////////////////////////////
                case $application_type == 'postavschikam':
                case $application_type == 'tendery':
                    $comment = [];
                    if( !empty( $parameters['title'] )) $comment[] = 'Наименование организации: ' . $parameters['title']; 
                    if( !empty( $parameters['inn'] )) $comment[] = 'ИНН: ' . $parameters['inn']; 
                    if( !empty( $parameters['address'] )) $comment[] = 'Юридический адрес: ' . $parameters['address']; 
                    if( !empty( $parameters['place'] )) $comment[] = 'Фактическое местонахождение: ' . $parameters['place']; 
                    if( !empty( $parameters['site'] )) $comment[] = 'Сайт компании: ' . $parameters['site']; 
                    if( !empty( $parameters['fio'] )) $comment[] = 'ФИО: ' . $parameters['fio']; 
                    if( !empty( $parameters['job'] )) $comment[] = 'Занимаемая должность: ' . $parameters['job']; 
                    if( !empty( $parameters['phone'] )) $comment[] = 'Контактный телефон: ' . $parameters['phone']; 
                    if( !empty( $parameters['email'] )) $comment[] = 'Адрес электронной почты: ' . $parameters['email']; 
                    if( !empty( $parameters['notes'] )) $comment[] = 'Дополнительные сведения: ' . $parameters['notes']; 
                    if( !empty( $parameters['cooperation'] )) $comment[] = 'Вариант сотрудничества: ' . ( $parameters['cooperation'] == 1 ? 'Поставка материалов' : 'Работы и услуги' ); 
                    if( !empty( $parameters['activity'] )) $comment[] = 'Направление деятельности: ' . $parameters['activity']; // 1-2; 
                    if( !empty( $parameters['regions'] )) $comment[] = 'Регионы осуществления поставок: ' . $parameters['regions']; 
                    if( !empty( $parameters['partnership'] )) $comment[] = 'Уже работали с ГК «СПМК»: ' . ( $parameters['partnership'] == 1 ? 'Да': 'Нет' ); //1-2; 
                    //заголовок
                    if( $application_type == 'postavschikam' ) $mailer_title = 'Запрос со страницы «Закупки»' . ' - '.date('d.m.Y');
                    else if( $application_type == 'tendery' ){
                        if( empty( $parameters['application_type_id'] ) ) {
                            $ajax_result['error'] = 'Wrong tender ID';
                            exit(0);
                        } else {
                            $tender = CommonDb::getItem( 'tendery', $sys_tables['tendery'] . '.id = ' . $parameters['application_type_id'] );
                            if( empty( $tender ) ) {
                                $ajax_result['error'] = 'Wrong tender ID';
                                exit(0);
                            } else $mailer_title = 'Запрос со страницы Тендера: «' . $tender['title'] . '» - '.date('d.m.Y');
                        }
                    }
                    
                    $parameters['comment'] = implode( '<br/><br/>', $comment );
                    break;
                ///////////////////////////////////////////////////////////////
                // Экскурсия
                ///////////////////////////////////////////////////////////////
                case strstr( $application_type, 'excursion' ):
                    $mailer_title = 'Запись на экскурсию ' . $parameters['date'];
                    break;
                
            }
            $mail_template = 'send.email.html';
            
            $time = Time::get();
            $data = array(
                'name' => !empty( $parameters['name'] ) ? $parameters['name'] : '',
                'phone' => !empty( $parameters['phone'] ) ? $parameters['phone'] : '',
                'email' => !empty( $parameters['email'] ) ? $parameters['email'] : '',
                'region' => !empty( $parameters['region'] ) ? $parameters['region'] : '',
                'user_comment' => !empty( $parameters['comment'] ) ? $parameters['comment'] : '',
                'date' => !empty( $parameters['date'] ) ? $parameters['date'] : '',
                'files' => !empty( $files ) ? $files : '',
                'ip' => $ip,
                'ref' => !empty( Host::getRefererURL() ) ? Host::getRefererURL() : '',
                'browser' => !empty( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '',
                'city' => !empty( $city ) ? $city ['city']: '',
                'type' => 2
            );
            Response::SetArray( 'data', $data );
            Time::clear();
            
            //отправка письма спамерам
            if( ( !empty( $time ) && $time < 120000 ) || ( !empty( $parameters['comment'] ) && preg_match( '#url|http|hello#msiU', $parameters['comment'] ) ) ){
                $db->insertFromArray( $sys_tables['applications_spam'], $data );
                $id = $db->insert_id;
                Response::SetInteger( 'id', $id );
                $mailer_title = 'Интересный запрос от спамеров #' . $id . ' - '.date('d.m.Y');
                Response::SetString( 'mailer_title', $mailer_title );

                $eml_tpl = new Template('send.email.spam.html', 'modules/applications/');
                $html = $eml_tpl->Processing();

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
                /*
                $result = $sendpulse->sendMail(
                    $mailer_title,
                    $html,
                    false,
                    false,
                    $mailer_title .' через сайт ' . Host::$host,
                    'no-reply@' . Host::$host,
                    $emails
                );
                */
            } else {
                $db->insertFromArray( $sys_tables['applications'], $data );
                $id = $db->insert_id;
                Response::SetInteger( 'id', $id );
                
                $eml_tpl = new Template( $mail_template, 'modules/applications/');
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
                
                if( !DEBUG_MODE && !empty( $wserwer ) ) {
                    if( !empty( $application_type ) && in_array( $application_type, [ 'tendery', 'postavschikam' ] ) ){
                        $emails[] = ['Е.С.А.',  "aae1958@inbox.ru" ];    
                        $emails[] = ['Отдел снабжения',  "snab@spmk.group" ];    
                        
                    } else {
                        $emails[] = ['Отдел продаж',  "market@spmk.group" ];    
                    }
                    $emails[] = ['Новицкая Лилия',  "nla@spmk.group" ];    
                }    
                
                $sendpulse = new Sendpulse( );
                $result = $sendpulse->sendMail(
                    $mailer_title,
                    $html,
                    false,
                    false,
                    $mailer_title .' через сайт ' . Host::$host,
                    'no-reply@spmk.group',
                    $emails
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
        Response::SetString( 'form_title', $application['form_title'] );
        $ajax_result['ok'] = true;
        $module_template = $application['template'];
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