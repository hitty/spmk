

function getPendingContent( _element, _url, _params, _cached, _effect, _func_on_success, _func_on_complete ){
    var elem = _element;
    if(typeof(_element) == 'string') elem = $(_element);     
    if(_element.length > 0){
        if(typeof(_params) == 'undefined' || !_params) _params = {ajax: true};
        else{
            if(typeof(_params) == 'string') _params = JSON.parse(_params);
            _params.ajax = true;
        } 
        if(typeof(_cached) == 'undefined') _cached = false;
        $.ajax({
            type: "POST", async: true,
            dataType: 'json', cache: _cached,
            url: _url, data: _params,
            success: function(msg){

                if( typeof(msg.ok)!='undefined' && msg.ok && typeof(_func_on_success) == 'object' || typeof(_func_on_success) == 'function') {
                    _func_on_success.call(this, msg);
                }

                if( typeof(msg)=='object' && typeof(msg.ok)!='undefined' && msg.ok && typeof(msg.html)=='string' && msg.html.length) {

                    if(typeof(_effect) == 'undefined') {
                        elem.fadeOut(100,function(){
                            elem.html(msg.html).fadeIn(200);
                        });
                    } else {
                        $('span.waiting').remove();
                        elem.html(msg.html);
                    } 
                    
                    
                } else {
                    
                }
                return msg;
            },
            error: function(XMLHttpRequest, textStatus, errorThrown){
                
            },
            complete: function(){
                if( typeof( _func_on_complete ) == 'object' || typeof( _func_on_complete ) == 'function') {
                    _func_on_complete();
                }
            }
        });
    }
    return true;
}
function getPending(_url, _params, _func_on_success, _func_on_error){
    if(typeof(_params) == 'undefined' || !_params) _params = {ajax: true};
    else _params.ajax = true;
        $.ajax({
            type: "POST", async: true,
            dataType: 'json', cache: true,
            url: _url, data: _params,
            success: function(msg){
                if( msg.ok == true && typeof _func_on_success == "function") _func_on_success.call(this, msg);
                else if( typeof( msg.error )!='undefined' && typeof _func_on_error == "function") _func_on_error.call(this, msg);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown){
                console.log('XMLHttpRequest: '+XMLHttpRequest+', textStatus: '+textStatus+', errorThrown: '+errorThrown+'; Не возможно выполнить операцию!');
                return false;
            }
        });
}
/* cookie functions */
function setSpmkCookie(name, value, expiredays, path, domain, secure){
    var cookie_string = name+"="+escape(value);
    if(expiredays){
        var exdate=new Date();
        exdate.setDate(exdate.getDate()+expiredays);
        cookie_string += "; expires=" + exdate.toGMTString();
    }
    if(path) cookie_string += "; path="+escape(path);
    if(domain) cookie_string += "; domain="+escape(domain);
    if(secure) cookie_string += "; secure";
    document.cookie = cookie_string;
}
function getSpmkCookie(name){
    var cookie=" "+document.cookie;
    var search=" "+name+"=";
    var setStr=null;
    var offset=0;
    var end=0;
    if(cookie.length>0){
        offset=cookie.indexOf(search);
        if(offset!=-1){
            offset+=search.length;
            end=cookie.indexOf(";",offset);
            if(end==-1) end=cookie.length;
            setStr=unescape(cookie.substring(offset,end));
        }
    }
    return setStr;
}
function getParameterByName(name)
{
    return decodeURI(
            (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
        );
}

$(document).ready(function(){
    $('.thumbs-list').each( function(){
        var _this = $( this );
    
        var gallery = [];
        $(".thumbs-list a" ).each(function (i) {
            console.log( $(this).attr("title") );
            gallery[i] = {
                href: $(this).attr("href"),
                title: $(this).attr("title") ,
                closeSpeed   : 50,
                openSpeed   : 50
            };
            $(this).on("click", function () {
                $.gallery(gallery, {
                    index: i,
                    helpers: {
                        title: {
                            type: 'inside',
                            position: 'top'
                        },
                        thumbs    : {
                            width    : 90,
                            height    : 60
                        }
                        
                    },
                    openSpeed: '50',
                    closeSpeed: '50',
                    autoDimensions: true
                });
                return false;
            }); // on
        }); // each
    }); 
    
    /* span links manage */
    $(document).on("click",  ".external-link",  function(e){
       if($(this).hasClass('disabled')) return true;
       var _link = $(this).data('link');  
       if(_link.indexOf('http://') == -1 && _link.indexOf('https://') == -1) _link = 'http://'+_link; 
       window.open(_link);
      
    });
   
    $(document).on("click", ".internal-link", function(e){
        var _link = "";
        if($(this).hasClass('disabled')) return true;
        if ($(this).data('link')!== undefined)
            _link = $(this).data('link');
        else
            _link = $(this).parent('.star').data('link'); 
        //если указано, открываем в новой вкладке
        if($(this).data('new-tab')!==undefined)
            window.open(_link);
        else
            document.location.href = _link;
        return false; 
    });
    
    $('button.disabled, .button.disabled').on('click', function(){
        return false;
    })
    
    _debug = $('#debug').length > 0;
            
    $('.switcher i').on('click', function(){
        $(this).toggleClass('active');
    })    
    
    $('.popup').each(function(){ $(this).popupWindow(); } )
    $('.calculator').each(function(){ $(this).calculatorSPMK(); } )
    
    
    $('.carousel').each( function(){
        _this = $(this);

        $(this).slick({
            pageDots: false,
            draggable: true,
            wrapAround: true,
            slidesToShow: _this.data('count-1280') > 0 ? _this.data('count-1280') : 1,
            slidesToScroll: _this.data('count-1280') > 0 ? _this.data('count-1280') : 1,
            responsive: [
                {
                  breakpoint: 1280,
                  settings: {
                    slidesToShow: _this.data('count-1280') > 0 ? _this.data('count-1280') : 1,
                    slidesToScroll: _this.data('count-1280') > 0 ? _this.data('count-1280') : 1,
                    infinite: true,
                    pageDots: false,
                  }
                },
                {
                  breakpoint: 1024,
                  settings: {
                    slidesToShow: _this.data('count-1024') > 0 ? _this.data('count-1024') : 1,
                    slidesToScroll: _this.data('count-1024') > 0 ? _this.data('count-1024') : 1,
                    infinite: true,
                    pageDots: false,
                  }
                },
                {
                  breakpoint: 768,
                  settings: {
                    slidesToShow: _this.data('count-768') > 0 ? _this.data('count-768') : 1,
                    slidesToScroll: _this.data('count-768') > 0 ? _this.data('count-768') : 1
                  }
                },
                {
                  breakpoint: 480,
                  settings: {
                    slidesToShow: _this.data('count-480') > 0 ? _this.data('count-480') : 1,
                    slidesToScroll: _this.data('count-480') > 0 ? _this.data('count-480') : 1
                  }
                }
            ]  
        });
    })                    
    
    $('.navigation a').each(function(){
        var t = $(this);
        t.on('click', function(e){
            e.preventDefault();
            if( $(t.attr('href')).length )
                $('html, body').animate({scrollTop: $(t.attr('href')).offset().top - $('header').height() - 30 }, '300');
            return false;
        })
    })
    
    if( $('.sticky-actions').length ){
        var _bound_top = $('.sticky-actions-bound').offset().top;
        var _top = parseInt($(window).scrollTop());
        $(window).scroll(function(){
            stickyActions(  $('.sticky-actions li:last-child').offset().top, _bound_top );
            return false;
        });
    }
    
    
    $('form.ajaxed').each( function(){
        $(this).formSubmit({
            'scroll_to_error': $(this).data('scroll_to_error')
        });
    })
    $('header .burger').on( 'click', function(){
        $('body').toggleClass('menu-top-is-open modal-active')    ;
    })
    
    const btn = $('.to-top-btn');
    const h = $(window).height();
    $(window).scroll(function() {
        if ($(window).scrollTop() > h) {
            btn.addClass('show');
        } else {
            btn.removeClass('show');
        }
    });

    btn.on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({scrollTop:0}, '300');
    });
    
    /* free gift banner */
    var f_g = getSpmkCookie( 'new_buildings_banner_new' );    
    if(  f_g === null && $('.new-buildings-button').length > 0 ){
        setTimeout( function(){
            $('.new-buildings-button').click();
            setSpmkCookie( 'new_buildings_banner_new', 60, '/' );
        }, 1300 )
    }

    /* copy email to clipboard */
    $('.copy-to-buffer').on('click', function(){
        var $this = $(this);
        var value = $this.data('content');
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(value).select();
        document.execCommand("copy");
        $temp.remove();
        $this.addClass('copied');
        setTimeout(function(){
            $this.removeClass('copied');
        }, 2500 )
        counterGoals( $this.data('goal') )
        return false;
    })
    
})          
function stickyActions( _el, _bound ) {
    if( _el > _bound ) $('.sticky-actions').addClass('hidden');
    else $('.sticky-actions').removeClass('hidden')
}
function validateEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}
function popupNewWindow(url, title, w, h) {
  var left = (screen.width/2)-(w/2);
  var top = (screen.height/2)-(h/2);
  return window.open(url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
} 
function makeSuffix(number, titles)  
{  
    cases = [2, 0, 1, 1, 1, 2];  
    return titles[ (number%100>4 && number%100<20)? 2 : cases[(number%10<5)?number%10:5] ];  
}
function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '')
    .replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
    toFixedFix = function(n, prec) {
      var k = Math.pow(10, prec);
      return '' + (Math.round(n * k) / k)
        .toFixed(prec);
    };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
    .split('.');
    if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '')
    .length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1)
      .join('0');
    }
    return s.join(dec);
}

