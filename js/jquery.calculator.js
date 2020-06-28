if($)(function(window, document, $, undefined){
    $.fn.calculatorSPMK = function(opts) {
        let defaults = {
            current_step          : 1, 
            step_button           : '.calculator-action-button',        /* след. шаг в форме */

            map                   : '.calculator-map',                  /* карта в svg */
            map_path              : '.calculator-map-path',             /* область карты */
            region_title          : '.calculator-region-title',         /* всплывашка с названием региона */
            region_title_target   : '.calculator-region-title-target__value', /* контейнер для хранения выбранного значения региона */
                                                                        
            building_type_parent  : '.calculator-building-types',       /* контейнер для предка выбора типа здания */
            building_type         : '.calculator-building-type',        /* контейнер для выбора типа здания */
            
            building_subtype_parent: '.calculator-building-subtypes',   /* контейнер для предка выбора назначения здания */
            building_subtype      : '.calculator-building-subtype',     /* контейнер для выбора назначения здания */
            
            form_input            : '.calculator-form-params__input',   /* элементы формы ввода площади и тонажа*/
            
            result_cost           : '.calculator-results__cost',        /* контейнеер для динамического расчета стоимости МК */
            result_delivery       : '.calculator-results__delivery',    /* контейнеер для динамического расчета стоимости МК */
            
            total_parent          : '.calculator-total__row',           /* контейнеер для итоговой таблицы выбранных параметров  */
            total_value           : '.calculator-total__value',         /* контейнеер для значения итоговой таблицы  */
            
            navigation_step       : '.calculator-navigation-step',      /* фрейм навигации */
            form_step             : '.calculator-form-step',            /* фрейм формы */
            active_statement      : 'active',                           /* класс активного состояния элемента */
            form_values           : {},                                 /* массив значений всех элементов */
            types                 : {},                                 /* массив значений стоимости и сроков доставки */
            error_template        : '<span class="error"></span>',
            onInit                : function(){},
            onSuccess             : function(data){}
        }
        let o = $.extend(defaults, opts || {});
        let init_selector = null;                           /* Элемент, к которому назначен вызов */
        
        /* функция стартовой инициализации */
        let start = function(){
            o.types =  [
                {
                    title: 'Цех',
                    cost : 74441,
                    days  : '21 + m/30',
                },
                {
                    title: 'Склад',
                    cost : 70051,
                    days  : '21 + m/35',
                },
                {
                    title: 'Другое ( производственные )',
                    cost : 74441,
                    days  : '21 + m/30',
                },
                {
                    title: 'Хранилище',
                    cost : 70051,
                    days  : '21 + m/35',
                },
                {
                    title: 'Ферма животные',
                    cost : 73180,
                    days  : '21 + m/40',
                },
                {
                    title: 'Ферма птицы',
                    cost : 73180,
                    days  : '21 + m/40',
                },
                {
                    title: 'Другое ( с/х )',
                    cost : 73180,
                    days  : '21 + m/40',
                },
                {
                    title: 'Торговый центр',
                    cost : 78110,
                    days  : '21 + m/25',
                },
                {
                    title: 'МФЦ',
                    cost : 78110,
                    days  : '21 + m/25',
                },
                {
                    title: 'Офисное здание',
                    cost : 79996,
                    days  : '21 + m/25',
                },
                {
                    title: 'Паркинг',
                    cost : 80000,
                    days  : '21 + m/25',
                }
            ]
            // переключение шагов кнопками
            $( o.step_button, init_selector ).on( 'click', function(){
                if( $(this).hasClass('disabled') ) return false;
                $( o.navigation_step + '[data-step=' + o.current_step + '] ', init_selector ).attr( 'data-complete', true ); //шаг заполнен
                ++o.current_step;
                switchSteps( );
            })
            
            // переключение шагов меню навигации
            $( init_selector ).on( 'click', o.navigation_step + '.' + o.active_statement + ',' + o.navigation_step + '[data-complete=true]', function(){
                o.current_step = $(this).data('step');
                switchSteps( );
            })
            
            ///////////////////////////
            // шаг 1: выбор региона  //
            ///////////////////////////
            // подсветка региона при наведении
            $(o.map_path, init_selector)
                .mouseenter(function() {
                    let region_title = $( o.region_title, init_selector );
                    region_title.text( $(this).data('region') );
                    region_title.css({
                        'left' : window.event.clientX - region_title.width() / 2,
                        'top' : window.event.clientY - 40,
                        'opacity': 1
                    })

                })
                .mouseleave( function(){
                    $( o.region_title, init_selector ).css({
                        'opacity': 0,
                        'left': 0,
                        'top': 0
                    })
                } )
            // выбор региона
            $(o.map_path, init_selector).on('click', function(){
                let _this = $(this);
                _this.addClass( o.active_statement ).siblings( ).removeClass( o.active_statement ); //
                let _region = _this.data('region');
                o.form_values['region'] = _region; // 
                $(o.region_title_target, init_selector).text( _region ) // текстовый вывод региона
                scrollTo( 'bottom' ); // прокрутка страницы к след.шагу
                stepComplete( ) // информация "шага" заполнена
            })             
            
            ///////////////////////////////
            // шаг 2: выбор типа здания  //
            ///////////////////////////////
            $(o.building_type, init_selector).on('click', function(){
                let _this = $(this);
                let _building_type = _this.data('type');
                _this.addClass( o.active_statement ).siblings().removeClass( o.active_statement );  // выделением активным типа здания
                $( o.building_subtype_parent + '[data-type=' + _building_type + ']', init_selector ).addClass( o.active_statement ).siblings().removeClass( o.active_statement );  // выделение активным назначения здания
                o.form_values['type'] = _this.text();
                stepComplete( ) // информация "шага" заполнена
            })

            ///////////////////////////////
            // шаг 3: выбор назначения   //
            ///////////////////////////////
            $(o.building_subtype, init_selector).on('click', function(){
                let _this = $(this);
                _this.addClass( o.active_statement ).siblings().removeClass( o.active_statement );  // выделением активным назначения здания
                o.form_values['subtype'] = _this.text();
                o.form_values['subtype_value'] = _this.data('subtype');
                stepComplete( ) // информация "шага" заполнена
            })

            ///////////////////////////////
            // шаг 4: обработка формы ввода площади и  тонажа
            ///////////////////////////////
            $(o.form_input, init_selector).on('keyup', function(){
                let _check = true;
                $(o.form_input, init_selector).each(function(){
                    let _this = $(this);
                    let _value = parseInt( _this.val() );
                    o.form_values[ _this.attr('name') ] = _value;
                    if( !( _value > 0 ) ) _check = false
                })
                if( _check === true ) stepComplete( )
            })

        }
        
        // прокрутка страницы к след.шагу
        let scrollTo = ( place ) => {
            if( place == 'bottom' ) $('body,html').animate({ scrollTop: init_selector.offset().top + init_selector.height() - $(window).height() }, 800);
            else $('body,html').animate({ scrollTop: init_selector.offset().top - 80 }, 500);
        }
        
        // информация "шага" заполнена
        let stepComplete = ( ) => {
            $( o.form_step + '[data-step=' + o.current_step + '] ' + o.step_button, init_selector ).removeClass('disabled'); //активные кнопки шага
            calculateResults ();
        }
        
        // переключение шагов
        let switchSteps = ( ) => {
            $( o.form_step + '[data-step=' + o.current_step + ']' + ', ' + o.navigation_step + '[data-step=' + o.current_step + ']' , init_selector ).addClass( o.active_statement ).siblings( ). removeClass( o.active_statement ); //активные кнопки шага
            scrollTo( 'top' );
        }

        //Заполнение рассчитанной информации        
        let calculateResults = ( ) => {
            //выбранные поля
            for (let [key, value] of Object.entries(o.form_values)) {
                $( o.total_value, $( o.total_parent + '.' + key, init_selector ) ).text( value ) ;
            }
            //расчет данных 
            if( o.form_values['subtype_value'] > 0 && o.form_values['weight'] > 0 ) {
                //стоимость 
                $( o.result_cost, init_selector ).text( o.types[ o.form_values['subtype_value'] -1 ][ 'cost' ] * o.form_values['weight'] )
                //срок доставки 
                let m = o.form_values['weight'];
                $( o.result_delivery, init_selector ).text( parseInt( eval( o.types[ o.form_values['subtype_value'] - 1 ]['days'] ) ) )
            }
        }

        return this.each(function(){
            init_selector = $(this);
            start();   
        });
    }
})(window, document, $);            
    