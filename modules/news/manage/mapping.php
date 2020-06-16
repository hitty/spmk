<?php
return array(
    'news' => array(
         'id' => array(
            'type' => TYPE_INTEGER,
            'nodisplay' => true,
            'allow_empty' => true, 
            'allow_null' => true
         )
        ,'title' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'full-width', 
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'text',
            'label' => 'Заголовок',
            'placeholder' => 'Заголовок '
        )
        
        ,'id_category' => array(
            'type' => TYPE_INTEGER,
            'parent_class' => 'half-width inline', 
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'select',
            'values' => array(0=>'- выберите категорию -'),
            'label' => 'Категория',
            'placeholder' => 'Категория новости '
        )
        ,'date' => array(
            'type' => TYPE_STRING,
            'allow_empty' => true, 
            'allow_null' => true,
            'parent_class' => 'half-width inline', 
            'fieldtype' => 'datetime',
            'values' => '',
            'label' => 'Дата'
        )
        ,'published' => array(
            'type' => TYPE_INTEGER,
            'min' => 1,
            'max' => 2,
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'radio',
            'values' => array(1=>'Да',2=>'Нет'),
            'label' => 'Показывать '
        )
        
        ,'content' => array(
            'type' => TYPE_STRING,
            'allow_empty' => true, 
            'allow_null' => false,
            'fieldtype' => 'textarea',
            'parent_class' => 'full-width', 
            'editor' => 'big',
            'label' => 'Описание',
            'tip' => ''
        ) 
        
        ,'_hr_1_' => array(
            'fieldtype' => 'hr'
        )

        ,'photogallery' => array(
            'url' => '/manage/news/photos/', 
            'limit' => 20,
            'parent_class' => 'full-width', 
            'fieldtype' => 'photos',
            'switcher' => 'true',
            'label' => 'Фотографии'
        )

        
    )
);
?>