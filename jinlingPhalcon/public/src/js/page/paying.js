define('paying', [
    '../app'
], function(app){
    $.modal.prototype.defaults.modalButtonOk = '关闭';
    var orderNo = app.search('orderNo');

    var payStatus;

    $.ajax({
        url: '/api/order/detail',
        type: 'POST',
        data: {
            securityKey: securityKey,
            version: version,
            system: system,
            lang: lang,
            APIGEEHEADER2: APIGEEHEADER2,
            orderNo: orderNo
        },
        cache: false,
        success: function (result) {
            if(result.code == 1000){
                var content = result.content;

                $('.product-name').html('<i class="iconfont icon-hotel"></i>' + content.hotelName);
                $('.item-name').html(content.roomStyleName + '<span class="item-attr">' + content.roomsNumber + '间 * ' +  + content.stayDays +'晚</span>');
                $('.item-info').html(content.roomsTypeAttr);
                $('.number').html(content.orderAmount);
                $('.item-tip').html(content.selectType == 2 ? '到店选房' : (content.selectType == 1 ? '在线选房' : ''));

                payStatus = content.payStatus;
            }
            else{
                $.alert(result.message);
            }
        }
    });

    $('.submit').on('click', function(){
        if(typeof payStatus == 'undefined'){
            $.alert('网络异常');
        }
        else if(payStatus == 1){
            $.alert('已付款订单不能再次付款');
        }
        else if(payStatus == 2){
            $.alert('已退款订单不能付款');
        }
        else{
            location.href = '/api/payment/pay?' + app.toQueryString({
                securityKey: securityKey,
                version: version,
                system: system,
                lang: lang,
                APIGEEHEADER2: APIGEEHEADER2,
                orderNo: orderNo,
                userId: userId
            });
        }
    });

    // 覆盖头部默认的返回按钮
    $('#pull-left').on('click', function(e){
        e.preventDefault();
        $.modal.prototype.defaults.modalButtonCancel = '取消';
        $.modal.prototype.defaults.modalButtonOk = '继续';
        if(app.search('fromOrder')){
            $.confirm('将回到“<span class="toast-spec">我的订单</span>”，确认放弃支付？', '', function(){
                history.go(-1);
            });
        }
        else{
            $.confirm('将回到房间信息页，如需继续支付，请前往“<span class="toast-spec">我的订单</span>”中查看', '', function(){
                location.href = '/front/index/detail?hotelId=' + app.search('hotelId');
            });
        }
    });
});

require(['paying']); // demo模块运行