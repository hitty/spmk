var _emailTest = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i; 

function formValidate(_selector){
    
    jQuery("input.digit",_selector).attr('data-inputmask',"'mask': '9'");
    jQuery("input[type=phone], input[name=phone]",_selector).attr('data-inputmask',"'mask': '+7 (999) 999-9999', 'greedy' : false"); //specifying options
    jQuery("input[type=email], input[name=email]",_selector).attr('data-inputmask',"'alias': 'email'"); //specifying options
    jQuery("input[name=date],input.datepicker",_selector).datetimepicker({
        timepicker:false,
        format:'d.m.y',
        minDate:'+1970/01/02',//yesterday is minimum date(for today use 0 or -1970/01/01)
    });  
    Inputmask().mask(document.querySelectorAll("input"));
    
    
    jQuery("input[type='checkbox']",_selector).each(function(){
        jQuery(this).on("click",function(){
            var _this = jQuery(this); 
            if(_this.is(':checked') == true) _value = _this.data('true-value');
            else _value = _this.data('false-value');
            jQuery('input#'+_this.attr('rel')).val(_value);
        })
    })
  
} 