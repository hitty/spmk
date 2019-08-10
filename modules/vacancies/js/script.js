jQuery(document).ready(function(){
    jQuery('.vacancies .list .item .title').on('click', function(){
        jQuery( '.content', jQuery(this).closest('.item') ).slideToggle();
        
    })
})