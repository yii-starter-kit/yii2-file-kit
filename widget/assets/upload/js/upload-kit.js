(function( $ ) {
    jQuery.fn.yiiUploadKit = function(options) {
        var input = this;
        var container = input.parent('div')
        var files = $('<div>', {"class":"files"}).prependTo(container);
        var defaults =  {
            fileuploadOptions: {
                dataType: 'json',
                autoUpload: true,
                singleFileUploads: false,
                getNumberOfFiles: function(){
                    return container.find('.files .upload-kit-item').length;
                }
                //acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                //maxFileSize: 5000000 // 5 MB
            }
        }
        var settings = $.extend(true, defaults, options);

        var methods = {
            init: function(){
                container.addClass('upload-kit');
                input.wrapAll($('<div class="upload-kit-input"></div>'))
                    .after($('<span class="glyphicon glyphicon-plus-sign add"></span>'))
                    .after($('<span/>', {"data-toggle":"popover", "class":"glyphicon glyphicon-exclamation-sign error-popover"}))
                    .after(
                        '<div class="progress" style="display: none">'+
                        '<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>'+
                        '</div>'
                     )
                if(settings.fileuploadOptions && settings.fileuploadOptions.maxNumberOfFiles && settings.fileuploadOptions.maxNumberOfFiles > 1){
                    var name = input.attr('name');
                    if(name.indexOf('[]') !== name.length - 2){
                        input.attr('name', name + '[]');
                    }
                    input.attr('multiple', true);
                }
                container.find('input[type=hidden]').each(function(i, file){
                    $(this).replaceWith(methods.createItem({
                        url: $(this).val()
                    }))
                })
                methods.fileuploadInit();
                methods.dragInit()

            },
            fileuploadInit: function(){
                var fileuploadOptions = $.extend({}, {
                    url: settings.url,
                    dropZone: input.parents('div'),
                    add: function(e,data){
                        var $this = $(this);
                        container.find('.upload-kit-input').removeClass('error');
                        data.process(function () {
                            return $this.fileupload('process', data);
                        }).done(function(){
                            if (data.autoUpload || (data.autoUpload !== false)){
                                data.submit();
                            }
                        }).fail(function () {
                            var errors = [];
                            for(var i = 0; i < data.files.length; i++){
                                errors.push(data.files[i].name + ': ' + data.files[i].error);
                            }
                            methods.showError(errors.join('<br/>'))
                        });
                    },
                    start: function (e) {
                        input.removeClass('error').addClass('in-progress')
                    },
                    progressall: function (e, data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        input.siblings('.progress-bar').attr('aria-valuenow', progress).css(
                            'width',
                            progress + '%'
                        ).text(progress + '%');
                    },
                    done: function (e, data) {
                        if(data.result) {
                            for (var i = 0; i < data.result.length; i++) {
                                var file = data.result[i];
                                var item = methods.createItem(file)
;                               files.append(item)
                            }
                        }
                    },
                    fail: function (e, data) {
                        methods.showError(data.errorThrown)
                    },
                    always: function(e){
                        $(e.target).parents('.upload-kit-item')
                            .removeClass('in-progress')
                            .find('.progress-bar').attr('aria-valuenow', 0)
                            .css('width', 0)
                            .text();
                    }

                }, settings.fileuploadOptions)
                input.fileupload(fileuploadOptions)
            },
            dragInit: function(){
                $(document).bind('dragover', function (e)
                {
                    $(e.target).parents('.upload-kit-item').addClass('drag-hover');
                    e.preventDefault();
                });
            },
            showError: function(error){
                container.find('.error-popover').attr('data-content', error).popover({html:true,trigger:"hover"});
                container.find('.upload-kit-input').addClass('error');
            },
            removeItem: function(){
                this.remove();
            },
            createItem: function(file){
                var ext = file.url.split('.').pop().toLowerCase();
                var isImage = ['png', 'jpg', 'jpeg', 'jpe', 'gif', 'webp', 'svg'].indexOf(ext) !== -1
                var item = $('<div>', {"class": "upload-kit-item"})
                    .append($('<input/>', {"type":"hidden", "value": file.url}))
                    .append($('<span class="extension"></span>'))
                    .append($('<span class="glyphicon glyphicon-remove-circle remove"></span>'))
                item.addClass('done')
                item.on('click', '.remove', function(){
                    methods.removeItem.call($(this).parents('.upload-kit-item'))
                })
                if(isImage){
                    item.removeClass('not-image').addClass('image');
                    item.css('backgroundImage', 'url(' + file.url +')');
                    item.find('span.extension').text('');
                } else {
                    item.removeClass('image').addClass('not-image');
                    item.css('backgroundImage', '');
                    item.find('span.extension').text(ext);
                }
                return item;
            }
        }

        methods.init.apply(this);
        return this;
    };
    /*jQuery.fn.yiiUploadKit = function(options) {
        var container = this;
        options = $.extend( {
            maxFiles : 1,
            fileupload: {
                dataType: 'json',
                autoUpload: true,
                //acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                //maxFileSize: 5000000 // 5 MB
            }
        }, options);

        var methods = {
            init: function(){
                container.addClass('upload-kit');
                container.addClass(options.maxFiles > 1 ? 'multiply' : null);
                $('body').on('click', '.upload-action .remove', methods.remove);

                container.find('input[type=file]').each(function(i){
                    var input = $(this);
                    var file = input.attr('value');
                    var item = methods.createItem();
                    input.replaceWith(item)
                    if(file){
                        methods.success.call(item, file)
                    }
                    container.append(item)
                })
                methods.dragInit()

            },
            success: function(url){
                var ext = url.split('.').pop().toLowerCase();
                var isImage = ['png', 'jpg', 'jpeg', 'jpe', 'gif', 'webp', 'svg'].indexOf(ext) !== -1;
                this.find('input[type=file]').attr('disabled', 'disabled');
                this.addClass('done')

                    .find('input[type=hidden]')
                        .val(url);
                if(isImage){
                    this.removeClass('not-image').addClass('image');
                    this.css('backgroundImage', 'url(' + url +')');
                    this.find('span.extension').text('');
                } else {
                    this.removeClass('image').addClass('not-image');
                    this.css('backgroundImage', '');
                    this.find('span.extension').text(ext);
                }
            },
            remove: function(){
                var uploadKitItem = $(this).parents('.upload-kit-item');
                if(methods.getItemsCount() > 1){
                    uploadKitItem.remove();
                } else {
                    uploadKitItem
                        .removeClass('done')
                        .css('backgroundImage', 'none');

                    uploadKitItem
                        .find('input[type=hidden]')
                        .val(null);

                    uploadKitItem
                        .find('input[type=file]')
                        .removeAttr('disabled')
                        .attr('value', null)
                        .replaceWith(uploadKitItem.find('input[type=file]'))
                }
            },
            fileupload: function(){
                var fileuploadOptions = $.extend({}, {
                    url: options.url,
                    dropZone: this,
                    start: function (e) {
                        $(e.target).parents('.upload-kit-item').removeClass(['error', 'image', 'not-image']).addClass('in-progress')
                    },
                    progress: function (e, data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        $(this).parents('.upload-kit-item').find('.progress-bar').attr('aria-valuenow', progress).css(
                            'width',
                            progress + '%'
                        ).text(progress + '%');
                    },
                    done: function (e, data) {
                        var uploadKitItem = $(e.target).parents('.upload-kit-item');
                        if(data.result && data.result[0]){
                            var file = data.result[0];
                            if(methods.getItemsCount() < options.maxFiles){
                                container.append(methods.createItem())
                            }
                            methods.success.call(uploadKitItem, file.url)
                        } else {
                            data.fail(e, data)
                        }
                    },
                    fail: function (e, data) {
                        $(e.target).parents('.upload-kit-item').addClass('error')
                    },
                    always: function(e, data){
                        $(e.target).parents('.upload-kit-item')
                            .removeClass('in-progress')
                            .find('.progress-bar').attr('aria-valuenow', 0)
                            .css('width', 0)
                            .text();
                    }

                }, options.fileupload)
                this.find('input[type=file]').fileupload(fileuploadOptions)
            },
            dragInit: function(){
                $(document).bind('dragover', function (e)
                {
                    $(e.target).parents('.upload-kit-item').addClass('drag-hover');
                    e.preventDefault();
                });
            },
            getItemsCount: function(){
                return container.find('.upload-kit-item').length
            },
            createItem: function(file){
                var item = $('<div class="upload-kit-item"></div>')
                    .append($('<input/>', {"type": "file"}))
                    .append($('<input/>', {"type": "hidden"}))
                    .append($('<span class="extension"></span>'))
                    .append(
                        $('<span class="upload-action"><span>')
                            .append([
                                '<span class="glyphicon glyphicon-plus-sign add"></span>',
                                '<span class="glyphicon glyphicon-trash remove"></span>',
                                '<div class="progress" style="display: none">'+
                                '<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>'+
                                '</div>'
                            ])
                    );
                methods.fileupload.apply(item);
                return item;
            }
        }

        methods.init.apply(this);
        return this;
    };*/
})(jQuery)
