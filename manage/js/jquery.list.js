if($)(function(window, document, $, undefined){
    $.fn.listManager = function(opts) {
        var defaults = {
            item                : '.item-block',                  
            filter_container    : '.filters-container',
            sorting_selector    : '.filters-container .list-selector',
            sort_selector       : '.sortable',
            paginator_selector  : '.paginator'         
        };
        var o = $.extend(defaults, opts || {});
        var init_selector = null;                               /* Элемент, к которому назначен вызов */
        var init = function( ){ 
        
            //фильтр + сортировка
            if( jQuery( o.filter_container, init_selector ).length > 0 ) {
                var _container = jQuery( o.filter_container, init_selector );
                
                var _url = _container.data('url');
                var _values = [];
                jQuery( 'input, textarea', _container ).on( 'change', function(){
                    
                    jQuery( 'input:not(.pick), textarea', _container ).each(function(){
                        var _this = jQuery(this);
                        var _type = _this.attr( 'type' );
                        var _name = _this.attr( 'name' );
                        if( _type == 'checkbox' && _this.parent().hasClass( 'on' )){
                            _value = 1;
                        } else {
                            _value = _type == 'radio' ? jQuery( 'input[name=' + _name + ']:checked', _pending_parent).val() : _this.val();
                        }
                        if( _name != undefined && _value != undefined && ( ('0123456789'.indexOf(_value) == -1 && _value !='' ) || ( '0123456789'.indexOf(_value) > -1 && _value > 0 ) ) ) _values.push(_name + '=' + _value);
                    }); 
                    document.location.href = _url + ( _values.length >= 0 ? ( _url.indexOf('?') > 0 ? '&' : '?' ) + _values.join( '&') : '' ); 
                })
            }
            //удаление записей
            jQuery( o.item + ' .delete', init_selector).each(function(){
                var _el = jQuery(this);
                _el.popupWindow({
                    popup_redirect: true,
                    popupCallback : function(data){
                        if( typeof data.ok ) {
                            var _parent = _el.closest('.item-block');
                            _parent.fadeOut(200) ;
                        }
                    }

                })
            });
            //видимость записей
            jQuery(o.item + ' .change-params').on( 'click', function(){
                var _this = jQuery(this);
                var _params = {};
                _params['id'] = _this.data('id');
                _params[ _this.data('field') ] =  _this.hasClass('active') ? 2 : 1;
                _this.toggleClass('active');
                
                getPending( _this.data('url') + 'edit_params/', _params );
            });
            //сортировка записей
            if( jQuery( o.sort_selector, init_selector ).length > 0 ) {
                jQuery( o.sort_selector, init_selector ).each( function(){
                    jQuery( this ).sortable({
                        placeholder: "hovered",
                        containment: "parent",
                        stop: function() {
                            
                            var _items_count = jQuery( o.sort_selector + ' ' + o.item, init_selector ).length;
                            jQuery( o.sort_selector + ' ' + o.item, init_selector ).each( function( index ){
                                getPending( jQuery(this).data('url'), { id: _id = jQuery(this).data( 'id' ), position: _items_count - index } )
                            })
                          }
                    });
                })
                
            }
            
            /*
            jQuery( 'input[name*=date]', _container ).each( function(){
                var _this = jQuery(this);
                _this.datepicker({
                    defaultDate: "0",
                    hideIfNoPrevNext: true,
                    numberOfMonths: 2,
                    dateFormat: 'dd.mm.yy'
                });    
            })
            */
        };
        return this.each(function(){
            init_selector = jQuery( this );
            init();         
        });        
    }                
})( window, document, jQuery ); 