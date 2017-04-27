define(['../../app'], function(app) {

    var Cookie = {
        write: function (key, value, options) {

            var options = app.merge({
                path: '/',
                domain: false,
                expires: 0,
                secure: false,
                encode: true
            }, (app.isStr(key) ? options : value) || {});

            if(app.isStr(key)){

                if (options.encode) {
                    value = encodeURIComponent(value);
                }
                if (options.domain) {
                    value += '; domain=' + options.domain;
                }
                if (options.path) {
                    value += '; path=' + options.path;
                }
                if (options.expires) {
                    var date = new Date();
                    date.setTime(date.getTime() + options.expires * 86400000); // 86400000 = 24 * 60 * 60 * 1000
                    value += '; expires=' + date.toGMTString();
                }
                if (options.secure) {
                    value += '; secure';
                }
                document.cookie = key + '=' + value;
            }
            else{
                app.forEach(key, function(v, k){
                    this.write(k, v, options);
                }, this);
            }

            return this;
        },

        read: function (key) {
            if(app.isStr(key)){
                var value = document.cookie.match('(?:^|;)\\s*' + key.replace(/([-.*+?^${}()|[\]\/\\])/g, '\\$1') + '=([^;]*)');
                return (value) ? decodeURIComponent(value[1]) : null;
            }
            else{
                var obj = {};
                app.forEach(key, function(v, i){
                    obj[v] = this.read(v);
                }, this);
                return obj;
            }
        },

        dispose: function () {

            if(arguments.length <= 0){
            }
            else if(arguments.length > 1 && app.isObj(arguments[arguments.length - 1])){
                var options = app.merge(Array.prototype.pop.call(arguments) || {}, {expires: -1});
                app.forEach(arguments, function(arg){
                    this.write(arg, '', options);
                }, this);
            }
            else{
                app.forEach(arguments, function(arg){
                    this.write(arg, '', { expires: -1 });
                }, this);
            }
            return this;
        }
    };

    return Cookie;
});