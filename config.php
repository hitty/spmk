<?php
return array(
    'win_os' => substr(strtolower(php_uname('s')),0,3)=='win',
    'site' => array(                // общие настройки сайта
        'title' => '',
        'description' => '',
        'keywords' => '',
        'charset' => 'UTF-8',
        'root_path' => ROOT_PATH
    ),
    'memcache' => array(            // настройки memcache
        'host' => '127.0.0.1',       // хост сервера memcached
        'port' => 11211,            // порт сервера memcached
        'time' => array(            // настройки времени хранения различных компонентов
            'config' => 600         // время кэширования файла конфигураци
        )
        ,'enabled' => false         // состояние memcached (вкл/выкл)
    ),
    'blocks_cache_time' => array(
        'coaches_block' => 7200               // блок преподавателей
    ),  
    'string_per_page' => array(
        'users'        => 20         // пользователи
        , 'news' => DEBUG_MODE ? 6 : 9 // новости
        , 'objects' => 10             // новости
        , 'objects_types' => 10       // типы объектов
        , 'assortment' => 10           // новости
    ),  
    'mysql' => array(
        'host' => 'localhost',
        'user' =>  DEBUG_MODE ? 'root' : 'cj32843_newspmk' ,
        'pass' => DEBUG_MODE ? 'root' : 'Hitty11))' ,
        'charset' => 'utf8',
        'db' => DEBUG_MODE ? 'spmk' : 'cj32843_newspmk',
        'lc_time_names' => 'ru_RU'
    ),
    'elasticsearch' => array(      // настройки Elasticsearch
        'index' => 'spmk',       // индекс
        'tables' => [ 'news', 'objects', 'production' ],           // таблицы
    )
    
    ,'social' => array(   // данные приложений для авторизации через соцсети
        'fb' => array(
            'secret' => '73698846a8ab94af10815f6394fd6bd4',
            'app_id' => '193703541243879'
        )
        ,'vk' => array(
            'client_secret' => '5e7f739f5e7f739f5e7f739f175e1d318555e7f5e7f739f04b82871ac8090b7f0ef9c63',
            'secret' => 'V1hIYn63XAWlB35WW4wo',
            'app_id' => '6439450'
        )
        ,'twitter' => array(
            'secret' => DEBUG_MODE ? 'wQGL6hbCotgwBMeDlADd8J0ofeJpEKruhxlYWXlDv79JpAex4c' : 'dHH2NHCROTs9BKnvlJctS8o1dEORoQm28wVbqKr9XonsYJ7ToH',
            'key' => DEBUG_MODE ? 'lzfEIpebXWm9hOYDvfRiUR56T' : 'PAy9d56U3HI8sxlWYmh8wbNw6'
        )
        ,'ok' => array(
            'secret' => '8D2B534507B262A4C5CED771',
            'public' => 'CBADJLGMEBABABABA',
            'app_id' => '1265275392'
        )                          
    ),
   
    'watermark_src' => '/img/watermark.png',
    
    'img_folders' => 'img/uploads',
    'file_folders' => 'files/uploads',
    'icons_folder' => 'img/icons',
    'video_folder' => 'img/videos',
    'emails'=> array(                       // email сотрудников
        'info' => 'info@' . getenv("HTTP_HOST")
    ),
    'months' => array (
        1=>'январь', 2=>'февраль', 3=>'март', 4=>'апрель', 5=>'май', 6=>'июнь', 7=>'июль', 8=>'август', 9=>'сентябрь', 10=>'октябрь', 11=>'ноябрь', 12=>'декабрь'
    ),
    'months_short' => array (
        1=>'янв', 2=>'фев', 3=>'мар', 4=>'апр', 5=>'май', 6=>'июн', 7=>'июл', 8=>'авг', 9=>'сен', 10=>'окт', 11=>'ноя', 12=>'дек'
    ),
    'months_genitive' => array (
        1=>'января', 2=>'февраля', 3=>'марта', 4=>'апреля', 5=>'мая', 6=>'июня', 7=>'июля', 8=>'августа', 9=>'сентября', 10=>'октября', 11=>'ноября', 12=>'декабря'
    ),
    'months_prepositional' => array (
        1=>'январе', 2=>'феврале', 3=>'марте', 4=>'апреле', 5=>'мае', 6=>'июне', 7=>'июле', 8=>'августе', 9=>'сентябре', 10=>'октябре', 11=>'ноябре', 12=>'декабре'
    ),   
    
    'sys_tables' => array(             // таблицы БД
        'users' => 'users',
        'users_photos' => 'users_photos',
        'users_groups' => 'common_groups',
        'users_restore' => 'users_restore',
        'common_pages' => 'common_pages',
        'common_pages_seo' => 'common_pages_seo',
        'common_pages_map' => 'common_pages_map',
        
        'applications' => 'applications',
        'applications_spam' => 'applications_spam',
        
        'news' => 'news',
        'news_photos' => 'news_photos',
        'news_categories' => 'news_categories',

        'objects' => 'objects',
        'objects_photos' => 'objects_photos',

        'objects_types' => 'objects_types',
        'objects_types_photos' => 'objects_types_photos',

        'tendery' => 'tendery',
        'tendery_photos' => 'tendery_photos',
        
        'assortment' => 'assortment',
        'assortment_photos' => 'assortment_photos',

        'production' => 'production',
        'production_photos' => 'production_photos',

        'news' => 'news',
        'news_categories' => 'news_categories',
        'news_photos' => 'news_photos',

        'nominees' => 'nominees',
        'nominees_photos' => 'nominees_photos',
        'nominees_categories' => 'nominees_categories',
        'nominees_files' => 'nominees_files',
        'nominees_videos' => 'nominees_videos',
        'nominees_years' => 'nominees_years',
        'nominees_voting' => 'nominees_voting',

        'media' => 'media',
        'media_photos' => 'media_photos',
        'media_videos' => 'media_videos'

    )
);
?>
