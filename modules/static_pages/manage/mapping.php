<?php
return array(
    'static_pages' => array(
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
            'allow_null' => false,
            'fieldtype' => 'text',
            'label' => 'URL',
            'tip' => 'Можно использовать только латиницу и символы _ и -'
        )
        ,'title' => array(
            'type' => TYPE_STRING,
            'max' => 255,
            'allow_empty' => false, 
            'allow_null' => false,
            'parent_class' => 'full-width',
            'fieldtype' => 'text',
            'label' => 'Название',
            'placeholder' => 'Название страницы (title)'
        )
        
        ,'content' => array(
            'type' => TYPE_STRING,
            'allow_empty' => true, 
            'allow_null' => false,
            'parent_class' => 'full-width',
            'fieldtype' => 'textarea',
            'editor' => 'true',
            'label' => 'Cодержимое страницы',
            'placeholder' => ''
        )      
    )
);
?>