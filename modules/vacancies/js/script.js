jQuery(document).ready(function(){
    jQuery('.vacancies .list .item .title').on('click', function(){
        toggleVacancy( jQuery(this) )
    })
    jQuery('.vacancies .list .item .toggle').on('click', function(){
        toggleVacancy( jQuery(this).parent() )
    })
})
function toggleVacancy( _el ){
    var _p = _el.closest('.item');
    jQuery( '.content', _p ).slideToggle();
    _p.toggleClass('toggled').siblings('.item').removeClass('toggled');
    _p.siblings('.item').find('.content').slideUp();    
}