jQuery(document).ready(function(){
    ymaps.ready(function () {
        var _element = jQuery('#map-box');
        var _lat = _element.data('lat');
        var _lng = _element.data('lng');
        
        myMap = new ymaps.Map('map-box', {
                center: [_lat, _lng], 
                zoom: 14,
                controls: []
        });
        

        // Создаем метку и задаем изображение для ее иконки
        placemark = new ymaps.Placemark([_lat, _lng], {
            iconCaption: _element.data('address')
        }, {
            preset: 'islands#redCircleDotIconWithCaption'
        });
        myMap.geoObjects.add(placemark); 
        myMap.behaviors.disable('scrollZoom');  
    });
});