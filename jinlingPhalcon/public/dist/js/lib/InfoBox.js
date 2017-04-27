var BMapLib=window.BMapLib=BMapLib||{},INFOBOX_AT_TOP=1,INFOBOX_AT_RIGHT=2,INFOBOX_AT_BOTTOM=3,INFOBOX_AT_LEFT=4;!function(){var t,e=t=e||{version:"1.5.0"};e.guid="$BAIDU$",function(){window[e.guid]=window[e.guid]||{},e.lang=e.lang||{},e.lang.isString=function(t){return"[object String]"==Object.prototype.toString.call(t)},e.lang.isFunction=function(t){return"[object Function]"==Object.prototype.toString.call(t)},e.lang.Event=function(t,e){this.type=t,this.returnValue=!0,this.target=e||null,this.currentTarget=null},e.object=e.object||{},e.extend=e.object.extend=function(t,e){for(var n in e)e.hasOwnProperty(n)&&(t[n]=e[n]);return t},e.event=e.event||{},e.event._listeners=e.event._listeners||[],e.dom=e.dom||{},e.dom._g=function(t){return e.lang.isString(t)?document.getElementById(t):t},e._g=e.dom._g,e.event.on=function(t,n,i){n=n.replace(/^on/i,""),t=e.dom._g(t);var o,s=function(e){i.call(t,e)},r=e.event._listeners,a=e.event._eventFilter,h=n;return n=n.toLowerCase(),a&&a[n]&&(o=a[n](t,n,s),h=o.type,s=o.listener),t.addEventListener?t.addEventListener(h,s,!1):t.attachEvent&&t.attachEvent("on"+h,s),r[r.length]=[t,n,i,s,h],t},e.on=e.event.on,e.event.un=function(t,n,i){t=e.dom._g(t),n=n.replace(/^on/i,"").toLowerCase();for(var o,s,r,a=e.event._listeners,h=a.length,_=!i;h--;)o=a[h],o[1]!==n||o[0]!==t||!_&&o[2]!==i||(s=o[4],r=o[3],t.removeEventListener?t.removeEventListener(s,r,!1):t.detachEvent&&t.detachEvent("on"+s,r),a.splice(h,1));return t},e.un=e.event.un,e.dom.g=function(t){return"string"==typeof t||t instanceof String?document.getElementById(t):t&&t.nodeName&&(1==t.nodeType||9==t.nodeType)?t:null},e.g=e.G=e.dom.g,e.dom._styleFixer=e.dom._styleFixer||{},e.dom._styleFilter=e.dom._styleFilter||[],e.dom._styleFilter.filter=function(t,n,i){for(var o,s=0,r=e.dom._styleFilter;o=r[s];s++)(o=o[i])&&(n=o(t,n));return n},e.string=e.string||{},e.string.toCamelCase=function(t){return t.indexOf("-")<0&&t.indexOf("_")<0?t:t.replace(/[-_][^-_]/g,function(t){return t.charAt(1).toUpperCase()})},e.dom.setStyle=function(t,n,i){var o,s=e.dom;return t=s.g(t),n=e.string.toCamelCase(n),(o=s._styleFilter)&&(i=o.filter(n,i,"set")),o=s._styleFixer[n],o&&o.set?o.set(t,i):t.style[o||n]=i,t},e.setStyle=e.dom.setStyle,e.dom.setStyles=function(t,n){t=e.dom.g(t);for(var i in n)e.dom.setStyle(t,i,n[i]);return t},e.setStyles=e.dom.setStyles,e.browser=e.browser||{},e.browser.ie=e.ie=/msie (\d+\.\d+)/i.test(navigator.userAgent)?document.documentMode||+RegExp.$1:void 0,e.dom._NAME_ATTRS=function(){var t={cellpadding:"cellPadding",cellspacing:"cellSpacing",colspan:"colSpan",rowspan:"rowSpan",valign:"vAlign",usemap:"useMap",frameborder:"frameBorder"};return e.browser.ie<8?(t["for"]="htmlFor",t["class"]="className"):(t.htmlFor="for",t.className="class"),t}(),e.dom.setAttr=function(t,n,i){return t=e.dom.g(t),"style"==n?t.style.cssText=i:(n=e.dom._NAME_ATTRS[n]||n,t.setAttribute(n,i)),t},e.setAttr=e.dom.setAttr,e.dom.setAttrs=function(t,n){t=e.dom.g(t);for(var i in n)e.dom.setAttr(t,i,n[i]);return t},e.setAttrs=e.dom.setAttrs,e.dom.create=function(t,n){var i=document.createElement(t),o=n||{};return e.dom.setAttrs(i,o)},t.undope=!0}();var n=BMapLib.InfoBox=function(t,e,n){this._content=e||"",this._isOpen=!1,this._map=t,this._opts=n=n||{},this._opts.offset=n.offset||new BMap.Size(0,0),this._opts.boxClass=n.boxClass||"infoBox",this._opts.boxStyle=n.boxStyle||{},this._opts.closeIconMargin=n.closeIconMargin||"2px",this._opts.closeIconUrl=n.closeIconUrl||"close.png",this._opts.enableAutoPan=!!n.enableAutoPan,this._opts.align=n.align||INFOBOX_AT_TOP};n.prototype=new BMap.Overlay,n.prototype.initialize=function(t){var n=this,i=this._div=e.dom.create("div",{"class":this._opts.boxClass});e.dom.setStyles(i,this._opts.boxStyle),i.style.position="absolute",this._setContent(this._content);var o=t.getPanes().floatPane;return o.style.width="auto",o.appendChild(i),this._getInfoBoxSize(),e.event.on(i,"onmousedown",function(t){n._stopBubble(t)}),e.event.on(i,"onmouseover",function(t){n._stopBubble(t)}),e.event.on(i,"click",function(t){n._stopBubble(t)}),e.event.on(i,"dblclick",function(t){n._stopBubble(t)}),i},n.prototype.draw=function(){this._isOpen&&this._adjustPosition(this._point)},n.prototype.open=function(t){var e,n=this;this._isOpen||(this._map.addOverlay(this),this._isOpen=!0,setTimeout(function(){n._dispatchEvent(n,"open",{point:n._point})},10)),t instanceof BMap.Point?(e=t,this._removeMarkerEvt()):t instanceof BMap.Marker&&(this._marker&&this._removeMarkerEvt(),e=t.getPosition(),this._marker=t,!this._markerDragend&&this._marker.addEventListener("dragend",this._markerDragend=function(t){n._point=t.point,n._adjustPosition(n._point),n._panBox(),n.show()}),!this._markerDragging&&this._marker.addEventListener("dragging",this._markerDragging=function(){n.hide(),n._point=n._marker.getPosition(),n._adjustPosition(n._point)})),this.show(),this._point=e,this._panBox(),this._adjustPosition(this._point)},n.prototype.close=function(){this._isOpen&&(this._map.removeOverlay(this),this._isOpen=!1,this._dispatchEvent(this,"close",{point:this._point}))},n.prototype.enableAutoPan=function(){this._opts.enableAutoPan=!0},n.prototype.disableAutoPan=function(){this._opts.enableAutoPan=!1},n.prototype.setContent=function(t){this._setContent(t),this._getInfoBoxSize(),this._adjustPosition(this._point)},n.prototype.setPosition=function(t){this._point=t,this._adjustPosition(t),this._removeMarkerEvt()},n.prototype.getPosition=function(){return this._point},n.prototype.getOffset=function(){return this._opts.offset},n.prototype.remove=function(){var t=this;this.domElement&&this.domElement.parentNode&&(e.event.un(this._div.firstChild,"click",t._closeHandler()),this.domElement.parentNode.removeChild(this.domElement)),this.domElement=null,this._isOpen=!1,this.dispatchEvent("onremove")},e.object.extend(n.prototype,{_getCloseIcon:function(){var t="";return t},_setContent:function(t){if(this._div){var e=this._getCloseIcon();"undefined"==typeof t.nodeType?this._div.innerHTML=e+t:(this._div.innerHTML=e,this._div.appendChild(t)),this._content=t,this._addEventToClose()}},_adjustPosition:function(t){var e=this._getPointPosition(t),n=this._marker&&this._marker.getIcon();switch(this._opts.align){case INFOBOX_AT_TOP:this._marker?this._div.style.bottom=-(e.y-this._opts.offset.height-n.anchor.height+n.infoWindowAnchor.height)-this._marker.getOffset().height+2+"px":this._div.style.bottom=-(e.y-this._opts.offset.height)+"px";break;case INFOBOX_AT_BOTTOM:this._marker?this._div.style.top=e.y+this._opts.offset.height-n.anchor.height+n.infoWindowAnchor.height+this._marker.getOffset().height+"px":this._div.style.top=e.y+this._opts.offset.height+"px"}this._marker?this._div.style.left=e.x-n.anchor.width+this._marker.getOffset().width+n.infoWindowAnchor.width-this._boxWidth/2+"px":this._div.style.left=e.x-this._boxWidth/2+"px"},_getPointPosition:function(t){return this._pointPosition=this._map.pointToOverlayPixel(t),this._pointPosition},_getInfoBoxSize:function(){this._boxWidth=parseInt(this._div.offsetWidth,10),this._boxHeight=parseInt(this._div.offsetHeight,10)},_addEventToClose:function(){var t=this;e.event.on(this._div.firstChild,"click",t._closeHandler()),this._hasBindEventClose=!0},_closeHandler:function(){var t=this;return function(e){t.close()}},_stopBubble:function(t){t&&t.stopPropagation?t.stopPropagation():window.event.cancelBubble=!0},_panBox:function(){if(this._opts.enableAutoPan){var t=parseInt(this._map.getContainer().offsetHeight,10),e=parseInt(this._map.getContainer().offsetWidth,10),n=this._boxHeight,i=this._boxWidth;if(!(n>=t||i>=e)){this._map.getBounds().containsPoint(this._point)||this._map.setCenter(this._point);var o,s,r,a=this._map.pointToPixel(this._point),h=i/2-a.x,_=i/2+a.x-e;if(this._marker)var p=this._marker.getIcon();switch(this._opts.align){case INFOBOX_AT_TOP:var d=this._marker?p.anchor.height+this._marker.getOffset().height-p.infoWindowAnchor.height:0;o=n-a.y+this._opts.offset.height+d+2;break;case INFOBOX_AT_BOTTOM:var d=this._marker?-p.anchor.height+p.infoWindowAnchor.height+this._marker.getOffset().height+this._opts.offset.height:0;s=n+a.y-t+d+4}panX=h>0?h:_>0?-_:0,r=o>0?o:s>0?-s:0,this._map.panBy(panX,r)}}},_removeMarkerEvt:function(){this._markerDragend&&this._marker.removeEventListener("dragend",this._markerDragend),this._markerDragging&&this._marker.removeEventListener("dragging",this._markerDragging),this._markerDragend=this._markerDragging=null},_dispatchEvent:function(t,n,i){0!=n.indexOf("on")&&(n="on"+n);var o=new e.lang.Event(n);if(i)for(var s in i)o[s]=i[s];t.dispatchEvent(o)}})}();