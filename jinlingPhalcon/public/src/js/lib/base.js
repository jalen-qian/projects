console.log("load base.js");
window.testing = false;
if (window.testing) {
    console.info("**************** 处于开发测试状态 **************");
}

/**
 * [validateOnline 判断当前网络是否可用]
 * @return {[type]} [description]
 */
function validateOnline(){
    if(!navigator.onLine){
        $.toast("网络有点问题，请重试");
        return false;
    } else {
        return true;
    }
    // $.ajax({
    //     type: "get",
    //     url: 'http://jslite.io/blank.gif',
    //     dataType: "json",
    //     data: {},
    //     success: function(data) {
    //         return true;
    //     },
    //     error: function(XMLHttpRequest, textStatus, errorThrown) {
    //         if(XMLHttpRequest.status != 200){
    //             $.toast('网络异常，请检查网络再重试');
    //             return false;
    //         } else {
    //             return true;
    //         }
    //     }
    // });
}

/**
 * [bindJLinkEvent 页面跳转事件绑定]
 * @return {[type]} [description]
 */
function bindJLinkEvent() {
    $(document).on("click", ".J_Link", function(e) {
        var $this = $(this),
            href = $this.data("href");
        if ($this.data("link") && $this.data("link") == "disabled") {
            return;
        }
        if (href) {
            if(!navigator.onLine){
                Layer.tips("网络有点问题，请重试");
                return;
            }
            location.href = href;
            console.log("跳转至：" + href);
        } else {
            console.log("点击但没跳转:缺少data-href值");
        }
    });
}

/*获取url中的参数*/
function getUrlParams(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null)
        return decodeURI(r[2]);
    return null;
}