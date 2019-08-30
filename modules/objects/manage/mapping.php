<?php
return [
    'objects' => [
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
            'placeholder' => 'Название объекта'
        ]
        
        ,'published' => [
            'type' => TYPE_INTEGER,
            'min' => 1,
            'max' => 2,
            'parent_class' => 'half-width inline', 
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'radio',
            'values' => [1=>'Да',2=>'Нет'],
            'label' => 'Показывать объект'
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
            'tip' => 'Короткое описание, лид'
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
        ,'place' => [
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'half-width inline', 
            'allow_empty' => true, 
            'allow_null' => true,
            'fieldtype' => 'text',
            'label' => 'Место',
            'placeholder' => 'Место объекта'
        ]
        ,'object_weight' => [
            'type' => TYPE_INTEGER,
            'parent_class' => 'half-width inline', 
            'allow_empty' => true, 
            'allow_null' => true,
            'fieldtype' => 'text',
            'label' => 'Вес конструкции',
            'placeholder' => 'Вес конструкции, т'
        ]
        ,'_hr_params_' => [
            'fieldtype' => 'hr'
        ]
        
        ,'proektirovanie' => array(
            'type' => TYPE_INTEGER,
            'min' => 1,
            'max' => 2,
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'switcher',
            'parent_class' => 'quater-width', 
            'values' => array(1=>'Да',2=>'Нет'),
            'label' => 'Проектирование'
        )
        ,'izgotovlenie' => array(
            'type' => TYPE_INTEGER,
            'min' => 1,
            'max' => 2,
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'switcher',
            'parent_class' => 'quater-width', 
            'values' => array(1=>'Да',2=>'Нет'),
            'label' => 'Изготовление'
        )
        ,'dostavka' => array(
            'type' => TYPE_INTEGER,
            'min' => 1,
            'max' => 2,
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'switcher',
            'parent_class' => 'quater-width', 
            'values' => array(1=>'Да',2=>'Нет'),
            'label' => 'Доставка'
        )
        ,'montazh' => array(
            'type' => TYPE_INTEGER,
            'min' => 1,
            'max' => 2,
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'switcher',
            'parent_class' => 'quater-width', 
            'values' => array(1=>'Да',2=>'Нет'),
            'label' => 'Монтаж'
        )
        ,'podkluch' => array(
            'type' => TYPE_INTEGER,
            'min' => 1,
            'max' => 2,
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'switcher',
            'parent_class' => 'quater-width', 
            'values' => array(1=>'Да',2=>'Нет'),
            'label' => 'Под ключ'
        )
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
            'label' => 'Сео заголовок страницы',
            'placeholder' => 'H1 заголовок в карточке'
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
            'url' => '/manage/objects/photos/', 
            'limit' => 20,
            'parent_class' => 'full-width', 
            'fieldtype' => 'photos',
            'switcher' => 'true',
            'label' => 'Фотографии объекта'
        ]
    ],
    'objects_types' => [
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
            'placeholder' => 'Название типа объекта'
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