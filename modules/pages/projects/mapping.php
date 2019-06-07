<?php
return array(
    'common_pages' => array(
         'id' => array(
            'type' => TYPE_INTEGER,
            'nodisplay' => true,
            'allow_empty' => true, 
            'allow_null' => true
         )
         ,'url' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'allow_empty' => true, 
            'allow_null' => false,
            'fieldtype' => 'plaintext',
            'label' => 'URL'
        )
        ,'alias' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'allow_empty' => true, 
            'allow_null' => false,
            'parent_class' => 'inline',
            'fieldtype' => 'text',
            'label' => 'Алиас',
            'placeholder' => 'Алиас страницы (используется как часть URL)'
        )
        ,'title' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'allow_empty' => false, 
            'allow_null' => false,
            'parent_class' => 'inline',
            'fieldtype' => 'text',
            'label' => 'Название',
            'placeholder' => 'Название страницы (title)'
        )

        ,'map_position' => array(
            'type' => TYPE_INTEGER,
            'allow_empty' => true,
            'allow_null' => false,
            'fieldtype' => 'select',
            'values' => array(0=>'нет родителя (страница в корне)'),
            'label' => 'Страница-родитель',
            'placeholder' => 'Страница-предок по иерархии сайта'
        )
        ,'template' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'allow_empty' => false, 
            'allow_null' => false,
            'parent_class' => 'inline',
            'fieldtype' => 'text',
            'label' => 'Шаблон страницы',
            'placeholder' => 'Путь к файлу шаблона страницы'
        )
        ,'module' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'allow_empty' => false, 
            'allow_null' => false,
            'parent_class' => 'inline',
            'fieldtype' => 'text',
            'label' => 'Модуль/скрипт',
            'placeholder' => 'Название модуля (имя папки), или адрес скрипта, подключаемого к странице'
        )
        ,'no_require_params' => array(
            'type' => TYPE_INTEGER,
            'min' => 1,
            'max' => 2,
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'switcher',
            'parent_class' => 'full-width', 
            'values' => array(1=>'разрешены',2=>'запрещены'),
            'label' => 'Параметры в странице',
            'placeholder' => 'Разрешение на передачу параметров в страницу через УРЛ'
        )
        ,'parameters' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'allow_empty' => true, 
            'allow_null' => false,
            'parent_class' => 'inline',
            'fieldtype' => 'text',
            'label' => 'Параметры для модуля',
            'placeholder' => 'Дополнительные параметры для PHP-модуля в форме строки GET-запроса'
        )
        ,'access' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'allow_empty' => true, 
            'allow_null' => false,
            'parent_class' => 'inline',
            'fieldtype' => 'text',
            'label' => 'Права доступа',
            'placeholder' => 'Набор прав доступа к странице по умолчанию'
        )
        ,'cache_time' => array(
            'type' => TYPE_INTEGER,
            'allow_empty' => true, 
            'allow_null' => false,
            'fieldtype' => 'text',
            'label' => 'Время кэширования',
            'placeholder' => 'Время статичного кэширования страницы (сек)'
        )
        ,'block_page' => array(
            'type' => TYPE_INTEGER,
            'min' => 1,
            'max' => 2,
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'radio',
            'values' => array(1=>'Блок',2=>'Страница'),
            'label' => 'Тип страницы',
            'placeholder' => 'Страница может быть вызвана по Url извне, а блок - только из шаблона'
        )
        ,'content' => array(
            'type' => TYPE_STRING,
            'allow_empty' => true, 
            'allow_null' => false,
            'parent_class' => 'full-width',
            'fieldtype' => 'textarea',
            'editor' => 'true',
            'label' => 'содержимое шаблона',
            'placeholder' => ''
        )      
    )
);
?>