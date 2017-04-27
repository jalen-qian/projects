console.log('load login.js');
define('login', [
    '../action/verify'
], function(){
	
	var vm = new Vue({
		el: "#app",
		data: {
			sourcePage: sourcePage,
			phone: '',
			password: '',
			btn_login: false // 登录按钮锁
		},
		methods: {
			register: function(){ // 注册页面
				if(validateOnline){
					window.location.href = webUrl + 'front/user/register?hotelId=' + hotelId
				}
			},
			rapidlogin: function(){ // 快速登陆页面
				if(validateOnline){
					window.location.href = webUrl + 'front/user/rapidlogin' + '?sourcePage=' + sourcePage + '&hotelId=' + hotelId;
				}
			},
			searchpassword: function(){ // 忘记密码页面
				if(validateOnline){
					window.location.href = webUrl + 'front/user/searchpassword' + '?sourcePage=' + encodeURIComponent('login') + '&hotelId=' + hotelId;
				}
			},
			login: function(){ // 登录
				// 点击锁
				if(this.btn_login) return;
				this.btn_login = true;

				if(!this.phone){
					$.toast('请输入手机号码');
					this.btn_login = false;
					return;
				}
				if(!$.verify.mobilePhone({ elem: '#J-mobile-phone' })){
					$.toast('请输入正确的手机号码');
					this.btn_login = false;
					return;
				}
				if(!this.password){
					$.toast('请输入密码');
					this.btn_login = false;
					return;
				}
				if(!$.verify.password({ elem: '#J-mobile-password' })){
					$.toast('密码格式错误，请输入6-32位数字或字母');
					this.btn_login = false;
					return;
				}
				$.showIndicator();
				$.ajax({
		            type: "post",
		            url: Host + 'front/user/loginpost',
		            dataType: "json",
//		            //传递给请求处理程序或页面的，用以获得jsonp回调函数名的参数名(一般默认为:callback)
//		            jsonp: "callback",
//		            //自定义的jsonp回调函数名称"jsonpCallback"，返回的json也必须有这个函数名称
//	    			jsonpCallback: "jsonpCallback",
		            data: {
		            	version: version,
				        system: system,
				        lang: lang,
				        APIGEEHEADER2: APIGEEHEADER2,
		            	hotelId: hotelId,
		            	telphone: this.phone,
		            	password: this.password,
		            	type: 0
		            },
		            success: function(data) {
		            	// 登陆成功，跳转来源页
		                if(data.code == 1000){
		                	if(sourcePage.indexOf('searchpassword') > 1 || sourcePage.indexOf('updatepassword') > 1 || sourcePage.indexOf('register') > 1){
		                		window.location.href = webUrl + 'front/index/index?hotelId=' + hotelId;
		                	} else {
		                		window.location.href = sourcePage;
		                	}
		                	vm.btn_login = false;
		                } else {
		                	$.toast(data.message);
		                	vm.btn_login = false;
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
		},
		// 实例创建后调用
		created: function(){
			bindJLinkEvent();
		},
		ready: function(){
			// 如果已经登录，则直接跳转到首页
			if(securityKey){
				window.location.href = webUrl + 'front/index/index?hotelId=' + hotelId;
			}
		}
	});
    /*
    *  注：现在同时兼容多文件合并压缩成单文件，和按需引入文件两种方式，可灵活使用，
    *  由于项目规模较小，建议尽量采用合并打包的方式，这样代码会简洁一些
    * */
});

require(['login']); // demo模块运行
