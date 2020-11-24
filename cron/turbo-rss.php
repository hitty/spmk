#!/usr/bin/php
<?php
$overall_time_counter = microtime(true);
// переход в корневую папку сайта
define('DEBUG', !empty($_SERVER['SCRIPT_NAME']) && preg_match('/.*\.int/msiU', $_SERVER['SCRIPT_NAME']) ? true : false);

$root = DEBUG ? realpath("..") : realpath('/home/c/cj32843/spmk/public_html/' );
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
require('includes/class.config.php');       // Config (конфигурация сайта)
Config::Init();
require('includes/class.host.php');         // Host (вспомогательные данные по текущему хосту)
Host::Init();
require_once('includes/class.convert.php');      // Convert, Validate (конвертирование, проверки валидности)
require_once('includes/class.db.mysqli.php');    // mysqli_db (база данных)
require_once('includes/class.sitemap.php');       // подключение класса генератора xml   
require_once('includes/class.email.php');
require_once('includes/class.db.mysqli.common.php');
CommonDb::Init();
require_once('includes/functions.php');

$db = new mysqli_db(Config::$values['mysql']['host'], Config::$values['mysql']['user'], Config::$values['mysql']['pass']);
$db->query("set names ".Config::$values['mysql']['charset']);
// вспомогательные таблицы
$sys_tables = Config::$sys_tables; 
$db->select_db( Config::$values['mysql']['db'] );
$GLOBALS['db']=$db;
$url = 'https://spmk.group';
$links_per_query = 45000;
// вспомогательные таблицы модуля
$sys_tables = Config::$sys_tables;
$base_memory_usage = memory_get_usage();
memoryUsage(memory_get_usage(), $base_memory_usage);//логи для почты
$log = array();


echo '<?xml version="1.0" encoding="UTF-8"?>
<rss xmlns:yandex="http://news.yandex.ru"
     xmlns:media="http://search.yahoo.com/mrss/"
     xmlns:turbo="http://turbo.yandex.ru"
     version="2.0">
    <channel>
        <!-- Информация о сайте-источнике -->
        <title>СПМК - завод металлоконструкций</title>
        <link>https://spmk.group/</link>
        <description>Новости металлоконструкций</description>
        <language>ru</language>
        <turbo:analytics></turbo:analytics>
        <turbo:adNetwork></turbo:adNetwork>';




$objects_list = CommonDb::getList( 'objects', false, '', 'datetime DESC', 'id' );
foreach( $objects_list as $pl => $objects_item ) {
     echo '<item turbo="true">
            <link>' . $url . '/objekty/' . $objects_item['chpu_title'] . '/</link>
            <turbo:source></turbo:source>
            <turbo:topic></turbo:topic>
            <pubDate>' . $objects_item['datetime'] . '</pubDate>
            <author>СПМК</author>
            <metrics>
                <yandex schema_identifier="Идентификатор">
                    <breadcrumblist>
                        <breadcrumb url="https://spmk.group/" text="Домашняя"/>
                        <breadcrumb url="https://spmk.group/objekty/" text="Наши объекты"/>
                        <breadcrumb url="https://spmk.group/objekty/' . $objects_item['chpu_title'] . '." text="Пример страницы"/>
                    </breadcrumblist>
                </yandex>
            </metrics>
            <yandex:related></yandex:related>
            <turbo:content>
                <![CDATA[
                    ' . $objects_item['content'] . '
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
echo '</channel>'.PHP_EOL;
echo '</rss>'.PHP_EOL;

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
?>
