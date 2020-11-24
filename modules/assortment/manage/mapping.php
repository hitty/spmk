<?php
return [
    'assortment' => [
         'id' => [
            'type' => TYPE_INTEGER,
            'nodisplay' => true,
            'allow_empty' => true, 
            'allow_null' => true
         ]
        ,'title' => [
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'full-width', 
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'text',
            'label' => 'Название',
            'placeholder' => 'Название '
        ]
        
        ,'published' => [
            'type' => TYPE_INTEGER,
            'min' => 1,
            'max' => 2,
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'radio',
            'values' => [1=>'Да',2=>'Нет'],
            'label' => 'Показывать '
        ]
        ,'id_type' => [
            'type' => TYPE_INTEGER,
            'parent_class' => 'half-width inline', 
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'select',
            'values' => [0=>'- выберите тип -'],
            'label' => 'Тип объекта'
        ]
        ,'content_short' => [
            'type' => TYPE_STRING,
            'allow_empty' => true, 
            'allow_null' => false,
            'fieldtype' => 'textarea',
            'editor' => 'big',
            'parent_class' => 'full-width', 
            'label' => 'Аннотация',
        ] 
        
        ,'properties' => [
            'type' => TYPE_STRING,
            'allow_empty' => true, 
            'allow_null' => false,
            'fieldtype' => 'textarea',
            'editor' => 'big',
            'parent_class' => 'full-width', 
            'label' => 'Свойства',
        ] 
        
        ,'content' => [
            'type' => TYPE_STRING,
            'allow_empty' => true, 
            'allow_null' => false,
            'fieldtype' => 'textarea',
            'parent_class' => 'full-width', 
            'editor' => 'big',
            'label' => 'Описание',
            'tip' => ''
        ] 
        
        ,'_hr_seo_' => [
            'fieldtype' => 'hr'
        ]

        ,'seo_title' => [
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'full-width', 
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'text',
            'label' => 'Сео заголовок страницы'
        ]
        ,'seo_description' => [
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'full-width', 
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'text',
            'label' => 'Description',
            'placeholder' => 'Сео параметр - описание страницы'
        ]
        
        ,'seo_title' => [
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'full-width', 
            'allow_empty' => true, 
            'allow_null' => true,
            'fieldtype' => 'text',
            'label' => 'Заголовок',
            'placeholder' => 'Сео параметр - заголовок страницы'
        ]
        
        ,'seo_keywords' => [
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'full-width', 
            'allow_empty' => true, 
            'allow_null' => true,
            'fieldtype' => 'text',
            'label' => 'Ключевые слова',
            'placeholder' => 'Сео параметр - ключевые слова страницы'
        ]
        
        ,'_hr_1_' => [
            'fieldtype' => 'hr'
        ]

        ,'photogallery' => [
            'url' => '/manage/assortment/photos/', 
            'limit' => 20,
            'parent_class' => 'full-width', 
            'fieldtype' => 'photos',
            'switcher' => 'true',
            'label' => 'Фотографии'
        ]
    ],
    'assortment_types' => [
         'id' => [
            'type' => TYPE_INTEGER,
            'nodisplay' => true,
            'allow_empty' => true, 
            'allow_null' => true
         ]
        ,'title' => [
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'full-width', 
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'text',
            'label' => 'Название',
            'placeholder' => 'Название типа'
        ]
        ,'text' => [
            'type' => TYPE_STRING,
            'allow_empty' => true, 
            'allow_null' => false,
            'fieldtype' => 'textarea',
            'parent_class' => 'full-width', 
            'editor' => 'big',
            'label' => 'Описание',
            'tip' => ''
        ]
        
    ]
];
?>