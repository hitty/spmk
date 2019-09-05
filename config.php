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
        , 'uslugi' => DEBUG_MODE ? 6 : 9 // новости
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
    
    'recaptcha' => [
        'public' => '6LdSwLIUAAAAALhUgUwk8mRi-81fSVfFq7SEsqiE',
        'secret' => '6LdSwLIUAAAAACKTAXo5kzcN0P5tAmuQlQBZM7Lq'
    ]
    ,'forms' => [   // данные форм запроса
        'raschet' => [   //получить расчет
            'form_title' => 'Получить расчет',
            'form_text' => 'Быстрый расчет стоимости проекта при наличии технической документации (АР, КМ, КМД)',
            'success_title' => 'Спасибо!<br/> Запрос отправлен.',
            'success_text' => 'Пожалуйста, не дублируйте свой запрос, наши специалисты с вами обязательно свяжутся.',
            'template' => 'templates/includes/form.raschet.html',
            'mailer_title' => 'Получить расчет'
        ]
        ,
        'application' => [  //оставить заявку
            'form_title' => 'Оставить заявку',
            'form_text' => 'На консультацию наших специалистов',
            'success_title' => 'Спасибо!<br/> Запрос отправлен.',
            'success_text' => 'Пожалуйста, не дублируйте свой запрос, наши специалисты с вами обязательно свяжутся.',
            'template' => 'templates/includes/form.html',
            'mailer_title' => 'Оставить заявку'
        ]
        ,
        'have_questions' => [  //остались вопросы
            'form_title' => 'Остались вопросы?',
            'form_text' => '',
            'success_title' => 'Спасибо!<br/> Запрос отправлен.',
            'success_text' => 'Пожалуйста, не дублируйте свой запрос, наши специалисты с вами обязательно свяжутся.',
            'template' => 'templates/includes/form.question.html',
            'mailer_title' => 'Новый вопрос'
        ]
        ,
        'ask_question' => [  //задать вопрос
            'form_title' => 'Задать вопрос',
            'form_text' => 'Не нашли ответов на сайте? Спросите нас тут, возможно, ответ уже есть.',
            'success_title' => 'Спасибо!<br/> Запрос отправлен.',
            'success_text' => 'Пожалуйста, не дублируйте свой запрос, наши специалисты с вами обязательно свяжутся.',
            'template' => 'templates/includes/form.question.html',
            'mailer_title' => 'Задать вопрос'
        ]
        ,
        'excursion' => [  // записаться на экскурсию
            'form_title' => 'Записаться на экскурсию',
            'form_text' => 'Экскурсия по производственной площадке в г.Рязани проходит по предварительной записи',
            'success_title' => 'Спасибо!<br/> Запрос отправлен.',
            'success_text' => 'Пожалуйста, не дублируйте свой запрос, наши специалисты с вами обязательно свяжутся.',
            'template' => 'templates/includes/form.excursion.html',
            'mailer_title' => 'Записаться на экскурсию'
        ]
        ,
        'call' => [  // заказать обратный звонок
            'form_title' => 'Заказ обратного звонка',
            'form_text' => 'Укажите свои данные - мы всегда перезваниваем!',
            'success_title' => 'Спасибо!<br/> Запрос отправлен.',
            'success_text' => 'Пожалуйста, не дублируйте свой запрос, наши специалисты с вами обязательно свяжутся.',
            'template' => 'templates/includes/form.call.html',
            'mailer_title' => 'Заказать звонок'
        ]
        ,
        'price' => [  // запросить прайс
            'form_title' => 'Получить прайс',
            'form_text' => 'Запрос прайс-листа на продукцию ГК «СПМК» на выбор',
            'success_title' => 'Спасибо!<br/> Запрос отправлен.',
            'success_text' => 'Пожалуйста, не дублируйте свой запрос, наши специалисты с вами обязательно свяжутся.',
            'template' => 'templates/includes/form.price.html',
            'mailer_title' => 'Запросить прайс',
            'production_list' => [
                'Металлоконструкции', 
                'Винтовые сваи',
                'Кованные изделия'
            ]
        ]
        ,
        'postavschikam' => [  // поставщикам
            'form_title' => '',
            'form_text' => '',
            'success_text' => 'Спасибо!<br/> Запрос отправлен. Пожалуйста, не дублируйте свой запрос, наши специалисты с вами обязательно свяжутся.',
            'template' => 'templates/includes/form.html',
            'mailer_title' => ''
        ]
        ,
        'tendery' => [  // тендеры
            'form_title' => '',
            'form_text' => '',
            'success_title' => 'Спасибо!<br/> Запрос отправлен.',
            'success_text' => 'Пожалуйста, не дублируйте свой запрос, наши специалисты с вами обязательно свяжутся.',
            'template' => 'templates/includes/form.html',
            'mailer_title' => ''
        ]
        ,
        'vacancies' => [  // вакансии
            'form_title' => 'Отправить резюме',
            'form_text' => '',
            'success_title' => 'Спасибо!<br/> Запрос отправлен.',
            'success_text' => 'Пожалуйста, не дублируйте свой запрос, наши специалисты с вами обязательно свяжутся.',
            'template' => 'modules/vacancies/templates/form.html',
            'mailer_title' => 'Резюме'
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

        'uslugi' => 'uslugi',
        'uslugi_photos' => 'uslugi_photos',

        'objects' => 'objects',
        'objects_photos' => 'objects_photos',

        'objects_types' => 'objects_types',
        'objects_types_photos' => 'objects_types_photos',

        'tendery' => 'tendery',
        'tendery_photos' => 'tendery_photos',
        
        'assortment' => 'assortment',
        'assortment_photos' => 'assortment_photos',

        'assortment_types' => 'assortment_types',
        'assortment_types_photos' => 'assortment_types_photos',

        'production' => 'production',
        'production_photos' => 'production_photos',

        'news' => 'news',
        'news_categories' => 'news_categories',
        'news_photos' => 'news_photos',

        'vacancies' => 'vacancies',
        
        'static_pages' => 'static_pages',

    )
);
?>
