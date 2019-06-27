<?php
return array(
    'projects' => array(
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
            'placeholder' => 'Название проекта'
        )
        
        ,'published' => array(
            'type' => TYPE_INTEGER,
            'min' => 1,
            'max' => 2,
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'radio',
            'values' => array(1=>'Да',2=>'Нет'),
            'label' => 'Показывать проект'
        )
        
        ,'content_short' => array(
            'type' => TYPE_STRING,
            'allow_empty' => true, 
            'allow_null' => false,
            'fieldtype' => 'textarea',
            'editor' => 'big',
            'parent_class' => 'full-width', 
            'label' => 'Аннотация',
            'tip' => 'Короткое описание, лид'
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
        
        ,'seo_keywords' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'full-width', 
            'allow_empty' => true, 
            'allow_null' => true,
            'fieldtype' => 'text',
            'label' => 'Ключевые слова',
            'placeholder' => 'Сео параметр - ключевые слова страницы'
        )
        
        ,'_hr_1_' => array(
            'fieldtype' => 'hr'
        )

        ,'photogallery' => array(
            'url' => '/manage/projects/photos/', 
            'limit' => 20,
            'parent_class' => 'full-width', 
            'fieldtype' => 'photos',
            'switcher' => 'true',
            'label' => 'Фотографии проекта'
        )

        
    )
);
?>