/* nav-top-search */
$(document).on('click', '.nav-top-search-btn', function () {
  if ($('.nav-top-search').hasClass('active')) return true;
  $('.nav-top-search').addClass('active');
  setTimeout(function () {
    $('.nav-top-search-input').trigger('focus');
  }, 400);
  return false;
});
$(document).on('click', function (event) {
  if ($(event.target).closest('.nav-top-search').length) return;
  $('.nav-top-search').removeClass('active');
});
/* nav-top-menu */
$(document).on('click', '.nav-top-menu-open', function (event) {
  event.preventDefault();
  $('html').addClass('nav-top-menu-opened nav-top-menu-is-open');
});
$(document).on('click', '.nav-top-menu-close', function (event) {
  event.preventDefault();
  closeNavTopMenu();
});
$(document).on('click', function (event) {
  if (event.isTrigger) return;
  if ($(event.target).closest('.nav-top-menu-open, .nav-top-menu-close, .nav-top-menu-body').length) return;
  if (!$('html').hasClass('nav-top-menu-opened')) return;
  closeNavTopMenu();
});

function closeNavTopMenu() {
  $('html').removeClass('nav-top-menu-is-open');
  setTimeout(function () {
    $('html').removeClass('nav-top-menu-opened');
  }, 400);
}


