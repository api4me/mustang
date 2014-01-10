define(function(require, exports){
    var $ = require('jquery');
    exports.$ = $;

    if (navigator.userAgent.match(/msie [678]/i)) {
        seajs.use('js/lib/html5shiv');
    }
    seajs.use('bootstrap/js/bootstrap.min');
    seajs.use('editor/ckeditor');
    seajs.use('editor/ckfinder/ckfinder');
    seajs.use('js/lib/underscore-min');

    seajs.use('js/lib/jquery.upload');
    seajs.use('js/site.min');
});
