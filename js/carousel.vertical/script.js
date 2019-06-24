// ==================================================
// Vertical Carousel v0.0.1
// ==================================================
$(document).ready(function(){
    $.fn.verticalCarousel = function(opts) {
        var defaults = {
            slide                 : '.item',
            active_class          : 'active',
            inactive_class        : 'inactive',
            filter                : '.filter',
            filter_el             : 'li',
            attr                  : 'data-pos',
            attr_button_text      : 'data-button-text',
            current_pos           : 0,
            total_slides          : 0,
            active_slide          : '',
            buttons               : '.buttons',
            onInit                : function(){},
            onFormSuccess         : function(data){}
        }
        var o = $.extend(defaults, opts || {});
        var init_selector = null; 
        
        var start = function(){
            //текущая активная позиция
            o.current_pos = parseInt( $( o.filter + ' ' + o.filter_el + '.' + o.active_class, init_selector).attr( o.attr ) );
            //определение активного слайда
            o.active_slider = $( o.slide + '.' + o.active_class, init_selector);
            //кол-во слайдов
            o.total_slides = $( o.slide, init_selector).length;
            //кнопки
            $( o.filter + ' ' + o.filter_el, init_selector).on( 'click', function(){
                changeSlide( $(this).attr( o.attr ) );
            })
        }
        var changeSlide = function( pos ){
            var old_active_slide =  $( o.slide + '.active', init_selector);
            o.current_pos = parseInt( pos );
            //слайд
            $( o.filter + ' ' + o.filter_el + '[' + o.attr + '=' + o.current_pos + ']', init_selector).addClass( o.active_class ).siblings( o.filter_el ).removeClass( 'active' );

            var new_active_slider = $( o.slide + '[' + o.attr + '=' + o.current_pos + ']', init_selector);
            new_active_slider.addClass( o.active_class );

            old_active_slide.removeClass( o.active_class ).addClass( o.inactive_class );
            setTimeout( function(){
                old_active_slide.removeClass( o.inactive_class );    
            }, 600 )
            
            o.active_slider = new_active_slider;
        }
        
        return this.each(function(){
            init_selector = $(this);
            start();   
        });
    }
    
    $('.carousel-vertical').each( function(){
        $(this).verticalCarousel();
    })
});