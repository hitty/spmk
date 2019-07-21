<?php
return array(
    'users' => array(
         'id' => array(
            'type' => TYPE_INTEGER,
            'nodisplay' => true,
            'allow_empty' => true, 
            'allow_null' => true
         )
         
         ,'published' => array(
            'type' => TYPE_INTEGER,
            'min' => 1,
            'max' => 2,
            'allow_empty' => true, 
            'allow_null' => false,
            'fieldtype' => 'switcher',
            'parent_class' => 'full-width', 
            'values' => array(1=>'Да',2=>'Нет'),
            'label' => 'Профиль активен'
        )
        ,'_hr_photos_' => array(
            'fieldtype' => 'hr'
        )
        
        
        ,'photogallery' => array(
            'allow_empty' => true, 
            'allow_null' => true,
            'url' => '/projects/users/photos/', 
            'limit' => 1,
            'parent_class' => 'full-width one-photo', 
            'fieldtype' => 'photos',
            'label' => 'Фотографии'
        )
        ,'_hr_name_' => array(
            'fieldtype' => 'hr'
        )
        
        
        ,'name' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'half-width inline', 
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'text',
            'label' => 'Имя',
            'placeholder' => 'Введите имя'
        )
        ,'lastname' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'half-width inline', 
            'allow_empty' => true, 
            'allow_null' => true,
            'fieldtype' => 'text',
            'label' => 'Фамилия',
            'placeholder' => 'Введите фамилию'
        )
        
        ,'_hr_name_' => array(
            'fieldtype' => 'hr'
        )
        ,'job' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'full-width', 
            'allow_empty' => true, 
            'allow_null' => true,
            'fieldtype' => 'text',
            'label' => 'Должность'
        )
        ,'company' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'full-width', 
            'allow_empty' => true, 
            'allow_null' => true,
            'fieldtype' => 'text',
            'label' => 'Компания'
        )
        ,'biography' => array(
            'type' => TYPE_STRING,
            'parent_class' => 'full-width', 
            'allow_empty' => true, 
            'allow_null' => true,
            'fieldtype' => 'textarea',
            'editor' => true,
            'label' => 'Биография'
        )
        ,'_hr_login_' => array(
            'fieldtype' => 'hr'
        )
        
        
        ,'login' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'inline', 
            'allow_empty' => false, 
            'allow_null' => false,
            'fieldtype' => 'text',
            'label' => 'Логин'
        )
        
        ,'email' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'inline', 
            'allow_empty' => true, 
            'allow_null' => true,
            'fieldtype' => 'text',
            'label' => 'Email'
        )
        ,'_hr_passwd_' => array(
            'fieldtype' => 'hr'
        )
        ,'passwd1' => array(
            'type' => TYPE_STRING,
            'max' => 255, 
            'allow_empty' => true, 
            'fieldtype' => 'password',
            'parent_class' => 'inline', 
            'label' => 'Пароль',
        )
        ,'passwd2' => array(
            'type' => TYPE_STRING,
            'max' => 255, 
            'allow_empty' => true, 
            'fieldtype' => 'password',
            'parent_class' => 'inline', 
            'label' => 'Подтвердить пароль',
        )
        ,'_hr_admin_' => array(
            'fieldtype' => 'hr'
        )
        ,'admin' => array(
            'type' => TYPE_INTEGER,
            'min' => 1,
            'max' => 2,
            'allow_empty' => true, 
            'allow_null' => false,
            'fieldtype' => 'switcher',
            'parent_class' => 'full-width', 
            'values' => array(1=>'Да',2=>'Нет'),
            'label' => 'Пользователь является администратором?'
        )
        
        
    )   
);
?>