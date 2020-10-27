jQuery(document).ready(function(){
    if(jQuery('.map').length>0){

        ymaps.ready(function(){
            jQuery('.map').each(function(){
                var _this = jQuery(this);
                _lat = _this.data('lat') ;
                _lng = _this.data('lng') ;

                    var markers = typeof markers == 'undefined' ? markers = new ymaps.GeoObjectCollection() : markers ;
                    // Создание экземпляра карты и его привязка к контейнеру с
                    // заданным id ("map").

                    var YMSR = new ymaps.Map( _this.attr('id'), {
                        // При инициализации карты обязательно нужно указать
                        // её центр и коэффициент масштабирования.
                        center: [_lat, _lng],
                        zoom: 16,
                        controls: ['zoomControl']
                    }),
                    placemark = new ymaps.Placemark([_lat, _lng], {
                        hintContent: '',
                    }, {
                        iconLayout: 'default#image',
                    });
                    YMSR.behaviors.disable('scrollZoom');

                    markers.add(placemark);
                    YMSR.geoObjects.add( markers );
            })
        });
    }
});