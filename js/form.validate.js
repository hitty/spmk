var _emailTest = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i; 

function formValidate(_selector){
    
    jQuery("input.digit",_selector).inputmask({"mask": "9999999999999999999999999"}); //specifying options
    jQuery("input[type=phone], input[name=phone]",_selector).inputmask({"mask": "8 (999) 999-9999"}); //specifying options
    jQuery("input[name=subdomain]",_selector).inputmask('Regex', { regex: "[0-9a-z]{1,}" });

    jQuery("input[type='checkbox']",_selector).each(function(){
        jQuery(this).on("click",function(){
            var _this = jQuery(this); 
            if(_this.is(':checked') == true) _value = _this.data('true-value');
            else _value = _this.data('false-value');
            jQuery('input#'+_this.attr('rel')).val(_value);
        })
    })
  
} 