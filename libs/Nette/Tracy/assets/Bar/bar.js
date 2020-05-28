(function(){function p(a){m(a.getElementsByTagName("script"),function(a){if(!(a.hasAttribute("type")&&"text/javascript"!==a.type&&"application/javascript"!==a.type||a.tracyEvaluated)){var b=a.ownerDocument.createElement("script");b.textContent=a.textContent;k&&b.setAttribute("nonce",k);a.ownerDocument.documentElement.appendChild(b);a.tracyEvaluated=!0}})}function w(a,b){var c=document.documentElement,d,e,g,u,h;b=b||{};var p=function(){v&&(r(a,{left:u+e,top:h+g}),requestAnimationFrame(p))},k=function(c){if(0===
c.buttons)return t(c);d||(b.draggedClass&&a.classList.add(b.draggedClass),b.start&&b.start(c,a),d=!0);u=c.touches?c.touches[0].clientX:c.clientX;h=c.touches?c.touches[0].clientY:c.clientY;return!1},t=function(e){d&&(b.draggedClass&&a.classList.remove(b.draggedClass),b.stop&&b.stop(e,a));v=null;c.removeEventListener("mousemove",k);c.removeEventListener("mouseup",t);c.removeEventListener("touchmove",k);c.removeEventListener("touchend",t);return!1},q=function(f){f.preventDefault();f.stopPropagation();
if(v)return t(f);var n=l(a);u=f.touches?f.touches[0].clientX:f.clientX;h=f.touches?f.touches[0].clientY:f.clientY;e=n.left-u;g=n.top-h;v=!0;d=!1;c.addEventListener("mousemove",k);c.addEventListener("mouseup",t);c.addEventListener("touchmove",k);c.addEventListener("touchend",t);requestAnimationFrame(p);b.start&&b.start(f,a)};m(b.handles,function(a){a.addEventListener("mousedown",q);a.addEventListener("touchstart",q);a.addEventListener("click",function(a){d&&a.stopImmediatePropagation()})})}function q(a){for(var b=
{left:a.offsetLeft,top:a.offsetTop};a=a.offsetParent;)b.left+=a.offsetLeft,b.top+=a.offsetTop;return b}function h(){return{width:document.documentElement.clientWidth,height:"BackCompat"===document.compatMode?window.innerHeight:document.documentElement.clientHeight}}function r(a,b){var c=h();"undefined"!==typeof b.right&&(b.left=c.width-a.offsetWidth-b.right);"undefined"!==typeof b.bottom&&(b.top=c.height-a.offsetHeight-b.bottom);a.style.left=Math.max(0,Math.min(b.left,c.width-a.offsetWidth))+"px";
a.style.top=Math.max(0,Math.min(b.top,c.height-a.offsetHeight))+"px"}function l(a){var b=h();return{left:a.offsetLeft,top:a.offsetTop,right:b.width-a.offsetWidth-a.offsetLeft,bottom:b.height-a.offsetHeight-a.offsetTop,width:a.offsetWidth,height:a.offsetHeight}}function m(a,b){Array.prototype.forEach.call(a,b)}var d=function(a){this.id=a;this.elem=document.getElementById(this.id);this.elem.Tracy=this.elem.Tracy||{}};d.prototype.init=function(){var a=this,b=this.elem;this.init=function(){};b.innerHTML=
b.dataset.tracyContent;Tracy.Dumper.init(this.dumps,b);delete b.dataset.tracyContent;delete this.dumps;p(b);w(b,{handles:b.querySelectorAll("h1"),start:function(){a.is(d.FLOAT)||a.toFloat();a.focus()}});b.addEventListener("mousedown",function(){a.focus()});b.addEventListener("mouseenter",function(){clearTimeout(b.Tracy.displayTimeout)});b.addEventListener("mouseleave",function(){a.blur()});b.addEventListener("mousemove",function(c){c.buttons&&!a.is(d.RESIZED)&&(b.style.width||b.style.height)&&b.classList.add(d.RESIZED)});
b.addEventListener("tracy-toggle",function(){a.reposition()});m(b.querySelectorAll(".tracy-icons a"),function(c){c.addEventListener("click",function(d){clearTimeout(b.Tracy.displayTimeout);"close"===c.rel?a.toPeek():"window"===c.rel&&a.toWindow();d.preventDefault()})});this.is("tracy-ajax")||Tracy.Toggle.persist(b)};d.prototype.is=function(a){return this.elem.classList.contains(a)};d.prototype.focus=function(a){var b=this.elem;this.is(d.WINDOW)?b.Tracy.window.focus():(clearTimeout(b.Tracy.displayTimeout),
b.Tracy.displayTimeout=setTimeout(function(){b.classList.add(d.FOCUSED);b.style.zIndex=Tracy.panelZIndex+d.zIndexCounter++;a&&a()},50))};d.prototype.blur=function(){var a=this.elem;this.is(d.PEEK)&&(clearTimeout(a.Tracy.displayTimeout),a.Tracy.displayTimeout=setTimeout(function(){a.classList.remove(d.FOCUSED)},50))};d.prototype.toFloat=function(){this.elem.classList.remove(d.WINDOW);this.elem.classList.remove(d.PEEK);this.elem.classList.add(d.FLOAT);this.elem.classList.remove(d.RESIZED);this.reposition()};
d.prototype.toPeek=function(){this.elem.classList.remove(d.WINDOW);this.elem.classList.remove(d.FLOAT);this.elem.classList.remove(d.FOCUSED);this.elem.classList.add(d.PEEK);this.elem.style.width="";this.elem.style.height="";this.elem.classList.remove(d.RESIZED)};d.prototype.toWindow=function(){var a=this,b=q(this.elem);b.left+="number"===typeof window.screenLeft?window.screenLeft:window.screenX+10;b.top+="number"===typeof window.screenTop?window.screenTop:window.screenY+50;var c=window.open("",this.id.replace(/-/g,
"_"),"left="+b.left+",top="+b.top+",width="+this.elem.offsetWidth+",height="+this.elem.offsetHeight+",resizable=yes,scrollbars=yes");if(!c)return!1;b=c.document;b.write('<!DOCTYPE html><meta charset="utf-8"><script src="?_tracy_bar=js&amp;XDEBUG_SESSION_STOP=1" onload="Tracy.Dumper.init()" async>\x3c/script><body id="tracy-debug">');b.body.innerHTML='<div class="tracy-panel tracy-mode-window" id="'+this.elem.id+'">'+this.elem.innerHTML+"</div>";p(b.body);this.elem.querySelector("h1")&&(b.title=this.elem.querySelector("h1").textContent);
c.addEventListener("beforeunload",function(){a.toPeek();c.close()});b.addEventListener("keyup",function(a){27!==a.keyCode||a.shiftKey||a.altKey||a.ctrlKey||a.metaKey||c.close()});this.elem.classList.remove(d.FLOAT);this.elem.classList.remove(d.PEEK);this.elem.classList.remove(d.FOCUSED);this.elem.classList.remove(d.RESIZED);this.elem.classList.add(d.WINDOW);this.elem.Tracy.window=c;return!0};d.prototype.reposition=function(a,b){var c=l(this.elem);c.width&&(r(this.elem,{left:c.left+(a||0),top:c.top+
(b||0)}),this.is(d.RESIZED)&&(a=h(),this.elem.style.width=Math.min(a.width,c.width)+"px",this.elem.style.height=Math.min(a.height,c.height)+"px"))};d.prototype.savePosition=function(){var a=l(this.elem);this.is(d.WINDOW)?localStorage.setItem(this.id,JSON.stringify({window:!0})):a.width?localStorage.setItem(this.id,JSON.stringify({right:a.right,bottom:a.bottom,width:a.width,height:a.height,zIndex:this.elem.style.zIndex-Tracy.panelZIndex,resized:this.is(d.RESIZED)})):localStorage.removeItem(this.id)};
d.prototype.restorePosition=function(){var a=JSON.parse(localStorage.getItem(this.id));a?a.window?(this.init(),this.toWindow()||this.toFloat()):this.elem.dataset.tracyContent&&(this.init(),this.toFloat(),a.resized&&(this.elem.classList.add(d.RESIZED),this.elem.style.width=a.width+"px",this.elem.style.height=a.height+"px"),r(this.elem,a),this.elem.style.zIndex=Tracy.panelZIndex+(a.zIndex||1),d.zIndexCounter=Math.max(d.zIndexCounter,a.zIndex||1)+1):this.elem.classList.add(d.PEEK)};d.PEEK="tracy-mode-peek";
d.FLOAT="tracy-mode-float";d.WINDOW="tracy-mode-window";d.FOCUSED="tracy-focused";d.RESIZED="tracy-panel-resized";d.zIndexCounter=1;var g=function(){};g.prototype.init=function(){var a=this;this.id="tracy-debug-bar";this.elem=document.getElementById(this.id);w(this.elem,{handles:this.elem.querySelectorAll("li:first-child"),draggedClass:"tracy-dragged",stop:function(){a.savePosition()}});this.elem.addEventListener("mousedown",function(a){a.preventDefault()});this.initTabs(this.elem);this.restorePosition();
(new MutationObserver(function(){a.restorePosition()})).observe(this.elem,{childList:!0,characterData:!0,subtree:!0})};g.prototype.initTabs=function(a){var b=this;m(a.getElementsByTagName("a"),function(c){c.addEventListener("click",function(a){if("close"===c.rel)b.close();else if(c.rel){var f=e.panels[c.rel];f.init();a.shiftKey?(f.toFloat(),f.toWindow()):f.is(d.FLOAT)?f.toPeek():(f.toFloat(),f.reposition(-Math.round(100*Math.random())-20,(Math.round(100*Math.random())+20)*(b.isAtTop()?1:-1)))}a.preventDefault()});
c.addEventListener("mouseenter",function(f){if(!f.buttons&&c.rel&&"close"!==c.rel&&!a.classList.contains("tracy-dragged")){var n=e.panels[c.rel];n.focus(function(){if(n.is(d.PEEK)){n.init();var a=l(n.elem);r(n.elem,{left:q(c).left+l(c).width+4-a.width,top:b.isAtTop()?q(b.elem).top+l(b.elem).height+4:q(b.elem).top-a.height-4})}})}});c.addEventListener("mouseleave",function(){c.rel&&"close"!==c.rel&&!a.classList.contains("tracy-dragged")&&e.panels[c.rel].blur()})});this.autoHideLabels()};g.prototype.autoHideLabels=
function(){var a=h().width;m(this.elem.children,function(b){for(var c=b.querySelectorAll(".tracy-label"),d=c.length-1;0<=d&&b.clientWidth>=a;d--)c.item(d).hidden=!0})};g.prototype.close=function(){document.getElementById("tracy-debug").style.display="none"};g.prototype.reposition=function(a,b){var c=l(this.elem);c.width&&(r(this.elem,{left:c.left+(a||0),top:c.top+(b||0)}),this.savePosition())};g.prototype.savePosition=function(){var a=l(this.elem);a.width&&localStorage.setItem(this.id,JSON.stringify(this.isAtTop()?
{right:a.right,top:a.top}:{right:a.right,bottom:a.bottom}))};g.prototype.restorePosition=function(){var a=JSON.parse(localStorage.getItem(this.id));r(this.elem,a||{right:0,bottom:0});this.savePosition()};g.prototype.isAtTop=function(){var a=l(this.elem);return 100>a.top&&a.bottom>a.top};var e=function(){};e.init=function(a,b){if(!document.documentElement.dataset)throw Error("Tracy requires IE 11+");e.layer=document.createElement("div");e.layer.setAttribute("id","tracy-debug");e.layer.innerHTML=a;
document.documentElement.appendChild(e.layer);p(e.layer);Tracy.Dumper.init();e.layer.style.display="block";e.bar.init();m(document.querySelectorAll(".tracy-panel"),function(a){e.panels[a.id]=new d(a.id);e.panels[a.id].dumps=b;e.panels[a.id].restorePosition()});e.captureWindow();e.captureAjax()};e.loadAjax=function(a,b){m(e.layer.querySelectorAll(".tracy-panel.tracy-ajax"),function(a){e.panels[a.id].savePosition();delete e.panels[a.id];a.parentNode.removeChild(a)});var c=document.getElementById("tracy-ajax-bar");
c&&c.parentNode.removeChild(c);e.layer.insertAdjacentHTML("beforeend",a);p(e.layer);c=document.getElementById("tracy-ajax-bar");e.bar.elem.appendChild(c);m(document.querySelectorAll(".tracy-panel"),function(a){e.panels[a.id]||(e.panels[a.id]=new d(a.id),e.panels[a.id].dumps=b,e.panels[a.id].restorePosition())});e.bar.initTabs(c)};e.captureWindow=function(){var a=h();window.addEventListener("resize",function(){var b=h();e.bar.reposition(b.width-a.width,b.height-a.height);for(var c in e.panels)e.panels[c].reposition(b.width-
a.width,b.height-a.height);a=b});window.addEventListener("unload",function(){for(var a in e.panels)e.panels[a].savePosition()})};e.captureAjax=function(){var a=Tracy.getAjaxHeader();if(a){var b=XMLHttpRequest.prototype.open;XMLHttpRequest.prototype.open=function(){b.apply(this,arguments);if(!1!==window.TracyAutoRefresh&&0>=arguments[1].indexOf("//")||0===arguments[1].indexOf(location.origin+"/"))this.setRequestHeader("X-Tracy-Ajax",a),this.addEventListener("load",function(){this.getAllResponseHeaders().match(/^X-Tracy-Ajax: 1/mi)&&
e.loadScript("?_tracy_bar=content-ajax."+a+"&XDEBUG_SESSION_STOP=1&v="+Math.random())})};if(window.fetch){var c=window.fetch;window.fetch=function(b,d){d=d||{};d.headers=new Headers(d.headers||{});var f=b instanceof Request?b.url:b;return!1!==window.TracyAutoRefresh&&0>=f.indexOf("//")||0===f.indexOf(location.origin+"/")?(d.headers.set("X-Tracy-Ajax",a),d.credentials=b instanceof Request&&b.credentials||d.credentials||"same-origin",c(b,d).then(function(b){b.headers.has("X-Tracy-Ajax")&&"1"===b.headers.get("X-Tracy-Ajax")[0]&&
e.loadScript("?_tracy_bar=content-ajax."+a+"&XDEBUG_SESSION_STOP=1&v="+Math.random());return b})):c(b,d)}}}};e.loadScript=function(a){e.scriptElem&&e.scriptElem.parentNode.removeChild(e.scriptElem);e.scriptElem=document.createElement("script");e.scriptElem.src=a;k&&e.scriptElem.setAttribute("nonce",k);document.documentElement.appendChild(e.scriptElem)};var v;if(document.currentScript)var k=document.currentScript.getAttribute("nonce")||document.currentScript.nonce,x=document.currentScript.dataset.id;
Tracy=window.Tracy||{};Tracy.panelZIndex=Tracy.panelZIndex||2E4;Tracy.DebugPanel=d;Tracy.DebugBar=g;Tracy.Debug=e;Tracy.getAjaxHeader=function(){return x};e.bar=new g;e.panels={}})();