$(function() {

    $('.return-btn').on('click',function(){
        location.href = '/front/index/detail?hotelId='+hotelId;
    });

    var longitude = getUrlParams('longitude');//经度
    var latitude = getUrlParams('latitude');//纬度
    var address = localStorage.address;
    var addressName = localStorage.addressName;

    var map = new BMap.Map('allmap');
    var geocoder = new BMap.Geocoder();
    var poi = new BMap.Point(longitude,latitude);
    map.centerAndZoom(poi, 16);
    map.addControl(new BMap.NavigationControl());
    map.addControl(new BMap.ScaleControl());
    var local = new BMap.LocalSearch(map, {
        renderOptions: {map: map}
    });
    var infoBox = new BMapLib.InfoBox(map,address,
        {
            boxStyle:{
                width: "257px",
                padding : '14px 20px',
                background : 'rgba(255,255,255,.9)',
                textAlign : 'left',
                color : '#222222',
                fontSize : '0.7rem',
                borderRadius : '4px'
            },
            closeIconMargin: "0",
            closeIconUrl: "none",
            enableAutoPan: true,
            alignBottom: false,
        });
    var marker = new BMap.Marker(poi);
    map.addOverlay(marker);
    infoBox.open(marker);
    infoBox.setContent('<span style="font:bold 14px/16px arial,sans-serif;margin:0;color:#cc5522;">'+
        addressName+
        '</span><br><span style="font-size: 12px">' +
        '地址：'+address+
        '</span>');
    /*geocoder.getPoint(address,function(point){
        if(point){
            longitude = point.lng;
            latitude = point.lat;
            var poi = new BMap.Point(longitude,latitude);
            map.centerAndZoom(poi, 16);
            map.addControl(new BMap.NavigationControl());
            map.addControl(new BMap.ScaleControl());
            var local = new BMap.LocalSearch(map, {
                renderOptions: {map: map}
            });
            var infoBox = new BMapLib.InfoBox(map,address,
                {
                    boxStyle:{
                        width: "257px",
                        padding : '14px 20px',
                        background : 'rgba(255,255,255,.9)',
                        textAlign : 'left',
                        color : '#222222',
                        fontSize : '0.7rem',
                        borderRadius : '4px'
                    },
                    closeIconMargin: "0",
                    closeIconUrl: "none",
                    enableAutoPan: true,
                    alignBottom: false,
                });
            var marker = new BMap.Marker(poi);
            map.addOverlay(marker);
            infoBox.open(marker);
            infoBox.setContent('<span style="font:bold 14px/16px arial,sans-serif;margin:0;color:#cc5522;">'+
                addressName+
                '</span><br><span style="font-size: 12px">' +
                '地址：'+address+
                '</span>');
        }
    });*/

    var imgUrl = comm.hotelImg;/*分享图片*/
    var shareTitle = "["+ comm.hotelName +"]-微信直销";/*分享标题*/
    wx.SHARE_ALL();
});