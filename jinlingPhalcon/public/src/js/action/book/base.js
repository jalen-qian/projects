define(['../../app', '../../action/util/Cookie'], function(app, Cookie) {

    var footPrice = $('.footer .price'),
        totalPrice = $('.total-price'),
        priceList = $('.detail-list'),
        nameBox = $('.name-box');

    var base = {
        // 页面初始化
        init: function(callback){

            $.ajax({
                url: '/api/Room/getStyleInfo',
                type: 'GET',
                data: app.merge({
                    securityKey: securityKey,
                    version: version,
                    system: system,
                    lang: lang,
                    APIGEEHEADER2: APIGEEHEADER2
                }, base.search),
                cache: false,
                success: function (result) {
                    if(result.code == 1000){
                        var content = base.content = result.content || {};

                        $('.product-name').html(content.hotelName);
                        $('.item-name').html(content.roomStyleName);
                        $('.item-info').html(content.roomTag);
                        $('.time-info').html((base.inDate.getMonth() + 1) + '-' + base.inDate.getDate() + ' 至 ' + (base.outDate.getMonth() + 1) + '-' + base.outDate.getDate() + '<span class="supply"> (共' + base.days + '晚)</span>');

                        if(typeof callback == 'function'){
                            callback.call(this, content);
                        }
                    }
                    else{
                        $.modal.prototype.defaults.modalButtonOk = '关闭';
                        $.alert(result.message);
                    }
                }
            });
        },
        // 初始化房间数量
        initBuyNum: function(){
            if(base.change.roomsNumber){
                base.buyNum = base.change.roomsNumber;
            }
            $('.room-number').html(base.buyNum);
        },
        // 初始化房间位置
        initLocation: function(content){

            // 如果在线选房设为开放
            if(content.choiceIs){

                // 如果cookie中选择的是到店选房
                if(parseInt(base.change.selectType) == 2){
                    $('.choose-room-text').html($('.choose-auto').addClass('current').html());
                }
                else{
                    $('.choose-room-text').html($('.choose-online').addClass('current').html());
                }

                $('.location').show();
            }
        },
        initTimeList: function(){
            var html = '',
                hourText = '17:00',
                today = new Date(), // 当前日期
                earliest = new Date(), // 今天最早时间
                latest = new Date(), // 今天最迟时间
                inDate = base.inDate; // 入住日期

            earliest.setHours(0);
            earliest.setMinutes(0);
            earliest.setSeconds(0);

            latest.setHours(23);
            latest.setMinutes(59);
            latest.setSeconds(59);

            inDate.setHours(23);

            // 在今天之前，不可选（页面上显示也是默认17:00）
            if(inDate < earliest){
                hourText = '17:00';
            }
            else if(inDate > latest){ // 在今天之后（入住日期从当天14:00到第二天4:00）
                for(var i = 0; i < 15; i++){
                    var hour = (14 + i) % 24;

                    if(hour == 17){
                        hourText = hour + ':00';
                        html += '<li class="time-item current">' + hourText + '<i class="iconfont icon-selected"></i></li>';
                    }
                    else if(hour <= 4){
                        html += '<li class="time-item">次日' + hour + ':00<i class="iconfont icon-selected"></i></li>';
                    }
                    else{
                        html += '<li class="time-item">' + hour + ':00<i class="iconfont icon-selected"></i></li>';
                    }
                }
            }
            else{ // 今天之内
                var now = today.getHours();

                for(var i = 0; i < 15; i++){
                    var abHour = (14 + i),
                        hour = abHour % 24;
                    if(abHour > now + 1){

                        if(abHour <= now + 2){
                            hourText = (hour <= 4 ? '次日' : '') + hour + ':00';
                            html += '<li class="time-item current">' + hourText + '<i class="iconfont icon-selected"></i></li>';
                        }
                        else{
                            html += '<li class="time-item">' + (hour <= 4 ? '次日' : '') + hour + ':00<i class="iconfont icon-selected"></i></li>';
                        }
                    }
                }
            }

            $('.choose-time-value').html(hourText);
            $('.time-list').html(html);
        },
        // 购买数量
        buyNum: 1,

        // 价格显示
        showPrice: function(content){
            var price = 0,
                priceObj = content.priceCalendar,
                html = '';

            if(priceObj){
                app.forEach(priceObj, function(v, k){
                    price += v;
                    html += '<li class="detail-item">' + k + '<span class="price-info">￥' + v + ' <span class="number">* ' + base.buyNum + '间</span></span></li>';
                });
            }

            var total = '￥' + Math.round(price * base.buyNum * 100) / 100;
            footPrice.html(total);
            totalPrice.html(total);
            priceList.html(html);
        },
        // 修改身份证输入框
        changeIdCard: function(){
            var nameRow = $('.name-row'),
                nameNum = nameRow.length;

            if(nameNum == base.buyNum){
                // 身份证输入框数量与房间数一致
            }
            else if(nameNum > base.buyNum){ // 身份证姓名输入框比房间数多
                for(var i = nameNum - 1, min = base.buyNum - 1; i > min; i--){
                    nameRow.eq(i).remove(false);
                }
            }
            else{// 房间数比身份证姓名输入框多
                for(var j = nameNum, max = base.buyNum - 1; j <= max; j++){
                    nameBox.append(
                        '<div class="info-row name-row">' +
                            '<span class="label">入住人</span>' +
                            '<input type="text" class="user-input name-input" name="consignees" placeholder="填写真实姓名(每间房只需要填一人)"/>' +
                        '</div>'
                    );
                }
            }


        },
        // 初始化输入控件值
        initInputVal: function(){
            var consignees = base.change.consignees

            if(consignees){
                var inputs = $('.name-input');
                app.forEach(consignees, function(name, i){
                    inputs.eq(i).val(name);
                });
            }

            if(base.change.telephone){
                $('.user-input[name=telephone]').val(base.change.telephone);
            }

            if(base.change.remark){
                $('.user-input[name=remark]').val(base.change.remark);
            }
        },
        // 提交表单
        submitOrder: function(){

            var nameMap = {},
                nameMsg = '',
                consignees = [];

            // 注：验证条件有优先级
            $('.name-box input').each(function(index, node){
                var value = $.trim($(node).val());

                if(!value){
                    nameMsg = '请输入入住人姓名';
                    return false;
                }
                else if(!(/^[\u4e00-\u9fa5]+$/.test(value) && value.length >= 2 && value.length <=8)){
                    nameMsg = '请输入2-8个汉字内的姓名';
                    return false;
                }

                if(nameMap[value]){
                    nameMsg = '入住人姓名不能重复';
                    return false;
                }
                else{
                    nameMap[value] = true;
                }

                consignees.push(value);
            });

            if(nameMsg){
                $.toast(nameMsg);
                return;
            }

            var phone = $.trim($('.user-input[name=telephone]').val());

            if(!phone){
                $.toast('请输入手机号码');
                return;
            }

            if(!/^1[0-9]{10}$/.test(phone)){
                $.toast('请输入正确的手机号码');
                return;
            }

            var submitObj = {
                securityKey: securityKey,
                version: version,
                system: system,
                lang: lang,
                APIGEEHEADER2: APIGEEHEADER2,
                consignees: consignees,
                telephone: phone,
                remark: $('.user-input[name=remark]').val(),
                checkInTime: base.checkInDate,
                checkOutTime: base.checkOutDate,
                stayDays: base.days,
                hotelId: base.hotelId,
                roomStyleId: base.roomStyleId,
                roomsNumber: base.buyNum,
                arrivalTime: $('.choose-time-value').html(),
                selectType: $('.choose-item.current').attr('data-type') || 0
            };

            $.showIndicator();
            $.ajax({
                url: '/api/order/create',
                type: 'POST',
                data: submitObj,
                cache: false,
                success: function (result) {
                    if(result.code == 1000){
                        location.href = '/front/pay/paying?orderNo=' + result.content.orderNo + '&hotelId=' + app.search('hotelId');
                    }
                    else{
                        $.modal.prototype.defaults.modalButtonOk = '关闭';
                        $.alert(result.message, '', function(){
                            $.hideIndicator();
                        });
                    }
                },
                error: function(){
                    $.modal.prototype.defaults.modalButtonOk = '关闭';
                    $.alert('网络异常', '', function(){
                        $.hideIndicator();
                    });
                }
            });
        }
    };

    var search = base.search = app.search(); // 从链接中获取到的参数

    base.checkInDate = search.startDate; // 入住日期字符串
    base.checkOutDate = search.endDate; // 离开日期字符串
    base.inDate = app.strToDate(base.checkInDate); // 入住日期
    base.outDate = app.strToDate(base.checkOutDate); // 离开日期
    base.days = (base.outDate.getTime() - base.inDate.getTime()) / 86400000; //入住天数 86400000 = 1000 * 60 * 60 * 24
    base.hotelId = search.hotelId; // 酒店id
    base.roomStyleId = search.roomStyleId; // 房型id

    base.cookieId = base.checkInDate + '-' + base.checkOutDate + '-' + search.roomStyleId + '-' + search.hotelId;

    base.change = app.decodeJson(Cookie.read(base.cookieId)) || {
        securityKey: securityKey,
        checkInTime: base.checkInDate,
        checkOutTime: base.checkOutDate,
        stayDays: base.days,
        hotelId: search.hotelId,
        roomStyleId: search.roomStyleId,
        roomsNumber: base.buyNum,
        selectType: 0,
        arrivalTime: '17:00'
    };

    return base;
});