define('hotelEntry',[], function(){
    var vm = new Vue({
        el : '#v-app',
        data : {
            orderNum : 0,
            orderName : '',
            hotelId : '',
            dafaultImg : '/src/images/index-picdemo.png',
            hotelData : {},
            imgShow : false
        },
        ready : function(){
            var _this = this;
            _this.hotelId = hotelId;
            if(validateOnline()){
                //获取酒店电话
                $.ajax({
                    data : {
                        version : version,
                        system : system,
                        lang : lang,
                        APIGEEHEADER2 : APIGEEHEADER2,
                        hotelId : hotelId,
                        securityKey : securityKey
                    },
                    url : '/api/Hotel/getInfo',
                    dataType : 'jsonp',
                    type : 'get',
                    success : function(result){
                        if(result.code && result.code == 1000){
                            _this.hotelData = result.content;
                        }
                    },
                    error : function(){
                        console.log('err');
                    }
                });

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
                                switch(result.content.orderStatus){
                                    case 1:
                                        _this.orderName = '等待付款';
                                        break;
                                    case 2:
                                        _this.orderName = '等待确认';
                                        break;
                                    case 3:
                                        _this.orderName = '已确认';
                                        break;
                                    case 7:
                                        _this.orderName = '退款中';
                                        break;
                                }
                            }
                        },
                        error : function(){
                            console.log('err');
                        }
                    })
                }
            }
        }
    })

    vm.$watch('hotelData',function(){
        this.imgShow = true;
    });
});
require(['hotelEntry']);