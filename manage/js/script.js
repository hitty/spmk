jQuery(document).ready(function(){
    //добавление - редактирование проекта
    jQuery('.item.add-button,.item .edit').each(function(){
        var _el = jQuery(this);
        initAddPopup( _el );
    })
    
    //удаление проекта
    jQuery('.item .del').each(function(){
        var _el = jQuery(this);
        initDelPopup( _el );
    })
    
    //статус страницы
    jQuery('.item .archive, .item .publish').each(function(){
        var _el = jQuery(this);
        _el.popupWindow({
       
            popupCallback:function(data){
                if( typeof data.ok ) {
                    var _parent = _el.closest('.item');
                    if( _el.hasClass('archive') ) _parent.addClass('inactive');
                    else _parent.removeClass('inactive');
                }
            }
            
        })    
    })
    
    
    //обработка формы редактирования
    if( jQuery( '.form-default' ).length > 0 ) jQuery( '.form-default' ).formEdit();
    
    
    
    
    //поисковый фильтр
    jQuery('.list-container').listManager();
    
}) 
 
function initDelPopup( _el ){
    _el.popupWindow({
       
        popupCallback:function(data){
            if( typeof data.ok ) {
                var _parent = _el.closest('.item');
                _parent.fadeOut(200) ;
            }
        }
        
    })    
}
function initAddPopup( _el ){
    _el.popupWindow({
        popupCallback:function(data){
            if( typeof data.html_additional == 'string' ) {
                if( _el.hasClass('edit') ) {
                    var _parent = _el.closest('.item');
                    _parent.html( data.html_additional ) ;
                    initAddPopup( jQuery( '.edit', _parent ) );
                    initDelPopup( _el.siblings('.del') )
                }
                else {
                    jQuery( data.html_additional ).insertBefore( jQuery('.item.add-button') );  
                    initAddPopup( jQuery( '.edit', jQuery('.item.add-button').prev('.item') )  );      
                    initDelPopup( jQuery( '.del', jQuery('.item.add-button').prev('.item') )  );      
                    
                }
            }
            
        }
        
    })    
}