define('hotelDetail',['../action/calendar/calendar'], function(calendar){
    var hotelSwiper;
    var needToReload = false;
    var firstSwiper = false;    //是否初始化过swiper
    var localStorage_checkInDay = "checkInDay_"+hotelId; //localStorage name
    var localStorage_checkOutDay = "checkOutDay_"+hotelId; //localStorage name

    var createDate = function(day){
                                var _day = day.split("-");
                                var d =  new Date(_day[0],parseInt(_day[1]-1),_day[2]);
                                return d;
    };
    //判断日期是否过期
     if(typeof localStorage[localStorage_checkInDay] != 'undefined'){
            var old = createDate(localStorage[localStorage_checkInDay]);
            var newDay = new Date();
            newDay.setHours(0);
            newDay.setMinutes(0);
            newDay.setSeconds(0);
            newDay.setMilliseconds(0);
            if(newDay.getTime()>old.getTime()){
                localStorage.removeItem(localStorage_checkInDay);
                localStorage.removeItem(localStorage_checkOutDay);
            }
    }
    var vm = new Vue({
        el : '#v-app',
        data : {
            pageShow : getUrlParams('page') || 'intro',
            hotelInfoShow : false,
            hotelInfoData : {},
            dafaultImg : staticUrl+'src/images/photo-list-default.png',
            orderNum : 0,
            roomData : {},
            roomInfo : {},
            checkInDay : '',
            checkOutDay : '',
            nightTotal : '',
            showMoreBtn : false,
            hotelId : hotelId,
            imgListLength : 0,
            year : (new Date()).getFullYear()
        },
        methods : {
            togglePage : function(page){
                this.pageShow = page;
                var url = 'detail?page='+page+'&hotelId='+hotelId;
                shareTitle = "["+ comm.hotelName +"]-介绍";/*分享标题*/
                descContent = "["+ comm.hotelName +"]"+this.hotelInfoData.intro;/*分享内容*/
                window.history.pushState({},0,url);
                lineLink = window.location.href;
                wx.SHARE_ALL();
                needToReload = true;
            },
            showRoomFull : function(){
                var scroll = $('body').scrollTop();
                console.log(scroll);
                $.alert('很抱歉此房间已订满，请选择其它房间');
                $('.modal-in').css('marginTop',scroll);
            },
            gotoNav : function(){
                localStorage.address = this.hotelInfoData.address;
                localStorage.addressName = this.hotelInfoData.name;
                location.href='/front/index/navigation?hotelId='+hotelId+'&longitude='+this.hotelInfoData.longitude+'&latitude='+this.hotelInfoData.latitude;
            },
            goBack : function(){
                if(document.referrer == ''){
                    location.href = 'index?hotelId='+hotelId;
                }
                else{
                    history.go(-1);
                }
            },
            showRoomInfo : function(roomId,checkInDay,checkOutDay){
                var _this = this;
                //获取单个房间信息
                $.ajax({
                    data : {
                        version : version,
                        system : system,
                        lang : lang,
                        APIGEEHEADER2 : APIGEEHEADER2,
                        hotelId : hotelId,
                        startDate : _this.year + '-' + checkInDay,
                        endDate : _this.year + '-' +checkOutDay,
                        roomStyleId : roomId
                    },
                    url : '/api/Room/getStyleInfo',
                    dataType : 'jsonp',
                    type : 'get',
                    success : function(result){
                        if(result.code && result.code == 1000){
                            _this.roomInfo = result.content;
                            _this.hotelInfoShow = true;
                            $(".hotelInfo-body")[0].scrollTop = 0;

                        }
                    },
                    error : function(){
                        console.log('err');
                    }
                });
            },
            GetDateStr : function(startDate,AddDayCount) {
                var dd = new Date(startDate);
                dd.setDate(dd.getDate()+AddDayCount);//获取AddDayCount天后的日期
                var y = dd.getFullYear();
                var m = dd.getMonth()+1;//获取当前月份的日期
                var d = dd.getDate();
                return y+"-"+m+"-"+d;
            },
            getRoomInfo : function(checkInDay,checkOutDay){
                var _this = this;
                //获取房间列表信息
                $.ajax({
                    data : {
                        version : version,
                        system : system,
                        lang : lang,
                        APIGEEHEADER2 : APIGEEHEADER2,
                        hotelId : hotelId,
                        startDate : checkInDay,
                        endDate : checkOutDay
                    },
                    url : '/api/Room/getStyleList',
                    dataType : 'jsonp',
                    type : 'get',
                    success : function(result){
                        if(result.code && result.code == 1000 && result.content != null){
                            _this.roomData = result.content;
                            var inDay = new Date(result.content.startDate.replace(/-/g,'/'));
                            var outDay = new Date(result.content.endData.replace(/-/g,'/'));
                            _this.checkInDay = (inDay.getMonth()+1)+'-'+inDay.getDate();
                            _this.checkOutDay = (outDay.getMonth()+1)+'-'+outDay.getDate();
                            _this.nightTotal = result.content.dayCount;
                        }
                    },
                    error : function(){
                        console.log('err');
                    }
                });
            }
        },
        ready : function(){
            var _this = this;

            window.addEventListener('popstate',function(e){
                if(!/showCalendar/g.test(location.href) && needToReload){
                    console.log('load');
                    location.reload();
                }
            });

            //显示更多信息
            $('.show-info').on('click',function(){
                $(".hotel-intro").css({
                    'maxHeight':'initial',
                    'overflow' : 'auto'
                });
                $(this).hide();
            });
            if(validateOnline()){
                if(securityKey){
                    //获取订单信息
                    $.ajax({
                        data : {
                            version : version,
                            system : system,
                            lang : lang,
                            APIGEEHEADER2 : APIGEEHEADER2,
                            hotelId : hotelId,
                            securityKey : securityKey
                        },
                        url : '/api/order/count',
                        dataType : 'jsonp',
                        type : 'get',
                        success : function(result){
                            if(result.code && result.code == 1000 && result.content != null){
                                _this.orderNum = result.content.hasOwnProperty('orderTotal')? result.content.orderTotal : 0;
                            }
                        },
                        error : function(){
                            console.log('err');
                        }
                    })
                }

                //获取酒店信息
                $.ajax({
                    data : {
                        version : version,
                        system : system,
                        lang : lang,
                        APIGEEHEADER2 : APIGEEHEADER2,
                        hotelId : hotelId
                    },
                    url : '/api/Hotel/getInfo',
                    dataType : 'jsonp',
                    type : 'get',
                    success : function(result){
                        if(result.code && result.code == 1000 && result.content != null){
                            _this.hotelInfoData = result.content;
                            _this.imgListLength = result.content.photoList.length;
                            $(".hotel-phone").removeClass('fn-hide');
                            if(_this.pageShow == 'detail'){
                                shareTitle = "["+ comm.hotelName +"]-介绍";/*分享标题*/
                                descContent = "["+ comm.hotelName +"]"+_this.hotelInfoData.intro;/*分享内容*/
                            }
                        }
                    },
                    error : function(){
                        console.log('err');
                    }
                });

                $.ajax({
                    data : {
                        version : version,
                        system : system,
                        lang : lang,
                        APIGEEHEADER2 : APIGEEHEADER2,
                        hotelId : hotelId
                    },
                    url : '/api/Room/getCalendarList',
                    dataType : 'jsonp',
                    type : 'get',
                    success : function(result){
                        if(result.code && result.code == 1000 && result.content != null){
                            var formatNum = function(num){
                                if(num<10){
                                    num = "0"+num;
                                }Date
                                return num.toString();
                            };
                            var formatDay = function(data){
                                var year = data.getFullYear();
                                var month = data.getMonth()+1;
                                var day = data.getDate();
                                return year+"-"+formatNum(month)+"-"+formatNum(day);
                            };
                            var mycallback = function(){
                                var checkInDay = formatDay(newCalendar.checkInDay);
                                var checkOutDay = formatDay(newCalendar.checkOutDay);
                                _this.getRoomInfo(checkInDay,checkOutDay);
                                localStorage[localStorage_checkInDay] = checkInDay;
                                localStorage[localStorage_checkOutDay] = checkOutDay;
                                /*_this.checkInDay = checkInDay;
                                 _this.checkOutDay = checkOutDay;*/
                            };
                            var tomorrow = _this.GetDateStr(result.content.startDate,1);
                            if(typeof localStorage[localStorage_checkInDay] == 'undefined'){
                                checkIn_Day = new Date(result.content.startDate);
                                checkOut_Day = new Date(tomorrow);
                            }else{
                                checkIn_Day =  new Date(localStorage[localStorage_checkInDay]);
                                checkOut_Day = new Date(localStorage[localStorage_checkOutDay]);
                            }
                            var calendar_config = {
                                checkInDay:checkIn_Day, //进店时间 默认今天
                                lastDay:new Date(result.content.endDate),  //最后一天时间
                                checkOutDay:checkOut_Day ,			//离店时间
                                maxDay:30,				//最大可选天数
                                parpendEle:$(".page"),  //父元素
                                pageSize:2,				//每次加载数
                                callback:mycallback
                            };
                            var newCalendar = new calendar(calendar_config);

                           $(".date-select").on("click",function(e){
                                newCalendar.show();
                                 e.preventDefault();
                            });
                            //监听
                            window.addEventListener("popstate", function() {
                               if(newCalendar.isShow){
                                    newCalendar.rerurnHide();
                                    return false;
                               }                             
                            });
                            //进来先默认取一些信息
                            _this.getRoomInfo(localStorage[localStorage_checkInDay],localStorage[localStorage_checkOutDay]);
                        }
                    },
                    error : function(){
                        console.log('err');
                    }
                });
            }
        }
    });
    vm.$watch('roomInfo',function(){
            if(firstSwiper){
                hotelSwiper.destroy(true);
            }
            hotelSwiper = new Swiper ('.hotelInfo-body .swiper-container', {
                direction: 'horizontal',
                loop: true,
                pagination: '#hotelSwipeNav',
                paginationType : 'fraction',
                lazyLoading : true,
                onInit: function(swiper){
                    if(!firstSwiper){
                        firstSwiper = !firstSwiper;
                    }
                }
            });
    });
    vm.$watch('pageShow',function(){
        var fz = parseInt($('html').css("fontSize"))*5.3;
        var contentHeight = $(".hotel-info").height();
        if(contentHeight > fz){
            this.showMoreBtn = true;
            $(".hotel-intro").css({
                maxHeight : '5.3rem',
                overflow: 'hidden'
            })
        }
    });
    vm.$watch('hotelInfoData',function(){
        var fz = parseInt($('html').css("fontSize"))*5.3;
        var contentHeight = $(".hotel-info").height();
        if(contentHeight > fz){
            this.showMoreBtn = true;
            $(".hotel-intro").css({
                maxHeight : '5.3rem',
                overflow: 'hidden'
            })
        }
    });
    vm.$watch('hotelInfoShow',function(){
        $(".hotelInfo-body")[0].scrollTop = 0;
    });
});
require(['hotelDetail']);
