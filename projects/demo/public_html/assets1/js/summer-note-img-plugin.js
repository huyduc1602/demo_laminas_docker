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
	      jilUploadImg: {
	        exampleText: 'Insert image',
	        dialogTitle: 'Insert image',
	        invalidFileMsg: 'Only image files is accepting',
	        okButton: 'Insert',
	        filePath: 'Folder path',
	        filePathExp: 'Ex: uploads/frontend/toppage'
	      }
	    },
	    'ja-JP': { /* US English(Default Language) */
	      jilUploadImg: {
	        exampleText: 'Insert image',
	        dialogTitle: 'Insert image',
	        invalidFileMsg: 'Only image files is accepting',
	        okButton: 'Insert',
	        filePathExp: 'Ex: uploads/frontend/toppage'
	      }
	    }
	});
	
	$.extend($.summernote.options, {
		jilUploadImg: {
	      icon: '<i class="note-icon-picture"/>',
	      tooltip: 'Insert image'
	    }
	});
	
	$.extend($.summernote.plugins, {
	    /**
		 * @param {Object}
		 *            context - context object has status of editor.
		 */
	    'jilUploadImg': function (context) {
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

	      context.memo('button.jilUploadImg', function () {
		      // Here we create a button
		      var button = ui.button({
		          // icon for button
		          contents: options.jilUploadImg.icon,
	
		          // tooltip for button
		          tooltip: lang.jilUploadImg.tooltip,
		          click:function (e) {
		            context.invoke('jilUploadImg.show');
		          }
		      });
		      return button.render();
	      });
	      
	      this.initialize = function() {

	          // This is how we can add a Modal Dialog to allow users to interact with the Plugin.

	          // get the correct container for the plugin how it's attached to the document DOM.
	          var $container = options.dialogsInBody ? $(document.body) : $editor;
	          var imageLimitation = '';
	          if (options.maximumImageFileSize) {
	              var unit = Math.floor(Math.log(options.maximumImageFileSize) / Math.log(1024));
	              var readableSize = (options.maximumImageFileSize / Math.pow(1024, unit)).toFixed(2) * 1 +
	                  ' ' + ' KMGTP'[unit] + 'B';
	              imageLimitation = "<small>" + (lang.image.maximumFileSize + ' : ' + readableSize) + "</small>";
	          }
	          // Build the Body HTML of the Dialog.
	          var body = [
	        	  '<form id="upload_img">',
	              '<div class="form-group note-form-group note-group-select-from-files">',
	              '<label class="note-form-label">' + lang.image.selectFromFiles + '</label>',
	              '<input class="note-image-input note-form-control note-input" ',
	              ' type="file" name="file" accept="image/*" multiple="multiple" />',
	              imageLimitation,
	              '</div>',
	              '<div class="form-group note-group-image-url" style="overflow:auto;">',
	              '<label class="note-form-label">' + lang.jilUploadImg.filePath + '</label>',
	              '<input name="folder" class="note-image-path form-control note-form-control note-input ',
	              ' col-md-12" type="text" />',
	              '<div class="clearfix" style="height:5px"></div><em class="required">',
	              lang.jilUploadImg.filePathExp,
	              '</em></form>',
	              '</div>'
	          ].join('');

	          // Build the Footer HTML of the Dialog.
	          var footer = '<a href="javascript:void(0);" class="btn btn-primary note-jil-upload-img-btn disabled not-allow">' + lang.jilUploadImg.okButton + '</a>'
	      
	          this.$dialog = ui.dialog({

		          // Set the title for the Dialog. Note: We don't need to build the markup for the Modal
		          // Header, we only need to set the Title.
		          title: lang.jilUploadImg.dialogTitle,
	
		          // Set the Body of the Dialog.
		          body: body,
	
		          // Set the Footer of the Dialog.
		          footer: footer
	          
		          // This adds the Modal to the DOM.
	          }).render().appendTo($container);
	      }
	      this.destroy = function () {
	          ui.hideDialog(this.$dialog);
	          this.$dialog.remove();
	      };
	      
		      /*this.bindEnterKey = function ($input, $btn) {
		          $input.on('keypress', function (event) {
		            if (event.keyCode === 13) $btn.trigger('click');
		          });
		      };*/
	      
	      this.bindLabels = function () {
	          self.$dialog.find('.form-control:first').focus().select();
	          self.$dialog.find('label').on('click', function () {
	            $(this).parent().find('.form-control:first').focus();
	          });
	      };
	      
	      this.show = function () {
	          var $img = $($editable.data('target'));
	          var editorInfo = {

	          };
	          this.showjilUploadImgDialog(editorInfo).then(function (editorInfo) {
	            ui.hideDialog(self.$dialog);
	            $note.val(context.invoke('code'));
	            $note.change();
	          });
	     };
	     
	     var uploadEvt = function(btn, form, deferred){
	    	 //context.invoke('editor.restoreRange');
	    	 
	    	 var formData = new FormData(form.get(0));
	    	 btn.addClass('disabled not-allow');
	    	 return $.ajax({
				url: '/jil-ad/upload/image',
				dataType: 'JSON',
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				enctype: 'multipart/form-data',
				/*beforeSend: function(request){
	    			request.setRequestHeader('Csrf-Token', token);
	    			formEncrypt.find('#encrypt_resp')
					.addClass('hidden')
		        }*/
			})
			.always(function(){
				btn.removeClass('disabled not-allow');
			})
			.done(function(respone) {
			   if( respone && respone['success'] ){
				   deferred.resolve(respone['path']);
					context.invoke(
					   'editor.insertImage', respone['path']
					);
					btn.attr('data-exists', '');
					ui.hideDialog(self.$dialog);
					form.trigger('reset');
			   }else{
				   context.invoke('editor.restoreRange');
			   }
		   })
		   .fail(function(){
			   context.invoke('editor.restoreRange');
		   })
		   ;
	     }
	     
	     // -- Check file exists
	     var fileIsExists = function(btn, formData){
	    	 return $.ajax({
				url: '/jil-ad/upload/file-exists',
	    		type: 'POST',
	    		dataType: 'JSON',
	    		data: formData || {},
	    	 })
	    	 .done(function(rs) {
			   if( rs['success'] && rs['exists'] ){
				   btn.attr('data-exists', 1);
			   }else {
				   btn.attr('data-exists', '');
			   }
			});
	     }
	     this.showjilUploadImgDialog = function(editorInfo) {
	         return $.Deferred(function (deferred) {
	           ui.onDialogShown(self.$dialog, function () {
	        	   
	        	   context.invoke('editor.saveRange');
	        	   var inputs = self.$dialog.find('input'),
	        	   	   btn = self.$dialog.find('.note-jil-upload-img-btn:first');
	        	   btn.addClass('not-allow disabled');
	        	   self.$dialog.find('input.note-image-input:first')
	        	   .on('change', function(evt){
	        		   if( this.files && this.files[0]
	        		   		&& this.files[0]['name'].length > 0 ){
	        			   var img = $('<img/>')
	        			   .on('error', function(){
	        				   notifyMe(lang.jilUploadImg.invalidFileMsg, 'inverse', 'bgm-red');
	        			   });
        				   img.attr('src', window.URL.createObjectURL(this.files[0]));
	        		   }
	        	   });
	        	   
	        	   inputs.on('change', function(evt){
	        		   var isValid = true;
	        		   inputs.each(function(){
	        			   isValid = isValid && (($(this).val()||'').trim().length > 0); 
	        		   });
	        		   var fileEle = self.$dialog.find('input.note-image-input:first'),
    				   		file = fileEle.get(0).files[0];
	        		   if( true == isValid && file['type'].indexOf('image/') == 0 ){
	        			   var img = $('<img/>')
	        			   .on('load', function(){
	        				   btn.removeClass('not-allow disabled');
    	        			   btn.attr('data-exists', '');
    	        			   fileIsExists(btn, {
    	        				   'folder': self.$dialog.find('input.note-image-path:first').val(), 
    	        				   'name': file['name']
    	        			   });
	        			   });
        				   img.attr('src', window.URL.createObjectURL(file));
        			   }
	        		   else btn.addClass('not-allow disabled');
	        	   });
	        	   
	        	   btn.on('click tab', (evt) => {
	        		   if( btn.attr('data-exists') ){
	        			   var oldIndx = self.$dialog.css('z-index');
	        			   self.$dialog.css('z-index', 1);
	        			   swal({
	 	     				  title: window.jsonSystemLanguage['warning'],
	 	     				  text: '<div style="color: red;font-size: 16px;">This file already exists. You want to replace it?</div>',
	 	     				  type: "warning", html: true,
	 	     				  showCancelButton: true,
	 	     				  confirmButtonClass: "btn-danger",
	 	     				  confirmButtonText: window.jsonSystemLanguage['yes'],
	 	     				  cancelButtonText: window.jsonSystemLanguage['cancel'],
	 	     				  closeOnConfirm: false,
	 	     				  showLoaderOnConfirm: true
		 	     			},
		 	     			function( rs ){
		 	     				self.$dialog.css('z-index', oldIndx);
		 	     				if( rs ) {
		 	     					uploadEvt(btn, self.$dialog.find('#upload_img'), deferred)
		 	     					.always(function(){
		 	     						swal.close();
		 	     					});
		 	     				}
		 	     			});
	        		   }else{
	        			   uploadEvt(btn, self.$dialog.find('#upload_img'), deferred);
	        		   }
	        	   });
	        	   
	        	   context.triggerEvent('dialog.shown');
		             /*$editBtn.click(function (e) {
		               e.preventDefault();
		               deferred.resolve({
	
		               });
		             });
		             self.bindEnterKey($editBtn);*/
		             self.bindLabels();
	           });
	           
	           ui.onDialogHidden(self.$dialog, function () {
	             //$editBtn.off('click');
	        	   if (deferred.state() === 'pending'){ deferred.reject();}
	        	   self.$dialog.find('input').off('change');
      	   	   	   self.$dialog.find('.note-jil-upload-img-btn:first')
      	   	   	   .off('click tab');
	           });
	           ui.showDialog(self.$dialog);
	         });
	     };
      }
	});
}));