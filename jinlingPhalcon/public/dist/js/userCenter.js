!function(o){function t(n){if(e[n])return e[n].exports;var s=e[n]={exports:{},id:n,loaded:!1};return o[n].call(s.exports,s,s.exports,t),s.loaded=!0,s.exports}var e={};return t.m=o,t.c=e,t.p="",t(0)}([function(o,t,e){var n,s;console.log("load userCenter.js"),n=[],!(s=function(){var o=new Vue({el:"#app",data:{phone:"",showPhone:"",hotelPhone:"",hotelId:hotelId},methods:{updatePhone:function(o){return o.substr(0,3)+"****"+o.substr(7)},outLogin:function(){$.modal({title:"",text:"确定退出登录?",buttons:[{text:"<span>取消</span>",onClick:function(){}},{text:"<span>确认</span>",onClick:function(){$.showIndicator(),$.ajax({type:"post",url:Host+"front/user/loginout",dataType:"jsonp",jsonp:"callback",jsonpCallback:"jsonpCallback",data:{version:version,system:system,lang:lang,APIGEEHEADER2:APIGEEHEADER2,hotelId:hotelId,telphone:o.phone,securetKey:securityKey},success:function(o){1e3==o.code?window.location.href=webUrl+"front/user/login?hotelId="+hotelId:$.toast(o.message)},complete:function(){$.hideIndicator()},error:function(){o.btn_login=!1,$.toast("网络异常，请检查网络再重试")}})}}]})}},created:function(){this.phone=userPhone,this.showPhone=this.updatePhone(this.phone),this.hotelPhone=getUrlParams("hotelPhone"),bindJLinkEvent()}})}.apply(t,n))}]);