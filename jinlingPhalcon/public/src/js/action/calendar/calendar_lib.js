define([], function(){
	var app = {};
	//4 => 04
	app.formatNum = function(num){
		if(num<10){
			num = "0"+num;
		}Date
		return num.toString();
	};
	//获得一个月的周数 
	app.getWeekCounts = function( y, m ) { 
	    var first = new Date(y, m,1).getDay();      
	    var last = 32 - new Date(y, m, 32).getDate(); 
	    return Math.ceil( (first + last)/7 );   
	};
	//获得一个月的周数 
	app.getDayCounts = function( y, m ) {     
	    var last = 32 - new Date(y, m, 32).getDate(); 
	    return last;   
	};
	//返回下一个月份
	app._getNextMonth = function(y,m){
		var month = m+1;
		if(month>=12){
			month = 0;
			y = y+1;
		}
		return [y,month];
	};
	//返回上一个月份
	app._getPreMonth = function(y,m){
		var month = m-1;
		if(month<0){
			month = 11;
			y = y-1;
		}
		return [y,month];
	};
	app._getfristDay= function(y,m){
		var newdate = new Date();
			newdate.setYear(y);
			newdate.setMonth(m);
			newdate.setDate(1);
		return newdate;
	};
	app.monthDistance = function(m1,m2){
		var month1 = m1.getMonth().toString();
		var month2 = m2.getMonth().toString();
		var year1 = m1.getFullYear().toString();
		var year2 = m2.getFullYear().toString();
		var d1 = year1+app.formatNum(month1);
		var d2 = year1+app.formatNum(month2);
		var dis = Math.abs(d1-d2);
		return dis;
	};
	app.dayDistance = function(date1,date2){
		var days = date1.getTime() - date2.getTime();
		if(days == 0 ){
			return 0;
		}
		var time = parseInt(days / (1000 * 60 * 60 * 24));
		return Math.abs(time);
	};
	app.isToday = function(year,month,day){
		var date = new Date();
		if(day==date.getDate()&&month==date.getMonth()&&year==date.getFullYear()){
			return true;
		}else{
			return false;
		}
	};
	app.formatDay = function(data){
		var year = data.getFullYear();
		var month = data.getMonth();
		var day = data.getDate();
		return year+app.formatNum(month)+app.formatNum(day);
	};
	app.createDate = function(day){
		var d =  new Date(day.toString().slice(0,4),day.toString().slice(4,6),day.toString().slice(6,8));
		return d;
	};
	app.transitionEnd = function(object,myScript){
		object.addEventListener("webkitTransitionEnd", myScript);  
		object.addEventListener("transitionend", myScript); 
	};
	return app;
});







