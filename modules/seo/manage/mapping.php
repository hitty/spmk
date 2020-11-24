<?php
return array(
    'seo' => array(
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
            'allow_null' => true,
            'parent_class' => 'inline',
            'fieldtype' => 'text',
            'label' => 'URL страницы',
            'placeholder' => 'URL страницы'
        )
        ,'pretty_url' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'allow_empty' => true, 
            'allow_null' => false,
            'parent_class' => 'inline',
            'fieldtype' => 'text',
            'label' => 'Подменный URL',
            'placeholder' => 'Подменный (красивый) URL страницы'
        )
        
        ,'only_params' => array(
            'type' => TYPE_INTEGER,
            'min' => 1,
            'max' => 2,
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'switcher',
            'parent_class' => 'full-width', 
            'values' => array(1=>'Да',2=>'Нет'),
            'label' => 'Только СЕО-параметры'
        )
       
        ,'title' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'allow_empty' => true, 
            'allow_null' => false,
            'fieldtype' => 'text',
            'parent_class' => 'full-width',
            'label' => 'TITLE',
            'placeholder' => 'Заголовок TITLE для страницы'
        )
        ,'h1_title' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'allow_empty' => true, 
            'allow_null' => false,
            'parent_class' => 'full-width',
            'fieldtype' => 'text',
            'label' => 'H1',
            'placeholder' => 'Заголовок H1 для страницы'
        )
        ,'description' => array(
            'type' => TYPE_STRING,
            'allow_empty' => true, 
            'allow_null' => false,
            'parent_class' => 'full-width',
            'fieldtype' => 'textarea',
            'label' => 'DESCRIPTION',
            'placeholder' => 'Описание для страницы'
        )
        ,'keywords' => array(
            'type' => TYPE_STRING,
            'allow_empty' => true, 
            'allow_null' => false,
            'parent_class' => 'full-width',
            'fieldtype' => 'textarea',
            'label' => 'KEYWORDS',
            'placeholder' => 'Ключевики для страницы'
        )
        ,'seo_text' => array(
            'type' => TYPE_STRING,
            'allow_empty' => true, 
            'allow_null' => false,
            'parent_class' => 'full-width',
            'fieldtype' => 'textarea',
            'editor' => 'true',
            'label' => 'SEO текст',
            'placeholder' => 'SEO текст'
        )
    )
);
?>