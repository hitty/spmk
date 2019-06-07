<?php
return array(
    'settings' => array(
        'admin_emails' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'half-width', 
            'allow_empty' => true, 
            'allow_null' => true,
            'fieldtype' => 'text',
            'label' => "Email'ы администратора",
            'placeholder' => '',
            'tip' => 'Введите список email через точку с запятой'
        )
        
        
        ,'_hr_voting_' => array(
            'fieldtype' => 'hr',
            'label' => 'Блок «Голосование»'
        )
        ,'applications_voting' => array(
            'type' => TYPE_INTEGER,
            'allow_empty' => true, 
            'allow_null' => true,
            'parent_class' => 'half-width', 
            'fieldtype' => 'switcher',
            'values' => array(1=>'Вкл',2=>'Выкл'),
            'label' => 'Голосование жюри по заявкам'
        )
        ,'voting_text' => array(
            'type' => TYPE_STRING,
            'parent_class' => 'full-width', 
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'textarea',
            'editor' => 'true',
            'label' => 'Текст на странице голосования'
        )
        ,'_hr_application_' => array(
            'fieldtype' => 'hr',
            'label' => 'Блок «Заявка»'
        )
        ,'applications_send' => array(
            'type' => TYPE_INTEGER,
            'allow_empty' => true, 
            'allow_null' => true,
            'parent_class' => 'half-width', 
            'fieldtype' => 'switcher',
            'values' => array(1=>'Вкл',2=>'Выкл'),
            'label' => 'Подать заявку'
        )
        ,'application_text' => array(
            'type' => TYPE_STRING,
            'parent_class' => 'full-width', 
            'allow_empty' => true, 
            'allow_null' => true,
            'fieldtype' => 'textarea',
            'editor' => 'true',
            'label' => 'Текст-заглушка на страницу «Заявки»'
        )
        
        ,'_hr_nominees_' => array(
            'fieldtype' => 'hr',
            'label' => 'Блок «Номинации»'
        )
        
        ,'nominees_show' => array(
            'type' => TYPE_INTEGER,
            'allow_empty' => true, 
            'allow_null' => true,
            'parent_class' => 'half-width', 
            'fieldtype' => 'switcher',
            'values' => array(1=>'Вкл',2=>'Выкл'),
            'label' => 'Отображать номинантов'
        )
        ,'nominees_text' => array(
            'type' => TYPE_STRING,
            'parent_class' => 'full-width', 
            'allow_empty' => true, 
            'allow_null' => true,
            'fieldtype' => 'textarea',
            'editor' => 'true',
            'label' => 'Заглушка на страницу Номинантов'
        )
        
        ,'_hr_mainpage_' => array(
            'fieldtype' => 'hr',
            'label' => 'Блок «Главная страница»'
        )
        ,'photogallery_mainpage' => array(
            'allow_empty' => true, 
            'allow_null' => true,
            'url' => '/projects/settings/photos/', 
            'limit' => 1,
            'min_width' => 1700,
            'parent_class' => 'half-width one-photo', 
            'fieldtype' => 'photos',
            'label' => 'Заглавная фотография',
            'tip' => 'Минимальная ширина фотографии 1700px'
        )
        ,'cite_mainpage' => array(
            'type' => TYPE_STRING,
            'parent_class' => 'full-width', 
            'allow_empty' => true, 
            'allow_null' => true,
            'fieldtype' => 'textarea',
            'editor' => 'true',
            'label' => 'Блок цитаты на главной странице'
        )
        
        
        ,'_hr_about_' => array(
            'fieldtype' => 'hr',
            'label' => 'Блок «О премии»'
        )
        
        ,'about_regulations' => array(
            'type' => TYPE_STRING,
            'parent_class' => 'full-width', 
            'allow_empty' => true, 
            'allow_null' => true,
            'fieldtype' => 'textarea',
            'editor' => 'true',
            'label' => 'О проекте - «Положение о Премии»'
        )
        ,'about_order' => array(
            'type' => TYPE_STRING,
            'parent_class' => 'full-width', 
            'allow_empty' => true, 
            'allow_null' => true,
            'fieldtype' => 'textarea',
            'editor' => 'true',
            'label' => 'О проекте - «Порядок рассмотрения заявок»'
        )
        ,'about_criteria' => array(
            'type' => TYPE_STRING,
            'parent_class' => 'full-width', 
            'allow_empty' => true, 
            'allow_null' => true,
            'fieldtype' => 'textarea',
            'editor' => 'true',
            'label' => 'О проекте - «Критерии Премии»'
        )
        
    )    
);
?>