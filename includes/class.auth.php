<?php
/**    
* Authorization
*/
if(!defined("COOKIE_SAVE_DAYS")) define("COOKIE_SAVE_DAYS", 30);

class Auth {
    private $users_table = "";
    private $photos_table = "";
    private $groups_table = "";
    private $users_restore_table = "";

    public $authorized = false;
    public $auth_trying = false;
    public $id = 0;
    public $passwd = "";
    public $name = ""; // user name
    public $login = ""; 
    public $lastname = "";
    public $id_group = 0;         
    public $email = "";
    public $phone = "";
    public $user_photo = "";
    public $photo = "";
    public $social_data = array();
    public $session_delay = 600; //session length, minutes
    public $session_hash = false;
    public $CookieSave = true;
    public $CookieName = 'sitecookie';
    public $user_rights = array();
    public $group_rights = array();
    public $sys_tables = array();

    public function __construct(){
        $host = getenv("HTTP_HOST") ? getenv("HTTP_HOST") : getenv("SERVER_NAME");
        $this->CookieName = 'au_'.sha1(DEBUG_MODE ? '.spmk.int' : '.spmk.group');
        Config::Init();
        $this->users_table = Config::$sys_tables['users'];
        $this->groups_table = Config::$sys_tables['users_groups'];
        $this->photos_table = Config::$sys_tables['users_photos'];
        $this->users_restore_table = Config::$sys_tables['users_restore'];
        $this->sys_tables = Config::$sys_tables;
        
    }
        
    public function checkAuth($login='', $password='', $cookie_save=false, $logoff=false){
        return $this->AuthCheck($login, $password, $cookie_save, $logoff);
    }
    public function checkAuthSocial($social_data){
        $this->social_data = array('field'=>$social_data['field'],'value'=>$social_data['value']) ;
        return $this->AuthCheck(false, false, true, false, $this->social_data);
    }
    public function checkSuperAdminAuth($id){
        return $this->AuthCheck(false, false, false, false, false, $id);
    }
    
 
    public function isAuthorized(){
        return $this->authorized;
    }

    /**
    * @desc чистим куки
    */
    private function ClearCookiesData() {
        Cookie::SetCookie($this->CookieName, '', -3600, '/', DEBUG_MODE ? '.spmk.int' : '.spmk.group');
    }
    /**
    * @desc запись юзера в куки
    */
    private function SetDataToCookies() {
        $auth = array(
            'user_email'=>$this->email,
            'user_phone'=>$this->phone,
            'user_login'=>$this->login,
            'hash_password'=>$this->passwd,
            'social_field' => !empty($this->social_data['field'])?$this->social_data['field']:false,
            'social_value' => !empty($this->social_data['value'])?$this->social_data['value']:false,
            'cookie_save'  => $this->CookieSave
        );
        Cookie::SetCookie($this->CookieName, $auth, 60*60*24*COOKIE_SAVE_DAYS, '/', DEBUG_MODE ? '.spmk.int' : '.spmk.group');
    }
    
    /**
    * @desc запись юзера в сессию
    */
    private function SetDataToSession() {
        $auth = array(  
            'user_email' => $this->email,
            'user_phone' => $this->phone,
            'user_login' => $this->login,
            'hash_password' => $this->passwd,
            'social_field' => !empty($this->social_data['field'])?$this->social_data['field']:false,
            'social_value' => !empty($this->social_data['value'])?$this->social_data['value']:false,
            'cookie_save'  => $this->CookieSave
        );
        Session::SetArray('auth_data', $auth );
    }
        
