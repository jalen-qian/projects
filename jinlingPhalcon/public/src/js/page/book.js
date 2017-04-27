define('book', [
    '../app', '../action/book/base', '../action/util/Cookie'
], function(app, base, Cookie){

    var body = $('body'),
        footer = $('.footer'),
        roomPlus = $('.room-plus'),
        roomMinus = $('.room-minus'),
        roomNumber = $('.room-number');

    // 页面初始化
    base.init(function(data){
        base.initLocation(data); // 初始化房间位置栏
        base.initTimeList(); // 更新时间断选项
        base.initBuyNum(); // 初始化选择房间数
        base.changeIdCard(); // 更新身份证姓名输入框
        base.showPrice(data); // 更新价格
        base.initInputVal(); // 初始化输入空间值

        // 如果没库存，增加房间数按钮置灰
        if(base.buyNum >= data.maxInputStockNum){
            roomPlus.addClass('disabled');
        }

        if(base.buyNum > 1){
            roomMinus.removeClass('disabled');
        }
    });

    // 增加房间数
    roomPlus.on('click', function(){
        if(roomPlus.hasClass('disabled')){
            $.toast("可预订的房间不足");
            return;
        }

        base.buyNum++;

        roomNumber.html(base.buyNum);

        if(base.buyNum >= base.content.maxInputStockNum){
            roomPlus.addClass('disabled');
        }
        roomMinus.removeClass('disabled');

        base.changeIdCard(); // 更新身份证姓名输入框
        base.showPrice(base.content); // 更新价格

        base.change.roomsNumber = base.buyNum;
        Cookie.write(base.cookieId, app.encodeJson(base.change));
    });

    // 减少房间数
    roomMinus.on('click', function(){
        if(roomMinus.hasClass('disabled')){
            return;
        }

        base.buyNum--;

        roomNumber.html(base.buyNum);

        if(base.buyNum <= 1){
            roomMinus.addClass('disabled');
        }
        roomPlus.removeClass('disabled');

        base.changeIdCard(); // 更新身份证姓名输入框
        base.showPrice(base.content); // 更新价格

        base.change.roomsNumber = base.buyNum;
        Cookie.write(base.cookieId, app.encodeJson(base.change));
    });

    $('.location .choose-label').on('click', function(){
        $.modal.prototype.defaults.modalButtonOk = '关闭';
        $.alert(
            '<p class="location-text">为了给客人更好的体验，满足客人的个性化需求，<span class="toast-spec">本酒店支持在线选房，在支付订单以后，客户可以选择所预订的房型具体的房间位置。</span></p>' +
            '<p class="location-text">喜欢高楼层？喜欢靠近电梯？这些都没有问题，由你选择！' + base.content.hotelName + '给你最尊贵的体验！</p>',
            '什么是在线选房？'
        );
    });

    // 在线选房弹出
    $('.choose-room').on('click', function(){
        body.addClass('room-pop-open');
    });
    $('.room-sure').on('click', function(){
        body.removeClass('room-pop-open');
    });

    // 选房方式选择
    $('.choose-item').on('click', function(){
        var el = $(this),
            type = el.attr('data-type'),
            text = el.html();

        $('.choose-item.current').removeClass('current');
        el.addClass('current');
        $('.choose-room-text').html(text);

        body.removeClass('room-pop-open');

        base.change.selectType = type;
        base.change.selectText = text;
        Cookie.write(base.cookieId, app.encodeJson(base.change));
    });

    $('.choose-room-pop').on('click', function(){
        body.removeClass('room-pop-open');
    });

    $('.pop-inner').on('click', function(e){
        e.stopPropagation();
    });


    // 时间段选择
    $('.info-time-btn').on('click', function(){
        body.addClass('room-time-open');
    });
    $('.time-list').on('click', '.time-item', function(){
        $('.time-item.current').removeClass('current');

        var arrivalTime = $(this).addClass('current').text();
        $('.choose-time-value').html(arrivalTime);

        body.removeClass('room-time-open');

        base.change.arrivalTime = arrivalTime;
        Cookie.write(base.cookieId, app.encodeJson(base.change));
    });
    $('.choose-time-pop').on('click', function(){
        body.removeClass('room-time-open');
    });

    $('.user-info').on('blur', '.user-input', function(){
        var consignees = [];

        // 注：验证条件有优先级
        $('.name-box input').each(function(index, node){
            var value = $(node).val();
            consignees.push(value);
        });

        base.change.consignees = consignees;
        base.change.telephone = $('.user-input[name=telephone]').val();
        base.change.remark = $('.user-input[name=remark]').val();
        Cookie.write(base.cookieId, app.encodeJson(base.change));
    });

    // 价格明细的展示收缩
    $('.detail-btn').on('click', function(){
        footer.toggleClass('open-detail');
    });
    $('.price-detail').on('click', function(){
        footer.removeClass('open-detail');
    });
    $('.detail-inner').on('click', function(e){
        e.stopPropagation();
    });

    // 提交订单
    $('.submit').on('click', function(e){
        base.submitOrder();
    });

    // 覆盖头部默认的返回按钮
    $('#pull-left').on('click', function(e){
        e.preventDefault();
        $.modal.prototype.defaults.modalButtonCancel = '取消';
        $.modal.prototype.defaults.modalButtonOk = '离开';
        $.confirm('您的订单未填写完成，是否确定要离开当前页面？', '', function(){
            history.go(-1);
        });
    });
});

require(['book']); // demo模块运行