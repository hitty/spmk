<?php
$action = empty($this_page->page_parameters[0]) ? "" : $this_page->page_parameters[0];
switch( true ){      
   ////////////////////////////////////////////////////////////////////////////////////////////////
   // авторизация
   ////////////////////////////////////////////////////////////////////////////////////////////////
   case $this_page->page_alias == 'authorization'
        || $this_page->requested_url == 'authorization'
        || $action == 'authorization':
            if(!empty($this_page->requested_url) && $this_page->requested_url == 'members/authorization') Host::Redirect('/authorization/');
            $h1 = empty($this_page->page_seo_h1) ? 'Войти на сайт' : $this_page->page_seo_h1;
            Response::SetString('h1', $h1);

            $parameters = Request::GetParameters(METHOD_GET);
            switch(true){
               ////////////////////////////////////////////////////////////////////////////////////////////////
               // форма авторизации
               ////////////////////////////////////////////////////////////////////////////////////////////////
               case $action == 'popup':
                    $ajax_result['ok'] = true;
                    $module_template = '/modules/authorization/templates/authorization.popup.html';
                    break;
               ////////////////////////////////////////////////////////////////////////////////////////////////
               // авторизация  через vk.com
               ////////////////////////////////////////////////////////////////////////////////////////////////
               case $action == 'vklogin':
                    if(!empty($parameters['code'])){
                        //успешная авторизация через вконтакте
                        $access = file_get_contents('https://oauth.vk.com/access_token?'.http_build_query(array(
                            'client_id'     => Config::Get('social/vk/app_id'),
                            'client_secret' => Config::Get('social/vk/secret'),
                            'code'          => $parameters['code'],
                            'redirect_uri'  => Host::$root_url.'/authorization/vklogin/'
                        )));     
                        $access = json_decode( $access );
                        if( empty( $access ) ) {
                            
                        } else {
                            $id_user_vk = Convert::ToInt( $access->user_id );
                            if( !empty( $id_user_vk ) ){
                                $social_data = array( 'field' => 'id_user_vk', 'value' => $id_user_vk );
                                $auth->checkAuthSocial( $social_data );
                            }
                            //получение данных о пользователе
                            $user = file_get_contents('https://api.vk.com/method/users.get?fields=photo_max_orig,city,verified&user_id='. $id_user_vk . '&access_token=' . $access->access_token . '&v=5.92');
                            $user = json_decode( $user );
                            if( !empty( $user ) ) {
                                $data = array(
                                    'id_user_vk' => $id_user_vk,
                                    'email' => !empty( $user->response[0]->email ) ? $user->response[0]->email : ( !empty( $access->email ) ? $access->email : '' ),
                                    'name' => !empty( $user->response[0]->first_name ) ? $user->response[0]->first_name : '',
                                    'lastname' => !empty( $user->response[0]->last_name ) ? $user->response[0]->last_name : ''
                                );
                            }
                            //регистрация нового пользователя
                            if( !empty( $data['email'] ) ) $user_info = $db->fetch( " SELECT * FROM " . $sys_tables['users'] ." WHERE email = ?", $data['email'] );
                            if( empty( $user_info ) && !empty( $data['id_user_vk'] ) ) $user_info = $db->fetch( " SELECT * FROM " . $sys_tables['users'] ." WHERE id_user_vk = ?", $id_user_vk );
                            if( !empty( $user_info ) ) {
                                $data = array(
                                    'id' => $user_info['id'],
                                    'id_user_vk' => $id_user_vk,
                                    'name' => !empty( $user_info['name'] ) ? $user_info['name'] : ( !empty( $data['name'] ) ? $data['name'] : '' ),
                                    'lastname' => !empty( $user_info['lastname'] ) ? $user_info['lastname'] : ( !empty( $data['lastname'] ) ? $data['lastname'] : '' ),
                                    'email' => $data['email']
                                );
                            }
                            if( !empty( $user_info ) ) $db->updateFromArray( 'users', $data, 'id' );
                            else if( !$auth->authorized ) $db->insertFromArray( 'users', $data );
                            $auth->checkAuthSocial( $social_data );
                            Host::Redirect(  '/authorization/' . $action  );

                        }
                    } else {
                        if( $auth->authorized ) Response::SetBoolean( 'redirect', true );
                        else Response::SetBoolean( 'error', true );
                        $module_template = '/modules/authorization/templates/authorization.html';
                    }
                    break;
               ////////////////////////////////////////////////////////////////////////////////////////////////
               // авторизация  через facebook.com
               ////////////////////////////////////////////////////////////////////////////////////////////////
               case $action == 'fblogin':
                    if(!empty($parameters['code'])){
                        //успешная авторизация через ФБ
                        $url = 'https://graph.facebook.com/oauth/access_token?client_id='.Config::Get('social/fb/app_id').'&'.http_build_query(array(
                            'client_secret' => Config::Get('social/fb/secret'),
                            'code'          => $parameters['code'],
                            'redirect_uri' => Host::$root_url.'/authorization/fblogin/'
                        ));
                        $response = @file_get_contents($url);   
                        $response = json_decode($response, true);;

                        $access = @file_get_contents('https://graph.facebook.com/v3.2/me?'.http_build_query(array(
                            'fields' => 'id,first_name,last_name,email',
                            'access_token' => $response['access_token']
                        )));
                        $access = json_decode($access);
                        $id_user_facebook = Convert::ToInt($access->id);
                        if(!empty($id_user_facebook)){
                            $social_data = array( 'field' => 'id_user_facebook', 'value' => $id_user_facebook );
                            $auth->checkAuthSocial($social_data);
                            
                            $data = array(
                                'id_user_facebook' => $id_user_facebook,
                                'name' => !empty( $access->first_name ) ? $access->first_name : '',
                                'email' => !empty( $access->email ) ? $access->email : '',
                                'lastname' => !empty( $access->last_name ) ? $access->last_name : ''
                            );
                            
                            if( !empty( $data['email'] ) ) $user_info = $db->fetch( " SELECT * FROM " . $sys_tables['users'] ." WHERE email = ?", $data['email'] );
                            if( empty( $user_info ) && !empty( $data['id_user_facebook'] ) ) $user_info = $db->fetch( " SELECT * FROM " . $sys_tables['users'] ." WHERE id_user_facebook = ?", $id_user_facebook );
                            if( !empty( $user_info ) ) {
                                $data = array(
                                    'id' => $user_info['id'],
                                    'id_user_facebook' => $id_user_facebook,
                                    'name' => !empty( $user_info['name'] ) ? $user_info['name'] : ( !empty( $data['name'] ) ? $data['name'] : '' ),
                                    'lastname' => !empty( $user_info['lastname'] ) ? $user_info['lastname'] : ( !empty( $data['lastname'] ) ? $data['lastname'] : '' ),
                                    'email' => $data['email']
                                );
                            }
                            if( !empty( $user_info ) ) $db->updateFromArray( 'users', $data, 'id' );
                            else if( !$auth->authorized ) $db->insertFromArray( 'users', $data );
                            $auth->checkAuthSocial( $social_data );
                            Host::Redirect(  '/authorization/' . $action  );

                        }
                        
                    } else {
                        if( $auth->authorized ) Response::SetBoolean( 'redirect', true );
                        else Response::SetBoolean( 'error', true );
                        $module_template = '/modules/authorization/templates/authorization.html';
                    }
                    break;
               ////////////////////////////////////////////////////////////////////////////////////////////////
               // авторизация  через odnoklassniki.ru
               ////////////////////////////////////////////////////////////////////////////////////////////////
               case $action == 'oklogin':
                    if(!empty($parameters['code'])){
                        //успешная авторизация через ОК
                        $params = array(
                            'client_id' =>  Config::Get('social/ok/app_id'),
                            'client_secret' => Config::Get('social/ok/secret'),
                            'code'          => $parameters['code'],
                            'grant_type'    => 'authorization_code',
                            'fields'        => 'email',
                            'redirect_uri'  => Host::$root_url.'/authorization/oklogin/'
                        );
                        $url = 'http://api.odnoklassniki.ru/oauth/token.do';
                        $result = curlThis($url, 'POST', $params);
                        $tokenInfo = json_decode($result, true);
                        if($tokenInfo){
                            //Получение информации о пользователе
                            if (!empty($tokenInfo['access_token'])) {
                                $curl = curl_init('http://api.odnoklassniki.ru/fb.do?access_token=' . $tokenInfo['access_token'] . '&application_key=' . Config::Get('social/ok/public') . '&method=users.getCurrentUser&sig=' . md5('application_key=' . Config::Get('social/ok/public') . 'method=users.getCurrentUser' . md5($tokenInfo['access_token'] . Config::Get('social/ok/secret'))));
                                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                                $s = curl_exec($curl);
                                curl_close($curl);
                                $userInfo = json_decode($s, true);
                                $id_user_ok = Convert::ToInt( $userInfo['uid'] );
                                if(!empty($id_user_ok)){
                                    $social_data = array('field'=>'id_user_ok','value'=>$id_user_ok);
                                    $auth->checkAuthSocial( $social_data );
                                }
                                $data = array(
                                    'id_user_ok' => $id_user_ok,
                                    'name' => !empty( $userInfo['first_name'] ) ? $userInfo['first_name'] : '',
                                    'email' => !empty( $userInfo['email'] ) ? $userInfo['email'] : '',
                                    'lastname' => !empty( $userInfo['last_name'] ) ? $userInfo['last_name'] : ''
                                );

                                //регистрация нового пользователя      
                                if( !empty( $data['email'] ) ) $user_info = $db->fetch( " SELECT * FROM " . $sys_tables['users'] ." WHERE email = ?", $data['email'] );
                                if( empty( $user_info ) &&  !empty( $data['id_user_ok'] ) ) $user_info = $db->fetch( " SELECT * FROM " . $sys_tables['users'] ." WHERE id_user_ok = ?", $id_user_ok );
                                if( !empty( $user_info ) ) {
                                    $data = array(
                                        'id' => $user_info['id'],
                                        'id_user_ok' => $id_user_ok,
                                        'name' => !empty( $user_info['name'] ) ? $user_info['name'] : ( !empty( $data['name'] ) ? $data['name'] : '' ),
                                        'lastname' => !empty( $user_info['lastname'] ) ? $user_info['lastname'] : ( !empty( $data['lastname'] ) ? $data['lastname'] : '' ),
                                        'email' => $data['email']
                                    );
                                }
                                if( !empty( $user_info ) ) $db->updateFromArray( 'users', $data, 'id' );
                                else if( !$auth->authorized ) $db->insertFromArray( 'users', $data );
                                $auth->checkAuthSocial( $social_data );
                                Host::Redirect(  '/authorization/' . $action  );
                            } 
                        } 
                    } else {
                        if( $auth->authorized ) Response::SetBoolean( 'redirect', true );
                        else Response::SetBoolean( 'error', true );
                        $module_template = '/modules/authorization/templates/authorization.html';
                    }
                    break;
                    
               ////////////////////////////////////////////////////////////////////////////////////////////////
               // авторизация  через twitter
               ////////////////////////////////////////////////////////////////////////////////////////////////
               case $action == 'twilogin':

                    if( !empty( $_GET['oauth_token'] ) && !empty( $_GET['oauth_verifier'] ) ) {
                        // готовим подпись для получения токена доступа

                        $user_data = OAuthTwitter::Authentication();
                        
                        if( !empty( $user_data ) ) {
                            $id_user_twitter = Convert::ToInt( $user_data['id'] );
                            if(!empty($id_user_twitter)){
                                $social_data = array( 'field' => 'id_user_twitter', 'value' => $id_user_twitter);
                                $auth->checkAuthSocial( $social_data );
                            }
                            $data = array(
                                'id_user_twitter' => $id_user_twitter,
                                'name' => !empty( $user_data['name'] ) ? $user_data['name'] : '',
                                'login' => !empty( $user_data['screen_name'] ) ? $user_data['screen_name'] : ''
                            );

                            //регистрация нового пользователя
                            if( !$auth->authorized ) {
                                $db->insertFromArray( 'users', $data );
                                $auth->checkAuthSocial( $social_data );
                            }
                            else $db->updateFromArray( 'users', $data, 'id_user_vk' );

                            Host::Redirect(  '/authorization/' . $action  );       
                        }                 
                    } else {
                        if( $auth->authorized ) Response::SetBoolean( 'redirect', true );
                        else Response::SetBoolean( 'error', true );
                        $module_template = '/modules/authorization/templates/authorization.html';
                    }                 
                }                 
                
                if( $auth->authorized ) {
                    //если авторизовались с помощью соцсети - закрываем окно
                    $auth_data = Session::GetArray('auth_data');
                    $ajax_result['ok'] = true;
                    $ajax_result['success'] = 'Авторизация...';
                    if( (!empty($auth_data) && !empty($auth_data['social_field']) && !empty($auth_data['social_value'])) ){
                        $ajax_result['close_window'] = true;
                    } 
                    $ajax_result[ 'redirect_url' ] = '/voting/';
                } else {
                    if($auth->auth_trying) {
                        $auth_login = Request::GetString( 'auth_login', METHOD_POST );
                        $ajax_result['error'] = 'Пароль неверный!';
                        $errors['auth_login'] = $errors['auth_passwd'] = '';
                        $ajax_result['errors'] = $errors;
                    }
                }
                    
                if( $action != 'popup' )    $module_template = '/modules/authorization/templates/authorization.html';
        break;
   break;
   ////////////////////////////////////////////////////////////////////////////////////////////////
   // регистрация нового пользователя
   ////////////////////////////////////////////////////////////////////////////////////////////////
   case $this_page->requested_url == 'registration' || $this_page->requested_url == 'registration/attach_account' || $this_page->requested_url == 'registration/popup': 
        if(!empty($this_page->requested_url) && $this_page->requested_url == 'members/registration') Host::Redirect('/registration/');
        
        $post_parameters = Request::GetParameters(METHOD_POST);
        $errors = array();
        $reg_email = $reg_name = '';
        switch(true){
           ////////////////////////////////////////////////////////////////////////////////////////////////
           // форма регистрации
           ////////////////////////////////////////////////////////////////////////////////////////////////
           case $action == 'popup':
                $ajax_result['ok'] = true;
                $module_template = '/modules/authorization/templates/registration.popup.html';
            break;
            default:
            
            if($ajax_mode){ 
                //ajax-регистрация
                $post_parameters['submit'] = true;
                $ajax_result['ok'] = true;
            } else {
                Response::SetBoolean('with_header',true);
                $GLOBALS['js_set'][] = '/modules/members/account_forms.js';
                $module_template = '/modules/authorization/templates/registration.html';

                $h1 = empty($this_page->page_seo_h1) ? 'Регистрация нового пользователя ' : $this_page->page_seo_h1;
                Response::SetString('h1', $h1);
                
                if($action == 'attach_account') Response::SetBoolean('not_show_social_authorization',true);
            }
            // если была отправка формы
            if(!empty($post_parameters['submit'])){
                
                //чтобы данные в форме не пропали
                Response::SetArray('form_data',array('email'=>$post_parameters['login_email'],'name'=>$post_parameters['login_name']));
                
                //если email непуст, проверяем его корректность
                if(!empty($post_parameters['login_email'])){
                    if(Validate::isEmail($post_parameters['login_email'])) $reg_email = $post_parameters['login_email'];
                    else{
                        $reg_email = $post_parameters['login_email'];
                        $errors['login_email'] = $ajax_result['error'] = 'Некорректный email';
                    } 
                }else $errors['login_email'] = $ajax_result['error'] = 'Некорректный email';
                // получение имени
                if(!empty($post_parameters['login_name'])) $reg_name = $post_parameters['login_name'];
                else $errors['login_name'] = $ajax_result['error'] = "Пожалуйста, введите имя";
                
                if(empty($errors)){
                    // проверка на существование такого пользователя
                    if(!empty($reg_email)){
                        $where = "email='".$db->real_escape_string($reg_email)."' OR login='".$db->real_escape_string($reg_email)."'";
                        $row = $db->fetch("SELECT id FROM ".$sys_tables['users']." WHERE ".$where);
                        if(!empty($row)) $errors['login_email'] = $ajax_result['error'] = 'Пользователь с такими данными уже существует';
                    }
                }
                
                // если все проверки пройдены - отсылаем сообщение пользователю с логином и паролем
                if(!empty($errors)){
                    $ajax_result['ok'] = false;
                    $ajax_result['errors'] = $errors;
                    Response::SetArray('errors',$errors);
                } 
                else {                
                    $ajax_result['ok'] = true;
                    if($ajax_mode){
                        // генерируем пароль
                        $reg_passwd = substr(md5(time()),-6);
                        //проверка на домен в черном списке
                        if(!empty($reg_email)){
                            $domain_parts = explode('@', $reg_email);
                            $domain = $domain_parts[1];
                        } else $domain = false;
                        if(!empty($domain) && !Validate::emailBlackList($domain)){
                        // создание нового пользователя в БД
                        $res = $db->query("INSERT INTO ".$sys_tables['users']."
                                            (email,name,passwd,datetime,access)
                                           VALUES
                                            (?,?,?,NOW(),'')"
                                           , $reg_email
                                           , $reg_name
                                           , sha1(sha1($reg_passwd)));
                                           
                        } else $res = true; //псеводзапись email в черном списке
                        if(empty($res)){
                            $errors['error'] = true;
                            $ajax_result['ok'] = false;
                        } else {
                            }

                            if(!empty($reg_email) && Validate::isEmail($reg_email)) {
                                // данные пользователя для шаблона
                                Response::SetArray( "data", 
                                    array('email'=>$reg_email, 
                                    'name'=>$reg_name, 
                                    'password'=>$reg_passwd) 
                                );
                                // данные окружения для шаблона
                                $env = array(
                                    'url' => Host::GetWebPath('/'),
                                    'host' => Host::$host,
                                    'ip' => Host::getUserIp(),
                                    'datetime' => date('d.m.Y H:i:s')
                                );
                                Response::SetArray('env', $env);
                                // инициализация шаблонизатора
                                $eml_tpl = new Template('registration_email.html', $this_page->module_path);
                                // формирование html-кода письма по шаблону
                                $html = $eml_tpl->Processing();         
                                
                                if( !class_exists('Sendpulse') ) require_once("includes/class.sendpulse.php");
                                //отправка письма
                                $sendpulse = new Sendpulse( 'subscriberes' );
                                $result = $sendpulse->sendMail( 'Регистрация на сайте ' . Host::$host, $html, $reg_name, $reg_email );
                                //добавление подписчика
                                $email = array(
                                    array(
                                        'email' => $reg_email,
                                        'variables' => array(
                                            'name' => $reg_name,
                                        )
                                    )
                                );
                                $sendpulse->addEmails( false, $email );
                                
                                if( !empty( $result ) )  Response::SetString('success','email');

                                // инициализация шаблонизатора
                                $eml_tpl = new Template('registration_email.manager.html', $this_page->module_path);
                                // формирование html-кода письма по шаблону
                                $html = $eml_tpl->Processing();         
                                $result = $sendpulse->sendMail( 'Регистрация на сайте ' . Host::$host, $html, 'Менеджеру БСН', 'scald@bsn.ru' );
                                
                                Session::SetArray(
                                    'fields',
                                    array(
                                        'email' =>  $reg_email,
                                        'name'  =>  $reg_name
                                    )
                                );
                                Session::SetArray(
                                    'fields',
                                    array(
                                        'email' =>  $reg_email,
                                        'name'  =>  $reg_name
                                    )
                                );

                                if($ajax_mode){
                                    $_authorized = $auth->checkAuth($reg_email, $reg_passwd);
                                    $ajax_result['ok'] = $auth->auth_trying && $auth->authorized;
                                    $ajax_result['auth_trying'] = $auth->auth_trying;
                                    $ajax_result['lq'] = '';
                                    $module_template = '/templates/popup.success.html';
                                    Response::SetString('text', 'На указанную почту отправлено письмо для подтверждения.');
                                    Response::SetString('title', 'Регистрация прошла успешно.');

                                } else {
                                    Host::Redirect('/registration/success/');
                                }

                            }
                            
                        }
                }
            }
            
            Response::SetBoolean('social_only_buttons',true);
            Response::SetBoolean('social_reverse_buttons',true);
            $ajax_result['errors'] = $errors;
            Response::SetArray('errors',$errors);
            Response::SetArray('fields',$post_parameters);    
            break;
        }
        break;
   ////////////////////////////////////////////////////////////////////////////////////////////////
   // редирект на успешную регистрацию нового пользователя
   ////////////////////////////////////////////////////////////////////////////////////////////////
    case $this_page->requested_url=='registration/success': 
        $fields = Session::GetArray('fields');
        if(empty($fields) || count($fields)!=2){
            $this_page->http_code = 404;
            break;
        }
        $module_template = '/modules/authorization/templates/registration.success.html';
        Response::SetArray('fields',$fields);
        break;
        
   ////////////////////////////////////////////////////////////////////////////////////////////////
   // форма восстановление утраченного пароля
   ////////////////////////////////////////////////////////////////////////////////////////////////
   case $this_page->requested_url=='lostpassword/popup':
        $ajax_result['ok'] = true;
        $module_template = '/modules/authorization/templates/lostpassword.popup.html';
        break;
   ////////////////////////////////////////////////////////////////////////////////////////////////
   // восстановление - отправка пароля
   ////////////////////////////////////////////////////////////////////////////////////////////////
   case $this_page->requested_url=='lostpassword/sent':
        $post_parameters = Request::GetParameters(METHOD_POST);
        $login = $post_parameters['login'];
        if( !empty( $login ) ){
            // проверка на существование такого пользователя
            $where = "login = '".$db->real_escape_string( $login )."'";
            $user_row = $db->fetch("SELECT * FROM ".$sys_tables['users']." WHERE ".$where);
            if(empty($user_row)) $ajax_result['error'] = 'Пользователя с такими данными не существует';
            else Response::SetArray( 'user', $user_row );
        }
        else $ajax_result['error'] = 'Пустой логин';
        
        if(empty($ajax_result['error'])) {
            $emails = !empty( $this_page->settings['admin_emails'] ) ? explode( ';', $this_page->settings['admin_emails'] ) : false;
            if( !empty( $emails ) ) {
                foreach( $emails as $email ){
                    if( Validate::isEmail( $email ) ) {
                        // данные пользователя для шаблона
                        Response::SetArray( 
                            "data", 
                            array( 
                                'login' => $login, 
                                'name'  => $user_row['name'] 
                            ) 
                        );
                        // данные окружения для шаблона
                        $env = array(
                            'url' => Host::GetWebPath('/'),
                            'host' => Host::$host,
                            'ip' => Host::getUserIp(),
                            'datetime' => date('d.m.Y H:i:s')
                        );
                        Response::SetArray('env', $env);
                        // инициализация шаблонизатора
                        $eml_tpl = new Template( 'lostpassword_email.html', $this_page->module_path );
                        // формирование html-кода письма по шаблону
                        $html = $eml_tpl->Processing();

                        if( !class_exists('Sendpulse') ) require_once("includes/class.sendpulse.php");
                        //отправка письма
                        $sendpulse = new Sendpulse( 'subscriberes' );
                        $result = $sendpulse->sendMail( 'Член жюри ' . $login . ' просит восстановить пароль ', $html, $user_row['name'], $email );
                    }
                }
            }
            
            if( !empty( $result ) ) Response::SetString('success','email');

           
            if( empty( $ajax_result['error'] )){
                $ajax_result['ok'] = true;
                $module_template = '/templates/popup.success.html';
                Response::SetString('text', 'Ваш запрос отправлен администраторам. ');
                Response::SetString('title', 'Спасибо за обращение.');
            }
        } else {
            $ajax_result['errors'] = array( 'login_email' => $ajax_result['error'] );
        }
        break;
   ////////////////////////////////////////////////////////////////////////////////////////////////
   // восстановление утраченного пароля
   ////////////////////////////////////////////////////////////////////////////////////////////////
   case $this_page->requested_url=='lostpassword' || $this_page->requested_path=='lostpassword':

        $module_template = '/modules/authorization/templates/lostpassword.html';

        //проверка на существование записи восстановления
        $get_parameters = Request::GetParameters(METHOD_GET);
        Response::SetArray( 'get_parameters', $get_parameters );
        if( !empty( $auth->id ) || ( empty( $get_parameters['email'] ) || empty( $get_parameters['code'] ) ) )  Host::Redirect('/');
        $user = $db->fetch("SELECT * FROM ".$sys_tables['users_restore']." WHERE users_email = ? AND confirm_code = ?",
           $get_parameters['email'] , $get_parameters['code']
        );
        if(empty($user)) Response::SetBoolean('wrong_email_code', true);
        else {
            $this_page->metadata['title'] = 'Восстановление пароля';
            // скрипт управления формой
            // получение переданных из формы данных
            $post_parameters = Request::GetParameters(METHOD_POST);
            $errors = array();
            $reg_email = '';
            // если была отправка формы (запрос кода или подтверждение кода с новым паролем)
            if( !empty($post_parameters['submit']) ){

                // проверка новых паролей
                if(isset($post_parameters['newpass1'])){
                    if(strlen($post_parameters['newpass1'])<3) $errors['newpass1'] = 'Пароль слишком короткий';
                    if(strlen($post_parameters['newpass1'])>64) $errors['newpass1'] = 'Пароль слишком длинный';
                    if($post_parameters['newpass1']!=$post_parameters['newpass2']){
                        $errors['newpass1'] = ' ';
                        $ajax_result['error'] = $errors['newpass2'] = 'Пароли не совпадают';
                    }
                }
                
                // получение ранее отправленного запроса
                if(!empty($user))
                    $restore_row = $db->fetch("SELECT * FROM ".$sys_tables['users_restore']." WHERE id_users=?", $user['id_users']);
                else $restore_row = array();

                
                // если ошибок нет и такой пользователь найден
                if(empty($errors)){
                    
                    Response::SetString('success','step2');
                    // обработка формы подтверждения смены пароля
                    $result = $db->query("UPDATE ".$sys_tables['users']."
                                          SET passwd=?
                                          WHERE id=?"
                                          , sha1(sha1($post_parameters['newpass1']))
                                          , $user['id_users']);
                    if($result){
                        $res = $db->query("DELETE FROM ".$sys_tables['users_restore']." WHERE id=?", $restore_row['id']);
                        Response::SetBoolean('completed',true);
                        $res = $auth->AuthCheck($get_parameters['email'], $post_parameters['newpass1']);
                        $ajax_result['ok'] = true;
                        $ajax_result['success'] = 'Успешно. Подождите немного...';
                        $ajax_result['popup_redirect'] = true;
                        
                    } else $ajax_result['error'] = $errors['error'] = 'Технические неполадки';
                }
            }
            //чтобы был футер
            Response::SetBoolean('small_footer',true);
            
            Response::SetArray('errors',$errors);
            Response::SetArray('fields',$post_parameters);
            //чтобы не уходить со второй формы, если ошибка
            if ((!empty($post_parameters['submit']))&&($post_parameters['submit']=='commit')){
                if ($errors){
                    Response::SetString('success','step2_error');
                }
                else{
                    Response::SetString('success','step2');
                    
                }
            }
        }
        break; 
   ////////////////////////////////////////////////////////////////////////////////////////////////
   // разлогинивание
   ////////////////////////////////////////////////////////////////////////////////////////////////
    case $this_page->requested_url=='logout':
        $auth->logout();
        Host::Redirect('/authorization/');
        break;   
}      
  
?>