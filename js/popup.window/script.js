if($)(function(window, document, $, undefined){
    $.fn.formSubmit = function(opts) {
        var defaults = {
            button                : null, 
            f_values              : {},                       /* массив значений всех элементов */
            popup_redirect        : false,
            scroll_to_error       : false,
            error_template        : '<span class="error"></span>',
            notification_class    : 'form-block notifications',
            uploader_index        : 0,
            onInit                : function(){},
            onFormSuccess         : function(data){}
        };
        var o = $.extend(defaults, opts || {});
        var init_selector = null;                           /* Элемент, к которому назначен вызов */
        
        /* функция стартовой инициализации */
        var start = function(){
            o.button = jQuery( 'input[type=submit], button, .button', init_selector);

            try {
                grecaptcha.ready(function () {
                    var _input = $('input[name=recaptcha_response]', init_selector);
                    grecaptcha.execute(_input.data('public'), {action: 'contact'}).then(function (token) {
                        var recaptchaResponse = document.getElementById('recaptchaResponse');
                        _input.attr('value', token);
                    });
                });
            } catch (e) {
                console.log('Recaptcha:' + e);
            }
            
            formValidate( init_selector );
            
            //уникальные поля
            init_selector.find( 'input.unique' ).each(function(){
                var search_timeout = undefined;
                var _input = jQuery(this);
                jQuery(this).bind( 'keyup', function() {
                    clearTimeout(search_timeout);
                    search_timeout = setTimeout(function() {
                        validateUnique( _input )
                    }, 250);
                });
            });
            
            init_selector.find( 'input, textarea' ).each(function(){ 
                jQuery(this).on( 'change', function(){
                    checkEl( jQuery(this) );    
                })

            });
            
            //обработка прикрепленных файлов
            
            jQuery( 'input[class*="inputfile"]', init_selector ).each( function(){
                manageUpload( jQuery(this) )
                
            } ) ;

            init_selector.on('submit', function(e) {
                if( jQuery( o.button, jQuery(this) ).hasClass( 'disabled' ) || jQuery( o.button, jQuery(this) ).hasClass( 'waiting' )) return false;
                jQuery( o.button, jQuery(this) ).addClass( 'waiting' );
                
                e.stopPropagation();
                e.preventDefault();

                jQuery( '.notifications div', init_selector ).addClass( 'inactive' );
                     
                //валидация формы
                init_selector.find( 'input, textarea' ).each(function(){
                    var _this = jQuery(this);
                    checkEl( _this );
                });  
                if( o.scroll_to_error == true && $('.error', init_selector).length > 0 ){
                    var _header = parseInt( $('header').height() );
                    $("html,body").animate({ scrollTop: $('.error', init_selector).first().offset().top - _header - 20}, "slow");
                    
                }
                o.f_values['popup_redirect'] = o.popup_redirect;
                if( jQuery( '.error', init_selector ).length ) {
                    jQuery(this).removeClass( 'waiting' );
                    return false;
                }
                jQuery.ajax({
                    type: "POST", 
                    async: true,
                    contentType: false,
                    processData:false, 
                    dataType: 'json', 
                    url: init_selector.attr('action'),
                    data: new FormData( this ),
                    cache: false,
                    success: function(msg){ 
                        var _error_notification = jQuery( '.' + o.notification_class.split(' ').join('.'), init_selector );
                        o.button.removeClass( 'waiting' );
                        if( msg.ok == true ) {
                            counterGoals('send_' + o.f_values['application_type']);
                            if(typeof o.onFormSuccess == "function") o.onFormSuccess.call(this, msg);
                            //вывод уведомления
                            if( typeof msg.html == 'string' || typeof msg.html_additional == 'string' ) {
                                _error_notification.remove();
                                init_selector.find( 'input, textarea' ).addClass('success');
                                if( msg.success ) jQuery( '.button-container', init_selector ).html ( msg.success ).addClass( 'notifications success' );

                                if( jQuery( '.modal-inner .closebutton, .modal-inner .modal-close-btn' ).length > 0 && !_debug ) {
                                    setTimeout(function(){
                                        jQuery( '.modal-inner .closebutton, .modal-inner .modal-close-btn' ).click();
                                    }, typeof msg.html == 'string' && msg.html.length > 20 ? 5200 : 3500 );
                                }

                                if( typeof msg.html == 'string' && msg.html.length > 20 ) init_selector.parent( 'div' ).html(msg.html);

                                if(o.popup_redirect == true || o.popup_redirect == 'true' || msg.popup_redirect == true){
                                    if( !_debug ){
                                        setTimeout(function(){
                                            window.location.href = msg.redirect_url ? msg.redirect_url : location.href.replace(location.hash, "");
                                        }, typeof msg.html == 'string' && msg.html.length > 20 ? 500 : 1700 )
                                    }
                                } 
                                                          
                            } else jQuery( '.modal-inner .closebutton, .modal-inner .modal-close-btn' ).click();
                                if( jQuery( '.result-html', init_selector ).length > 0 && msg.success_text.length > 0 ) {
                                    jQuery( '.result-html', init_selector ).html( msg.success_text ).addClass( 'success' );
                                    jQuery( 'input,textarea,.list-selector', init_selector ).attr('disabled', 'disabled').addClass('disabled');
                                }

                        } else if( msg.error ||  msg.errors )  {
                            if( _error_notification.length > 0 ) _error_notification.html( msg.error ).addClass('active');
                            else jQuery( '<div class="' + o.notification_class + '">' + msg.error + '</div>' ).insertBefore( jQuery('.form-block', init_selector ).first() ) ;
                            if( msg.errors ){
                                for(var index in msg.errors) { 
                                    notification( jQuery( '[name=' + index + ']', init_selector ),  true, msg.error != msg.errors[index] ? '<span class="error">' + msg.errors[index] + '</span>' : '' )
                                }
                            }
                        } 
                    }
                });
                return false;                    
            });
                
            
            if(typeof o.onInit == "function"){
                o.onInit();
            }    
            
            //обработка клавиатуры
            jQuery(document).keyup(function(e) {
                switch(e.keyCode){
                    case 13: o.button.click();
                    case 27: jQuery( '#background-shadow-inner .closebutton' ).click();
                }
            });        
        };
        //проверка на уникальность
        var validateUnique = function( _this ){
            var _params = {};
            _params[ _this.attr( 'name' ) ] = _this.val();
            var _url = _this.data( 'url' );
            getPending( _url, _params, function( data ){ 
                    notification( _this,  !data.ok, '<span class="error">' + data.error + '</span>' );
                } 
            );
        };
        //обработка элементов формы
        var checkEl = function( _this ){
            _this.removeClass('error').next('span').remove();
            if( _this.parent().hasClass( 'list-selector' )) _this.parent().removeClass( 'error' ).next( 'span' ).removeClass( 'active' );
            var _type = _this.attr( 'type' );
            _required = _this.attr( 'required' );
            _name = _this.attr( 'name' );
            if( _name != false ) {
                if( _type == 'checkbox' && _this.parent().hasClass( 'on' )){
                    _value = 1;
                } else {
                    _value = _type == 'radio' ? jQuery( 'input[name=' + _name + ']:checked', init_selector).val() : _this.val();
                }
                if( 
                    ( ( _this.is('[data-inputmask]') || _this.is('[data-inputmask-regex]') ) && !_this.inputmask("isComplete") && _value.length > 0 ) || 
                    ( _required == 'required' && ( _value == '' || _value == 0) )
                ) {
                        //отдельно для селекторов
                    if( _this.parent().hasClass( 'list-selector' )) _this.parent().addClass( 'error' ).parents( '.row' ).append(o.error_template);
                    else  notification( _this, true, o.error_template ); 
                    
                } else {
                    o.f_values[_name] = _value;
                    if( !_this.hasClass( 'unique' ) ) notification( _this, false, '' );
                }
            }            
        };
        //уведомления
        var notification = function( _this, _error, _text ){
            
            if( _error == true ){
                _this.addClass( 'error' ).parents( '.row' ).append( _text );
            } else {
                _this.removeClass( 'error' ).parents( '.row' ).find( '.error' ).remove();
            }

        };
        //мультизагрузка файлов
        var manageUpload = function( input ){
            
            o.uploader_index++;
            var _parent = input.closest('div');
            var _clone = _parent.clone();
            jQuery( '.close', _parent ).on( 'click', function(){
                _parent.remove();
            });
            var label = jQuery( 'label', _parent );  
            labelVal  = label.html();
            input.on('change', function(e){   
                
                var fileName = '';
                fileName = e.target.value.split( '\\' ).pop();

                if( fileName )
                    jQuery( 'span', _parent ).html(fileName);
                else
                    label.html( labelVal );
                    
                _clone.insertBefore( _parent );
                _parent.addClass( 'active' );
                var _identificator = jQuery( '[id*="file_upload"]', _clone ).attr( 'id' ); 
                jQuery( '[id*="file_upload"]', _clone ).attr( 'id', _identificator + o.uploader_index );
                jQuery( 'label[for*="file_upload"]', _clone ).attr( 'for', _identificator + o.uploader_index );
                manageUpload( jQuery( 'input', _clone ) )
            })  
            
            
        };
        return this.each(function(){
            init_selector = $(this);
            start();   
        });
    };

    $.fn.popupWindow = function(opts) {
        var defaults = {
            container               : null,                     /* элемент DOM с html */
            url                     : null,                     /* URL для получения html */
            background_template     : '<div class="modal-container modal-container-opened"><div class="modal-bg"></div><div class="modal-inner"><div class="modal-stage"><div class="modal-slide"></div></div></div></div>', /* задний фон */
            closebutton             : '.closebutton',           /* закрытие формы */
            active_class            : 'active',           
            background_container    : '.modal-bg',           
            inner_container         : '.modal-slide',           
            close_container         : '.modal-close',           
            close_template          : '<div class="modal-close"></div>',   
            closebutton_template    : '<span class="closebutton" data-icon="close"></span>',                   
            popup_redirect          : true,
            width                   : 0,           /* ширина внутреннего блока */
            popupCallback           : function(data){}, /* функция, предваряющая закрытие function(item_id){} */
            onInit                  : function(){}
            
        };
        var o = $.extend(defaults, opts || {});
        var init_selector = null;                           /* Элемент, к которому назначен вызов formexpand */
        var current_item_data = null;                       /* Информация о текущем элементе */
        
        /* функция стартовой инициализации */
        var start = function(){
            

            init_selector.on( 'click', function(){
                jQuery( 'body' ).css( { 'overflow' : 'hidden' } );
                    
                _gpval = init_selector.attr( 'data-location' );
                //setGPval();
                
                o.container = init_selector.attr( 'data-container' );
                o.url = init_selector.attr( 'data-url' );
                
                if( $( o.inner_container ).length > 0 && $( o.inner_container ).hasClass( 'active' )){
                    $( o.inner_container ).remove();
                    $( o.background_container ).remove();
                    init_selector.click();
                }
                
                container = $( o.container );

                //загрузка заднего фона
                $( 'body' ).append(o.background_template);
                
                //получение контента в зависимости от способа получения данных
                if( typeof o.container == 'string' ) $( o.inner_container ).append( container.html() ).addClass( container.attr( 'class' ) );
                else if( typeof o.url == 'string' ) getPendingContent( o.inner_container, o.url , false, false, false, 
                    function(){ 
                        setTimeout(function(){
                            //эффект появления контентной части
                            o.width = $( o.inner_container ).outerWidth();
                            o.height = parseInt( $( o.inner_container ).outerHeight() );
                            o.window_height = parseInt( $( window ).height() );
                            
                            $( o.inner_container ).addClass( 'active' );

                            checkBoxesInit( $( o.inner_container ) ); 
                            listSelectorInit( $( o.inner_container ) );
                            $( o.inner_container + ' .popup' ).each(
                                function(){ 
                                    $(this).popupWindow();
                                }
                            );
                            formValidate( o.inner_container );
                            
                            //closebutton
                            var _popup_redirect = o.popup_redirect && ( typeof init_selector.attr( 'data-redirect' ) == 'string' ? init_selector.attr( 'data-redirect' ) : false );
                            if( typeof $( o.inner_container + ' form' ).formEdit !== "undefined" ) $( o.inner_container + ' form' ).formEdit();
                            $( o.inner_container + ' form' ).formSubmit(
                                { 
                                    popup_redirect : _popup_redirect,
                                    onFormSuccess: function(data){ o.popupCallback(data) }
                                }
                            ); 
                            if(typeof o.onInit == "function"){
                                o.onInit(container );
                            }
                            $( o.inner_container ).children(0).prepend( o.closebutton_template );
                            $( o.inner_container ).append( o.close_template );

                        }, 20 );
                        
                    }
                );
                else return false;
                //эффект появления заднего фона
                $( o.background_container ).fadeIn(100);
                    
            });
            //закрытие формы
            $(document).on("click", o.close_container + ', ' + o.inner_container + ' ' + o.closebutton, closePopupWindow );

        };
       var closePopupWindow = function(){
            //эффект исчезания контентной части
            $( o.inner_container ).removeClass( 'active' ).addClass('inactive');

            //эффект исчезания заднего фона
            setTimeout(
                function(){
                    $( o.background_container ).fadeOut(100, function(){
                        
                        $(this).parent().remove();
                        $( o.inner_container ).remove();
                        jQuery( 'body' ).css( { 'overflow' : 'auto' } );
                    })
                }, 250
            );
            _gpval = '';
            //setGPval();
            return false;
       };
       $.fn.popupWindow.destroy = function() {
       };
       
       return this.each(function(){
            init_selector = $(this);
            start();  
       });
    }                
            
})(window, document, $);            