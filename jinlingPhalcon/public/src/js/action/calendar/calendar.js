define(["./calendar_data","./calendar_lib"], function(data,lib){
	var _href = location.origin+location.pathname+location.search;
	var calendar =  function(config){
		this.config = config||false;
		if(!this.config){ return false;}
		//parpendEle
		this.parpendEle = config.parpendEle||$("body");
		this.calendarBox = $("<div class='calendarBox'/>");
		//calendarBox_c
		this.calendarBox_c = $("<div class='calendarBox_c'/>");

		this.calendarBox.append(this.header_template).append(this.weekbox_template).append(this.calendarBox_c);

		this.parpendEle.append(this.calendarBox);

		this.box_height = this.calendarBox.height();


		//初始化

		this.isShow = false;
		this.today =  new Date();
		//选中的开始时间
		this.checkInDay = config.checkInDay||new Date();
		//选中的离开时间
		this.checkOutDay = config.checkOutDay||null;
		
		//可选的最后时间，如果没有设置，则设为180天之后
		this.lastDay = config.lastDay||new Date(86400000*180+this.checkInDay.getTime());
		//可选时间,默认为1
		this.maxDay = config.maxDay||1;
		//pageSize 每页加载数
		this.pageSize = config.pageSize||2;

		this.fotmatToday = lib.formatDay(this.today);
		this.fotmatLastDay = lib.formatDay(this.lastDay);

		this.callBack = config.callback || false;

		this.init(this.config);
		this.flat = true; // 开关 ，防止多次选择时间导致的回退问题
		this.events();
	};
	calendar.prototype = {
		init:function(config){
			this.calendarBox.hide();
			// 是否加载到最后一月
			this.islast_flag = false;

			// 是否可以加载新一页
			this.canLoad_flag = true;

			//初始化时加载的月数
			var fristLoadNum = 2;
			if(this.today.getMonth() != this.checkInDay.getMonth()){
				fristLoadNum = 2+lib.monthDistance(this.today,this.checkInDay);
			}
			//清空
			this.calendarBox_c.html("");
			//生成月份
			this._year = this.today.getFullYear();
			this._month = this.today.getMonth();

			for(var i =0;i<fristLoadNum;i++){
				this.createCalendar(this._year,this._month);
				this.increaseMounth();
				if(this.islast_flag){
					this.calendarBox_c.append('<div class="tips" style="padding-bottom:5px">已加载全部日期</div>');
					break;
				}
			}
			

			// this.showMoreBtn();
			
			//设置入住日期
			this.setCheckInDay(lib.formatDay(this.checkInDay));

			if(this.maxDay>1){
				//离店日期
				if(this.checkOutDay == null){
					$.toast("请选择离店日期");
				}else{
					this.setCheckOutDay(lib.formatDay(this.checkOutDay));
				}

				if(this.maxDay>1&&this.checkOutDay != null){
					this.setInnerDay();
				}
			}
			
		},
		header_template : '<header class="bar bar-nav">'+
							'	<button class="button button-link button-nav pull-left" >'+
							'	    <i class="iconfont icon-login back">&#xe600;</i>'+
							'	 </button>'+
							'	<h1 class="title">选择入住和离店日期</h1>'+
							'</header>',
		weekbox_template : '<div class="weekBox">'+
							'	<span class="weekend">日</span>'+
							'	<span class="">一</span>'+
							'	<span class="">二</span>'+
							'	<span class="">三</span>'+
							'	<span class="">四</span>'+
							'	<span class="">五</span>'+
							'	<span class="weekend">六</span>'+
							'</div>',
		get_monthBox_template : function(year,month){
			var box = $("<div class='monthBox' />");
			box.append('<div class="t">'+year+'-'+lib.formatNum(month+1)+'</div>');
			var _table = $("<table />");
			//生成日历
			var fristday = lib._getfristDay(year,month);    //第一天日期
			var weekCounts = lib.getWeekCounts(year,month); //周数
		    var _fw = fristday.getDay(); 					//第一天是周几
		    var daysLength = lib.getDayCounts(year,month);  //当月天数
		    //周
			for(var i = 0 ; i<weekCounts ; i++){
				var _tr = $("<tr />");
				var _tdHtml = "";
				var i2 = 1 ;
				//各周循环
				for(i2;i2<=7;i2++){
					var day = i*7+i2-_fw;
					var _td = $("<td />");
					if(day>=1&&day<=daysLength){
						//生成日期/判断状态
						var _formatDay = lib.formatDay(new Date(year,month,day));
						// var _formatDay2 = lib.formatDay(new Date(year,month+1,day));
						_td.attr({"date":_formatDay,"year":year,"month":month,"day":day});
						//判断是否今天
						if(lib.isToday(year,month,day)){
							_td.html("今天");
							_td.addClass("today");
						}else{
							_td.html(day);
						}
						//判断是否可选
						if(_formatDay<this.fotmatToday||_formatDay>this.fotmatLastDay){
							_td.addClass("forbit");
						}
						if(this.isWeekend(new Date(year,month,day))){
							_td.addClass("weekend");
						}
						//判断是否最后一天
						this.islast(_formatDay);
					}
					_tr.append(_td);
				}
				_table.append(_tr);
			}
			//节假日
			box.append(_table);
			return box;
		},
		createCalendar:function(year,month){
			var _html = this.get_monthBox_template(year,month);
			this.calendarBox_c.append(_html);
			this.getFestival(year,month);
			
		},
		getFestival:function(year,month){
			//节日
			var _m = parseInt(month)+1;
			_m = lib.formatNum(_m);
			if(data.festival&&data.festival[_m]){
				for(var i in data.festival[_m]){
					var name = data.festival[_m][i];
					_m = lib.formatNum(month);
					var day = year+_m+i;
					$("td[date='"+day+"']").html(name);	
				}
			}
			_m = parseInt(month)+1;
			_m = lib.formatNum(_m);
			//农历
			if(data.lunarFestival&&data.lunarFestival[year]&&data.lunarFestival[year][_m]){
				for(var i in data.lunarFestival[year][_m]){
					var name =data.lunarFestival[year][_m][i];
					_m = lib.formatNum(month);
					
					var day = year+_m+i;
					$("td[date='"+day+"']").html(name);
				}
			}
		},
		setSpan:function(ele,value){
			if(ele.find("span").length>0){
				ele.find("span").html(value);
			}else{
				ele.append("<span>"+value+"<span>");		
			}
		},
		events:function(){
			var that = this;
			this.calendarBox.on("scroll",function(e){
				var scrollT = that.calendarBox.scrollTop();
				// console.log(scrollT+that.box_height-that.calendarBox_c[0].clientHeight);
				if(scrollT+that.box_height-that.calendarBox_c[0].clientHeight>=-0&&!that.islast_flag&&that.canLoad_flag){
					that.canLoad_flag = false;
					that.calendarBox_c.append('<div class="tips loading" style="padding-bottom:15px">加载更多日期</div>');
					setTimeout(function(){
						that.loadNewPage();
						that.canLoad_flag = true;
						that.calendarBox_c.find(".loading").remove();
					},1000);
				}
			});

			this.calendarBox_c.on("click",".more",function(){
				$(this).hide();
				that.loadNewPage();
				var $that = $(this);
				that.calendarBox_c.append($that);
				if(!that.islast_flag){
					$that.show();
				}
			});

			this.calendarBox_c.on("click","td",function(){
				if(that.flat&&!$(this).hasClass("forbit")&&$(this).html().length!=0){
					if(that.maxDay>1){
						//第一次点击
						if(that.checkOutDay != null){
							that.clearCheckDay();
							var day = $(this).attr("date");
							that.setCheckInDay(day);
							that.flat = true;
							setTimeout(function(){
								if(that.checkOutDay == null){
									$.toast("请选择离店日期");
								}
							},500);
						}else{
							//第二次点击
							$(".toast").remove();
							if($(this).hasClass("select")){
								return false;
							}
							var day = $(this).attr("date");
							var _oneDay = lib.formatDay(that.checkInDay);
							if(day>_oneDay){
								that.flat = false;
								that.setCheckOutDay(day);
								
							}else{
								that.clearCheckDay();
								var day = $(this).attr("date");
								that.setCheckInDay(day);
								// that.setCheckInDay(day);
								// that.setCheckOutDay(_oneDay);
							}
							setTimeout(function(){
								$(".toast").remove();
								that.hide();
							},500);	
						}
					}else{
							that.clearCheckDay();
							var day = $(this).attr("date");
							that.setCheckInDay(day);
							setTimeout(function(){
								that.hide();
							},500);	
					}
				}
				
			});
			this.calendarBox.on("click",".pull-left",function(){
				if(that.maxDay>1&&that.checkOutDay == null){
					//选取页面无效时，直接退出
					that.calendarBox.removeClass("calendarBox_transform_none");
					that.parpendEle.removeClass("calendarBox_par");
					setTimeout(function(){
						that.calendarBox.removeClass("calendarBox_show");
						setTimeout(function(){
							that.calendarBox.hide();
							//还原
							that.clearCheckDay();
							that.setCheckInDay(lib.formatDay(that.bup_checkInDay));
							that.setCheckOutDay(lib.formatDay(that.bup_checkOutDay));
							window.history.go(-1);
						},300);
					},100);
				}else{
					that.hide();
				}
				that.isShow = false;
			})
		},
		loadNewPage:function(){
			for(var i =0;i<2;i++){
				if(!this.islast_flag){
					this.createCalendar(this._year,this._month);
					this.increaseMounth();
				}else{
					this.calendarBox_c.append('<div class="tips" style="padding-bottom:5px">已加载全部日期</div>');
				}
			}
		},
		increaseMounth:function(){
			this._month++;
			if(this._month==12){
				this._month = 0;
				this._year++;
			}
		},
		islast:function(day){
			if(day==this.fotmatLastDay){
				this.islast_flag = true;
			}
		},
		showMoreBtn:function(){
			if(this.calendarBox_c.find(".tips").length>0){
					return false;
				}
			if(!this.islast_flag&&this.box_height>this.calendarBox_c[0].clientHeight){
				this.calendarBox_c.append('<div class="tips more" style="padding-bottom:5px">加载更多</div>');
				this.canLoad_flag = false;
			}
		},
		isWeekend:function(day){
			if(day.getDay()==0||day.getDay()==6){
				return true;
			}else{
				return false;
			}
		},
		setCheckInDay:function(day){
			if(this.maxDay == 1){
				$("td[date='"+day+"']").addClass("select");
				this.setSpan($("td[date='"+day+"']"),"选择");
				this.checkInDay = lib.createDate(day);
			}else{
				$("td[date='"+day+"']").addClass("select");
				this.setSpan($("td[date='"+day+"']"),"入住");
				this.checkInDay = lib.createDate(day);
			}
		},
		setCheckOutDay:function(day){
			var newDay = lib.createDate(day);
			var dist = lib.dayDistance(newDay,this.checkInDay);
			if(this.maxDay>1&&dist>this.maxDay){
				$.toast("最多只能预订"+this.maxDay+"天");
				this.flat = true;
				return false;
			}
			$("td[date='"+day+"']").addClass("select");
			this.setSpan($("td[date='"+day+"']"),"离店");
			this.checkOutDay = lib.createDate(day);
			this.setInnerDay();
		},
		setInnerDay:function(){
			var _in = lib.formatDay(this.checkInDay),
				_out = lib.formatDay(this.checkOutDay);
			var len = _out-_in;
			for(var i = 1 ; i<len ; i++){
				var dates = parseInt(_in)+i;
				$("td[date='"+dates+"']").addClass("inner");
			}
		},
		clearCheckDay:function(){
			this.calendarBox_c.find(".select span").remove();
			this.calendarBox_c.find("td").removeClass("select");
			this.calendarBox_c.find("td").removeClass("inner");
			this.checkInDay = null;
			this.checkOutDay = null;
		},
		hide:function(){
			if(!this.isShow){
				return false;
			}
			var that = this;
			if(this.maxDay>1&&this.checkOutDay == null){
				$.toast("请选择离店日期");
				return false;
			}
			this.isShow = false;
			that.calendarBox.removeClass("calendarBox_transform_none");
			this.parpendEle.removeClass("calendarBox_par");
			setTimeout(function(){
				that.calendarBox.removeClass("calendarBox_show");
				setTimeout(function(){
					that.calendarBox.hide();
				},300);
			},100);
			if(this.callBack){
				setTimeout(function(){
					that.callBack();
				},500);
			}
			window.history.go(-1);
			// history.replaceState(null, "", _href);
		},
		rerurnHide:function(){
					var that = this;
					//选取页面无效时，直接退出
					that.calendarBox.removeClass("calendarBox_transform_none");
					that.parpendEle.removeClass("calendarBox_par");
					setTimeout(function(){
						that.calendarBox.removeClass("calendarBox_show");
						setTimeout(function(){
							that.calendarBox.hide();
							//还原
							that.clearCheckDay();
							that.setCheckInDay(lib.formatDay(that.bup_checkInDay));
							that.setCheckOutDay(lib.formatDay(that.bup_checkOutDay));
						},300);
					},100);
					// history.replaceState(null, "", _href);
					this.isShow = false;
		},
		show:function(){
			this.calendarBox.show();
			this.showMoreBtn();
			//备份当前的日期
			this.bup_checkInDay = this.checkInDay;
			this.bup_checkOutDay = this.checkOutDay;

			//history.replaceState
			history.pushState(null, "", _href+"#showCalendar=1");
			this.isShow = true;
			this.flat = true;
			var that = this;
			setTimeout(function(){
				that.parpendEle.addClass("calendarBox_par");
				that.calendarBox.addClass("calendarBox_show");
				setTimeout(function(){
					that.calendarBox.addClass("calendarBox_transform_none");
				},300);
			},200);
		}

	};
	return calendar;
});













