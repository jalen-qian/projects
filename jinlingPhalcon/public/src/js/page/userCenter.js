console.log('load userCenter.js');

define('userCenter', [], function(){ // 在define中引入app和Cookie，表示app.js和Cookie.js的源码引入demo.js，合并成一个大的

	var vm = new Vue({
		el: '#app',
		data: {
			phone: '',
			showPhone: '',
			hotelPhone: '',
			hotelId: hotelId
		},
		methods: {
			updatePhone: function(phone){
				return phone.substr(0, 3) + '****' + phone.substr(7);
			},
			// updatePassword: function(){ // 修改密码
			// 	window.location.href = webUrl + 'front/user/updatepassword?sourcePage=' + encodeURIComponent('userCenter');
			// },
			// orderlist: function(){ // 我的订单
			// 	window.location.href = webUrl + 'front/userCenter/orderlist';
			// },
			outLogin: function(){ // 退出登录
				$.modal({
					title: '',
					text: '确定退出登录?',
					buttons: [{
						text: '<span>取消</span>',
				        onClick: function() {
			            	// console.log('取消')
				        }
					}, {
						text: '<span>确认</span>',
				        onClick: function() {
				        	$.showIndicator();
				        	$.ajax({
					            type: "post",
					            url: Host + 'front/user/loginout',
					            dataType: "jsonp",
					            //传递给请求处理程序或页面的，用以获得jsonp回调函数名的参数名(一般默认为:callback)
					            jsonp: "callback",
					            //自定义的jsonp回调函数名称"jsonpCallback"，返回的json也必须有这个函数名称
				    			jsonpCallback:"jsonpCallback",
					            data: {
					            	version: version,
							        system: system,
							        lang: lang,
							        APIGEEHEADER2: APIGEEHEADER2,
					            	hotelId: hotelId,
					            	telphone: vm.phone,
					            	securetKey: securityKey
					            },
					            success: function(data) {
					            	// 退出登陆成功，跳转来登录页
					                if(data.code == 1000){
					                	window.location.href = webUrl + 'front/user/login?hotelId=' + hotelId;
					                } else {
					                	$.toast(data.message);
					                }
					            },
					            complete: function(){
					            	$.hideIndicator();
					            },
					            error: function() {
					            	vm.btn_login = false;
					                $.toast('网络异常，请检查网络再重试');
					            }
					        });
				        }
					}]
				});
			}
		},
		// 实例创建后调用
		created: function(){
			this.phone = userPhone;
			this.showPhone = this.updatePhone(this.phone);
			this.hotelPhone = getUrlParams('hotelPhone');

			bindJLinkEvent();
		}
	});

    /*
    *  注：现在同时兼容多文件合并压缩成单文件，和按需引入文件两种方式，可灵活使用，
    *  由于项目规模较小，建议尽量采用合并打包的方式，这样代码会简洁一些
    * */
});

require(['userCenter']); // demo模块运行