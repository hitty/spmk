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
    
    ,'forms' => [   // данные форм запроса
        'raschet' => [   //получить расчет
            'form_title' => 'Получить расчет',
            'success_text' => 'Спасибо за проявленный интерес! Проект передан на расчет. Ответ поступит на электронную почту. ',
            'template' => 'templates/includes/form.html',
            'mailer_title' => 'Новая заявка на расчет'
        ]
        ,
        'application' => [  //оставить заявку
            'form_title' => 'Оставить заявку',
            'success_text' => 'Спасибо за проявленный интерес! Ваша заявка обрабатывается. Ожидайте звонка менеджера. ',
            'template' => 'templates/includes/form.html',
            'mailer_title' => 'Новая заявка'
        ]
        ,
        'have_questions' => [  //остались вопросы
            'form_title' => 'Остались вопросы?',
            'success_text' => 'Спасибо! Мы обязательно ответим. Пожалуйста, ожидайте. ',
            'template' => 'templates/includes/form.question.html',
            'mailer_title' => 'Новый вопрос'
        ]
        ,
        'ask_question' => [  //задать вопрос
            'form_title' => 'Задать вопрос',
            'success_text' => 'Спасибо! Мы обязательно ответим. Пожалуйста, ожидайте. ',
            'template' => 'templates/includes/form.question.html',
            'mailer_title' => 'Новый вопрос'
        ]
        ,
        'excursion' => [  // записаться на экскурсию
            'form_title' => 'Записаться на экскурсию',
            'success_text' => 'Спасибо за проявленный интерес! Ваша заявка обрабатывается. Ожидайте звонка менеджера. ',
            'template' => 'templates/includes/form.excursion.html',
            'mailer_title' => 'Запись на экскурсию'
        ]
        ,
        'call' => [  // заказать обратный звонок
            'form_title' => 'Заказ обратного звонка',
            'success_text' => 'Данные получены. Ожидайте звонка, пожалуйста. ',
            'template' => 'templates/includes/form.call.html',
            'mailer_title' => ''
        ]
        ,
        'price' => [  // запросить прайс
            'form_title' => 'Запросить прайс',
            'success_text' => 'Спасибо за проявленный интерес! Ваша заявка обрабатывается. Ожидайте звонка менеджера. ',
            'template' => 'templates/includes/form.html',
            'mailer_title' => 'Запрос прайса'
        ]
        ,
        'postavschikam' => [  // поставщикам
            'form_title' => '',
            'success_text' => 'Ваше предложение передано на предварительную обработку.<br/>С вами свяжутся в случае вопросов.',
            'template' => 'templates/includes/form.html',
            'mailer_title' => ''
        ]
        ,
        'tendery' => [  // тендеры
            'form_title' => '',
            'success_text' => 'Ваше предложение передано на предварительную обработку.<br/>С вами свяжутся в случае вопросов.',
            'template' => 'templates/includes/form.html',
            'mailer_title' => ''
        ]
                                  
    ],
   
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
