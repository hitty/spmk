!function(){"use strict";var e=tinymce.util.Tools.resolve("tinymce.PluginManager"),t=tinymce.util.Tools.resolve("tinymce.dom.DOMUtils"),n=tinymce.util.Tools.resolve("tinymce.EditorManager"),r=tinymce.util.Tools.resolve("tinymce.Env"),i=tinymce.util.Tools.resolve("tinymce.util.Tools"),c=function(e){return e.getParam("importcss_merge_classes")},o=function(e){return e.getParam("importcss_exclusive")},s=function(e){return e.getParam("importcss_selector_converter")},u=function(e){return e.getParam("importcss_selector_filter")},l=function(e){return e.getParam("importcss_groups")},a=function(e){return e.getParam("importcss_append")},f=function(e){return e.getParam("importcss_file_filter")},m=function(e){var t=r.cacheSuffix;return"string"==typeof e&&(e=e.replace("?"+t,"").replace("&"+t,"")),e},g=function(e,t){var r=e.settings,i=!1!==r.skin&&(r.skin||"lightgray");return!!i&&t===(r.skin_url?e.documentBaseURI.toAbsolute(r.skin_url):n.baseURL+"/skins/"+i)+"/content"+(e.inline?".inline":"")+".min.css"},p=function(e){return"string"==typeof e?function(t){return-1!==t.indexOf(e)}:e instanceof RegExp?function(t){return e.test(t)}:e},v=function(e,t,n){var r=[],c={};i.each(e.contentCSS,function(e){c[e]=!0}),n||(n=function(e,t){return t||c[e]});try{i.each(t.styleSheets,function(t){!function c(t,o){var s,u=t.href;if((u=m(u))&&n(u,o)&&!g(e,u)){i.each(t.imports,function(e){c(e,!0)});try{s=t.cssRules||t.rules}catch(l){}i.each(s,function(e){e.styleSheet?c(e.styleSheet,!0):e.selectorText&&i.each(e.selectorText.split(","),function(e){r.push(i.trim(e))})})}}(t)})}catch(o){}return r},h=function(e,t){var n,r=/^(?:([a-z0-9\-_]+))?(\.[a-z0-9_\-\.]+)$/i.exec(t);if(r){var o=r[1],s=r[2].substr(1).split(".").join(" "),u=i.makeMap("a,img");return r[1]?(n={title:t},e.schema.getTextBlockElements()[o]?n.block=o:e.schema.getBlockElements()[o]||u[o.toLowerCase()]?n.selector=o:n.inline=o):r[2]&&(n={inline:"span",title:t.substr(1),classes:s}),!1!==c(e)?n.classes=s:n.attributes={"class":s},n}},d=function(e,t){return null===t||!1!==o(e)},y=h,_=function(e){e.on("renderFormatsMenu",function(n){var r,c={},o=p(u(e)),m=n.control,g=(r=l(e),i.map(r,function(e){return i.extend({},e,{original:e,selectors:{},filter:p(e.filter),item:{text:e.title,menu:[]}})})),y=function(n,r){if(_=n,T=c,!(d(e,x=r)?_ in T:_ in x.selectors)){p=n,y=c,d(e,v=r)?y[p]=!0:v.selectors[p]=!0;var o=(l=e,a=e.plugins.importcss,f=n,((g=r)&&g.selector_converter?g.selector_converter:s(l)?s(l):function(){return h(l,f)}).call(a,f,g));if(o){var u=o.name||t.DOM.uniqueId();return e.formatter.register(u,o),i.extend({},m.settings.itemDefaults,{text:o.title,format:u})}}var l,a,f,g,p,v,y,_,x,T;return null};a(e)||m.items().remove(),i.each(v(e,n.doc||e.getDoc(),p(f(e))),function(e){if(-1===e.indexOf(".mce-")&&(!o||o(e))){var t=(r=g,c=e,i.grep(r,function(e){return!e.filter||e.filter(c)}));if(t.length>0)i.each(t,function(t){var n=y(e,t);n&&t.item.menu.push(n)});else{var n=y(e,null);n&&m.add(n)}}var r,c}),i.each(g,function(e){e.item.menu.length>0&&m.add(e.item)}),n.control.renderNew()})},x=function(e){return{convertSelectorToFormat:function(t){return y(e,t)}}};e.add("importcss",function(e){return _(e),x(e)})}();