    /**
    * @desc проверка авторизации
    */
    public function AuthCheck($login='', $password='', $cookie_save=false, $logoff=false, $social_data = false, $super_admin_id = false){
        // если просят разлогинить - разлогиниваем и всё
        $logoff = $logoff || Request::GetParameter('logoff',METHOD_POST);
        $logoff = $logoff || Request::GetParameter('logoff',METHOD_GET);
        if(!empty($logoff)){
            $this->Logoff();
        } else {
            // если есть ID суперадмина - берем информацию из базы и авторизуем
            if( !empty($super_admin_id) ){
                if($this->Login(false, false, false, false, true, false, $super_admin_id)) {
                    $this->SetDataToSession();
                    $this->SetDataToCookies();
                    return true;
                }
            // если есть куки - берем инфо из куков и на его основе авторизуем и создаем сессию
            } elseif($auth = Cookie::GetArray($this->CookieName)){
                 if(!empty($auth['social_field']) && !empty($auth['social_value'])) $this->social_data = array('field'=>$auth['social_field'],'value'=>$auth['social_value']) ;
                 if($this->Login($auth['user_email'],$auth['user_phone'],$auth['user_login'],$auth['hash_password'], !empty( $social_data ) ? true : $auth['cookie_save'], $this->social_data ) ) {
                    $this->SetDataToSession();
                    $this->SetDataToCookies();
                    return true;
                }
            
            // если есть сессия - берем информацию из сессии и на её основе авторизуем
            } else if($auth = Session::GetParameter('auth_data')){
                if(!empty($auth['social_field']) && !empty($auth['social_value'])) $this->social_data = array('field'=>$auth['social_field'],'value'=>$auth['social_value']) ;
                if($this->Login( $auth['user_email'], $auth['user_phone'], $auth['user_login'], $auth['hash_password'], $auth['cookie_save'], $this->social_data)) {
                    $this->SetDataToSession();
                    $this->SetDataToCookies();
                    return true;
                }
            }
            if(!$this->isAuthorized()){
                // если пустые данные - пытаемся получить их из формы
                if(empty($login)) $login = Request::GetString('auth_login', METHOD_POST);
                if(empty($password)) $password = Request::GetString('auth_passwd', METHOD_POST);
                if(empty($cookie_save)){
                    $cookie_save = Request::GetString('auth_cookie_save', METHOD_POST);
                    $cookie_save = !(empty($cookie_save) && $cookie_save == "false");
                }
                if(!empty($login) || !empty($password)) $this->auth_trying = true;
                $email = $phone = '';
                // проверка логина
                if(Validate::isEmail($login)){
                    $email = $login; // это почтовый адрес
                } else {
                    $phone_login = preg_replace('![^0-9]!','', $login);
                    if(strlen($phone_login)>=10){
                        $phone = substr($phone_login,-10); // это мобильный телефон
                    }
                }
                if((empty($password) || strlen($password)<3) && empty($social_data)) {
                    return false;
                }
                if($this->Login($email,$phone,$login,!empty( $password ) ? sha1( $password ) : false,$cookie_save, $social_data, $super_admin_id)){
                    $this->SetDataToSession();
                    $this->SetDataToCookies();
                    return true;
                }
            }
        }
        return false;
    }

    /**
    * Процедура залогинивания
    * @param string $email
    * @param string $phone
    * @param string $hash_password - hach(password)
    * @param boolean $save_in_cookie
    * @return 
    */
    private function Login($email, $phone, $login, $hash_password, $save_in_cookie=false, $social_data = false, $super_admin_id = false){
        global $db;
        if( empty($email) && empty($phone) && empty($login) && empty($hash_password) && empty($social_data) && empty($super_admin_id) ) return false;
        $where_auth = $where = array();
        if(!empty($social_data)){
            $where = $social_data['field']." = ".$social_data['value'];    
        } else {
            if(!empty($email)) $where_auth[] = $this->users_table.".`email` = '" . $db->real_escape_string($email). "'";
            if(!empty($phone)) $where_auth[] = $this->users_table.".`phone` = '" . $db->real_escape_string($phone). "'";
            if(!empty($login)) $where_auth[] = $this->users_table.".`login` = '" . $db->real_escape_string($login). "'";
            if(!empty($where_auth)) $where[] = " ( " . implode(" OR ", $where_auth) . " ) ";                                            
            if(!empty($hash_password)) $where[] = $this->users_table.".`passwd` = '" . sha1($db->real_escape_string($hash_password)) . "'";
            if(!empty($super_admin_id)) $where[] = $this->users_table.".`id` = ".$super_admin_id;
            $where = implode (" AND ", $where);
        }
        
        $sql = "SELECT
                    ".$this->users_table.".*,
                    ".$this->groups_table.".`access` as `group_access`,
                    ".$this->photos_table.".`name` as `user_photo`, 
                    LEFT (".$this->photos_table.".`name`,2) as `photo_subfolder`
                FROM ".$this->users_table."
                LEFT JOIN ".$this->groups_table." on ".$this->groups_table.".`id` = ".$this->users_table.".`id_group`
                LEFT JOIN ".$this->photos_table." ON ".$this->photos_table.".id = ".$this->users_table.".id_main_photo
                WHERE ".$where;

        $res = $db->fetch($sql);
        if(!empty($res)){
            $this->CookieSave = $save_in_cookie;
            $this->id = $res['id'];
            $this->passwd = $hash_password;
            $this->login = $res['login'];
            $this->name = $res['name'];
            $this->lastname = $res['lastname'];
            $this->email = $res['email'];
            $this->phone = (Validate::isPhone($res['phone'])?$res['phone']:"");
            $this->id_group = $res['id_group'];
            $this->user_photo = !empty($res['user_photo']) ? $res['photo_subfolder'].'/'.$res['user_photo'] : '';
            $res['user_photo_folder'] = Config::Get('img_folders/users');
            Response::SetArray('auth_data',$res);
            
            // разбор пользовательских прав доступа
            $this->user_rights = array();
            if(!empty($res['access']) && preg_match_all('!([\S]+)\s+([\S]+)!m', $res['access'], $matches, PREG_SET_ORDER)){
                foreach($matches as $match) {
                    $this->user_rights[] = array(
                        'path'=>$match[1],
                        'rights'=>$match[2]
                    );
                }
            }
            // разбор групповых прав доступа
            $this->group_rights = array();
            if(!empty($res['group_access']) && preg_match_all('!([\S]+)\s+([\S]+)!m', $res['group_access'], $matches, PREG_SET_ORDER)){
                foreach($matches as $match) {
                    $this->group_rights[] = array(
                        'path'=>$match[1],
                        'rights'=>$match[2]
                    );
                }
            }
            
            $db->query("UPDATE ".$this->users_table." SET `last_enter` = NOW() WHERE `id` =".$this->id) or die($db->error);
            $this->authorized = true;
            
            return true;
        }
        return false;
    }    
    
