(function (factory) {
  /* Global define */
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module.
    define(['jquery'], factory);
  } else if (typeof module === 'object' && module.exports) {
    // Node/CommonJS
    module.exports = factory(require('jquery'));
  } else {
    // Browser globals
    factory(window.jQuery);
  }
}(function ($) {
  $.extend(true, $.summernote.lang, {
      'en-US': { /* US English(Default Language) */
        jilLink: {
            link: 'Link',
            insert: 'Insert Link',
            unlink: 'Unlink',
            edit: 'Edit',
            textToDisplay: 'Text to display',
            url: 'To what URL should this link go?',
            openInNewWindow: 'Open in new window'
        }
      },
      'ja-JP': { /* US English(Default Language) */
        jilLink: {
            link: 'Link',
            insert: 'Insert Link',
            unlink: 'Unlink',
            edit: 'Edit',
            textToDisplay: 'Text to display',
            url: 'To what URL should this link go?',
            openInNewWindow: 'Open in new window'
        }
      }
  });
  
  $.extend($.summernote.options, {
    jilLink: {
        icon: '<i class="note-icon-link"/>',
        tooltip: 'Insert link'
      }
  });
  
  $.extend($.summernote.plugins, {
      /**
     * @param {Object}
     *            context - context object has status of editor.
     */
      'jilLink': function (context) {
         var self      = this,

           // ui has renders to build ui elements
           // for e.g. you can create a button with 'ui.button'
            ui        = $.summernote.ui,
            $note     = context.layoutInfo.note,

            // contentEditable element
            $editor   = context.layoutInfo.editor,
            $editable = context.layoutInfo.editable,
            $toolbar  = context.layoutInfo.toolbar,
            
            // options holds the Options Information from Summernote and what we extended above.
            options   = context.options,
            
            // lang holds the Language Information from Summernote and what we extended above.
            lang      = options.langInfo;

        context.memo('button.jilLink', function () {
          // Here we create a button
          var button = ui.button({
              // icon for button
              contents: options.jilLink.icon,
  
              // tooltip for button
              tooltip: lang.jilLink.tooltip,
              click:function (e) {
                context.invoke('jilLink.show');
              }
          });
          return button.render();
        });
        
        this.initialize = function() {

            var $container = options.dialogsInBody ? $(document.body) : $editor;
            var body = [
                '<div class="form-group note-form-group">',
                "<label class=\"note-form-label\">" + lang.link.textToDisplay + "</label>",
                '<input class="note-link-text form-control note-form-control  note-input" type="text" />',
                '</div>',
                '<div class="form-group note-form-group">',
                "<label class=\"note-form-label\">" + lang.link.url + "</label>",
                '<input class="note-link-url form-control note-form-control note-input" type="text" value="http://" />',
                '</div>'
            ].join('');
            var buttonClass = 'btn btn-primary note-btn note-btn-primary note-link-btn';
            var footer = "<button type=\"submit\" href=\"#\" class=\"" + buttonClass + "\" disabled>" + lang.link.insert + "</button>";
            this.$dialog = ui.dialog({
                className: 'link-dialog',
                title: lang.link.insert,
                fade: options.dialogsFade,
                body: body,
                footer: footer
            }).render().appendTo($container);
        };

        this.destroy = function () {
            ui.hideDialog(this.$dialog);
            this.$dialog.remove();
        };
        
        this.bindEnterKey = function ($input, $btn) {
          $input.on('keypress', function (event) {
              if (event.keyCode === 13 ) {
                  event.preventDefault();
                  $btn.trigger('click');
              }
          });
        };

        this.toggleLinkBtn = function ($linkBtn, $linkText, $linkUrl) {
            ui.toggleBtn($linkBtn, $linkText.val() && $linkUrl.val());
        };

        this.showjilLinkDialog = function (linkInfo) {
            var _this = this;
            return $.Deferred(function (deferred) {
                var $linkText = _this.$dialog.find('.note-link-text');
                var $linkUrl = _this.$dialog.find('.note-link-url');
                var $linkBtn = _this.$dialog.find('.note-link-btn');
                var $openInNewWindow = _this.$dialog.find('input[type=checkbox]');
                ui.onDialogShown(_this.$dialog, function () {

                    context.triggerEvent('dialog.shown');
                    // if no url was given, copy text to url
                    if (!linkInfo.url) {
                        linkInfo.url = linkInfo.text;
                    }
                    $linkText.val(linkInfo.text);
                    var handleLinkTextUpdate = function () {
                        _this.toggleLinkBtn($linkBtn, $linkText, $linkUrl);
                        // if linktext was modified by keyup,
                        // stop cloning text from linkUrl
                        linkInfo.text = $linkText.val();
                    };
                    $linkText.on('input', handleLinkTextUpdate).on('paste', function () {
                        setTimeout(handleLinkTextUpdate, 0);
                    });
                    var handleLinkUrlUpdate = function () {
                        _this.toggleLinkBtn($linkBtn, $linkText, $linkUrl);
                        // display same link on `Text to display` input
                        // when create a new link
                        if (!linkInfo.text) {
                            $linkText.val($linkUrl.val());
                        }
                    };
                    $linkUrl.on('input', handleLinkUrlUpdate).on('paste', function () {
                        setTimeout(handleLinkUrlUpdate, 0);
                    }).val(linkInfo.url);
                    // if (!env.isSupportTouch) {
                    //     $linkUrl.trigger('focus');
                    // }
                    _this.toggleLinkBtn($linkBtn, $linkText, $linkUrl);
                    _this.bindEnterKey($linkUrl, $linkBtn);
                    _this.bindEnterKey($linkText, $linkBtn);
                    var isChecked = linkInfo.isNewWindow !== undefined
                        ? linkInfo.isNewWindow : context.options.linkTargetBlank;
                    $openInNewWindow.prop('checked', isChecked);
                    $linkBtn.one('click', function (event) {
                      
                        event.preventDefault();
                        deferred.resolve({
                            range: linkInfo.range,
                            url: $linkUrl.val(),
                            text: $linkText.val(),
                            isNewWindow: $openInNewWindow.is(':checked')
                        });
                        ui.hideDialog(_this.$dialog);
                    });
                });
                ui.onDialogHidden(_this.$dialog, function () {
                    // detach events
                    $linkText.off('input paste keypress');
                    $linkUrl.off('input paste keypress');
                    $linkBtn.off('click');
                    if (deferred.state() === 'pending') {
                        deferred.reject();
                    }
                });
                ui.showDialog(_this.$dialog);
            }).promise();
        };

        
        this.show = function () {
          
          var linkInfo = context.invoke('editor.getLinkInfo');
          context.invoke('editor.saveRange');
          this.showjilLinkDialog(linkInfo).then(function (linkInfo) {
              if ( linkInfo.url ) {
                if ( linkInfo.url.indexOf('http') < 0 ) {
                  if ( linkInfo.url.indexOf('/') < 0 ) {
                    linkInfo.url = '/' + linkInfo.url;
                  }
                  linkInfo.url = 'https://{{domain}}' + linkInfo.url;
                }
              }
              context.invoke('editor.restoreRange');
              context.invoke('editor.createLink', linkInfo);

          }).fail(function () {
              context.invoke('editor.restoreRange');
          });
        };
      }
  });
}));