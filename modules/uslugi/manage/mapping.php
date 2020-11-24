<?php
return [
    'uslugi' => [
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
            'label' => 'Заголовок',
            'placeholder' => 'Заголовок '
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
        
        ,'_hr_1_' => [
            'fieldtype' => 'hr'
        ]
        ,'seo_h1' => [
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'full-width', 
            'allow_empty' => TRUE, 
            'allow_null' => TRUE,
            'fieldtype' => 'text',
            'label' => 'Сео H1',
            'placeholder' => 'Заголовок Сео H1 '
        ]
        ,'seo_title' => [
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'full-width', 
            'allow_empty' => true, 
            'allow_null' => true,
            'fieldtype' => 'text',
            'label' => 'Сео Title'
        ]
        ,'seo_description' => [
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'full-width', 
            'allow_empty' => true, 
            'allow_null' => true,
            'fieldtype' => 'text',
            'label' => 'Сео Description' 
        ]
        ,'photogallery' => [
            'url' => '/manage/uslugi/photos/', 
            'limit' => 20,
            'parent_class' => 'full-width', 
            'fieldtype' => 'photos',
            'switcher' => 'true',
            'label' => 'Фотографии'
        ]

        
    ]
];
?>