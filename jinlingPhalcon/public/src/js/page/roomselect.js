var rightFun = function(text){
	var modal = $.modal({
      title: '<div class="iconfont  icon-routine_success rs_alert_routine_success"></div>',
      text:  '<div class="rs_alert_t">选房成功</div><div class="rs_alert_p">'+text+'</div>',
      buttons: [
        {
          text: '查看订单',
          onClick: function() {
            location.href="/front/userCenter/orderlist?hotelId="+hotelId;
          }
        }
      ]
	});
};
var errorFun = function(text,callback){
	var modal = $.modal({
      title: '<div class="iconfont  icon-routine_fail rs_alert_routine_fail"></div>',
      text:  '<div class="rs_alert_t">选房失败</div><div class="rs_alert_p">'+text+'</div>',
      buttons: [
        {
          text: '重新选择',
          onClick: function() {
            if(callback){
              callback();
            }
            // $.alert('You clicked second button!')
          }
        }
      ]
	});
}

//房间拖动模块
var dragFun = function(ele){
  var box = ele;
  var _x = 0;
  var _clientX = 0
  var sw = $(ele).find("span").width();
  var _w = $('body').width()-$(".resultView .leftBox").width();
  var setTranslate = function(x){
    box.style.transform = 'translate('+x+'px,0)';
    box.style.webkitTransform = 'translate('+x+'px,0)';
  };
  var setTransition = function(css){
    box.style.transition = "all, 0.3s, ease, 0s";
    box.style.webkitTransition = "all, 0.3s, ease, 0s";
  };
  var removeTransition = function(css){
    box.style.transition = "none";
    box.style.webkitTransition = "none";
  };
  var handleTouchStart = function(e) {
    var touch = e.touches[0];
    _clientX = touch.clientX-_x;
    removeTransition();
  };
  var handleTouchMove = function(e) {
    var touch = e.touches[0];
    _x = (_clientX-touch.clientX)*-1;
    setTranslate(_x);
    e.preventDefault(); 
  };
  var handleTouchEnd = function(e) {
    var touch = e.changedTouches[0];
    _clientX = touch.clientX;
    if($(ele).width()<_w){
      setTransition();
      setTranslate(0);
      _x = 0;
      return false;
    }
    if(_x>0){
      setTransition();
      setTranslate(0);
      _x = 0;
      return false;
    }
    var maxT = _w-ele.clientWidth;
    if(_x<maxT){
      setTransition();
      setTranslate(maxT);
      _x = maxT;
      return false;
    }
  };
  ele.addEventListener($.touchEvents.start, handleTouchStart);
  ele.addEventListener($.touchEvents.move, handleTouchMove);
  ele.addEventListener($.touchEvents.end, handleTouchEnd);
};
var setrightBox_w = function(){
  setTimeout(function(){
    var len = $(".resultView .rightBox span").length;
    var _w = $(".resultView .rightBox span").width()*len+len*20;
    $(".resultView .rightBox").width(_w);
  },200);
  
};
// 应对light7渲染问题
// setTimeout(function(){
//   // setrightBox_w();
//   dragFun($(".resultView .rightBox")[0]);
// },300);

// $.popup_dvb($(".float_box"),11,function(){
//   $(".select_p1").addClass("show");
// });
// $.popup_dvb_close($(".float_box"),function(){
//   $(".select_p1").removeClass("show");
// })


