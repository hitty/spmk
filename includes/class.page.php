<?php
/**    
* Основной класс обработки URL запросов
*/
class Page {
    private $full_host = "";
    private $requested_url = "";
    private $requested_path = "";
    private $real_url = "";
    private $real_path = "";
    private $real_params = array();
    private $query_params = array();
    private $template = "";
    private $cachetime = "";
    private $http_code = 200;
    private $error_message = "";
    private $incacheobjects = array();
    
    private $first_instance = true;
    private $block = false;                     //вызов блока
    private $clean_html = false;                 //получить чистый html
    private $module_path = "";                  // путь к папке модуля

    private $page_id = 0;
    private $page_url = "";                     // полный URI страницы
    private $page_alias = "";                   // последняя часть URI страницы
    private $page_title = "";                   // заголовок страницы (переопределяется в seo)
    private $page_module = "";                  // подключаемый модуль (с путем от корня сайта)
    private $page_parameters = array();         // параметры для модуля (из URL)
    private $module_parameters = array();       // параметры для модуля (из DB)
    private $page_cache_time = 0;               // время кэширования страницы (0 - не кэшировать)
    private $page_block = false;                // страница-блок (работает только из внутренних вызовов, не доступна по прямому url из браузера)
    private $page_template = "";                // шаблон оформления внутреннего содержимого
    private $page_content = "";                 // текст модуля 
    private $page_access = "";
    private $page_seo_title = "";               
    private $page_seo_h1 = "";
    private $page_pretty_url = "";
    public $page_seo_breadcrumbs = array();
    private $page_seo_keywords = "";
    private $page_seo_descriprion = "";
    private $page_seo_text = "";
    private $page_breadcrumbs = array();        // хлебные крошки для текущей страницы
    private $metadata = array();                // метаданные страницы (могут переопределяться в модуле)
    
    private $menu = array();                    // хранилище для меню
    private $menu_response_in_module = false;   //чтобы делался Response $this->menu во время обработки модуля, не позже - для страниц авторизации-регистрации-восстановления
    private $last_visited_page = "";            //последняя посещенная страница
    
    public $is_admin_page = false;
    public $is_manage_page = false;
    public $is_teach_page = false;
    public $is_profile_page = false;
    public $is_edit_mode = false;
    public $is_members_page = false;
    public $is_advertising_page = false;
    public $is_finished_webinar_page = false;
    public $settings = array();                    // страницы для a/b тестирования
    
