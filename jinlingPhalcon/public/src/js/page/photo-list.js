define('photoList',[], function(){
    var vm = new Vue({
        el : '#v-app',
        data : {
            hotelImgData : {},
            navLength : 0,
            imgtotal:'',
            noShow : false
        },
        methods : {
            scrollTo : function(target){
                $("body")[0].scrollTop = $('#'+target).offset().top;
            }
        },
        ready : function(){
            var _this = this;
            btn_left = function(){
                document.referrer==''?location.href='/front/index/index?hotelId='+hotelId : history.go(-1);
            };
            if(validateOnline()){
                $.ajax({
                    data : {
                        version : version,
                        system : system,
                        lang : lang,
                        APIGEEHEADER2 : APIGEEHEADER2,
                        hotelId : hotelId,
                    },
                    url : '/api/Hotel/getPhoto',
                    dataType : 'jsonp',
                    type : 'get',
                    success : function(result){
                        if(result.code && result.code == 1000){
                            _this.hotelImgData = result.content;
                            _this.navLength = result.content.photoList.length;
                            _this.imgtotal = result.content.photoCount;
                            if(result.content.photoCount == 0){
                                _this.noShow = true;
                            }
                        }
                    },
                    error : function(){
                        console.log('err');
                    }
                })
            }
        }
    })

    vm.$watch('hotelImgData',function(){
        $(".lazy").lazyload();

        var swiper = new Swiper('.swiper-container', {
            slidesPerView: 5,
            paginationClickable: true,
            spaceBetween: 0,
            onInit : function(swiper){
                var length = $('.swiper-slide').length;
                if(length < 5){
                    var liLen = $(window).width() / length;
                    $('.swiper-slide').css('width',liLen+'px');
                }
            }
        });

        //pic gallery
        //解决层覆盖
        $('.gradual,.image-name').on('click',function(){
            $(this).parents('.imgItem').find('img').trigger('click');
        });

        $('.select-images-zone').on('click','li',function(){
            $(this).addClass('active').siblings().removeClass('active');
        })

        bindLoadPic();
        function bindLoadPic() {
            $(".pswp__ui").hide();
            initPhotoSwipeItems();
            initPhotoSwipeFromDOM('.J_Gllery', {
                afterChange: function() {
                    $(".extend-state").text($(".pswp__counter").text());
                    var curImageUrl = this.currItem.src;
                    var curEl = this.currItem.el;
                    if ($(curEl).attr("data-scenic")) {
                        $(".extend-state-scenicName").text($(curEl).attr("data-scenic"));
                    } else {
                        $(".extend-state-scenicName").text("");
                    }
                }
            });

            $(".J_Close").click(function(event) {
                /* Act on the event */
                var $this = $(this);
                $this.hide();
                $(".pswp__button--close").click();
                setTimeout(function() {
                    $this.show();
                }, 400);
            });

            function bindJLinkEvent() {
                $(document).on("click", ".J_Link", function(e) {
                    var href = $(this).data("href");
                    if (href) {
                        location.href = href;
                    }
                });
            }

            function initPhotoSwipeItems() {
                $(".lazy").each(function() {
                    var $this = $(this),
                        pic = $this.attr("data-original"),
                        originPic = $this.attr("data-original").split("?")[0],
                        $a = $this.closest('a');
                    $a.attr({
                        href: pic,
                        "data-med": pic
                    });
                });
            }

            function updatePhotoSwipeItem(el) {
                var $a = $(el).closest('a');
                var medSize = el.width + "x" + el.height;
                $a.attr({
                    "data-size": medSize,
                    "data-med-size": medSize
                });
            }
        }
    });
});
require(['photoList']);