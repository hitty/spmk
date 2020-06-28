<?php
return array(
    'tendery' => array(
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
            'label' => 'Название',
            'placeholder' => 'Название '
        )
        ,'date_start' => array(
            'type' => TYPE_STRING,
            'allow_empty' => true, 
            'allow_null' => true,
            'parent_class' => 'half-width inline', 
            'fieldtype' => 'datetime',
            'values' => '',
            'label' => 'Дата размещения'
        )
        ,'date_end' => array(
            'type' => TYPE_STRING,
            'allow_empty' => true, 
            'allow_null' => true,
            'parent_class' => 'half-width inline', 
            'fieldtype' => 'datetime',
            'values' => '',
            'label' => 'Актуален до'
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
        
        ,'_hr_seo_' => array(
            'fieldtype' => 'hr'
        )

        ,'seo_h1' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'full-width', 
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'text',
            'label' => 'H1',
            'placeholder' => 'H1 заголовок в карточке'
        )
        ,'seo_description' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'full-width', 
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'text',
            'label' => 'Description',
            'placeholder' => 'Сео параметр - описание страницы'
        )
        
        ,'seo_title' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'full-width', 
            'allow_empty' => true, 
            'allow_null' => true,
            'fieldtype' => 'text',
            'label' => 'Заголовок',
            'placeholder' => 'Сео параметр - заголовок страницы'
        )
        
        ,'photogallery' => array(
            'url' => '/manage/tendery/photos/', 
            'limit' => 20,
            'parent_class' => 'full-width', 
            'fieldtype' => 'photos',
            'switcher' => 'true',
            'label' => 'Фотографии'
        )

        
    )
);
?>