    public function logout(){
        $this->Logoff();
    }

    public function Logoff(){
        $this->authorized = false;
        $this->id = 0;
        $this->passwd = "";
        $this->email = "";
        $this->phone = "";
        $this->group_rights = $this->user_rights = array();
        $this->SetDataToSession();
        $this->ClearCookiesData();
        Host::Redirect( Host::$referer_uri );
    }
}
// определяем изначальные конфигурационные данные
define( 'REQUEST_TOKEN_URL', 'https://api.twitter.com/oauth/request_token' );
define( 'AUTHORIZE_URL', 'https://api.twitter.com/oauth/authorize' );
define( 'ACCESS_TOKEN_URL', 'https://api.twitter.com/oauth/access_token' );
define( 'ACCOUNT_DATA_URL', 'https://api.twitter.com/1.1/users/show.json' );
define( 'CALLBACK_URL', Host::$root_url . '/authorization/twilogin/' );
// формируем подпись для получения токена доступа
define('URL_SEPARATOR', '&');
class OAuthTwitter { 
    private static $oauth_token_secret = '';
    private static $oauth_token = '';
    private static $screen_name = '';
    
    public static function getLink(){
        $oauth_nonce = md5(uniqid(rand(), true));
        $oauth_timestamp = time();

        $params = array(
            'oauth_callback=' . urlencode(CALLBACK_URL) . URL_SEPARATOR,
            'oauth_consumer_key=' . Config::Get('social/twitter/key')  . URL_SEPARATOR,
            'oauth_nonce=' . $oauth_nonce . URL_SEPARATOR,
            'oauth_signature_method=HMAC-SHA1' . URL_SEPARATOR,
            'oauth_timestamp=' . $oauth_timestamp . URL_SEPARATOR,
            'oauth_version=1.0'
        );

        $oauth_base_text = implode('', array_map('urlencode', $params));
        $key = Config::Get('social/twitter/secret') . URL_SEPARATOR;
        $oauth_base_text = 'GET' . URL_SEPARATOR . urlencode(REQUEST_TOKEN_URL) . URL_SEPARATOR . $oauth_base_text;
        $oauth_signature = base64_encode(hash_hmac('sha1', $oauth_base_text, $key, true));


        // получаем токен запроса
        $params = array(
            URL_SEPARATOR . 'oauth_consumer_key=' . Config::Get('social/twitter/key'),
            'oauth_nonce=' . $oauth_nonce,
            'oauth_signature=' . urlencode($oauth_signature),
            'oauth_signature_method=HMAC-SHA1',
            'oauth_timestamp=' . $oauth_timestamp,
            'oauth_version=1.0'
        );
        $url = REQUEST_TOKEN_URL . '?oauth_callback=' . urlencode(CALLBACK_URL) . implode('&', $params);

        $response = file_get_contents($url);
        parse_str($response, $response);

        self::$oauth_token = $response['oauth_token'];
        self::$oauth_token_secret = $response['oauth_token_secret'];


        // генерируем ссылку аутентификации
        return AUTHORIZE_URL . '?oauth_token=' . self::$oauth_token;        
    }   
    