    /** 
    * объект страницы
    * 
    * @param string URL страницы
    * @param array массив объектов для вхождения в ключ кеширования ('post','cookie')
    * @return Page
    */
    public function __construct( $url, $incacheobjects=null){
        global $auth;
        $this->full_host = Host::$protocol . '://' . Host::$host;
        Response::SetString( 'full_host', $this->full_host );
        // полный запрошенный урл (здесь могут быть GET параметры)
        $this->requested_url = $this->real_url =  $url;
        $parsed_url = parse_url(Host::getWebPath( $url ) );
        // чистый URI страницы
        $this->requested_path = $this->real_path = trim( $parsed_url['path'],'/');
        $ajax_mode = (isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && !empty( $_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || !empty( $internal_mode);
        // определение признака первого запуска (страница или блок)
        $this->block = preg_match( '#\/block\/#msiU', $parsed_url['path'] );
        if( !defined( '__CLASS_PAGE_FIRST_INSTANCE__' ) ) {
            $GLOBALS['js_set'] = $GLOBALS['css_set'] = array();
            define( '__CLASS_PAGE_FIRST_INSTANCE__', 1 );
            $this->first_instance = true;
            // сео - подмена урлов
            $this->urlChecker( $url);
            // проверка региона
        } else $this->first_instance = false;
        // заглатывание списка значащих объектов для ключа кэша
        if( !empty( $incacheobjects ) ){
            foreach( $incacheobjects as $key=>$val){
                if(in_array( $key,array('post','session','cookie','POST','SESSION','COOKIE' ) ))
                    $this->incacheobjects[strtolower( $key)] = $val;
            }
        }           
        if(substr( $this->requested_path,0,8)=='manage') $this->is_manage_page = true;
        if(substr( $this->requested_path,0,7)=='profile') $this->is_profile_page = true;
       
    }
    
    
    private function urlChecker( $url = '', $seo = true){
        global $db, $ajax_mode;
        if( empty( $url ) ) $url = Host::getWebPath( $url);
        $parsed_url = parse_url( $url);
        if( !isset( $parsed_url['path'] ) ) $parsed_url['path'] = "";
        $query = $clearquery = '';
        $gets = $additional_query = array();
        //редирект при пустом параметре
        $empty_get_parameters = false;
        if( !empty( $parsed_url['query'] ) ){
            $qry = explode('&', $parsed_url['query']);
            foreach( $qry as $q) {
                list( $key,$val) = explode('=',$q.'=');
                if( !isset( $val ) ) $empty_get_parameters = true;
                else $gets[$key] = $q;
                
            }
            //if( !empty( $empty_get_parameters ) ) Host::Redirect( $parsed_url['path'] .'?' . implode('&', $gets ) );
            if( !empty( $gets['page'] ) ) {
                $page = $gets['page'];
                unset( $gets['page']);
            }
            if( !empty( $gets['sortby'] ) ) {
                $sortby = $gets['sortby'];
                unset( $gets['sortby']);
            }
                    
            ksort( $gets);
            $query = $clearquery = implode('&',$gets);
            $this->query_params = $gets;
            if( !empty( $sortby ) ) $additional_query[] = $sortby;
            if( !empty( $search_type ) ) $additional_query[] = $search_type;
            if( !empty( $page ) ) $additional_query[] = $page;
            $query .= !empty( $additional_query) ? ( empty( $query)?'':'&').implode('&',$additional_query) : '';
            
        }
        
        if( !empty( $seo ) ){
            $checking_url = trim( $parsed_url['path'], '/').( empty( $clearquery)?'':'/?'.$clearquery);
            // подкачка данных по СЕО для страницы
            $ajax_mode = (isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && !empty( $_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || !empty( $internal_mode);
            if( empty( $ajax_mode ) ){
                $page_seo = $db->fetch("SELECT * FROM ".Config::$sys_tables['common_pages_seo']."
                                    WHERE ? = url", $checking_url);
                if( !empty( $page_seo['pretty_url']) && $page_seo['pretty_url'] != $page_seo['url'] && !empty( $page_seo['only_params']) && $page_seo['only_params'] == 2) {
                    $new_url = ( !empty( $this->ab_test) ? 'new/' : '' ) . trim( $page_seo['pretty_url']).'/';
                    Host::Redirect( $new_url.( !empty( $additional_query) ? '?'.implode('&',$additional_query) : '' ) );
                }
                if( !empty( $parsed_url['query']) && $query != $parsed_url['query']) {
                    Host::Redirect( ( !empty( $this->ab_test) ? 'new/' : '' ) . trim( $parsed_url['path'], '/').'/'.( empty( $query)?'':'?'.$query ) );
                }
            }
            
        }
    }
    public function Render( $is_block = false){
        global $memcache, $ajax_mode, $db, $auth;
        //переопределение глобальных таблиц БД
        $sys_tables = Config::$sys_tables; 
        // сигнатура кэша
        $page_signature = $this->createPageSignature();
        
        //определение id сессии (при загрузке файлов пользователем)
        $sessname = Session::GetName();
        $internal_mode = Request::GetString( $sessname, METHOD_POST );
        //вывод чистого html аякс-запросом
        $this->clean_html = Request::GetString( 'clean_html', METHOD_POST );
        $ajax_mode = (isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && !empty( $_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || !empty( $internal_mode);
        if( $ajax_mode) {
            $page_signature = 'ajax::'.$page_signature;
            $ajax_result = array('ok'=>false);
            Response::SetBoolean('ajax_mode',$ajax_mode);
        } elseif( $is_block)  $page_signature = 'block::'.$page_signature;

        
        if( !$ajax_mode && $this->first_instance && !$this->is_admin_page && !$this->is_manage_page && !$this->is_teach_page && !Host::isBot( ) ) {
            //запоминание последней посещенной страницы
            $last_visited_page = Session::GetString('last_visited_page');
            $this->last_visited_page = !empty( $last_visited_page ) ? $last_visited_page : '';
            Session::SetString('last_visited_page', $this->requested_url );
        }

        // проверка наличия в кэше страницы и блоки не AJAX
        if(Config::$values['memcache']['enabled']){
            $return = $memcache->get( $page_signature);
            if( $return !== FALSE) {
                if( !$ajax_mode) return $return;   
                else{ 
                    header("Content-type: application/json; charset=utf-8");
                    $return['cache_out_date'] = date('d.m.Y H:i:s'); 
                    echo Convert::json_encode( $return);
                    exit(0);    
                }
            }
        }
                
        // здесь проверить в сео на предмет подмены адреса
        if( $this->first_instance){
            // подкачка данных по СЕО для страницы
            $page_seo = $db->fetch("SELECT * FROM ".$sys_tables['common_pages_seo']."
                                    WHERE ? = pretty_url
                                       OR ? = url
                                    ORDER BY LENGTH(pretty_url) DESC, LENGTH(url) DESC"
                                    , $this->requested_path
                                    , $this->requested_url);
                                    
            if( !empty( $page_seo ) ){
                $this->page_seo_title = $page_seo['title'];
                $this->page_seo_h1 = $page_seo['h1_title'];
                $this->page_seo_description = $page_seo['description'];
                $this->page_seo_keywords = $page_seo['keywords'];
                $this->page_pretty_url = $page_seo['pretty_url'];
                //СЕО хлебные крошки. Имеют больший приоритет, чем обычные
                if( !empty( $page_seo['breadcrumbs']) && empty( $this->ab_test ) ) {
                    $seo_bc = explode(',',$page_seo['breadcrumbs']);
                    foreach( $seo_bc as $k=>$bc){
                        $bc = explode("=>",$bc);
                        if( !empty( $bc[1] ) ) $this->page_seo_breadcrumbs[$k] = array('title'=>$bc[1], 'level'=>$k, 'url'=>$bc[0]);
                    }
                    
                }

                $gets = array();
                $pd2 = parse_url( $page_seo['url']);
                $qr2 = empty( $pd2['query']) ? array() : Convert::StringGetToArray( $pd2['query']);
                foreach( $qr2 as $key=>$val) {
                    $gets[$key]=$val;
                    Response::SetParameter( $key, $val, METHOD_GET);
                }
                $pd1 = parse_url( $this->requested_url);
                $qr1 = empty( $pd1['query']) ? array() : Convert::StringGetToArray( $pd1['query']);
                foreach( $qr1 as $key=>$val) $gets[$key]=$val;
                $this->real_path = trim( $pd2['path'],'/');
                $this->real_url =  $this->real_path.'/'.( empty( $gets) ? "" : '?'.Convert::ArrayToStringGet( $gets ) );
                $this->urlChecker( $this->real_url, false);
                $this->page_seo_text = trim( $page_seo['url'], '/') == trim( $this->real_url, '/') ? $page_seo['seo_text'] : '';
            }
        }        
        //проект по умолчанию
        $temp_path = $this->real_path;
        Response::SetString('basic_img_dolder', Config::$values['img_folders']);
        // поиск в текстовых страницах

        // загрузка страницы из DB
        $page = $db->fetch("SELECT p.*,m.path,m.level FROM ".$sys_tables['common_pages']." p
                            LEFT JOIN ".$sys_tables['common_pages_map']." m ON p.id=m.object_id
                            WHERE (".$db->quoted( $temp_path)." = p.url
                                    OR ".$db->quoted( $temp_path)." LIKE CONCAT(p.url,'/%' ) )
                                ".( $this->first_instance ? " AND p.block_page!=1 " : "")."
                            ORDER BY LENGTH(p.url) DESC");        
                              
        // если страницу не нашли, или запрошенный адрес включает параметры, а найденная страница параметры не принимает
        if( empty( $page) || (strlen( $page['url'])<strlen( $this->real_path) && $page['no_require_params']==1 ) ) {
            $this->error_message = "Page not found";
            $this->http_code = 404;
            $this_page =& $this;
        } else {
            $this->page_id = $page['id'];
            $this->page_url = $page['url'];
            $this->page_alias = $page['alias'];
            $this->page_title = $page['title'];
            $this->page_block = $page['block_page']==1;
            $this->page_cache_time = $page['cache_time'];
            $this->page_module = $page['module'];
            $this->page_template = $page['template'];
            $this->page_access = $page['access'];
            $this->page_content = $page['content'];
            $this->module_parameters = Convert::StringGetToArray( $page['parameters']);
            
            if( !empty( $this->module_parameters['payed_format'] ) ) Response::SetBoolean('payed_format',true);
            $this->page_parameters = array();
            if(strlen( $page['url'])<strlen( $this->real_path ) ){
                //не заменяем + на %2B, потому что urldecode заменит его на пробел
                $params = explode('/',substr( $this->real_path, strlen( $page['url'])+1 ) );
                //добавлено 01032016 - экранируем кавычки
                $params = array_map('addslashes',array_map('urldecode',$params ) );
                $this->page_parameters = $params;
            }
                        
            // дубликат для использования в модуле
            $this_page =& $this;
            //для модерации открываем доступ
            if( !$this->checkAccess( ) ){
                $this->error_message = "Not enough rights";
                $this->http_code = 403;
                Response::SetArray('current_page', get_object_vars( $this ) );
            } else {
                if( $this->first_instance ){                    
                    // подкачка списка страниц для хлебных крошек если нет СЕО хлебных крошек
                    if( !empty( $page['path']) && empty( $this->page_seo_breadcrumbs) )
                        $bc = $db->fetchall("SELECT m.object_id,m.level,p.title,p.url
                                        FROM ".$sys_tables['common_pages_map']." m
                                        LEFT JOIN ".$sys_tables['common_pages']." p ON m.object_id=p.id
                                        WHERE m.path IN (?)
                                        ORDER BY m.level", false, $page['path']);
                    if( !empty( $bc ) ) $this->page_breadcrumbs = $bc;
                }
                Response::SetArray('current_page', get_object_vars( $this ) );

                $this->metadata = array(
                    'title' => $this->page_title,
                    'description' => $this->page_title,
                    'keywords' => $this->page_title
                );
                //настройки сайта
                
                $this_page->menuClear( 1 );
                $this_page->menuAdd( 'О компании', 'about', 1 );
                $this_page->menuAdd( 'Услуги', 'uslugi', 1 );
                    $this_page->menuAdd( 'Проектирование', 'proektirovanie', 2, false, false, 'uslugi' );
                    $this_page->menuAdd( 'Изготовление', 'izgotovlenie', 2, false, false, 'uslugi'  );
                    $this_page->menuAdd( 'Монтаж', 'montazh', 2, false, false, 'uslugi'  );
                    $this_page->menuAdd( '«Под ключ»', 'pod_kluch', 2, false, false, 'uslugi'  );
                $this_page->menuAdd( 'Объекты', 'objekty', 1 );
                $this_page->menuAdd( 'Прайс', 'price', 1 );
                $this_page->menuAdd( 'Закупки', 'tenders', 1 );
                
                //###########################################################################
                // подключение и выполнение модуля
                //###########################################################################
                $page_module_file_exists = file_exists( $this->page_module );
                if( $page_module_file_exists ) $this->module_path = dirname( $this->page_module );

                if( !$page_module_file_exists || !require( $this->page_module ) ) {
                    $this->http_code = 404;
                    $this->error_message = "Page not found";
                }
                //добавление мета-тега canonical для chpu страниц
                if( $this->first_instance && !$this->is_admin_page && !$this->is_manage_page && !$this->is_teach_page ){
                    $canonical = !empty( $page_seo['pretty_url']) ? $page_seo['pretty_url'] : $this->requested_path;
                    $canonical_array = explode('/', trim( $canonical, '/' ) );
                    foreach( $canonical_array as $k=>$item) if( $k>3) unset( $canonical_array[$k]);
                    Response::SetString( 'meta_canonical', '/' .  implode( '/', $canonical_array ) . ( $canonical != '' ? '/' : '' ) ) ;
                }

                // информация об авторизованном пользователе
                if( $auth->isAuthorized()===true) {
                    $title = $auth->name;
                    if( empty( $title ) ) $title = $auth->lastname;
                    $auth_array = array(
                        'phone' => $auth->phone,
                        'name' => $auth->name,
                        'lastname' => $auth->lastname,
                        'email' => $auth->email,
                        'id' => $auth->id,
                        'id_group' => $auth->id_group,
                        'user_photo_folder' => Config::Get('img_folders')
                    );
                    Response::SetArray('auth',$auth_array);
                    
                }
                //шаблон окружения получения html аякс-запросом
                if( $ajax_mode && $this->clean_html ) $this->page_template = 'templates/ajax.html';
                
                // результат работы модуля
                if( $this->http_code==200){
                    if( $this->first_instance){
                        $GLOBALS['css_set'][] = '/css/adaptive.css';
                        // добавление css и js
                        $this->addScriptsAndCss();
                        // главное меню
                        if( !empty( $this->menu[1] ) ) Response::SetArray( 'mainmenu', $this->menu[1] );
                        if( !empty( $this->menu[2] ) ) Response::SetArray( 'mainmenu_second', $this->menu[2] );
                        
                    }
                    if( !empty( $this->page_seo_title ) ) $this->metadata['title'] = $this->page_seo_title;
                    if( !empty( $this->page_seo_description) ) $this->metadata['description'] = $this->page_seo_description;
                    elseif( !empty( $h1 ) ){  //если < 3 слов в description, то равно h1
                        $exploded_description = preg_replace("![^a-zа-я0-9\s]!msiU","",$this->metadata['description']);
                        $exploded_description = explode(" ",trim( $this->metadata['description'] ) );
                        if(count( $exploded_description)<=3) $this->metadata['description'] = $h1;
                    }                    
                    if( !empty( $this->page_seo_keywords ) ) $this->metadata['keywords'] = $this->page_seo_keywords; 
                    elseif( !empty( $h1 ) ){  //если < 3 слов в keywords, то равно h1
                        $exploded_keywords = preg_replace("![^a-zа-я0-9\s]!msiU","",$this->metadata['keywords']);
                        $exploded_keywords = explode(" ",trim( $this->metadata['keywords'] ) );
                        if(count( $exploded_keywords)<=3) $this->metadata['keywords'] = $h1;
                    }
                    if( !empty( $this->page_seo_text ) ) $this->metadata['seo_text'] = $this->page_seo_text; 
                    
                    Response::SetArray('metadata', $this->metadata);
                    
                    if( !( $this->is_admin_page && $this->is_manage_page && $this->is_teach_page && !empty( $module_content ) ) ) {
                        if( empty( $module_template ) ) $this->http_code = 404;
                        else {
                            $tpl = new Template( $module_template, $this_page->module_path);
                            $module_content = $tpl->Processing();
                        }
                    }
                    if( !empty( $this->page_seo_breadcrumbs ) ) Response::SetArray('breadcrumbs', $this->page_seo_breadcrumbs);
                    elseif( !empty( $this->page_breadcrumbs ) ) Response::SetArray('breadcrumbs', $this->page_breadcrumbs);
                    if( !empty( $module_template ) ) Response::SetString( 'content', $module_content );

                    if( !$this->first_instance ) {
                        $result_html = $module_content;
                    }
                    else{ 
                        $tpl = new Template( $this->page_template );   
                        $result_html = $tpl->Processing();
                    }
                    if( $this->page_cache_time>0 )
                        $memcache->set( $page_signature, $result_html, FALSE, $this->page_cache_time );
                }       
            }
        } 

        
        if( $ajax_mode && !$this->clean_html ){
            header("Content-type: application/json; charset=utf-8");
            $ajax_result['http_code'] = $this->http_code;
            if( !empty( $module_template_additional ) ) {
                $tpl = new Template( $module_template_additional, $this_page->module_path );
                $module_content_additional = $tpl->Processing();
                $ajax_result['html_additional'] = $module_content_additional;
                if( DEBUG_MODE ) $ajax_result['module_additional'] = $module_template_additional;
            }
            if( !empty( $module_template ) ) {
                $tpl = new Template( $module_template, $this_page->module_path );
                $module_content = $tpl->Processing();
                $ajax_result['html'] = $module_content;
                if( DEBUG_MODE ) $ajax_result['module'] = $module_template;
            }
            if( $this->page_cache_time>0){
                $ajax_result['cache_in_date'] = date('d.m.Y H:i:s');
                $memcache->set( $page_signature, $ajax_result, FALSE, $this->page_cache_time );
            }
            echo Convert::json_encode( $ajax_result );
            exit(0);    
        }
                        
        if( $this->http_code!=200){
            // обработка ошибочных кодов страницы
            if( $this->http_code==404){
                sendHTTPStatus(404);
                $GLOBALS['css_set'][] = '/css/404.css';
                $this->metadata['title'] = 'Ошибка 404. Страница не найдена.';
                Response::SetArray('metadata', $this->metadata);
                $error_template = '/templates/404.html'; 
                // главное меню
                $session_404_order = Session::GetInteger('session_404_order');
                if( empty( $session_404_order) || $session_404_order > 2) $session_404_order = 1;
                else ++$session_404_order;
                Session::SetInteger('session_404_order', $session_404_order);
                Response::SetInteger('session_404_order', $session_404_order);
            } elseif ( $this->http_code == 403 ) {
                $GLOBALS['css_set'][] = '/css/central.css';
                $GLOBALS['css_set'][] = '/manage/css/style.css';
                $GLOBALS['css_set'][] = '/manage/css/controls.css';
                $GLOBALS['css_set'][] = '/manage/css/form.css';
                $GLOBALS['css_set'][] = '/css/403.css';
                
                sendHTTPStatus(403);
                $error_template = '/templates/403.html';
            } else {
                sendHTTPStatus(500);
                $error_template = '/templates/500.html';
                file_put_contents('situation.log',date('d.m.Y H:i:s')."\n".$this->requested_url."\n".$this->error_message."\n----------\n");
            }
            if( $this->first_instance){
                $this->addScriptsAndCss();
                // главное меню
                if( !empty( $this->menu[1] ) ) Response::SetArray( 'mainmenu', $this->menu[1] );
            }
            Response::SetString( 'error_message', $this->error_message );
            $tpl = new Template( $error_template );
            $result_html = $tpl->Processing();
        }
        return $result_html;
    }

    /**
    * добавление скриптов и стилей в шаблон
    *    
    */
    private function addScriptsAndCss(){
        global $memcache;
        $js_array = array_unique( $GLOBALS['js_set'], SORT_REGULAR);
        $css_array = array_unique( $GLOBALS['css_set'], SORT_REGULAR);
        $js_key  = md5('js::'.implode('|',$js_array ) );
        $css_key = md5('css::'.implode('|',$css_array ) );
        // ожидание освобождения файла с наборами js и css
        $counter = 50;
        do{
            $write_sig = $memcache->get('scripts_and_css_write_sig');
            if( $write_sig!== false ) usleep(10000);
            $counter--;
        }while( $write_sig!== false  && $counter);
        // загрузка файла с наборами js и css
        $scripts_and_css = FileData::Load();
        // если мы будем дополнять файл новыми наборами, блокируем файл для других
        if( empty( $scripts_and_css['js'][$js_key]) || empty( $scripts_and_css['css'][$css_key] ) )
            $write_sig = $memcache->set('scripts_and_css_write_sig',1);
        if( empty( $scripts_and_css['counter'] ) ) $scripts_and_css['counter'] = 0; // если файл был пустой
        // смотрим js
        if( empty( $scripts_and_css['js'][$js_key] ) ){
            $js_id = $scripts_and_css['counter']+1;
            $scripts_and_css['js'][$js_key] = array(
                'id' => empty( $scripts_and_css['counter'])?1:$scripts_and_css['counter']+1,
                'files' => $js_array
            );
            $scripts_and_css['counter'] = $js_id;
        } else {
            $js_id = $scripts_and_css['js'][$js_key]['id'];
        }
        // смотрим css
        if( empty( $scripts_and_css['css'][$css_key] ) ){
            $css_id = $scripts_and_css['counter']+1;
            $scripts_and_css['css'][$css_key] = array(
                'id' => empty( $scripts_and_css['counter'])?1:$scripts_and_css['counter']+1,
                'files' => $css_array
            );
            $scripts_and_css['counter'] = $css_id;
        } else {
            $css_id = $scripts_and_css['css'][$css_key]['id'];
        }
        // если мы дополняли - записываем и освобождаем файл для других
        FileData::Save( $scripts_and_css);
        if( !$this->is_admin_page ) {
            include('js.php');
            include('css.php');
        }
        Response::SetInteger('js_id',$js_id);
        Response::SetInteger('css_id',$css_id);
    }
    /**
    * добавление хлебных крошек
    * 
    * @param string $title - название
    * @param string $url - url
    * @param integer $level - уровень вложенности
    */
    private function addBreadcrumbs( $title, $url, $level = false , $list = false){
        if( empty( $this->page_seo_breadcrumbs) || !empty( $this->ab_test ) ){
            $level = $level!== false  ? $level : count( $this->page_breadcrumbs);
            if( !empty( $list ) ){
                $new_list = array();
                foreach( $list as $k => $item) $list[$k] = is_array( $item) ? $item['title'] : $item;
            }
            if( !empty( $title) && !empty( $url ) ) $this->page_breadcrumbs[$level] = array('title'=>$title, 'level'=>$level-1, 'url'=>$url, 'list' => $list);
        }
    }
    /**
    * удаление хлебных крошек
    * 
    */
    private function clearBreadcrumbs(){
        $this->page_breadcrumbs = array();
    }

    /**
    * управление метаданными страницы
    * 
    * @param array $module_metadata - массив title, keywords, description
    * @param boolean $replace полная замена значений
    * @param string $glue строка склейки
    */
    private function manageMetadata( $new_metadata, $replace = false , $glue=' - '){
        if( !empty( $new_metadata ) ){
            $this->metadata['replace'] = $replace;
            if( !empty( $new_metadata['title'] ) ){
                $this->metadata['title'] = $new_metadata['title'] . ( $replace ? "" : ( empty( $this->metadata['title']) ? "" : $glue.$this->metadata['title'] ) );
            }
            if(isset( $new_metadata['keywords'] ) ){
                $this->metadata['keywords'] = $new_metadata['keywords'] . ( $replace ? "" : ( empty( $this->metadata['keywords']) ? "" : ', '.$this->metadata['keywords'] ) );
            } elseif( empty( $new_metadata['keywords']) || (strlen( $new_metadata['keywords'])<10 && strlen( $new_metadata['title'])>20 ) ) {
                $this->metadata['keywords'] = ( trim( strtolower( $new_metadata['title'] ) ) ).', '.$this->metadata['keywords'];
            }
            if(isset( $new_metadata['description'] ) ){
                $this->metadata['description'] = $new_metadata['description'] . ( $replace ? "" : ( empty( $this->metadata['description']) ? "" : '. '.$this->metadata['description'] ) );
            }
            if(isset( $new_metadata['seo_text'] ) ) $this->metadata['seo_text'] = $new_metadata['seo_text'];
            
        }
    }
    
    /**
    * Формирование сигнатуры для кеширования страницы
    * в сигнатуру входят указанные при создании
    * объекты из массивов POST, SESSION и COOKIE
    * 
    * @return string Сигнатура
    */
    private function createPageSignature( $custom_field = false ){
        global $auth;
        $signature = Host::$host . '::'.$this->requested_url;
        // добавление в сигнатуру POST - параметров
        if( !empty( $this->incacheobjects['post'] ) ){
            $array = array();
            foreach( $this->incacheobjects['post'] as $key){
                $array[$key] = Request::GetParameter( $key,METHOD_POST);
            }
            $signature .= ":p:".sha1(Convert::ToString( $array ) );
        }
        // добавление в сигнатуру Custom значениея
        if( !empty( $custom_field ) ) $signature .= ":custom:".sha1(Convert::ToString( $custom_field ) );  
  
        // добавление в сигнатуру COOKIE - параметров
        if( !empty( $this->incacheobjects['cookie'] ) ){
            $array = array();
            foreach( $this->incacheobjects['cookie'] as $key){
                $array[$key] = Cookie::GetParameter( $key);
            }
            $signature .= ":c:".sha1(Convert::ToString( $array ) );
        }
        return $signature;
    }           

    /**
    * Проверка доступа текущего пользователя к странице с указанным путем
    * @param string путь к странице (если не задан - берется путь к текущей странице)
    * @param string проверяемые права (например 'r' | 'w' | 'rw')
    * @return boolean разрешение доступа
    */
    public function checkAccess( $requested_path=null, $checkedRights=null){
        global $auth;
        if( empty( $requested_path ) ) $requested_path = $this->requested_path;
        if( empty( $checkedRights ) ) $checkedRights = 'r';
        // доступ для страницы (общий)
        $access_allow = true;
        for( $i=0;$i<strlen( $checkedRights);$i++){
            $access_allow = $access_allow && strpos( $this->page_access, $checkedRights[$i].'-')=== false ;
        }
        $page_path = "";
        // доступ для групп пользователей
        foreach( $auth->group_rights as $right){
            if(strlen( $right['path'])>strlen( $page_path) && strpos( $requested_path,$right['path'])===0){
                $page_path = $right['path'];
                for( $i=0;$i<strlen( $checkedRights);$i++){
                    $access_allow = $access_allow ? strpos( $right['rights'],$checkedRights[$i].'-')=== false  : strpos( $right['rights'],$checkedRights[$i].'+')!== false ;
                }
            }
        }
        $page_path = "";
        // индивидуальный доступ для пользователя
        foreach( $auth->user_rights as $right){
            if(strlen( $right['path'])>strlen( $page_path) && strpos( $requested_path,$right['path'])===0){
                $page_path = $right['path'];
                for( $i=0;$i<strlen( $checkedRights);$i++){
                    $access_allow = $access_allow ? strpos( $right['rights'],$checkedRights[$i].'-')=== false  : strpos( $right['rights'],$checkedRights[$i].'+')!== false ;
                }
            }
        }
        return $access_allow;
    }
    
    /**
    * Добавление элемента в меню
    * @param string заголовок пункта меню
    * @param string URL пункта меню
    * @param integer уровень меню (1 - главное,  2 - подменю, 4-меню справа)
    * @param boolean активность элемента - выбранный/текущий пункт 
    * @param string имя класса элемента 
    * @param string потомок меню
    * @param integer потомок меню
    */
    public function menuAdd( $title, $url, $level=1, $active = false , $class = false , $child = false ,$reset_active = false){
        $menulevel = $level <= 1 ? 1 : $level;
        $active_state = empty( $reset_active ) && 
                        ( $active                                                                         // принудительно установлена активность пункта
                        || ( !empty( $url) && substr( $this->requested_url,0,strlen( $url ) )==$url)          // URL совпадает с запрошенным урлом страницы
                        || ( !empty( $url) && substr( $this->real_url,0,strlen( $url ) )==$url)               // URL совпадает с реальным урлом страницы
                        || ( empty( $url) && ( empty( $this->requested_url) || empty( $this->real_url ) ) ) );    // главная страница
        if( !empty( $active_state ) ) {
            $this->menu[$menulevel-1]['active_state'] = true;
        }
        if( $url == 'service' && strstr( $this->real_url, 'ratings' ) != '' ) $active_state = false;
        if( !empty( $child ) ) {
            if( empty ( $this->menu[$menulevel][$child] ) ) $this->menu[$menulevel][$child] = array();
            array_push( $this->menu[$menulevel][$child],array('title'=>$title, 'url'=>$url, 'active'=>$active_state, 'class'=>$class, 'amount'=>$amount, 'external_link' =>  !empty( $external_link) ? true : false ) );
        } else $this->menu[$menulevel][] = array('title'=>$title, 'url'=>$url, 'active'=>$active_state, 'class'=>$class);
        //если это верхнее меню, можем указать что элемент не должен быть <a>
        if( $level == 4 && !empty( $internal_link ) ) $this->menu[$menulevel][count( $this->menu[$menulevel]) - 1]['internal_link'] = true;
    }
    
    /**
    * Очистка меню
    * @param integer уровень меню для очистки. 1, 2 - соответственно 1 или 2 уровень. Если не указан или не равен этим значениям - то очищается всё меню
    */
    public function menuClear( $level=0){
        if( $level===1 || $level===2 || $level===3) unset( $this->menu[$level]);
        else unset( $this->menu);
    }
    
    /**
    * Редактирование меню
    * @param integer уровень меню для редактирования
    * @param integer индекс элемента (с нуля)
    * @param string название
    * @param string url 
    * @param boolean  
    */
    public function menuEdit( $level=0, $item, $title=null, $url=null, $active=null){
        if( !empty( $this->menu[$level][$item] ) ) {
            if( !is_null( $title ) ) $this->menu[$level][$item]['title'] = $title;     
            if( !is_null( $url ) ) $this->menu[$level][$item]['url'] = $url;     
            if( !is_null( $active ) ) $this->menu[$level][$item]['active'] = $active;     
        }
    }
    /**
    * Переопределение шаблона окружения
    * @param string шаблон окружения
    */    
    public function setPageTemplate( $template = false ){
        if( !empty( $template ) ) $this->page_template = $template;
        
    }
}
?>