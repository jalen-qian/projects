define('demo', [
    '../app', '../action/util/Cookie'
], function(app,  Cookie){ // 在define中引入app和Cookie，表示app.js和Cookie.js的源码引入demo.js，合并成一个大的

    Cookie.write('aaa', 'bbb'); // Cookie.js的方法可以直接用


    console.log('demo页运行……');

    /*
    *  注：现在同时兼容多文件合并压缩成单文件，和按需引入文件两种方式，可灵活使用，
    *  由于项目规模较小，建议尽量采用合并打包的方式，这样代码会简洁一些
    * */
});

require(['demo']); // demo模块运行