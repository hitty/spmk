if($)(function(window, document, $, undefined){
    
    $.fn.formEdit = function(opts) {
        var defaults = {
            container               : null,                     /* элемент DOM с html */
            url                     : null,                     /* URL для получения html */
            id                      : null,                     /* id */
            tinyMCE_selector        : '.tinyMCE',               /* селектор для визуального редактора */
            tinyMCE_url             : '/js/tinymce',            /* папка визуального редактора */
            tinyMCE_additional_css  : "/manage/css/style.css",/* файл со стилями для визуального редактора */
                                    
            fileupload_selector     : '.fileupload',            /* селектор для загрузки фотогалереи */
            photoupload_selector    : '.photoupload',            /* селектор для загрузки фотогалереи */
            
            select_filter_selector  : '.select-filter',          
            
            video_list_selector     : '.video-list-container',          
            video_list_url          : '',          
            
            switcher_selector       : '.switcher',          
            visibility_attr         : 'data-visibility-selector',          
            
            ajax_pending_selector   : '.pending',               /* селектор для аякс-подгрузки блоков */
            datepicker_selector     : 'input[name*=date]',      /* селектор для выбора дат */
            tinyMCE_toolbar         :   {
                                            'extended' : "code | paste |  undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | table bullist numlist | link image media | spellchecker preview fullscreen",
                                            'default': "code | paste |  undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | link image preview"
                                        },
            onInit                  : function(){}
            
        };
        var o = $.extend( defaults, opts || {} );
        var init_selector = null;                           /* Элемент, к которому назначен вызов */
        
        /* функция стартовой инициализации */
        
        var start = function( selector ){
            
            jQuery( o.tinyMCE_selector, selector ).each( function(){
                var _this = jQuery( this );
                //tinyMCE
                tinyMCE.baseURL = o.tinyMCE_url;
                tinyMCE.init({
                    selector : '#' + _this.attr('id'),
                    browser_spellcheck: true,
                    relative_urls: true,
                    menubar:false,
                    statusbar: false,   
                    branding: false,
                    language:"ru", // язык  
                    plugins: [
                        ["code advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker"],
                        ["searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking"],
                        ["save table contextmenu directionality emoticons template paste placeholder"]
                    ],
                    add_unload_trigger: false,   
                    schema: "html5",
                    toolbar: o.tinyMCE_toolbar[_this.data('toolbar') == undefined ? 'default' : _this.data('toolbar')],
                    statusbar: false, 
                    //  Указываем CSS сайта
                    content_css : o.tinyMCE_additional_css, 
                    setup : function(ed) {
                        ed.on('change', function(e) {
                            jQuery( 'textarea#' + jQuery(e.target).attr('id') ).text( ed.getContent() ).attr( 'value', ed.getContent() ).change();
                        })
                    },
                    // without images_upload_url set, Upload tab won't show up
                    images_upload_url: '/manage/upload/',
                    
                    // override default upload handler to simulate successful upload
                    images_upload_handler: function (blobInfo, success, failure) {
                        var xhr, formData;
                      
                        xhr = new XMLHttpRequest();
                        xhr.withCredentials = false;
                        xhr.open('POST', '/manage/upload/');
                      
                        xhr.onload = function() {
                            var json;
                        
                            if (xhr.status != 200) {
                                failure('HTTP Error: ' + xhr.status);
                                return;
                            }
                        
                            json = JSON.parse(xhr.responseText);
                        
                            if (!json || typeof json.location != 'string') {
                                failure('Invalid JSON: ' + xhr.responseText);
                                return;
                            }
                        
                            success(json.location);
                        };
                      
                        formData = new FormData();
                        formData.append('file', blobInfo.blob(), blobInfo.filename());
                      
                        xhr.send(formData);
                    },
                });
            })
            
            //files upload
            if( jQuery( o.fileupload_selector, selector ).length > 0 ){
                jQuery( o.fileupload_selector, selector ).each( function(){
                    var _el = jQuery( this );
                    _el.uploadifile( { 
                        'queueSizeLimit' : 1000,
                        'fileType' : typeof _el.data('filetypes') == 'string' ? _el.data('filetypes') : 'image' 
                    } );    
                })
            }
            //photos upload
            if( jQuery( o.photoupload_selector, selector ).length > 0 ){
                jQuery( o.photoupload_selector, selector ).each( function(){
                    var _el = jQuery( this );
                    _el.uploadifive( { 
                        'queueSizeLimit' : _el.data('limit'),
                        'fileType' : 'image' 
                    } );    
                })
            }
            //checkbox
            jQuery("input[type='checkbox']",selector).each(function(){
                jQuery(this).on("click",function(){
                    var _this = jQuery(this); 
                    if(_this.is(':checked') == true) _value = _this.data('true-value');
                    else _value = _this.data('false-value');
                    jQuery('input#'+_this.attr('rel')).val(_value);
                })
            })
            //datepicker
            if( jQuery( o.datepicker_selector, selector ).length > 0 ){
                jQuery( o.datepicker_selector, selector ).each( function(){
                    var _this = jQuery( this );
                    _this.datepicker({
                        defaultDate: "0",
                        hideIfNoPrevNext: true,
                        numberOfMonths: 2,
                        dateFormat: 'dd.mm.yy'
                    });    
                })
            }
            //count letters
            jQuery('input').each(function(){
                var _length = jQuery(this).val().length;
                jQuery(this).siblings('span.count-letters').text(_length);
                jQuery(this).on('keyup', function(){
                    var _length = jQuery(this).val().length;
                    jQuery(this).siblings('span.count-letters').text(_length);
                })
            })
            //pending blocks
            if( jQuery( o.ajax_pending_selector, selector ).length > 0 ){
                jQuery( o.ajax_pending_selector, selector ).each( function(){
                    var _this = jQuery( this );
                    getPendingContent(
                        '#' + _this.attr('id'),
                        _this.data('url'),
                        {id:_this.data('id')}, 
                        false,
                        false,
                        function(){ 
                            init( _this )    
                        }
                    )
                })
            }       
            
            if( selector.closest( o.ajax_pending_selector ).length > 0 ){
                var _pending_parent = selector.closest( o.ajax_pending_selector );
                 
                //init selectors
                listSelectorInit( _pending_parent );
                
                //update pending blocks
                jQuery( 'input, textarea', _pending_parent ).on( 'change', function(){
                    var _values = {};
                    _pending_parent.find( '.item' ).each(function(){
                        var item_values = {};
                        var _item = jQuery( this );
                        var _id = _item.data('id');
                        _item.find( 'input, textarea' ).each(function(){
                            var _this = jQuery( this );
                            var _type = _this.attr( 'type' );
                            var _name = _this.attr( 'name' );
                            if( _type == 'checkbox' && _this.parent().hasClass( 'on' )){
                                _value = 1;
                            } else {
                                _value = _type == 'radio' ? jQuery( 'input[name=' + _name + ']:checked', _pending_parent).val() : _this.val();
                            }
                            item_values[_name] = _value;
                        }); 
                        _values[_id] = item_values;
                    }); 
                    getPending( _pending_parent.data( 'url') + 'edit/' + _pending_parent.data('id') + '/', {values: _values } );
                })
                
                //add pending block
                jQuery( '.add', _pending_parent ).on( 'click', function(){
                    getPending( 
                        _pending_parent.data( 'url') + 'add/',
                        {id: _pending_parent.data('id')},
                        function( data ){
                            if( jQuery( '.item:last', _pending_parent ).length > 0 ) jQuery( data.html ).insertAfter( jQuery( '.item:last', _pending_parent ) );    
                            else _pending_parent.prepend( jQuery( data.html ) );
                            init( _pending_parent );
                        }
                    )
                })
                //delete pending block
                jQuery( '.delete', _pending_parent ).on( 'click', function(){
                    var _item = jQuery( this ).closest( '.item' );
                    getPending( 
                        _pending_parent.data( 'url') + 'delete/',
                        {id: _item.data('id')},
                        function( ){
                            _item.fadeOut(350);
                        }
                    )
                    
                    
                })            
            }
            
            if( jQuery( '.add-button', selector ).length > 0 ){
                jQuery('.add-button', selector).each(function(){ 
                    var _this = jQuery( this );
                    _this.popupWindow(
                        {
                            popupCallback : function( data ){
                                var _list_selector = _this.next( 'div' );
                                jQuery( '.list-data', _list_selector ).append( '<li data-value="' + data.id + '">' + data.title + '</li>');
                                listSelectorInit( _list_selector.parent() );
                                jQuery( '.list-data li[data-value=' + data.id + ']', _list_selector ).click();
                            }        
                        }
                    ); 
                } )    
            }
            
            jQuery("input[type=phone], input[name=phone]", selector).inputmask({"mask": "8 (999) 999-9999"}); //specifying options
            
            //switcher
            if( jQuery( o.switcher_selector, selector ).length > 0 ){
                jQuery( o.switcher_selector, selector ).each( function(){
                    jQuery( this ).on('click', function(){
                        var _this = jQuery( this );
                        _this.toggleClass('active');
                        jQuery( 'input', _this ).attr( 'value', _this.hasClass( 'active' ) ? 1 : 2 );
                        if( typeof jQuery( this ).attr( o.visibility_attr ) != 'undefined' ) checkElVisibility( _this );
                    })
                    if( typeof jQuery( this ).attr( o.visibility_attr ) != 'undefined' ) checkElVisibility( jQuery( this ) );
                })
            }          
            //select filter
            if( jQuery( o.select_filter_selector, selector ).length > 0 ){
                jQuery( o.select_filter_selector, selector ).each( function(){
                    var _this = jQuery(this);
                    var _parent_el = jQuery( '[name=' + _this.data('filter') + ']', selector );
                    selectFilterVisibility( _this, _parent_el.attr('value') );
                    _parent_el.on( 'change', function(){
                        selectFilterVisibility( _this, jQuery(this).attr('value') );
                    })
                })
            }          
            
            //video list
            if( jQuery( o.video_list_selector, selector ).length > 0 ){
                jQuery( o.video_list_selector, selector ).each( function(){
                    var _this = jQuery(this);
                    var _input = jQuery(this).siblings( 'input' );
                    var _button = jQuery(this).siblings( '.add-video' );
                    o.video_list_url = _input.data( 'url' );
                    o.video_limit = typeof _input.data( 'limit' ) == 'number' ? _input.data( 'limit' ) : o.video_limit ;
                    o.id = _input.data( 'id' );
                    getPendingContent( 
                        _this, 
                        o.video_list_url + 'list/', 
                        {id: o.id},
                        false,
                        false,
                        function( data ){
                            if( data.list ) {
                                o.video_count = data.list.length;
                                checkVideoLimit( _this );
                                for( i=0; i < data.list.length; i++ ){
                                    videoShowInfo( _this, data.list[i] ) 
                                }
                            }
                        }
                         
                    );  
                    _button.on('click', function(){
                        addVideo( _input, _this )     
                    } 
                    );
                    _input.on('keypress', function (e) {
                         if(e.which === 13) {
                             addVideo( _input,  _this )
                             return false;
                         }
                   });
                   
                })
            }
            
            jQuery.each(jQuery('textarea'), function() {
                resizeTextarea(this)
                jQuery(this).on('keyup input', function() { resizeTextarea(this); });
            });  
        }
        addVideo = function( input, _this ){
             input.removeClass( 'red-border' ).next('span').text('');
             console.log( input )
             var _link = input.val();
             if( _link == '' ) {
                 input.addClass( 'red-border' ).next('span').text('Пустое поле');
                 
             }
             getPending( 
                o.video_list_url + 'add/', 
                {
                    id: o.id, 
                    link: _link
                }, 
                function( data ){ 
                    videoShowInfo( _this, data.list);
                    input.removeClass( 'red-border' ).val('');
                    o.video_count++;
                    checkVideoLimit( );
                },
                function( data ) {
                    input.addClass( 'red-border' ).next('span').text( data.error );
                }
             ); 
            
         }
        //проверка на лимит видео
        checkVideoLimit = function( ) {  
            var _el = jQuery( 'input[name=video_upload]' );
            if( o.video_limit <= o.video_count ) _el.hide().siblings('.button').hide();
            else _el.show().siblings('.button').show();
        }            
        //добавление блока управления видео
        videoShowInfo = function( _parent, _data ) {                
            _parent.append( '<div class="item" data-id="' + _data.id + '">\
                <input type="text" name="file_title[' + _data.id + ']" class="title" value="' + _data.title + '">\
                <div class="boxcaption-wrap"><div class="boxcaption_del"  data-icon="delete" title="Удалить файл"></div></div>\
                <span class="link"> ' + _data.link + '</span>\
                <iframe width="590" height="345" src="' + _data.embed_link + '" frameborder="0" allowfullscreen></iframe>\
            </div>');
            _this_item = jQuery( "[data-id=" + _data.id + "]", _parent );
                
            //удаление файла
            jQuery( '.boxcaption_del', _this_item ).bind('click', function() {
                   videoDeleteFile( jQuery(this) );
                   return false;
            });
            //редактирование заголовка
            jQuery( '.title', _this_item).bind('change', function() {
                videoSetTitle( jQuery(this) );
                
            });    
            jQuery( '.title', _this_item).on('keypress', function (e) {
                 if(e.which === 13){
                    videoSetTitle( jQuery(this) );
                    return false;
                 }
           });
           
        } 
        videoSetTitle = function( _this ) {
            getPending( o.video_list_url + 'setTitle/', {id: _this.parent().attr('data-id'), title: _this.val( ) } )   
            _this.trigger('blur');
        }
                
        videoDeleteFile = function( _this ) {
            getPending( 
                o.video_list_url + 'del/', 
                {id: _this.closest('.item').attr('data-id') },
                function(){
                    _this.closest('.item').fadeOut( 200 );
                    o.video_count--;
                    checkVideoLimit( );
                }
            )   
            _this.trigger('blur');
        }
                
        /* проверка полей на видимость */
        var checkElVisibility = function( _el ){
            var _selector = _el.attr( o.visibility_attr );
            var _active = _el.hasClass('active');
            console.log( _selector );
            jQuery( _selector ).not(_el.closest('.row')).each( function(){
                
                jQuery(this).toggleClass( 'disabled', !_active );
            })
        }  
        /* управление зависимыми выпадающими списками */
        var selectFilterVisibility = function( _el, _value ){
            if( _value == 0 ) jQuery( 'li', _el).first().click().siblings().removeClass('active');
            else {
                jQuery( 'li:not(:first)', _el).removeClass('active').siblings( 'li:not(:first)[data-parent-id=' + _value + ']').addClass('active');
                if( !jQuery( 'li.selected', _el).hasClass('active') ) jQuery( 'li', _el).first().click()
            }
        }

        /* textarea resize*/
        var resizeTextarea = function(el) {
            var offset = el.offsetHeight - el.clientHeight;
            jQuery(el).css('height', 'auto').css('height', el.scrollHeight + offset);
        };
        $.fn.formEdit.destroy = function() {
        }
       
        return this.each(function(){
            init_selector = $(this);
            start( init_selector );
        });
    }                
            
})(window, document, jQuery);            