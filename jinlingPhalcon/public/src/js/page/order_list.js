define('order_list', [
    '../app'
], function(app){

    var orderList = $('.order-list'),
        orderTpl = $('#orderTpl').val(),
        hotelId = app.search('hotelId');

    var loadListFn = function(){
        $.showPreloader();
        var queryList = [];
        $.ajax({
            url: '/api/order/list',
            type: 'GET',
            data: {
                securityKey: securityKey,
                version: version,
                system: system,
                lang: lang,
                APIGEEHEADER2: APIGEEHEADER2,
                hotelId: hotelId
            },
            cache: false,
            success: function (result) {
                if(result.code == 1000){
                    var content = result.content || [];
                    if(content.length){

                        app.forEach(content, function(obj){

                            if(obj.needToCheck == 1){
                                queryList.push(obj.orderNo);
                            }

                            obj.cls = 'statue-' + obj.orderStatus;

                            // 不是在线选房
                            if(obj.selectType != 1){
                                obj.cls += ' no-online';
                            }
                            else if(obj.hasSelectRoom == 2){ // 已选房不能再选
                                obj.cls += ' no-online';
                            }

                            if(!obj.remark){
                                obj.remark = '无';
                            }

                            if(obj.orderStatus == 1){ // 待付款
                                obj.orderTip = '<i class="iconfont icon-tips"></i> 如果订单无法确认，房费全额退还';
                            }
                            else if(obj.orderStatus == 2 || obj.orderStatus == 3){ // 待确认、已确认
                                obj.orderTip = '<i class="iconfont icon-tips"></i> 订单确认后，不可取消';
                            }
                            else{
                                obj.orderTip = '';
                            }

                            if(!obj.selectRoomText){
                                obj.locationCls = 'hidden'
                            }
                        });

                        $('.order-list').html(app.tplToHtml(orderTpl, content)).show();
                    }
                    else{
                        $('.order-empty').show();
                    }

                    $.ajax({
                        url: '/api/order/query',
                        type: 'POST',
                        data: {
                            securityKey: securityKey,
                            version: version,
                            system: system,
                            lang: lang,
                            APIGEEHEADER2: APIGEEHEADER2,
                            orderNoStr: queryList.join(',')
                        },
                        cache: false,
                        success: function (result) {
                            if(result.code == 1000){
                                var data = result.content.data || [];

                                $.each(data, function(i, d){
                                    $('.item-' + d.orderNo).removeClass('statue-1 statue-2 statue-3 statue-4 statue-5 statue-6 statue-7 statue-8')
                                        .addClass('statue-' + d.orderStatus).find('.status').html(d.orderStatusText);
                                });
                            }
                        }
                    });

                    $.hidePreloader();
                }
                else{
                    $.modal.prototype.defaults.modalButtonOk = '关闭';
                    $.alert(result.message);
                }
            },
            error: function(){
                $.modal.prototype.defaults.modalButtonOk = '关闭';
                $.alert('网络异常');
            }
        });
    };
    loadListFn();

    orderList.on('click', '.show-btn', function(e){
        e.stopPropagation();
        $(this).parents('.order-item').addClass('open');
    });

    orderList.on('click', '.retract-btn', function(e){
        e.stopPropagation();
        $(this).parents('.order-item').removeClass('open');
    });



    orderList.on('click', '.cancel-btn', function(e){
        var item = $(this).parents('.order-item');

        e.stopPropagation();
        $.modal.prototype.defaults.modalButtonCancel = '取消';
        $.modal.prototype.defaults.modalButtonOk = '确认';
        $.confirm('确认要取消订单', '', function(){

            $.ajax({
                url: '/api/order/cancel',
                type: 'POST',
                data: {
                    securityKey: securityKey,
                    version: version,
                    system: system,
                    lang: lang,
                    APIGEEHEADER2: APIGEEHEADER2,
                    orderNo: item.attr('data-no')
                },
                cache: false,
                success: function (result) {
                    if(result.code == 1000){

                        var text = '订单已取消';

                        // 待确认
                        if(item.hasClass('statue-2')){
                            text = '订单已取消，我们将会在3天内安排退款，请留意订单状态变更';
                        }

                        $.modal.prototype.defaults.modalButtonOk = '关闭';
                        $.alert(text, function(){
                            loadListFn();
                        });
                    }
                    else{
                        $.modal.prototype.defaults.modalButtonOk = '关闭';
                        $.alert(result.message);
                    }
                },
                error: function(){
                    $.modal.prototype.defaults.modalButtonOk = '关闭';
                    $.alert('网络异常');
                }
            });
        });
    });

    orderList.on('click', '.online-btn', function(e){
        var item = $(this).parents('.order-item');

        e.stopPropagation();

        location.href = '/front/pay/roomSelect?orderNo=' + item.attr('data-no')+'&hotelId=' + hotelId;
    });

    orderList.on('click', '.pay-btn', function(e){
        var item = $(this).parents('.order-item');

        e.stopPropagation();

        location.href = '/front/pay/paying?orderNo=' + item.attr('data-no') + '&fromOrder=1&hotelId=' + hotelId;
    });

    $('.go-btn').on('click', function(){
        location.href = '/front/index/detail?hotelId=' + hotelId;
    });
});

require(['order_list']); // demo模块运行