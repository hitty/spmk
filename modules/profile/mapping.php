<?php
return array(
    'profile' => array(
         'id' => array(
            'type' => TYPE_INTEGER,
            'nodisplay' => true,
            'allow_empty' => true, 
            'allow_null' => true
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
        ,'email' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'parent_class' => 'half-width', 
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
            'label' => 'Новый пароль',
        )
        ,'passwd2' => array(
            'type' => TYPE_STRING,
            'max' => 255, 
            'allow_empty' => true, 
            'fieldtype' => 'password',
            'label' => 'Подтвердить новый  пароль',
        )
        
    )   
);
?>