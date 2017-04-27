define('pay_success', [
    '../app'
], function(app){
    $.modal.prototype.defaults.modalButtonOk = '关闭';
    var orderNo = app.search('orderNo');

    $.ajax({
        url: '/api/payment/paycomplete',
        type: 'GET',
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

                $('.info-orider').html('订单号：' + content.orderNo);
                $('.phone').html(content.telephone);

                $('.btn-left').attr('href', '/front/userCenter/orderlist?hotelId=' + content.hotelId);
                $('.btn-right').attr('href', '/front/index/index?hotelId=' + content.hotelId);

                // 在线选房
                if(content.selectType == 1){
                    $('.pay-link').show();
                }
            }
            else{
                $.alert(result.message);
            }
        }
    });

    $('.link-btn').on('click', function(){
        location.href = '/front/pay/roomSelect?orderNo=' + orderNo + '&hotelId='+ hotelId;
    });
});

require(['pay_success']);