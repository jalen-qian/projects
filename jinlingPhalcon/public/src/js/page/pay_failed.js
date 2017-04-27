define('pay_failed', [
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

                $('.btn-left').attr('href', '/front/pay/paying?orderNo=' + orderNo + '&hotelId=' + content.hotelId);
                $('.btn-right').attr('href', '/front/index/index?hotelId=' + content.hotelId);

                var remainTimeEl = $('.remain-time'),
                    offsetTime = Math.floor(new Date().getTime() / 1000 - parseInt(content.nowTime)), // 本地时间与服务器时间的误差
                    endTime = Math.floor(parseInt(content.closeTime) + offsetTime); // 服务器订单结束时间对应的本地时间

                var endId = setInterval(function(){

                    var remainTime = Math.floor(endTime - new Date().getTime() / 1000),
                        dayRemain,
                        hourTime,
                        minuteTime,
                        secondTime;

                    if(remainTime <= 0){
                        remainTimeEl.html('00:00:00');
                        clearInterval(endId);
                    }
                    else{
                        dayRemain = remainTime % 86400; // 截断天后剩余秒数
                        hourTime = app.converToZero(Math.floor(dayRemain / 3600));
                        minuteTime = app.converToZero(Math.floor((dayRemain % 3600) / 60));
                        secondTime = app.converToZero(remainTime % 60);

                        remainTimeEl.html(hourTime + ':' + minuteTime + ':' + secondTime);

                    }

                }, 100);
            }
            else{
                $.alert(result.message);
            }
        }
    });
});

require(['pay_failed']); // demo模块运行