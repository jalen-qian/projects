/** 
 * 功能：分销系统公用表单数据验证插件
 * 时间：2015-12-18
 * 作者：GuoShuoHui
 * 联系：2604637615
 *
 */

; (function ($) {

    $.verify = {

        /**
         * 获取字节长度 ok
         * @param {string} val 要计算字节长度的字符串
	     * @return number 字节长度
         */

        getByte: function (val) {
            var len = 0;
            for (var i = 0; i < val.length; i++) {
                if (val[i].match(/[^\x00-\xff]/ig) != null) { //全角
                    len += 2;
                } else {
                    len += 1;
                }
            };
            return len;
        },

        /**
         * 公司名称 ok
         * @param {object} options 参数对象
	     * @return 空、公司名称
         * @rules 中英文、数字、下划线_、减号-、中文括号（）、英文括号()
         */

        company: function (options) {

            var options = $.extend({
                elem: null,
                must: true
            }, options);

            var $elem = options.elem instanceof jQuery ? options.elem : $(options.elem);
            var result = '';

            if ($elem.length) {

                var defaultValue = $elem[0].defaultValue;
                var val = $.trim($elem.val());
                var reg = /^[A-Za-z0-9_()（）\-\u4e00-\u9fa5]+$/;

                if (options.must) {
                    result = (val == defaultValue || !reg.test(val)) ? '' : val;
                } else {
                    if (val != '' && val != defaultValue) {
                        result = reg.test(val) ? val : '';
                    } else {
                        result = true;
                    }
                }

            }

            return result;

        },

        /**
         * 电话号码/传真 ok
         * @param object 参数对象options
	     * @return 空、公司名称
         * @rules 数字、减号-
         */

        telephone: function (options) {

            var options = $.extend({
                elem: null,
                must: true
            }, options);

            var $elem = options.elem instanceof jQuery ? options.elem : $(options.elem);
            var result = '';

            if ($elem.length) {

                var defaultValue = $elem[0].defaultValue;
                var val = $.trim($elem.val());
                var reg = /^[0-9-]+$/;

                if (options.must) {
                    result = !reg.test(val) ? '' : val;
                } else {
                    if (val != '' && val != defaultValue) {
                        result = reg.test(val) ? val : '';
                    } else {
                        result = true;
                    }
                }

            }

            return result;

        },

        /**
         * 手机号码 ok
         * @param object 参数对象options
	     * @return 空、公司名称
         * @rules 11位手机号
         */

        mobilePhone: function (options) {

            var options = $.extend({
                elem: null,
                must: true
            }, options);

            var $elem = options.elem instanceof jQuery ? options.elem : $(options.elem);
            var result = '';

            if ($elem.length) {

                var defaultValue = $elem[0].defaultValue;
                var val = $.trim($elem.val());
                var dReg = /^1[3578][01379]\d{8}$/g; // 电信
                var lReg = /^1[34578][01256]\d{8}$/g; // 联通
                var yReg = /^(134[0-8]\d{7}|1[34578][0-47-8]\d{8})$/g; // 移动

                if (options.must) {
                    if (val != defaultValue && (dReg.test(val) || lReg.test(val) || yReg.test(val))) {
                        result = val;
                    } else {
                        result = '';
                    }
                } else {
                    if (val != '' && val != defaultValue) {
                        result = reg.test(val) ? val : '';
                    } else {
                        result = true;
                    }
                }

            }

            return result;

        },

        /**
         * QQ号码 ok
         * @param object 参数对象options
	     * @return 空、公司名称
         * @rules 数字
         */

        qq: function (options) {

            var options = $.extend({
                elem: null,
                must: false
            }, options);

            var $elem = options.elem instanceof jQuery ? options.elem : $(options.elem);
            var result = '';

            if ($elem.length) {

                var defaultValue = $elem[0].defaultValue;
                var val = $.trim($elem.val());
                var reg = /^[0-9]+$/;

                if (options.must) {
                    result = (val == defaultValue || !reg.test(val)) ? '' : val;
                } else {
                    if (val != '' && val != defaultValue) {
                        result = reg.test(val) ? val : '';
                    } else {
                        result = true;
                    }
                }

            }

            return result;

        },

        /**
         * email ok
         * @param object 参数对象options
	     * @return 空、公司名称
         * @rules 必须带@
         */

        email: function (options) {

            var options = $.extend({
                elem: null,
                must: false
            }, options);

            var $elem = options.elem instanceof jQuery ? options.elem : $(options.elem);
            var result = '';

            if ($elem.length) {

                var defaultValue = $elem[0].defaultValue;
                var val = $.trim($elem.val());
                var reg = /\w+((-w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+/;

                if (options.must) {
                    result = (val == defaultValue || !reg.test(val)) ? '' : val;
                } else {
                    if (val != '' && val != defaultValue) {
                        result = reg.test(val) ? val : '';
                    } else {
                        result = true;
                    }
                }

            }

            return result;

        },

        /**
         * 注册编码 ok
         * @param object 参数对象options
	     * @return 空、公司名称
         * @rules 中英文、数字、下划线、横杆
         */

        crCode: function (options) {

            var options = $.extend({
                elem: null,
                must: false
            }, options);

            var $elem = options.elem instanceof jQuery ? options.elem : $(options.elem);
            var result = '';

            if ($elem.length) {

                var defaultValue = $elem[0].defaultValue;
                var val = $.trim($elem.val());
                var reg = /^[A-Za-z0-9_\-\u4e00-\u9fa5]+$/;

                if (options.must) {
                    result = (val == defaultValue || !reg.test(val)) ? '' : val;
                } else {
                    if (val != '' && val != defaultValue) {
                        result = reg.test(val) ? val : '';
                    } else {
                        result = true;
                    }
                }

            }

            return result;

        },

        /**
         * 登录账号/用户名 ok
         * @param object 参数对象options
	     * @return 空、公司名称
         * @rules 中英文、数字、下划线_或者减号-，最少4个或最多30个字节，1个中文等于2个字节
         */

        username: function (options) {

            var options = $.extend({
                elem: null,
                must: true
            }, options);

            var $elem = options.elem instanceof jQuery ? options.elem : $(options.elem);
            var result = '';

            if ($elem.length) {

                var defaultValue = $elem[0].defaultValue;
                var val = $.trim($elem.val());
                var reg = /^[A-Za-z0-9_\-\u4e00-\u9fa5]+$/;
                if (options.must) {
                    result = (val == defaultValue || !reg.test(val) || this.getByte(val) < 4 || this.getByte(val) > 30) ? '' : val;
                } else {
                    if (val != '' && val != defaultValue) {
                        result = (!reg.test(val) || this.getByte(val) < 4 || this.getByte(val) > 30) ? '' : val;
                    } else {
                        result = true;
                    }
                }

            }

            return result;

        },


        /**
         * 密码验证
         * @param object 参数对象options
	     * @return 空、公司名称
         * @rules 长度必须为6至32位,且包含数字或字母
         */

        password: function (options) {

            var options = $.extend({
                elem: null,
                must: true
            }, options);

            var $elem = options.elem instanceof jQuery ? options.elem : $(options.elem);
            var result = '';

            if ($elem.length) {

                var defaultValue = $elem[0].defaultValue;
                var val = $.trim($elem.val());
                var reg = /^[0-9|a-zA-Z]{6,32}$/;

                if (options.must) {
                    result = (val == defaultValue || !reg.test(val) || val.indexOf(' ') > -1) ? '' : val;
                } else {
                    if (val != '' && val != defaultValue) {
                        result = reg.test(val) ? val : '';
                    } else {
                        result = true;
                    }
                }

            }

            return result;

        }

    }

})(jQuery);