<?php   

$overall_memory_usage = memory_get_peak_usage();
$overall_time_counter = microtime(true);
//DEBUG - local
define("DEBUG_MODE", (isset($_SERVER['SERVER_ADDR']) && isset($_SERVER['REMOTE_ADDR']) && $_SERVER['SERVER_ADDR']==$_SERVER['REMOTE_ADDR'] && $_SERVER['SERVER_ADDR']=="127.0.0.1") || (!empty($_SERVER['HTTP_HOST']) && substr($_SERVER['HTTP_HOST'], -4) == '.int'));

//рутовый путь
define( "ROOT_PATH", str_replace( "\\", '/', __DIR__ ) );
if(DEBUG_MODE){
    // абсолютно все ошибки логируются и показываются в общем порядке, время исполнения скрипта увеличено
    error_reporting(E_ALL);
    set_time_limit(45); 
} else {
    // все ошибки только логируются, на экран не выводятся, время выполнения скрипта стандартное
    error_reporting(0);
    set_time_limit(10);
    // подключение обработчиков ошибок
    include('includes/lib.errorhandler.php');
    set_error_handler('newErrorHandler');
    register_shutdown_function('newFatalCatcher');
}

// абсолютно все ошибки логируются и показываются в общем порядке, время исполнения скрипта увеличено
error_reporting( E_ALL );
set_time_limit( 45 ); 



// подключение классов ядра
require('includes/class.config.php');               // Config (конфигурация сайта)
Config::Init();

require('includes/class.convert.php');              // Convert, Validate (конвертирование, проверки валидности)
require('includes/class.storage.php');              // Session, Cookie, Responce, Request
Session::Init();
Request::Init();
Cookie::Init();
Time::Init();
require('includes/class.host.php');                 // Host (вспомогательные данные по текущему хосту)
Host::Init();
$host = new ReflectionClass( new Host );            //получение всех свойств класса host
Response::SetArray( 'host', $host->getStaticProperties() );

require('includes/class.memcache.php');             // MCache (memcached, кеширование в памяти)
require('includes/class.db.mysqli.php');            // mysqli_db (база данных)
require('includes/class.db.mysqli.common.php');     // стандартные запросы к базе
CommonDb::Init();
require('includes/class.auth.php');                 // Auth (авторизация)
require('includes/class.template.php');             // Template (шаблонизатор), FileCache (файловое кеширование)
require('includes/class.filedata.php');             // FileData (работа с файловым хранилищем рабочих данных)
require('includes/class.page.php');                 // Page
require('includes/functions.php');                  // набор функций
require_once('includes/class.photos.php');          // обработка фотографий

require_once('includes/class.paginator.php');         // управления блоками страниц 
$memcache = new MCache(Config::$values['memcache']['host'], Config::$values['memcache']['port']);
$db = new mysqli_db(Config::$values['mysql']['host'], Config::$values['mysql']['user'], Config::$values['mysql']['pass']);
$db->select_db( Config::$values['mysql']['db'] );
$db->query("set names ".Config::$values['mysql']['charset']);
$db->query("SET lc_time_names = '".Config::$values['mysql']['lc_time_names']."';");
$ip = Host::getUserIp( true );
Response::SetString( 'ip', $ip );
FileCache::Init( 'filecache' );
$auth = new Auth();
// проверка авторизации
$_authorized = $auth->checkAuth();
Response::SetBoolean( 'authorized', $auth->authorized );

$requested_page = new Page( Host::getRequestedUri() );     

// определение режима ajax-запроса
$ajax_mode = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || !empty($internal_mode);


if( empty( $ajax_mode ) ) {
    //подключение стилей и js-скриптов
    $GLOBALS['js_set'][] = '/js/jquery.min.js';
    $GLOBALS['css_set'][] = '/js/datetimepicker/jquery.datetimepicker.css';
    $GLOBALS['js_set'][] = '/js/datetimepicker/jquery.datetimepicker.js';
    $GLOBALS['js_set'][] = '/js/inputmask/inputmask.min.js';
    $GLOBALS['js_set'][] = '/js/form.validate.js';
    $GLOBALS['js_set'][] = '/js/main.js';
    $GLOBALS['js_set'][] = '/js/interface.js';
    $GLOBALS['js_set'][] = '/js/fancybox.js';
    $GLOBALS['js_set'][] = '/js/map.js';
    
    $GLOBALS['css_set'][] = '/css/variables.css';
    $GLOBALS['css_set'][] = '/css/fonts.css';
    
    if( empty( $requested_page->is_manage_page ) ) {
        $GLOBALS['css_set'][] = '/css/reset.css';
        $GLOBALS['css_set'][] = '/css/common.css';
        
        $GLOBALS['css_set'][] = '/css/controls.css';
        $GLOBALS['css_set'][] = '/css/central.css';
        $GLOBALS['css_set'][] = '/css/header.css';
        $GLOBALS['css_set'][] = '/css/content.css';
        $GLOBALS['css_set'][] = '/js/popup.window/styles.css';    
    } else {
        $GLOBALS['css_set'][] = '/manage/css/reset.css';
        $GLOBALS['css_set'][] = '/manage/css/fonts.css';
        $GLOBALS['css_set'][] = '/manage/css/common.css';
        $GLOBALS['css_set'][] = '/manage/css/content.css';
        $GLOBALS['css_set'][] = '/manage/css/popup.window.css';    
    }
    $GLOBALS['js_set'][] = '/js/popup.window/script.js';
}
Response::SetBoolean('debug',DEBUG_MODE);
$content = $requested_page->Render();

if(substr($content,0,5)=='<?xml') header('Content-Type: application/xml; charset='.Config::$values['site']['charset']);
else header('Content-Type: text/html; Charset='.Config::$values['site']['charset']);

if( !$ajax_mode ) echo $content;
else {
    header("Content-type: application/json; charset=utf-8");
    $ajax_result['ok'] = true;
    $ajax_result['html'] = $content;
    echo Convert::json_encode( $ajax_result );
    exit(0);    
}
$querylog = Convert::ArrayKeySort($db->querylog, 'time', true);
$overall_time_counter = round(microtime(true) - $overall_time_counter, 4);
$overall_memory_usage = memory_get_peak_usage() - $overall_memory_usage;
if($overall_time_counter>0.1 || (defined("DEBUG_MODE") && DEBUG_MODE)) file_put_contents('query.log', print_r($querylog,true) );
if(!empty($_GET['showtime']) || (defined("DEBUG_MODE") && DEBUG_MODE)){
    echo "\n<!--";
    printf("\nExecution time: %01.4f", $overall_time_counter);
    printf("\nAlocated memory: %d", $overall_memory_usage);
    echo "\n-->";
}
?>