    public static function Authentication(){
        $oauth_nonce = md5(uniqid(rand(), true));
        $oauth_timestamp = time();
        self::$oauth_token = $_GET['oauth_token'];
        $oauth_verifier = $_GET['oauth_verifier'];


        $oauth_base_text = "GET&";
        $oauth_base_text .= urlencode(ACCESS_TOKEN_URL)."&";

        $params = array(
            'oauth_consumer_key=' . Config::Get('social/twitter/key') . URL_SEPARATOR,
            'oauth_nonce=' . $oauth_nonce . URL_SEPARATOR,
            'oauth_signature_method=HMAC-SHA1' . URL_SEPARATOR,
            'oauth_token=' . self::$oauth_token . URL_SEPARATOR,
            'oauth_timestamp=' . $oauth_timestamp . URL_SEPARATOR,
            'oauth_verifier=' . $oauth_verifier . URL_SEPARATOR,
            'oauth_version=1.0'
        );

        $key = Config::Get('social/twitter/secret') . URL_SEPARATOR . self::$oauth_token_secret;
        $oauth_base_text = 'GET' . URL_SEPARATOR . urlencode(ACCESS_TOKEN_URL) . URL_SEPARATOR . implode('', array_map('urlencode', $params));
        $oauth_signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));

        // получаем токен доступа
        $params = array(
            'oauth_nonce=' . $oauth_nonce,
            'oauth_signature_method=HMAC-SHA1',
            'oauth_timestamp=' . $oauth_timestamp,
            'oauth_consumer_key=' . Config::Get('social/twitter/key'),
            'oauth_token=' . urlencode(self::$oauth_token),
            'oauth_verifier=' . urlencode($oauth_verifier),
            'oauth_signature=' . urlencode($oauth_signature),
            'oauth_version=1.0'
        );
        $url = ACCESS_TOKEN_URL . '?' . implode('&', $params);

        $response = file_get_contents($url);
        parse_str($response, $response);


        // формируем подпись для следующего запроса
        $oauth_nonce = md5(uniqid(rand(), true));
        $oauth_timestamp = time();

        self::$oauth_token = $response['oauth_token'];
        self::$oauth_token_secret = $response['oauth_token_secret'];
        self::$screen_name = $response['screen_name'];

        $params = array(
            'oauth_consumer_key=' . Config::Get('social/twitter/key') . URL_SEPARATOR,
            'oauth_nonce=' . $oauth_nonce . URL_SEPARATOR,
            'oauth_signature_method=HMAC-SHA1' . URL_SEPARATOR,
            'oauth_timestamp=' . $oauth_timestamp . URL_SEPARATOR,
            'oauth_token=' . self::$oauth_token . URL_SEPARATOR,
            'oauth_version=1.0' . URL_SEPARATOR,
            'screen_name=' . self::$screen_name
        );
        $oauth_base_text = 'GET' . URL_SEPARATOR . urlencode(ACCOUNT_DATA_URL) . URL_SEPARATOR . implode('', array_map('urlencode', $params));

        $key = Config::Get('social/twitter/secret') . '&' . self::$oauth_token_secret;
        $signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));

        // получаем данные о пользователе
        $params = array(
            'oauth_consumer_key=' . Config::Get('social/twitter/key'),
            'oauth_nonce=' . $oauth_nonce,
            'oauth_signature=' . urlencode($signature),
            'oauth_signature_method=HMAC-SHA1',
            'oauth_timestamp=' . $oauth_timestamp,
            'oauth_token=' . urlencode(self::$oauth_token),
            'oauth_version=1.0',
            'screen_name=' . self::$screen_name
        );

        $url = ACCOUNT_DATA_URL . '?' . implode(URL_SEPARATOR, $params);

        $response = file_get_contents($url);
        return json_decode($response, true);        
    }
}
?>