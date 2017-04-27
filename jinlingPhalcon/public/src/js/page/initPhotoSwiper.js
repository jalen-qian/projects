(function() {

    var initPhotoSwipeFromDOM = function(gallerySelector,option) {

        var parseThumbnailElements = function(el) {
            var thumbElements = returnSuitableItems(el),
                numNodes = thumbElements.length,
                items = [],
                el,
                childElements,
                thumbnailEl,
                size,
                item;



            for (var i = 0; i < numNodes; i++) {
                el = thumbElements[i];

                // include only element nodes 
                if (el.nodeType !== 1) {
                    continue;
                }

                childElements = el.children;

                size = el.getAttribute('data-size').split('x');

                // create slide object
                item = {
                    src: el.getAttribute('href'),
                    w: parseInt(size[0], 10),
                    h: parseInt(size[1], 10),
                    author: el.getAttribute('data-author')
                };

                item.el = el; // save link to element for getThumbBoundsFn

                if (childElements.length > 0) {
                    item.msrc = childElements[0].getAttribute('src'); // thumbnail url
                    if (childElements.length > 1) {
                        item.title = childElements[1].innerHTML; // caption (contents of figure)
                    }
                }


                var mediumSrc = el.getAttribute('data-med');
                if (mediumSrc) {
                    size = el.getAttribute('data-med-size').split('x');
                    // "medium-sized" image
                    item.m = {
                        src: mediumSrc,
                        w: parseInt(size[0], 10),
                        h: parseInt(size[1], 10)
                    };
                }
                // original image
                item.o = {
                    src: item.src,
                    w: item.w,
                    h: item.h
                };

                items.push(item);
            }

            return items;
        };

        // find nearest parent element
        var closest = function closest(el, fn) {
            return el && (fn(el) ? el : closest(el.parentNode, fn));
        };

        var onThumbnailsClick = function(e) {
            e = e || window.event;
            e.preventDefault ? e.preventDefault() : e.returnValue = false;

            var eTarget = e.target || e.srcElement;

            var clickedListItem = closest(eTarget, function(el) {
                return el.tagName === 'A';
            });

            if (!clickedListItem) {
                return;
            }

            var clickedGallery = returnSuitableParent(clickedListItem.parentNode);

            // var J_items = clickedGallery.querySelectorAll(".J_PhotoSwpie");
            // if(J_items.length>0){
            //  clickedGallery = closest(J_items[0], function(el) {
            //      return el.className  === 'waterfall J_Gllery';
            //  });
            // }

            var childNodes = returnSuitableItems(clickedGallery),
                numChildNodes = childNodes.length,
                nodeIndex = 0,
                index;
            //console.log(childNodes);
            for (var i = 0; i < numChildNodes; i++) {
                if (childNodes[i].nodeType !== 1) {
                    continue;
                }

                if (childNodes[i] === clickedListItem) {
                    index = nodeIndex;
                    break;
                }
                nodeIndex++;
            }

            if (index >= 0) {
                openPhotoSwipe(index, clickedGallery);
            }
            return false;
        };

        var returnSuitableParent = function(el) {
            var J_items = el.querySelectorAll(".J_PhotoSwpie");
            if (J_items.length > 0) {
                el = closest(J_items[0], function(el) {
                    return hasClass(el, "J_Gllery");;
                });
            }
            return el;
        }

        var returnSuitableItems = function(parent) {
            if (hasClass(parent, "J_Gllery")) {
                return parent.querySelectorAll(".J_PhotoSwpie");
            }
            return parent.childNodes;
        }

        var hasClass = function(el, className) {
            var str = el.className ? el.className : "",
                arr = str.split(" ");
            return str.split(" ").indexOf(className) != -1;
        }

        var photoswipeParseHash = function() {
            var hash = window.location.hash.substring(1),
                params = {};

            if (hash.length < 5) { // pid=1
                return params;
            }

            var vars = hash.split('&');
            for (var i = 0; i < vars.length; i++) {
                if (!vars[i]) {
                    continue;
                }
                var pair = vars[i].split('=');
                if (pair.length < 2) {
                    continue;
                }
                params[pair[0]] = pair[1];
            }

            if (params.gid) {
                params.gid = parseInt(params.gid, 10);
            }

            return params;
        };

        var openPhotoSwipe = function(index, galleryElement, disableAnimation, fromURL) {
            var pswpElement = document.querySelectorAll('.pswp')[0],
                gallery,
                options,
                items;

            items = parseThumbnailElements(galleryElement);
            window.items = items;
            //console.log(items)
            // define options (if needed)
            options = {
                captionAndToolbarOpacity : 0.6,
                galleryUID: galleryElement.getAttribute('data-pswp-uid'),

                getThumbBoundsFn: function(index) {
                    // See Options->getThumbBoundsFn section of docs for more info
                    var thumbnail = items[index].el.children[0],
                        pageYScroll = window.pageYOffset || document.documentElement.scrollTop,
                        rect = thumbnail.getBoundingClientRect();

                    return {
                        x: rect.left,
                        y: rect.top + pageYScroll,
                        w: rect.width
                    };
                },

                addCaptionHTMLFn: function(item, captionEl, isFake) {
                    if (!item.title) {
                        captionEl.children[0].innerText = '';
                        return false;
                    }
                    captionEl.children[0].innerHTML = item.title + '<br/><small>Photo: ' + item.author + '</small>';
                    return true;
                },
                getDoubleTapZoom: function(isMouseClick, item) {

                    // isMouseClick          - true if mouse, false if double-tap
                    // item                  - slide object that is zoomed, usually current
                    // item.initialZoomLevel - initial scale ratio of image
                    //                         e.g. if viewport is 700px and image is 1400px,
                    //                              initialZoomLevel will be 0.5

                    if (isMouseClick) {

                        // is mouse click on image or zoom icon

                        // zoom to original
                        return 2;

                        // e.g. for 1400px image:
                        // 0.5 - zooms to 700px
                        // 2   - zooms to 2800px

                    } else {

                        // is double-tap

                        // zoom to original if initial zoom is less than 0.7x,
                        // otherwise to 1.5x, to make sure that double-tap gesture always zooms image
                        return item.initialZoomLevel < 0.7 ? 1 : 1.5;
                    }
                }
            };


            if (fromURL) {
                if (options.galleryPIDs) {
                    // parse real index when custom PIDs are used 
                    // http://photoswipe.com/documentation/faq.html#custom-pid-in-url
                    for (var j = 0; j < items.length; j++) {
                        if (items[j].pid == index) {
                            options.index = j;
                            break;
                        }
                    }
                } else {
                    options.index = parseInt(index, 10) - 1;
                }
            } else {
                options.index = parseInt(index, 10);
            }

            // exit if index not found
            if (isNaN(options.index)) {
                return;
            }



            var radios = document.getElementsByName('gallery-style');
            for (var i = 0, length = radios.length; i < length; i++) {
                if (radios[i].checked) {
                    if (radios[i].id == 'radio-all-controls') {

                    } else if (radios[i].id == 'radio-minimal-black') {
                        options.mainClass = 'pswp--minimal--dark';
                        options.barsSize = {
                            top: 0,
                            bottom: 0
                        };
                        options.captionEl = false;
                        options.fullscreenEl = false;
                        options.shareEl = false;
                        options.bgOpacity = 0.85;
                        options.tapToClose = true;
                        options.tapToToggleControls = false;
                    }
                    break;
                }
            }

            if (disableAnimation) {
                options.showAnimationDuration = 0;
            }

            // Pass data to PhotoSwipe and initialize it
            gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, items, options);

            // see: http://photoswipe.com/documentation/responsive-images.html
            var realViewportWidth,
                useLargeImages = false,
                firstResize = true,
                imageSrcWillChange;

            gallery.listen('beforeResize', function() {

                var dpiRatio = window.devicePixelRatio ? window.devicePixelRatio : 1;
                dpiRatio = Math.min(dpiRatio, 2.5);
                realViewportWidth = gallery.viewportSize.x * dpiRatio;


                if (realViewportWidth >= 1200 || (!gallery.likelyTouchDevice && realViewportWidth > 800) || screen.width > 1200) {
                    if (!useLargeImages) {
                        useLargeImages = true;
                        imageSrcWillChange = true;
                    }

                } else {
                    if (useLargeImages) {
                        useLargeImages = false;
                        imageSrcWillChange = true;
                    }
                }

                if (imageSrcWillChange && !firstResize) {
                    gallery.invalidateCurrItems();
                }

                if (firstResize) {
                    firstResize = false;
                }

                imageSrcWillChange = false;

            });

            gallery.listen('gettingData', function(index, item) {
                //console.log(index);
                if (useLargeImages) {
                    item.src = item.o.src;
                    item.w = item.o.w;
                    item.h = item.o.h;
                } else {
                    item.src = item.m.src;
                    item.w = item.m.w;
                    item.h = item.m.h;
                    resizeSwipeImg(item.m.src, item, 2.5);
                }
            });

            gallery.listen('afterChange', function(index, item) {
                //gallery.updateSize()
                //console.log(index);
                if(option && option.afterChange && typeof option.afterChange == "function"){
                    option.afterChange.call(this);
                }
            });

            function resizeSwipeImg(src, item, scale) {
                var win_H = document.documentElement.clientHeight,
                    win_W = document.documentElement.clientWidth,
                    win_scale = win_W / win_H;
                var img = new Image();
                img.src = src;
                img.onload = function() {
                    var img_scale = img.width / img.height,
                        cur_H, cur_W;
                    if (img_scale >= win_scale) {
                        cur_W = win_W;
                        cur_H = cur_W / img_scale;
                    } else {
                        cur_H = win_H;
                        cur_W = cur_H * img_scale;
                    }
                    item.h = cur_H * scale;
                    item.w = cur_W * scale;
                    gallery.updateSize();
                }
            }

            gallery.init();
            window.gallery = gallery;
        };

        // select all gallery elements
        var galleryElements = document.querySelectorAll(gallerySelector);
        for (var i = 0, l = galleryElements.length; i < l; i++) {
            galleryElements[i].setAttribute('data-pswp-uid', i + 1);
            galleryElements[i].onclick = onThumbnailsClick;
        }

        // Parse URL and open gallery if it contains #&pid=3&gid=1
        var hashData = photoswipeParseHash();
        if (hashData.pid && hashData.gid) {
            openPhotoSwipe(hashData.pid, galleryElements[hashData.gid - 1], true, true);
        }
        window.openPhotoSwipe = openPhotoSwipe;
    };

    window.initPhotoSwipeFromDOM = initPhotoSwipeFromDOM;

})();
