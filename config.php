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
        'nominees'     => 20              // база знакний
        , 'users'        => 20              // пользователи
        , 'news' => 10                       // новости
        , 'partners' => 10                       // новости
        , 'media' => 20                // фото-видео
    ),  
    'mysql' => array(
        'host' => 'localhost',
        'user' =>  DEBUG_MODE ? 'root' : 'arerusru_awards' ,
        'pass' => DEBUG_MODE ? 'root' : 'J7gj1tDTtIYIe01vIgpg' ,
        'charset' => 'utf8',
        'db' => DEBUG_MODE ? 'spmk' : 'arerusru_awards',
        'lc_time_names' => 'ru_RU'
    ),
    'elasticsearch' => array(      // настройки Elasticsearch
        'index' => 'awards',       // индекс
        'tables' => [ 'news', 'nominees', 'users', 'media' ],           // таблицы
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
        
        'news' => 'news',
        'news_photos' => 'news_photos',
        'news_categories' => 'news_categories',

        'partners' => 'partners',
        'partners_photos' => 'partners_photos',
        'partners_categories' => 'partners_categories',

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
