define(function(require){
    var $ = require('jquery');

    if (navigator.userAgent.match(/msie [678]/i)) {
        seajs.use('js/lib/html5shiv');
    }
    seajs.use('bootstrap/js/bootstrap.min');
    seajs.use('js/site.min');
});
