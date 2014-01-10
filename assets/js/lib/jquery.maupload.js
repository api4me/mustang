;(function(){
	var 
		UPLOAD_VERSION = "0.1",
		$ = window.jQuery || window.$ || (window.$ = {}),
		setting = {
            modal: "#modal-upload",
            file: '#modal-upload #upload',
			progress: '#modal-upload .progress',
			percent: '#modal-upload .bar',
            message: '#modal-upload span.label',
            url: '/api/upload',
			target: '#image',
            automodal: true
		}
	;
	if (!$.fn.modal) {
       	throw new Error("Bootstrap modal miss, please load before this.");
    }
	if (!$.fn.fileupload) {
       	throw new Error("jQuery plugin fileupload miss, please load before this.");
    }
	////////////////////////// PRIVATE METHODS ////////////////////////
	function _run(callback) {
        $(setting.file).fileupload({
            url: setting.url,
            dataType: 'json',
            acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
            maxNumberOfFiles: 1,
            fileInput: $(setting.file),
            maxFileSize: 5000,
            previewMaxWidth : 200,
            previewMaxHeight : 200,
            add: function(e, data) {
                data.submit();
                $(setting.modal).modal('show');
                $(setting.progress).show();
                $(setting.progress).addClass('progress-success').removeClass('progress-danger');
            },
            formData: {},
            progressall: function (e, data) {
                var percent = parseInt(data.loaded / data.total * 100, 10);
                $(setting.percent).css('width', percent + '%');
            },
            done: function (e, data) {
                if (data.textStatus != "success" || data.result.status != 0){
                    $(setting.progress).addClass('progress-danger').removeClass('progress-success');
                    data.result.msg && $(setting.message).addClass("label-important").removeClass("label-success");
                } else {
                    data.result.msg && $(setting.message).addClass("label-success").removeClass("label-important");
                    if (typeof callback == 'function') {
                        callback(data.result);
                    }
                    setTimeout(function(){
                         $(setting.file).fileupload('destroy');
                         $(setting.message).removeClass("label-success").removeClass("label-important").text('');
                         $(setting.progress).hide();
                    }, 500);
                }
                data.result.msg && $(setting.message).text(data.result.msg.replace(/<[^>].*?>/g,''));
            }
        });
    }

	function _init() {
        if (setting.automodal) {
            if ($('#modal-upload').length == 0) {
                var modal = '<div id="modal-upload" class="modal hide fade upload" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">'+
                    '<div class="modal-header">'+
                        '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>'+
                        '<h3 id="myModalLabel">文件上传</h3>'+
                    '</div>'+
                    '<div class="modal-body">'+
                        '<p>'+
                            (setting.file == '#modal-upload #upload' ?  '<input type="file" name="upload" id="upload" />' : '')+
                            '<span class="label"></span>'+
                        '</p>'+
                        '<div class="progress progress-striped">'+
                            '<div class="bar" style="width: 0%;"></div>'+
                        '</div>'+
                    '</div>'+
                    '<div class="modal-footer">'+
                        '<button class="btn" data-dismiss="modal" aria-hidden="true">关闭</button>'+
                    '</div>'+
                '</div>';
                $('body').append(modal);  
            }
        }
	}

	////////////////////////// PUBLIC INTERFACE /////////////////////////
	$.maupload = {
		version: UPLOAD_VERSION,
		run: function(callback){
            _init();
			_run(callback);
		},
        config: function(set) {
            if (typeof set != 'object') {
                return false;
            }
            for(i in set){
                if(set.hasOwnProperty(i) && setting.hasOwnProperty(i)) {
                    setting[i] = set[i];
                }
            }
        }        
	};
}
)();
