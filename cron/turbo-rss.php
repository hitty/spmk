<?php
$overall_time_counter = microtime(true);
// переход в корневую папку сайта
define('DEBUG_MODE', !empty($_SERVER['DOCUMENT_ROOT']) && preg_match('/.*\.int/msiU', $_SERVER['DOCUMENT_ROOT']) ? true : false);

$root = DEBUG_MODE ? realpath("..") : realpath('/home/c/cj32843/spmk/public_html/');
if(defined("PHP_OS")) $os = PHP_OS; else $os = php_uname();
if(strtolower(substr( $os, 0, 3 ) ) == "win" )  $root = str_replace( "\\", '/', $root );
define( "ROOT_PATH", $root );
chdir(ROOT_PATH);

mb_internal_encoding('UTF-8');
setlocale(LC_ALL, 'ru_RU.UTF-8');
mb_regex_encoding('UTF-8');

//запись всех ошибок в лог
$error_log = ROOT_PATH.'/cron/error.log';
$test_performance = ROOT_PATH.'/cron/test_performance.log';
file_put_contents($error_log,'');
file_put_contents($test_performance,'');
ini_set('error_log', $error_log);
ini_set('log_errors', 'On');

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
$host = new ReflectionClass(new Host);            //получение всех свойств класса host
Response::SetArray('host', $host->getStaticProperties());

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
$db->select_db(Config::$values['mysql']['db']);
$db->query("set names ".Config::$values['mysql']['charset']);
$db->query("SET lc_time_names = '" . Config::$values['mysql']['lc_time_names'] . "';");
$ip = Host::getUserIp(true);
Response::SetString('ip', $ip);
FileCache::Init('filecache');
$auth = new Auth();
// проверка авторизации
$_authorized = $auth->checkAuth();
Response::SetBoolean('authorized', $auth->authorized);

$url = 'https://spmk.group';
$links_per_query = 45000;
// вспомогательные таблицы модуля
$sys_tables = Config::$sys_tables;
$base_memory_usage = memory_get_usage();
$log = array();

$text = '';
$text .= '<rss xmlns:yandex="http://news.yandex.ru" xmlns:media="http://search.yahoo.com/mrss/" xmlns:turbo="http://turbo.yandex.ru" version="2.0">
    <channel>
        <!-- Информация о сайте-источнике -->
        <title>СПМК - завод металлоконструкций</title>
        <link>https://spmk.group/</link>
        <description>Новости металлоконструкций</description>
        <language>ru</language>';
/*
 * Продукция
 */
$assortment_list = CommonDb::getList('assortment', false, '', 'datetime DESC', 'id');
foreach ($assortment_list as $pl => $assortment_item) {
    Response::SetArray('item', $assortment_item);
    $photos = Photos::getList('assortment', $assortment_item['id']);
    Response::SetArray('photos', $photos);
    $eml_tpl = new Template('item.turbo.html', 'modules/assortment/');
    $html = $eml_tpl->Processing();

    $text .= '
           <item turbo="true">
            <title>' . $assortment_item['title'] . '/</title>
            <link>' . $url . '/assortment/' . $assortment_item['chpu_title'] . '/</link>
            <pubDate>' . $assortment_item['datetime'] . '</pubDate>
            <author>СПМК</author>
            <metrics>
                <yandex schema_identifier="54690451">
                    <breadcrumblist>
                        <breadcrumb url="https://spmk.group/" text="Главная страница"/>
                        <breadcrumb url="https://spmk.group/assortment/" text="Наша продукция"/>
                        <breadcrumb url="https://spmk.group/assortment/' . $assortment_item['chpu_title'] . '/" text="' . $assortment_item['title'] . '"/>
                    </breadcrumblist>
                </yandex>
            </metrics>
            <yandex:related></yandex:related>
            <turbo:content>
                <![CDATA[
                    ' . $html . '
                ]]>
            </turbo:content>
        </item>';
}
/*
 * Объекты
 */
$objects_list = CommonDb::getList( 'objects', false, '', 'datetime DESC', 'id' );
foreach( $objects_list as $pl => $objects_item ) {
    Response::SetArray('item', $objects_item);
    $photos = Photos::getList('objects', $objects_item['id']);
    Response::SetArray('photos', $photos);
    $eml_tpl = new Template('item.turbo.html', 'modules/objects/');
    $html = $eml_tpl->Processing();

    $text .= '
           <item turbo="true">
            <title>' . $objects_item['title'] . '/</title>
            <link>' . $url . '/objekty/' . $objects_item['chpu_title'] . '/</link>
            <pubDate>' . $objects_item['datetime'] . '</pubDate>
            <author>СПМК</author>
            <metrics>
                <yandex schema_identifier="54690451">
                    <breadcrumblist>
                        <breadcrumb url="https://spmk.group/" text="Главная страница"/>
                        <breadcrumb url="https://spmk.group/objekty/" text="Наши объекты"/>
                        <breadcrumb url="https://spmk.group/objekty/' . $objects_item['chpu_title'] . '/" text="' . $objects_item['title'] . '"/>
                    </breadcrumblist>
                </yandex>
            </metrics>
            <yandex:related></yandex:related>
            <turbo:content>
                <![CDATA[
                    ' . $html . '
                ]]>
            </turbo:content>
        </item>';
}
/*
$sitemap->add_sitemap_url( $url . '/uslugi/',       '2019-09-04T00:00:00+04:00', 'monthly', '0.4' );
$uslugi_list = CommonDb::getList( 'uslugi', false, '', 'datetime DESC', 'id' );
foreach( $uslugi_list as $pl => $uslugi_item ) {
    $sitemap->add_sitemap_url( 
        $url . '/uslugi/' . $uslugi_item['chpu_title'] . '/', 
        $uslugi_item['datetime'],
        'monthly', 
        '0.4' 
    );
}

$sitemap->add_sitemap_url( $url . '/news/',       '2020-07-04T00:00:00+04:00', 'monthly', '0.4' );
$uslugi_list = CommonDb::getList( 'news', false, '', 'date DESC', 'id' );
foreach( $uslugi_list as $pl => $uslugi_item ) {
    $sitemap->add_sitemap_url( 
        $url . '/news/' . $uslugi_item['category_chpu_title'] . '/' . $uslugi_item['chpu_title'] . '/', 
        $uslugi_item['date'],
        'monthly', 
        '0.4' 
    );
}
*/
$text .= '</channel>' . PHP_EOL;
$text .= '</rss>' . PHP_EOL;

function getLastItem($table, $date_field, $where=''){
    global $db, $sys_tables;
    $item =  $db->fetch("SELECT 
                            DATE_FORMAT(MAX(".$date_field."),'%Y-%m-%dT%H:%i:%s+04:00') as last_change, 
                            DATEDIFF( CURDATE( ), MAX(".$date_field.")) as date_diff
                       FROM ".$sys_tables[$table]
                       .(!empty($where)?" WHERE ".$where:" ")
    );
    
    if(!empty($item['last_change'])) return $item;
    else return $db->fetch("SELECT 
                                        DATE_FORMAT(CURDATE() - INTERVAL 370 DAY,'%Y-%m-%dT%H:%i:%s+04:00') as last_change, 
                                        370 as date_diff");
    
}


echo str_replace('<head/>', '', $text);
?>
