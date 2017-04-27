define([], function () {

    var app = {};

    var ua = navigator.userAgent.toLowerCase(),
        match = ua.match(/(opera|firefox|chrome|version)[\s\/:]([\w\d\.]+)?.*?(safari|version[\s\/:]([\w\d\.]+)|$)/) || [null, 'unknown', 0];

    app['browser'] = (match[1] == 'version') ? match[3] : match[1];                                 //浏览器类型opera|firefox|chrome|safari
    app['version'] = parseFloat((match[1] == 'opera' && match[4]) ? match[4] : match[2]);

    //只有ie有documentMode
    if(document.documentMode){
        app['browser'] = 'ie';
        app['version'] = document.documentMode; //ie浏览器版本
    }

    //新版本的opera，会同时有chrome和safari的信息，所以用opr
    if(match = ua.match(/(opr)[\s\/:]([\w\d\.]+)?/)){
        app['browser'] = 'opera';
        app['version'] = match[2];
    }

    app[app['browser']] = parseInt(app['version'], 10);                                              //浏览器类型名称作为属性，值为版本整数值，可以当boolean值用
    app[app['browser'] + parseInt(app['version'], 10)] = true;                                       //浏览器类型加版本号作为属性值为true

    app['webkit'] = (ua.indexOf('webkit') != -1);


    /* 基础方法 */
    var methods = {

        isDef: function(value){
            //return typeof value != 'undefined'; 慢
            return value !== undefined;
        },

        isNull: function(value){
            return value !== null;
        },

        isArr: function (value) {
            return Object.prototype.toString.call(value) === '[object Array]';
        },

        isDate: function (value) {
            return Object.prototype.toString.call(value) === '[object Date]';
        },

        isObj: (app['ie'] <= 8) ? function (value) {
            return !!value && Object.prototype.toString.call(value) === '[object Object]' && value.hasOwnProperty && !value.callee;
        }: function(value){
            return Object.prototype.toString.call(value) === '[object Object]';
        },

        isFun: function (value) {
            //return Object.prototype.toString.call(value) === '[object Function]'; 慢
            return typeof value === 'function';
        },

        isNum: function (value) {
            //return Object.prototype.toString.call(value) === '[object Number]' && isFinite(value); 慢
            //除去Infinity和NaN
            return (typeof value === 'number') && isFinite(value);
        },

        isStr: function (value) {
            return typeof value === 'string';
        },

        isBool: function (value) {
            //return Object.prototype.toString.call(value) === '[object Boolean]'; 慢
            return typeof value === 'boolean';
        },

        isDom: function(value){
            return this.isNode(value) || this.isWin(value);
        },

        isNode: function(value){
            return value ? !! value.nodeType : false;
        },

        isTag: function (value) {
            return value ? !!value.tagName : false;
        },

        isBody: function(value){
            return (/^(?:body|html)$/i).test(value.tagName);
        },

        isDoc: function (value) {
            return value && value.nodeType == 9;
        },

        isWin: function (value) {
            var str = value ? value.toString() : '';
            return str == '[object Window]' || str == '[object DOMWindow]';
        },


        typeOf: function(item){
            if (item == null) {
                return 'null';
            }
            if (this.isArr(item)){
                return 'array';
            }
            if(this.isDate(item)){
                return 'date'
            }
            if (item.nodeName){
                if (item.nodeType == 1) {
                    return 'element';
                }
                if (item.nodeType == 3) {
                    return (/\S/).test(item.nodeValue) ? 'textnode' : 'whitespace';
                }
            }
            else if (typeof item.length == 'number'){
                if (item.callee) {
                    return 'arguments';
                }
            }

            return typeof item;
        },


        interceptor: function (fn, filter, scope, breakOffValue) {
            var method = fn;
            if (!this.isFun(filter)) {
                return fn;
            }
            else {
                return function () {
                    filter.target = this;
                    filter.method = fn;
                    return (filter.apply(scope || this, arguments) !== false) ? fn.apply(this, arguments) : breakOffValue || null;
                };
            }
        },

        delegate: function (fn, obj, args, appendArgs) {

            if (!this.isFun(fn)) {
                return fn;
            }
            return function () {

                var callArgs;

                if (appendArgs === true) {
                    callArgs = Array.prototype.slice.call(arguments, 0);
                    callArgs = callArgs.concat(args);
                }
                else if (app.isNum(appendArgs)) {
                    callArgs = Array.prototype.slice.call(arguments, 0);
                    Array.prototype.splice.apply(callArgs, [appendArgs, 0].concat(args));
                }
                else{
                    callArgs = args || arguments;
                }
                return fn.apply(obj || window, callArgs);
            };
        },

        defer: function (fn, millis, obj, args, appendArgs) {
            fn = this.delegate(fn, obj, args, appendArgs);
            if (millis > 0) {
                return setTimeout(fn, millis);
            }
            fn();
            return 0;
        },

        sequence: function (fn, nextFn, scope) {

            if (!this.isFun(nextFn)) {
                return fn;
            }
            else {
                return function () {
                    var retval = fn.apply(this || window, arguments);
                    nextFn.apply(scope || this || window, arguments);
                    return retval;
                };
            }
        },

        limit: function (num, min, max) {
            return Math.min(max, Math.max(min, num));
        },

        round: function (num, precision) {
            precision = Math.pow(10, precision || 0).toFixed(precision < 0 ? -precision : 0);
            return Math.round(num * precision) / precision;
        },

        times: function (num, fn, bind) {
            for (var i = 0; i < num; i++){
                fn.call(bind, i, num);
            }
        },

        test: function (string, regex, params) {
            return ((this.typeOf(regex) == 'regexp') ? regex : new RegExp('' + regex, params)).test(string);
        },

        trim: function (string) {
            return string ? string.replace(/^\s+|\s+$/g, '') : '';
        },

        camelCase: function (string) {
            return string ? string.replace(/-\D/g, function (match) {
                return match.charAt(1).toUpperCase();
            }) : '';
        },

        hyphenate: function (string) {
            return string ? string.replace(/[A-Z]/g, function (match) {
                return ('-' + match.charAt(0).toLowerCase());
            }) : '';
        },

        capitalize: function (string) {
            return string ? string.replace(/\b[a-z]/g, function (match) {
                return match.toUpperCase();
            }) : '';
        },

        escapeRegExp: function (string) {
            return string ? string.replace(/([-.*+?^${}()|[\]\/\\])/g, '\\$1') : '';
        },

        substitute: function (string, object, regexp) {
            return string ? string.replace(regexp || (/\\?\{([^{}]+)\}/g), function (match, name) {
                if (match.charAt(0) == '\\') {
                    return match.slice(1);
                }
                return (object[name] != null) ? object[name] : '';
            }) : '';
        },

        /*
         * Date对象转字符串
         * @param date 要转换的Date对象
         * @param pattern 字符串格式模板，其中字符"y"、"M"、"d"、"h"、"m"、"s"、"w"、"q"、"S"会被替代成日期对应的数值，长度由字符数决定
         * @return 满足pattern格式的字符串
         * 例：
         * pattern 为 "yyyy-MM-dd"，则返回如 "2015-08-18"
         * pattern 为 "yyyy-MM-dd hh:mm:ss"，则返回如 "2015-08-18 09:41:13"
         * pattern 为 "yyyy-MM-dd hh:mm:ss"，则返回如 "2015-08-18 09:41:13"
         * pattern 为 "yyyy-MM-dd hh:mm:ss +(S)"，则返回如 "2015-08-18 09:43:44 +(902)"
         * pattern 为 "yy年第q季度"，则返回如 "15年第3季度"
         * pattern 为 "今天是星期w"，则返回如 "今天是星期二"
         * */
        dateToStr: function(date, pattern) {
            if(this.isStr(date)){
                return date;
            }

            var pattern = pattern || 'yyyy-MM-dd';

            var o = {
                "M+" : date.getMonth() + 1, //month 从 Date 对象返回月份 (0 ~ 11)，加以改成1~12月份
                "d+" : date.getDate(),      //day 从 Date 对象返回一个月中的某一天 (1 ~ 31)。
                "h+" : date.getHours(),     //hour 返回 Date 对象的小时 (0 ~ 23)。
                "m+" : date.getMinutes(),   //minute  返回 Date 对象的分钟 (0 ~ 59)。
                "s+" : date.getSeconds(),   //second 返回 Date 对象的秒数 (0 ~ 59)。
                "w+" : "\u65e5\u4e00\u4e8c\u4e09\u56db\u4e94\u516d".charAt(date.getDay()),   //“日一二三四五六”中的某一个
                "q+" : Math.floor((date.getMonth() + 3) / 3),  //季度
                "S"  : date.getMilliseconds() //Date 对象的毫秒(0 ~ 999)。
            };

            //将"yyyy-MM-dd"中的"y"替换成具体年份，并根据"y"的数量保留对应的位数
            if (new RegExp("(y+)").test(pattern)) {
                pattern = pattern.replace(RegExp.$1, (date.getFullYear() + "").substr(4 - RegExp.$1.length));
            }

            //替换剩下的模板，如果有的模板长度大于1，如"yyyy-MM-dd"中的"MM"、"dd"，则定为两位，实际字符串长度不足两位的，前面用0补足
            for(var k in o){
                if (new RegExp("("+ k +")").test(pattern)){
                    pattern = pattern.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length));
                }
            }

            return pattern;
        },

        /*
         * 字符串转换成Date对象
         * @param str 要转换的字符串
         * @param delimiter 字符串年月日分隔符，默认为 "-"
         * @param pattern 字符串年月日模板（年月日的索引对应字符串按delimiter分割后的数组索引）
         * @return 日期对象
         * 例：
         * app.strToDate('2015-03-28') 返回Sat Mar 28 2015 00:00:00 GMT+0800 (中国标准时间) 的Date对象
         * app.strToDate('2015/03/28', '/') 返回Sat Mar 28 2015 00:00:00 GMT+0800 (中国标准时间) 的Date对象
         * app.strToDate('03/28/2015', '/', 'mdy') 返回Sat Mar 28 2015 00:00:00 GMT+0800 (中国标准时间) 的Date对象
         * */
        strToDate: function(str, delimiter, pattern) {
            if(this.isDate(str)){
                return str;
            }

            delimiter = delimiter || "-";
            pattern = pattern || "ymd";
            var a = str.split(delimiter);//当前字符串值的年月日数组
            var y = parseInt(a[pattern.indexOf("y")], 10);//年的整数值

            //如果年的值小，则为20xx年
            if(y.toString().length <= 2) {
                y += 2000;
            }

            //如果年不是数字，则取当前年
            if(isNaN(y)) {
                y = new Date().getFullYear();
            }

            var m = parseInt(a[pattern.indexOf("m")], 10) - 1;//月为字符串月的整数值减一，变成下班从0开始

            var d = parseInt(a[pattern.indexOf("d")], 10);//字符串日的整数值

            //如果日不是数字，则是1日
            if(isNaN(d)) {
                d = 1;
            }

            return new Date(y, m, d);
        },

        addYear: function(date, n, month, day){
            return new Date(date.getFullYear() + (n || 0), month || date.getMonth(), day || date.getDate());
        },

        // 返回date所在月份的后面第n个月，日为day或与当前日相同，如果当前日超过后面第n个月的最大日数，则返回后面第n + 1个月的稍前日
        addMonth: function(date, n, day){
            return new Date(date.getFullYear(), date.getMonth() + (n || 0), day === 0 ? day : (day || date.getDate()));
        },

        addDay: function(date, n){
            return new Date(date.getTime() + (n || 0) * 86400000); // 86400000 == 24 * 60 * 60 * 1000
        },

        yearFirstDay: function(date){
            return new Date(date.getFullYear(), 0, 1);
        },

        yearLastDay: function(date){
            return new Date(date.getFullYear(), 12, 0);
        },

        monthFirstDay: function(date){
            return new Date(date.getFullYear(), date.getMonth(), 1);
        },

        monthLastDay: function(date){
            return new Date(date.getFullYear(), date.getMonth() + 1, 0);
        },

        indexOf: function (array, item, from) {
            var length = array.length;
            for(var i = (from < 0) ? Math.max(0, length + from) : from || 0; i < length; i++){
                if (array[i] === item) return i;
            }
            return -1;
        },

        append: function (array, newArray) {
            array.push.apply(array, newArray);
            return array;
        },

        getLast: function (array) {
            return (array.length) ? array[array.length - 1] : null;
        },

        include: function (array, item) {
            if (!this.contains(array, item)) {
                array.push(item);
            }
            return array;
        },

        combine: function (array, newArray) {
            for (var i = 0, l = newArray.length; i < l; i++) {
                array.include(newArray[i]);
            }
            return array;
        },

        pick: function (array) {
            for (var i = 0, l = array.length; i < l; i++) {
                if (array[i] != null) {
                    return array[i];
                }
            }
            return null;
        },



        merge: function (source, k, v) {

            if (this.isStr(k)) {
                source[k] = v;
            }
            else{
                for (var i = 1, l = arguments.length; i < l; i++) {
                    var object = arguments[i];
                    for (var key in object) {
                        if(this.isObj(object[key])){
                            if(this.isObj(source[key])){
                                source[key] = this.merge(source[key], object[key]);
                            }
                            else if(this.isObj(source)){
                                source[key] = this.merge({}, object[key]);
                            }
                        }
                        else if(this.isObj(source)){
                            source[key] = object[key];
                        }
                    }
                }
            }
            return source;
        },

        mergeIf: function(source, k, v){
            if (this.isStr(k)) {
                if(!source[k]){
                    source[k] = v;
                }
            }
            else{
                for (var i = 1, l = arguments.length; i < l; i++) {
                    var object = arguments[i];
                    for (var key in object) {
                        if(this.isObj(object[key])){
                            if(this.isObj(source[key])){
                                source[key] = this.merge(source[key], object[key]);
                            }
                            else if(this.isObj(source)){
                                if(!source[key]){
                                    source[key] = this.clone(object[key]);
                                }
                            }
                        }
                        else if(this.isObj(source)){
                            if(!this.isDef(source[key])){
                                source[key] = this.clone(object[key]);
                            }
                        }
                    }
                }
            }
            return source;
        },

        aquire: function(source, k, v) {

            if (this.isStr(k)) {
                source[k] = v;
            }
            else{
                for (var i = 1, l = arguments.length; i < l; i++) {
                    var object = arguments[i];
                    for (var key in object) {
                        source[key] = object[key];
                    }
                }
            }
            return source;
        },

        aquireIf: function(source, k, v) {

            if (this.isStr(k) && !this.isDef(source[k])) {
                source[k] = v;
            }
            else{
                for (var i = 1, l = arguments.length; i < l; i++) {
                    var object = arguments[i];
                    for (var key in object) {
                        if(!this.isDef(source[key])){
                            source[key] = object[key];
                        }
                    }
                }
            }
            return source;
        },

        subset: function () {
            var object = arguments[0],
                results = {};

            for (var i = 1, l = arguments.length; i < l; i++) {
                var k = arguments[i];
                if (k in object) {
                    results[k] = object[k];
                }
            }
            return results;
        },

        keys: function (object) {
            var keys = [];
            for (var key in object) {
                if(object.hasOwnProperty && object.hasOwnProperty(key)){
                    keys.push(key);
                }
            }
            return keys;
        },

        values: function (object) {
            var values = [];
            for (var key in object) {
                if(object.hasOwnProperty && object.hasOwnProperty(key)){
                    values.push(object[key]);
                }
            }
            return values;
        },

        getLength: function (object) {
            return this.keys(object).length;
        },

        keyOf: function (object, value) {
            for (var key in object) {
                if (object.hasOwnProperty && object.hasOwnProperty(key) && object[key] === value) {
                    return key;
                }
            }
            return null;
        },



        clone: function (object) {
            var clone;
            if(this.isArr(object)){
                clone = [];
                for(var i = 0, len = object.length; i < len; i++){
                    clone.push(this.clone(object[i]));
                }
            }
            else if(this.isObj(object)){
                clone = {};
                for (var key in object) {
                    clone[key] = this.clone(object[key]);
                }
            }
            else{
                clone = object;
            }
            return clone;
        },

        forEach: function (object, fn, bind, inverse) {

            if(this.isObj(object)){
                for (var key in object) {
                    var a = object[key];
                    if(object.hasOwnProperty && object.hasOwnProperty(key)){
                        fn.call(bind || this, object[key], key, object);
                    }
                }
            }
            else if(this.isArr(object) || (object && object.length)){
                if(inverse){
                    for (var n = object.length - 1; n >= 0; n--) {
                        fn.call(bind || this, object[n], n, object);
                    }
                }
                else{
                    for (var i = 0, l = object.length; i < l; i++) {
                        fn.call(bind || this, object[i], i, object);
                    }
                }
            }
        },

        every: function (object, fn, bind, inverse) {
            if(this.isArr(object)){
                if(inverse){
                    for (var n = object.length - 1; n >= 0; n--) {
                        if(!fn.call(bind || this, object[n], n, object)){
                            return false;
                        }
                    }
                }
                else{
                    for (var i = 0, l = object.length; i < l; i++) {
                        if(!fn.call(bind || this, object[i], i, object)){
                            return false;
                        }
                    }
                }
            }
            else{
                for (var key in object) {
                    if(object.hasOwnProperty && object.hasOwnProperty(key)){
                        if (!fn.call(bind || this, object[key], key, object)) {
                            return false;
                        }
                    }
                }
            }
            return true;
        },


        some: function (object, fn, bind, inverse) {
            if(this.isArr(object)){
                if(inverse){
                    for (var n = object.length - 1; n >= 0; n--) {
                        if(fn.call(bind || this, object[n], n, object)){
                            return true;
                        }
                    }
                }
                else{
                    for (var i = 0, l = object.length; i < l; i++) {
                        if (fn.call(bind || this, object[i], i, object)) {
                            return true;
                        }
                    }
                }
            }
            else{
                for (var key in object) {
                    if(object.hasOwnProperty && object.hasOwnProperty(key)){
                        if (fn.call(bind || this, object[key], key, object)) {
                            return true;
                        }
                    }
                }
            }
            return false;
        },


        filter: function (object, fn, bind, inverse) {
            var results;
            if(this.isArr(object)){
                results = [];

                if(inverse){
                    for (var n = object.length - 1; n >= 0; n--) {
                        if(fn.call(bind || this, object[n], n, object)){
                            results.push(object[n]);
                        }
                    }
                }
                else{
                    for (var i = 0, l = object.length; i < l; i++) {
                        if (fn.call(bind || this, object[i], i, object)) {
                            results.push(object[i]);
                        }
                    }
                }
            }
            else{
                results = {};

                for (var key in object) {
                    if(object.hasOwnProperty && object.hasOwnProperty(key)){
                        if (fn.call(bind || this, object[key], key, object)) {
                            results[key] = object[key];
                        }
                    }
                }
            }
            return results;
        },


        map: function (object, fn, bind, inverse) {
            var results;
            if(this.isArr(object)){
                results = [];

                if(inverse){
                    for (var n = object.length - 1; n >= 0; n--) {
                        results.push(fn.call(bind || this, object[n], n, object));
                    }
                }
                else{
                    for (var i = 0, len = object.length; i < len; i++) {
                        results.push(fn.call(bind || this, object[i], i, object));
                    }
                }
            }
            else{
                results = {};
                for (var key in object) {
                    if(object.hasOwnProperty && object.hasOwnProperty(key)){
                        results[key] = fn.call(bind || this, object[key], key, object);
                    }
                }
            }
            return results;
        },

        clean: function (object) {
            if(this.isStr(object)){
                return this.trim(object.replace(/\s+/g, ' '));
            }
            else{
                return this.filter(object, function (item) {
                    return item != null;
                });
            }
        },

        contains: function (object, item, from) {
            if(this.isStr(object)){
                return object.indexOf(item) > -1;
            }
            else if(this.isArr(object)){
                return this.indexOf(object, item, from) != -1;
            }
            else{
                return this.keyOf(object, item) != null;
            }
        },

        erase: function (object, item) {
            if(this.isArr(object)){
                for (var i = object.length; i--; ) {
                    if (object[i] === item) {
                        object.splice(i, 1);
                    }
                }
            }
            else{
                for (var key in object) {
                    if(object.hasOwnProperty && object.hasOwnProperty(key) && key === item){
                        delete object[key];
                    }
                }
            }
            return object;
        },


        empty: function (object) {
            if(this.isArr(object)){
                object.length = 0;
            }
            else{
                for (var key in object) {
                    if(object.hasOwnProperty && object.hasOwnProperty(key)){
                        delete object[key];
                    }
                }
            }
            return object;
        },



        getDocBody: function(){
            return document.compatMode == 'CSS1Compat' ? document.documentElement : document.body;
        },

        execScript: function(text){
            if (!text) {
                return text;
            }

            //IE支持window.execScript
            if (window.execScript) {
                window.execScript(text);
            }
            else {
                var script = document.createElement('script');
                script.setAttribute('type', 'text/javascript');
                script.text = text;
                document.head.appendChild(script);
                document.head.removeChild(script);
            }
            return text;
        },

        stripScripts: function (text, exec) {
            var scripts = '';
            var text = text.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function (all, code) {
                scripts += code + '\n';
                return '';
            });

            if (exec === true) {
                this.execScript(scripts);
            }
            else if (typeof exec == 'function') {
                exec(scripts, text);
            }

            return text;
        },

        loadScripts: function(text){
            var text = text.replace(/<script([^>]*?)>[^<>]*<\/script>/gi, function (all, code) {
                var src = code.match(/\ssrc=["']([\s\S]*)['"]/i)[1];
                this.importScript(src);
            });

            return text;
        },

        importScript: function(src){
            var script = document.createElement('script');
            script.setAttribute('type', 'text/javascript');
            script.setAttribute('src', src);
            document.head.appendChild(script);
            //document.head.removeChild(script);
        },

        rgbToHex: function (string) {
            var rgb = string.match(/\d{1,3}/g);
            if(rgb){
                if(rgb.length < 3){
                    return null;
                }
                if(rgb.length == 4 && rgb[3] == 0){
                    return 'transparent';
                }

                var hex = [];
                for (var i = 0; i < 3; i++) {
                    var bit = (rgb[i] - 0).toString(16);
                    hex.push((bit.length == 1) ? '0' + bit : bit);
                }
                return  '#' + hex.join('');
            }
            else{
                return null;
            }
        },

        hexToRgb: function (string) {
            var hex = string.match(/^#?(\w{1,2})(\w{1,2})(\w{1,2})$/);
            if(hex){
                var rgbs = hex.slice(1),
                    len = rgbs.length,
                    rgb = [];

                if (len != 3) {
                    return null;
                }

                for(var i = 0; i < len; i++){
                    var value = rgbs[i];
                    if(value.length == 1){
                        value += value;
                    }
                    rgb.push(parseInt(value, 16));
                }

                return 'rgb(' + rgb + ')';
            }
            else{
                return null;
            }
        },


        dateToJson: (function(){

            var f = function(n) {
                return n < 10 ? '0' + n : n;
            };

            return function(date){

                return isFinite(date.valueOf()) ?
                    date.getUTCFullYear()     + '-' +
                        f(date.getUTCMonth() + 1) + '-' +
                        f(date.getUTCDate())      + 'T' +
                        f(date.getUTCHours())     + ':' +
                        f(date.getUTCMinutes())   + ':' +
                        f(date.getUTCSeconds())   + 'Z' : null;
            };
        })(),

        //ie7下没有window.JSON，因此出现分支
        encodeJson: (window.JSON && window.JSON.stringify) ? function(obj){
            return window.JSON.stringify(obj);
        } : (function(){

            var special = {'\b': '\\b', '\t': '\\t', '\n': '\\n', '\f': '\\f', '\r': '\\r', '"' : '\\"', '\\': '\\\\'};

            var escape = function(chr){
                return special[chr] || '\\u' + ('0000' + chr.charCodeAt(0).toString(16)).slice(-4);
            };

            return function(obj){

                switch (app.typeOf(obj)){
                    case 'string':
                        return '"' + obj.replace(/[\x00-\x1f\\"]/g, escape) + '"';
                    case 'array':
                        return '[' + app.clean(app.map(obj, app.encodeJson)) + ']';
                    case 'date':
                        return app.encodeJson(app.dateToJson(obj));
                    case 'object':
                        var string = [];
                        app.forEach(obj, function(value, key){
                            var json = app.encodeJson(value);
                            if (json) {
                                string.push(app.encodeJson(key) + ':' + json);
                            }
                        });
                        return '{' + string + '}';
                    case 'number':
                    case 'boolean':
                        return '' + obj;
                    case 'null':
                        return 'null';
                }

                return null;
            };
        })(),

        //ie7下没有window.JSON，因此出现分支
        decodeJson: (window.JSON && window.JSON.parse) ? function(string){
            if (!string || !app.isStr(string)) {
                return null;
            }
            return window.JSON.parse(string);
        } : function(string){
            if (!string || !app.isStr(string)) {
                return null;
            }
            return eval('(' + string + ')');
        },


        toQueryString: function(object, base){

            if(typeof object == 'string'){
                return object;
            }

            var queryString = [];

            this.forEach(object, function(value, key){
                if (base) {
                    key = base + '[' + key + ']';
                }
                var result;
                switch (this.typeOf(value)){
                    case 'object':
                        result = this.toQueryString(value, key); break;
                    case 'array':
                        var qs = {};
                        app.forEach(value,function(val, i){
                            qs[i] = val;
                        });
                        result = this.toQueryString(qs, key);
                        break;
                    default:
                        result = key + '=' + encodeURIComponent(value);
                }
                if (value != null) {
                    queryString.push(result);
                }
            }, this);

            return queryString.join('&');
        },

        encodeSearch: function(name, value, search){

            if(typeof name == 'string'){
                search = search || location.search;

                if(search){
                    if(search.indexOf('?') != 0){
                        search = '?' + search;
                    }

                    if(search.match(new RegExp('&' + name + '=[^&]*'))){
                        return search.replace(new RegExp('&' + name + '=[^&]*'), function(old){
                            var params = old.split('=');
                            params[1] = value;
                            return (value || typeof value == 'number') ? params.join('=') : '';
                        });
                    }
                    else if(search.match(new RegExp('\\?' + name + '=(\\s|\\S)&'))){
                        return search.replace(new RegExp('\\?' + name + '=(\\s|\\S)&'), function(old){
                            var params = old.split('=');
                            params[1] = value + '&';
                            return (value || typeof value == 'number') ? params.join('=') : '?';
                        });
                    }
                    else if(search.match(new RegExp('\\?' + name + '=[^&]*'))){
                        return search.replace(new RegExp('\\?' + name + '=[^&]*'), function(old){
                            var params = old.split('=');
                            params[1] = value;
                            return (value || typeof value == 'number') ? params.join('=') : '?';
                        });
                    }
                    else{
                        return search + ((value || typeof value == 'number') ? ('&' + name + '=' + value) : '');
                    }
                }
                else{
                    return (value || typeof value == 'number') ? ('?' + name + '=' + value) : '';
                }
            }
            else{
                search = value;
                for(var p in name){
                    search = this.encodeSearch(p, name[p], search);
                }
                return search;
            }
        },

        decodeSearch: function(obj, search){
            var newObj = {},
                match;

            if(obj){
                if(app.isStr(obj)){
                    newObj = this.decodeSearch(null, obj);
                }
                else{
                    search = decodeURIComponent(search || location.search);
                    for(var p in obj){
                        match = search.match(new RegExp('[?&]' + p + '=([^&]*)'));
                        newObj[p] = match ? match[1] : obj[p];
                    }
                }
            }
            else{
                search = decodeURIComponent(search || location.search);
                search.replace(new RegExp('[?&]([\\S]+)*?[^&]*', 'g'), function(expression){
                    var exp = expression.replace(/^\s+|\s+$/g, '').slice(1),
                        exps;
                    if(exp){
                        exps = exp.split('=');
                        if(exps){
                            newObj[exps[0]] = exps[1] || '';
                        }
                    }
                });
            }

            return newObj;
        },

        search: function(name, value, overwrite){
            if(!this.isDef(name)){
                return this.decodeSearch();
            }
            else if(this.isObj(name)){
                //value此时等同于overwrite
                if(value){
                    var search = [];
                    this.forEach(name, function(v, p){
                        search.push(p + '=' + v);
                    });
                    location.search = '?' + search.join('&');
                }
                else{
                    location.search = this.encodeSearch(name);
                }
            }
            else{
                if(!this.isDef(value)){
                    if(this.isStr(name)){
                        var match = location.search.match(new RegExp('[?&]' + name + '=([^&]*)'));
                        return match ? match[1] : '';
                    }
                    else if(this.isArr(name)){
                        var search = location.search,
                            newObj = {};

                        this.forEach(name, function(n, index){
                            match = search.match(new RegExp('[?&]' + n + '=([^&]*)'));
                            newObj[n] = match ? match[1] : '';
                        });
                        return newObj;
                    }
                }
                else{
                    if(overwrite){
                        location.search = ('?' + name + '=' + value);
                    }
                    else{
                        location.search = this.encodeSearch(name, value);
                    }
                }
            }
        },

        eventObject: {},

        addEvent: function (type, fn) {
            if(this.isStr(type)){
                if(!fn){
                    return this;
                }
                else if(this.isArr(fn)){
                    this.forEach(fn, function(elem, index){
                        this.addEvent(type, elem);
                    }, this);
                }
                else {
                    var types = this.eventObject[type] || [];

                    if(this.indexOf(types, fn) == -1){
                        types.push(fn);
                    }

                    this.eventObject[type] = types;
                }
            }
            else{
                for (var t in type) {
                    this.addEvent(t, type[t]);
                }
            }
            return this;
        },

        fireEvent: function () {
            var type = arguments[0],
                args = Array.prototype.slice.call(arguments, 1),
                events = this.eventObject[type];

            if (!events) {
                return this;
            }
            args = args || [];
            this.forEach(events, function (fn) {
                if(fn.apply(this, args) === false){
                    return false;
                }
            }, this);
            return true;
        },

        removeEvent: function (type, fn) {
            if(!type){
                this.eventObject = {};
            }
            else if(this.isStr(type)){
                if(!fn){
                    this.eventObject[type] = [];
                }
                else{
                    var events = this.eventObject[type];
                    if (events) {
                        var index = this.indexOf(events, fn);
                        if (index != -1) {
                            delete events[index];
                        }
                    }
                }
            }
            else{
                for(var t in type){
                    this.removeEvent(t, type[t]);
                }
            }
            return this;
        }
    };

    methods.aquire(app, methods);

    /* 扩展属性、方法 */
    methods.aquire(app, {
        supportsTouch: ('ontouchstart' in window),

        win: $(window),

        body: $('body'),

        formParams: function(form){
            var This = this,
                formEl = $(form),
                datas = [];

            formEl.find('input:not([type=checkbox],[type=radio]), select, textarea').each(function(index, node){
                if(node['name'] && !$(node).prop('disabled')){
                    var key = node['name'];
                    datas.push(key + '=' + node['value']);
                }
            });

            formEl.find('input[type=radio]:checked').each(function(index, node){
                if(node['name'] && !$(node).prop('disabled')){
                    var key = node['name'];
                    datas.push(key + '=' + node['value']);
                }
            });

            formEl.find('input[type=checkbox]:checked').each(function(index, node){
                if(node['name'] && !$(node).prop('disabled')){
                    var key = node['name'];
                    datas.push(key + '=' + node['value']);
                }
            });

            return datas.join('&');
        },

        tplToHtml: function(tpl, datas){
            var items = [],
                datas = this.isArr(datas) ? datas : [datas];

            this.forEach(datas, function(data){
                items.push(tpl.replace(new RegExp("[\\{](\\s|\\S)*?[\\}]", "g"), function(prop) {
                    var value = data[prop.slice(1,-1)];
                    return app.isNum(value) ? value : value || '';
                }));
            });

            return items.join('');
        },

        prevFillWord: function(str, word, len){
            return (new Array(len + 1).join(word) + str).substr(("" + str).length);
        },

        converToZero: function(str){
            return ('00' + str).substr(('' + str).length);
        }
    });



    /* jquery扩展 */
    $.fn.extend({
        //动画
        startFx: function(node, styles, speed, easing, callback){
            $(node).stop(true).animate(styles, speed, easing, callback);
        },
        //事件是否在节点上
        hovered: function(node, event){
            var node = $(node),
                pos = node.position(),
                w = node.width(),
                h = node.height(),
                l = pos.left,
                r = l + w,
                t = pos.top,
                b = t + h;

            return (event.pageX >= l && event.pageX <= r && event.pageY >= t && event.pageY <= b);
        },
        // 更新select内容
        sUpdate: function(datas, valueProp, textProp, append, callback, value, disableOpt){
            var node = this.get(0);

            if(!node){
                return;
            }

            valueProp = valueProp || 'value';
            textProp = textProp || 'text';

            if(!append){
                node.options.length = 0;
            }

            for(var i = 0, len = datas.length; i < len; i++){
                var option = document.createElement("option");

                // 对于不满足callback条件的option，使禁用
                if(disableOpt){

                    node.options.add(option);
                    option.setAttribute('value', datas[i][valueProp]);

                    // 如果callback返回假值，当前option为禁用
                    if(typeof callback == 'function' && !callback(datas[i], option)){
                        option.disabled = true;
                    }
                    else if(value == datas[i][valueProp]){ //如果option值为指定值，设为选中
                        option.setAttribute('selected', '1');
                    }

                    option.appendChild(document.createTextNode(datas[i][textProp]));
                }
                else if(typeof callback != 'function' || callback(datas[i], option)){ // 如果没有callback，或者callback返回真值，才添加option
                    node.options.add(option);
                    option.setAttribute('value', datas[i][valueProp]);
                    if(value == datas[i][valueProp]){
                        option.setAttribute('selected', '1');
                    }
                    option.appendChild(document.createTextNode(datas[i][textProp]));
                }
            }
        }
    });


    return app;
});