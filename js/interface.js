var _previous_value = null;         
var _gpval = '';  
jQuery(document).ready(function(){
    checkBoxesInit('');
    listSelectorInit('');
    
    jQuery( '.filter span,.scroll-to' ).on( 'click', function(){
        var _this = jQuery(this);
        _this.addClass( 'active' ).siblings().removeClass( 'active' );
        jQuery("html,body").animate({ scrollTop: jQuery( _this.data( 'element' ) ).offset().top }, "slow");
    });

    if( typeof jQuery('.search') == "object" ){
        var _s = jQuery('.search');
        jQuery('.list-selector', _s ).on( 'change', function(){
            var _this = jQuery(this);
            var _i = jQuery('input[type=hidden]', _this);
            document.location.href = _this.data('url') + '?' + _i.attr('name') + '=' + _i.attr('value');
        })
    }
});
           

function checkBoxesInit(parent_object){
    jQuery(".checkbox, .checkbox-group, .radio", parent_object).each(function(){
        var _elem = jQuery(this);
        var _type = _elem.hasClass('radio') ? 'radio' : 'checkbox';
        jQuery("input[type='"+_type+"']", _elem).change(function(){
            if(_type == 'checkbox'){
                if(jQuery(this).is(":checked")) jQuery(this).parent().addClass("on");
                else jQuery(this).parent().removeClass("on");
            } else jQuery(this).parent().addClass("on").siblings('label').removeClass('on')  
        });
        if(_type == 'checkbox') jQuery("input[type='"+_type+"']", parent_object).each(function(){jQuery(this).change()});
    });
}
function listSelectorInit( parent_object ){
    /* list-selector */
    _opened_listelector = null;
    jQuery(".list-selector", parent_object).each(function(){
        var _selector = jQuery(this);
        jQuery(".select, .pick", _selector).click(function(e){   
            
            if( ( jQuery(e.target).hasClass('pick') && _selector.hasClass('dropped') ) || jQuery(this).hasClass('disabled') || jQuery(this).parent().hasClass('disabled') ) return false;
            jQuery(".select, .pick", _selector).parent(".list-selector").not(_selector).removeClass("dropped");
            _selector.toggleClass("dropped");
            if(_selector.hasClass("dropped")) _opened_listelector = _selector;
            else  _opened_listelector = null;                                 
            jQuery( '.list-data li', _selector ).removeClass('hidden');
            
            return false;
        });
        jQuery(".list-data li:not(.disabled)", _selector).click(function(event, first_call){
            
            if(typeof first_call == 'undefined') first_call = false;
            var _li = jQuery(this);
            var _lhtml = _li.html();
            _li.addClass("selected").siblings('li').removeClass("selected");
            if(_li.data('title')!='' && typeof _li.data('title')=='string') {_lhtml = _li.data('title');}
            if(_lhtml!=jQuery(".pick", _selector).html()){
                var _val = _li.attr("data-value");
                var _pick = jQuery(".pick", _selector);
                if( !_pick.is('a') ) _pick.val(_val < 1 ? '' : _lhtml).attr('placeholder',_lhtml);
                else _pick.html(_lhtml).attr('title',_lhtml);
                _previous_value =  jQuery('input[type="hidden"]',_selector).val();
                jQuery('input[type="hidden"]',_selector).val(_val).trigger('change');
                if(_val.length <= 1 && (_val=='' || _val==0)) _selector.removeClass('active');
                else  _selector.addClass('active');
                if(!first_call) _selector.trigger('change',_lhtml);
            }
            _selector.removeClass("dropped");
            _opened_listelector = null;
        });
        var _def_val = jQuery('input[type="hidden"]',_selector).val();
        var _active_item = jQuery('.list-data li[data-value="'+_def_val+'"]', _selector);
        if( _active_item.length == 0 ) _active_item = jQuery('.list-data li:first', _selector);
        _active_item.trigger("click", true);
        
        jQuery('.pick', _selector ).on('keyup', function(){
            var _picker = jQuery(this).val().toLowerCase();
            
            if( _picker == '' ) jQuery( '.list-data li', _selector ).removeClass('hidden');
            else {
                jQuery( '.list-data li', _selector ).each( function(){
                    var _this = jQuery(this);
                    if (_this.data('value').length > 0) {
                        if( parseInt( ( jQuery(this).text().toLowerCase() ).indexOf(_picker) ) >= 0 ) _this.removeClass('hidden');
                        else _this.addClass('hidden');
                    }
                })   
            } 
        })
    });
    jQuery(document).click(function(){
        if(_opened_listelector){jQuery(".select", _opened_listelector).click(); _opened_listelector=null;}
    })
}