var _createPage = function(callbackdata){
  var setRoomsData = function(data){
    var _d = [];
    var floors = data.content.roomStyleList;
    for(var floor in floors){
      for(var room in floors[floor].roomList){
        _d.push(floors[floor].roomList[room]);
      }
    }
    return _d;
  };
  var RoomsData = setRoomsData(callbackdata);


  // 定义
  var roomselectComponent = Vue.extend({

  });

  // 注册
  Vue.component('roomselect-component', roomselectComponent);

  var myData = {
     selectfloat:callbackdata.content.roomStyleList[0],
     selectConut:0,
  };
  // 创建根实例
  var my_roomselectComponent = new Vue({
    el: '#roomselect',
    data:{
      ajaxdata:callbackdata, //后台返回的数据
      rooms:RoomsData,  //把所有房间都提取出来，保留到一个数组里，方便遍历出已经选择的房间
      myData:myData     //selectfloat=>当前楼层的数据 selectConut=>选择房间数量
    },
    methods: {
      init:function(){
        //初始化数据,在每层楼加入该楼层已选房间数
        var _d = this.$get("ajaxdata.content.roomStyleList");
        var len = _d.length;
        for(var i =0 ; i<len ; i++){
          this.$set("ajaxdata.content.roomStyleList["+i+"].selectConut","0");
        }
      },
      selectfloat: function (event) {
        // // 方法内 `this` 指向 vm
        // alert('Hello ' + this.name + '!')
        // // `event` 是原生 DOM 事件
        // alert(event.target.tagName)
        //选择楼层事件
        if(this.$get("myData.selectfloat") == this.$get("ajaxdata.content.roomStyleList["+_index+"]")){
          return false;
        }
        var _index = event.target.getAttribute("index");
        var newFloat =  this.$get("ajaxdata.content.roomStyleList["+_index+"]");
        this.$set("myData.selectfloat",newFloat);
        this.showRoomsBox(_index);
      },
      showRoomsBox:function(_index){
          //显示_index楼层房间，在selectfloat里调用
          $.popup_dvb_close($(".float_box"),function(){
              $(".select_p1").attr({"show":"n"})
              $(".select_p1").removeClass("show");
          });
          $.popup_dvb($(".room_box"),11,function(){
            $(".select_p2").attr({"show":"y"})
              $(".select_p2").addClass("show");
            },function(){
              $(".select_p2").attr({"show":"n"})
              $(".select_p2").removeClass("show");
          });
      },
      selectroom:function(_index){
          //选择房间事件
          var roomdata = this.$get("myData.selectfloat.roomList["+_index+"]");
          //该房间不可选
          if(roomdata.isSold == 1||roomdata.roomStyleId!=this.$get("ajaxdata.content.roomStyleId")){
            return false;
          }

          var count = this.$get("myData.selectConut");  //已选总数量
          var f_count = this.$get("myData.selectfloat.selectConut"); //该楼层已选数量
          //判断是否该用户已选的房间，如果是，就取消选择
          if(this.$get("myData.selectfloat.roomList["+_index+"].isSelect")==true){
            this.$set("myData.selectfloat.roomList["+_index+"].isSelect",false);
             this.$set("myData.selectConut",--count);
             this.$set("myData.selectfloat.selectConut",--f_count);
          }else{
            //是否超出数量
            if(this.$get("myData.selectConut") == this.$get("ajaxdata.content.roomAmount")){
              $.toast("你只预定了"+this.$get("myData.selectConut")+"间房",2000,"2");
              return false;
            }
             this.$set("myData.selectfloat.selectConut",++f_count);
             this.$set("myData.selectfloat.roomList["+_index+"].isSelect",true);
             this.$set("myData.selectConut",++count);
          }
          setrightBox_w();
      },
      removeroom:function(_index){
        //取消选择
        var count = this.$get("myData.selectConut");
        var f_count = this.$get("myData.selectfloat.selectConut");
        this.$set("myData.selectConut",--count);
        this.$set("rooms["+_index+"].isSelect",false);
        this.$set("myData.selectfloat.selectConut",--f_count);
        setrightBox_w();
      },
      clearSelect:function(){
        var that = this;
        // //清空选择
        // for(var i in this.$get("rooms")){
        //    this.$set("rooms["+i+"].isSelect",false);
        // }
        // for(var i2 in this.$get("ajaxdata.content.roomStyleList")){
        //   this.$set("ajaxdata.content.roomStyleList["+i2+"].selectConut",0);
        // }
        // // this.$set("myData.selectfloat.selectConut",0);
        // this.$set("myData.selectConut",0);
        $.showIndicator();
        $.get("/api/selectroom/list?callback=?",_pagedata,function(data){
                    $.hideIndicator();
                    if(data.code==1000){
                       var RoomsData = setRoomsData(data);
                       var myData = {
                           selectfloat:data.content.roomStyleList[0],
                           selectConut:0,
                        };
                        that.$set("ajaxdata",data);
                        that.$set("rooms",RoomsData);
                        that.$set("myData",myData);
                        that.init();
                    }else{
                      $.toast(data.message);
                    }
        },'json');

      },
      submit:function(){
        //提交
        var that = this;
        //隐藏弹出模块
        $.popup_dvb_close($(".float_box"),function(){
          $(".select_p1").attr({"show":"n"})
          $(".select_p1").removeClass("show");
        });
        $.popup_dvb_close($(".room_box"),function(){
            $(".select_p2").attr({"show":"n"})
            $(".select_p2").removeClass("show");
        })

        var selectCon = this.$get("myData.selectConut");
        var bookCon = this.$get("ajaxdata.content.roomAmount");
        //是否选满
        if(selectCon!=bookCon){
              var _remain = bookCon-selectCon;
              // $.alert("您还有"+_remain+"间房未选房", []);
              var modal = $.modal({
                  text:  "您还有"+_remain+"间房未选房",
                  buttons: [
                    {
                      text: '继续选房'
                    }
                  ]
              });
              return false;
        }
        var _data = {
          version:version,
          system:system,
          lang:lang,
          APIGEEHEADER2:APIGEEHEADER2,
          securityKey:securityKey,
          orderNo:orderNo
        };
        // _data.orderNo = this.$get("ajaxdata.content.orderNo");
        _data.roomStyleId = this.$get("ajaxdata.content.roomStyleId");
        _data.roomNumIds = [];
        var _rooms = []; //选中的房间号码
       $(".resultView .rightBox span").each(function(i){
          _data.roomNumIds.push($(this).find("i").attr("roomid"));
          _rooms.push($(this).attr("numname"));
       });
       _data.roomNumIds = _data.roomNumIds.join(",");
       _rooms = _rooms.join(",");
       if(validateOnline()){
          $.showIndicator();//loading
           $.post("/api/selectroom/userselect",_data,function(data){
              $.hideIndicator();
              if(data.code==1000){
                rightFun(_rooms+"房间等待你的到来～");
              }else{
                errorFun(data.message,function(){
                     that.clearSelect();
                  });
              }
            },'json');
       }
      }
    }
  });
  my_roomselectComponent.init();

  //弹出选择楼层
  $(".select_p1").on("click",function(){
    if($(this).attr("show") != "y"){
      //
      $.popup_dvb_close($(".room_box"),function(){
          $(".select_p2").attr({"show":"n"})
          $(".select_p2").removeClass("show");
      })

      $.popup_dvb($(".float_box"),11,function(){
        $(".select_p1").attr({"show":"y"})
          $(".select_p1").addClass("show");
        },function(){
          $(".select_p1").attr({"show":"n"})
          $(".select_p1").removeClass("show");
      });
    }else{
       $.popup_dvb_close($(".float_box"),function(){
          $(".select_p1").attr({"show":"n"})
          $(".select_p1").removeClass("show");
      })
    }
  });

  //弹出房间选择
  $(".select_p2").on("click",function(){
    if($(this).attr("show") != "y"){
       //
      $.popup_dvb_close($(".float_box"),function(){
          $(".select_p1").attr({"show":"n"})
          $(".select_p1").removeClass("show");
      })

      $.popup_dvb($(".room_box"),11,function(){
        $(".select_p2").attr({"show":"y"})
          $(".select_p2").addClass("show");
        },function(){
          $(".select_p2").attr({"show":"n"})
          $(".select_p2").removeClass("show");
      });
    }else{
       $.popup_dvb_close($(".room_box"),function(){
          $(".select_p2").attr({"show":"n"})
          $(".select_p2").removeClass("show");
      })
    }
  });
  dragFun($(".resultView .rightBox")[0]);
};

var orderNo = getUrlParams("orderNo");

//页面数据初始化
var _pagedata = {
  version:version,
  system:system,
  lang:lang,
  APIGEEHEADER2:APIGEEHEADER2,
  securityKey:securityKey,
  orderNo:orderNo
};
$.showIndicator();//loading
$("#roomselect").hide();
jQuery(document).ready(function(){ 
        if(orderNo == null){
           $.hideIndicator();
           $.toast("订单号不存在，不能选房");
        }else{
          $.get("/api/selectroom/list?callback=?",_pagedata,function(data){
            $.hideIndicator();
            if(data.code==1000){
              // data.content.roomAmount = 1; //测试
               $("#roomselect").show();
                _createPage(data);
            }else{
              $.toast(data.message);
            }
          },'json');
        }
});






