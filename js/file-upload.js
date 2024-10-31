/**
 * file upload js
 * @since 8.4
 **/

var isCartBlock = false;
var upload_instance = Array();
var file_count = Array();
var $filelist_DIV = Array();
var cfom_file_progress = '';
var featherEditor = '';
var uploaderInstances = {};

jQuery(function($){
	

	$(document).on('cfom_image_ready', function(e){
	    
	    var image_url   = e.image_url;
	    var image_id	= e.image.id;
	    var data_name   = e.data_name;
	    var input_type	= e.input_type;
	    
	    if(input_type === 'cropper'){
	    	cfom_show_cropped_preview( data_name, image_url, image_id );
	    }
	    
	    // moving modal to body end
        $('.cfom-modals').appendTo('body');
	});
	
	// On file removed
	$(document).on('cfom_uploaded_file_removed', function(e) {
	    
	    var field_name  = e.field_name;
	   // var fileid      = e.fileid;
	   
	    cfom_reset_cropping_preview(field_name);
		cfom_update_option_prices();
	})
	

	// Peview cropped image
    $(".cfom-croppie-btn").on('click', function(e) {
        
        e.preventDefault();
        
        var file_id = $(this).data('fileid');
        var croppie = $filelist_DIV[file_id]['cropped'];
        var imag_id	= $filelist_DIV[file_id]['image_id'];
        
        $filelist_DIV[file_id]['croppie'].croppie('result', {
        	type: 'rawcanvas',
        	// size: { width: 300, height: 300 },
        	format: 'png'
        }).then(function (canvas) {
			/*popupResult({
				src: canvas.toDataURL()
			});*/
			
			var fileCheck = $('<input checked="checked" name="cfom[fields]['+file_id+']['+imag_id+'][cropped]" type="checkbox"/>')
    				                .val(canvas.toDataURL())
    				                .css('display','none')
    				                .appendTo($filelist_DIV[file_id]);
			
			var modalID = 'modalCrop_'+file_id;
// 			console.log(canvas.toDataURL());
			$("#"+modalID).find('.cfom-cropped-image').attr('src', canvas.toDataURL());
			$("#"+modalID).modal();
		});
		
    });
    
    // Croppie update size
    $('.cfom-croppie-preview').on('change', '.cfom-cropping-size', function(e) {
       
       var data_name = $(this).data('field_name');
       $filelist_DIV[data_name]['croppie'].croppie('destroy');
       
       var viewport = {'width':$('option:selected', this).data('width'),'height':$('option:selected', this).data('height')};
       cfom_set_croppie_options(data_name, viewport);
       
    });
	
    $.each(cfom_file_vars.file_inputs, function(index, file_input){
        
        var file_data_name = file_input.data_name;
        var file_section    = file_input.section;
        
        file_count[file_data_name] = 0;
    	// delete file
   	$(".cfom-wrapper").on('click','.u_i_c_tools_del', function(e){
    		e.preventDefault();
    
    		var del_message = cfom_file_vars.delete_file_msg;
    		var a = confirm(del_message);
    		if(a){
    			// it is removing from uploader instance
    			var fileid = $(this).closest('.u_i_c_box').attr("data-fileid");
    			
    // 			console.log(fileid);
    			
    			upload_instance[file_data_name].removeFile(fileid);
    
    			var filename  = $('input:checkbox[name="cfom['+file_section+']['+file_data_name+']['+fileid+'][org]"]').val();
    			
    			// it is removing physically if uploaded
    			$("#u_i_c_"+fileid).find('img').attr('src', cfom_file_vars.plugin_url+'/images/loading.gif');
    			
    			// console.log('filename cfom[fields][<?php echo ]$args['id']?>['+fileid+']');
    			var data = {action: 'cfom_delete_file', file_name: filename};
    			
    			$.post(cfom_file_vars.ajaxurl, data, function(resp){
    				alert(resp);
    				$("#u_i_c_"+fileid).hide(500).remove();
    
    				// it is removing for input Holder
    				$('input:checkbox[name="cfom['+file_section+']['+file_data_name+']['+fileid+'][org]"]').remove();
    				
    				// Removing file container
    				$(this).closest('.u_i_c_box').remove();
    				
    				// Trigger
    				$.event.trigger({type: "cfom_uploaded_file_removed",
                                field_name:file_data_name,
                                fileid: fileid,
                                time: new Date()
                                });
    				
    				file_count[file_data_name] -= 1;		
    			});
    		}
    	});
    	
    	$filelist_DIV[file_data_name] = $('#filelist-'+file_data_name);
    	
    	cfom_setup_file_upload_input( file_input );
    	
    	
    	// ==================== If Aviary Editor is Enabled =======================
    	if(cfom_file_vars.aviary_api_key !== '' && file_input.photo_editing == 'on') {
    	    
    	    
            featherEditor = new Aviary.Feather({
                apiKey: cfom_file_vars.aviary_api_key,
                apiVersion: 3,
                theme: 'dark',
                onSave: function(imageID, newURL) {
                    var img = document.getElementById(imageID);
                    img.src = newURL;
                    save_edited_photo(imageID, newURL);
                    featherEditor.close();
                },
                onError: function(errorObj) {
                    alert(errorObj.message);
                }
            });
    	} 
    	
    });         // $.each(cfom_file_vars

	
});	//	jQuery(function($){});

// generate thumbbox 
function add_thumb_box(file, $filelist_DIV){

	var inner_html	= '<div class="u_i_c_thumb"><div class="progress_bar"><span class="progress_bar_runner"></span><span class="progress_bar_number">(' + plupload.formatSize(file.size) + ')<span></div></div>';
	inner_html		+= '<div class="u_i_c_name"><strong>' + file.name + '</strong></div>';
	  
	jQuery( '<div />', {
		'id'	: 'u_i_c_'+file.id,
		'class'	: 'u_i_c_box',
		'data-fileid': file.id,
		'html'	: inner_html,
		
	}).appendTo($filelist_DIV);

	// clearfix
	// 1- removing last clearfix first
	$filelist_DIV.find('.u_i_c_box_clearfix').remove();
	
	jQuery( '<div />', {    
		'class'	: 'u_i_c_box_clearfix',				
	}).appendTo($filelist_DIV);
	
}


// save croped/edited photo
function save_edited_photo(img_id, photo_url){
			
	//console.log(img_id);
	
	//setting new image width to 75
	jQuery('#'+img_id).attr('width', 75);
	
	//disabling add to cart button for a while
	jQuery('form.cart').block({
                message: null,
                overlayCSS: {
                background: "#fff",
                opacity: .6
		                    }
         });
	var post_data = {action: 'cfom_save_edited_photo', image_url: photo_url,
						filename: jQuery('#'+img_id).attr('data-filename')
	};
	
	jQuery.post(cfom_file_vars.ajaxurl, post_data, function(resp) {
	    
	    //console.log( resp );
	    jQuery('form.cart').unblock();
	    
	});
}

// Cropping image with Croppie
function cfom_show_cropped_preview( file_name, image_url, image_id ) {
    
    
    var cropp_preview_container = jQuery(".cfom-croppie-wrapper-"+file_name);
    // Enable preview button & size option
    cropp_preview_container.find('.cfom-croppie-btn').show();
    cropp_preview_container.find('.cfom-cropping-size').show();
    
    $filelist_DIV[file_name]['croppie']     = cropp_preview_container.find('.cfom-croppie-preview');
	$filelist_DIV[file_name]['image_id']    = image_id;    
	$filelist_DIV[file_name]['image_url']   = image_url;
	
	cfom_set_croppie_options( file_name );
}

function cfom_set_croppie_options( file_name, viewport ) {
    
    var croppie_options = cfom_file_vars.croppie_options;
	jQuery.each(croppie_options, function(field_name, option){
	    
	    if( file_name === field_name ) {
	        
	       option.url = $filelist_DIV[file_name]['image_url'];
	       if( viewport !== undefined ) {
	           option.viewport = viewport;
	       }
        // console.log(option);
	    $filelist_DIV[file_name]['croppie'].croppie(option);
	    }
	});
}

// Reset cropping when image removed
function cfom_reset_cropping_preview(file_name) {
    
    var cropp_preview_container = jQuery(".cfom-croppie-wrapper-"+file_name);
    // Enable preview button
    cropp_preview_container.find('.cfom-croppie-btn').hide();
    // Reseting preview DOM
    cropp_preview_container.find('.cfom-croppie-preview').html('');
}

// Attach FILE API with DOM
function cfom_setup_file_upload_input( file_input ) {
    
    var file_data_name = file_input.data_name;
    var file_section    = file_input.section;
    
    if( upload_instance[file_data_name] !== undefined ) {
        upload_instance[file_data_name].destroy();
    }
    
    
    upload_instance[file_data_name] = new plupload.Uploader({
    		runtimes 			: cfom_file_vars.plupload_runtime,
    		browse_button 		: 'selectfiles-'+file_data_name, // you can pass in id...
    		container			: 'cfom-file-container-'+file_data_name, // ... or DOM Element itself
    		drop_element		: 'cfom-file-container-'+file_data_name,
    		url 				: cfom_file_vars.ajaxurl,
    		multipart_params 	: {'action' : 'cfom_upload_file', 'settings': file_input},
    		max_file_size 		: file_input.file_size,
    		max_file_count 		: parseInt(file_input.files_allowed),
    	    
    	    chunk_size: '1mb',
    		
    	    // Flash settings
    // 		flash_swf_url 		: cfom_file_vars.plugin_url+'/js/plupload-2.1.2/js/Moxie.swf?nocache='+Math.random(),
    		// Silverlight settings
    // 		silverlight_xap_url : cfom_file_vars.plugin_url+'/js/plupload-2.1.2/js/Moxie.xap',
    		
    		filters : {
    			mime_types: [
    				{title : "Filetypes", extensions : file_input.file_types}
    			]
    		},
    		
    		init: {
    			PostInit: function() {
    			    
    				// $filelist_DIV[file_data_name].html('');
    
    				/*$('#uploadfiles-'+file_data_name).bind('click', function() {
    					upload_instance[file_data_name].start();
    					return false;
    				});*/
    			},
    
    			FilesAdded: function(up, files) {
    
                    // Adding progress bar
                	var file_pb = jQuery('<div/>')
                	               .addClass('progress')
                	               .appendTo($filelist_DIV[file_data_name]);
                	var file_pb_runner = jQuery('<div/>')
                	                    .addClass('progress-bar')
                	                    .attr('role', 'progressbar')
                	                    .attr('aria-valuenow', 0)
                	                    .attr('aria-valuemin', 0)
                	                    .attr('aria-valuemax', 100)
                	                    .css('height', '15px')
                	                    .css('width', 0)
                	                    .appendTo(file_pb);
                	                    
    				var files_added = files.length;
    				var max_count_error = false;
    
    				if((file_count[file_data_name] + files_added) > upload_instance[file_data_name].settings.max_file_count){
    					alert(upload_instance[file_data_name].settings.max_file_count + cfom_file_vars.mesage_max_files_limit);
    				}else{
    					
    					plupload.each(files, function (file) {
    						file_count[file_data_name]++;
    			    		// Code to add pending file details, if you want
    			            add_thumb_box(file, $filelist_DIV[file_data_name], up);
    			            setTimeout('upload_instance[\''+file_data_name+'\'].start()', 100);
    			        });
    				}
    			    
    				
    			},
    			
    			FileUploaded: function(up, file, info){
    				
    				// console.log($.parseJSON(info.response));
    
    				var obj_resp = jQuery.parseJSON(info.response);
    				
    				if(obj_resp.file_name === 'ThumbNotFound'){
    					
    					upload_instance[file_data_name].removeFile(file.id);
    					jQuery("#u_i_c_"+file.id).hide(500).remove();
    					file_count[file_data_name]--;	
    					
    					alert('There is some error please try again');
    					return;
    					
    				}else if(obj_resp.status == 'error'){
    					
    					upload_instance[file_data_name].removeFile(file.id);
    					
    					jQuery("#u_i_c_"+file.id).hide(500).remove();
    
    					file_count[file_data_name]--;	
    					alert(obj_resp.message);
    					return;
    				};
    				
    				var file_thumb 	= ''; 
    
                    /*if( file_input.file_cost != "" ) {
                        jQuery('input[name="woo_file_cost"]').val( file_input.file_cost );
                    }*/
    
    				$filelist_DIV[file_data_name].find('#u_i_c_' + file.id).html(obj_resp.html)
    				.trigger({
                            	type: "cfom_image_ready",
                            	image: file,
                            	data_name: file_data_name,
                            	input_type: file_input.type,
                            	image_url: obj_resp.file_url,
                            	image_resp: obj_resp,
                            	time: new Date()
                            });
    
    				
    				// checking if uploaded file is thumb
    				ext = obj_resp.file_name.substring(obj_resp.file_name.lastIndexOf('.') + 1);					
    				ext = ext.toLowerCase();
    				
    				if(ext == 'png' || ext == 'gif' || ext == 'jpg' || ext == 'jpeg'){
    
    					
    					var file_full 	= cfom_file_vars.file_upload_path + obj_resp.file_name;
    					// thumb thickbox only shown if it is image
    					$filelist_DIV[file_data_name]
    					.find('#u_i_c_' + file.id)
    					.find('.u_i_c_thumb')
    					.append('<div style="display:none" id="u_i_c_big' + file.id + '"><img src="'+file_full+ '" /></div>');
    
    					// Aviary editing tools
    					if( file_input.photo_editing === 'on' && cfom_file_vars.aviary_api_key !== ''){
    						var editing_tools = file_input.editing_tools;
    						$filelist_DIV[file_data_name]
    						 .find('#u_i_c_' + file.id)
    						 .find('.u_i_c_tools_edit')
    						 .append('<a onclick="return   (\'thumb_'+file.id+'\', \''+file_full+'\', \''+obj_resp.file_name+'\', \''+editing_tools+'\')" href="javascript:;" title="Edit"><img width="15" src="'+cfom_file_vars.plugin_url+'/images/edit.png" /></a>');
    					}
    
    					is_image = true;
    				}else{
    					file_thumb = cfom_file_vars.plugin_url+'/images/file.png';
    					$filelist_DIV[file_data_name].find('#u_i_c_' + file.id)
    					                       .find('.u_i_c_thumb')
    					                       .html('<img src="'+file_thumb+ '" id="thumb_'+file.id+'" />')
    					is_image = false;
    				}
    				
    				// adding checkbox input to Hold uploaded file name as array
    				var file_container  = $filelist_DIV[file_data_name].find('.u_i_c_box');
    				var fileCheck = jQuery('<input checked="checked" name="cfom['+file_section+']['+file_data_name+']['+file.id+'][org]" type="checkbox"/>')
    				                .attr('data-price', file_input.file_cost)
    				                .attr('data-label', obj_resp.file_name)
    				                .attr('data-title', file_input.title)
    				                .attr('data-onetime', file_input.onetime)
    				                .val(obj_resp.file_name)
    				                .css('display','none')
    				                .addClass('cfom-file-cb-'+file_data_name)
    				                .addClass('cfom-file-cb')
    				                .appendTo(file_container);
    				cfom_update_option_prices();
    				
    				jQuery('form.cart').unblock();
    				isCartBlock = false;
    				
    				// Removing progressbar
    				$filelist_DIV[file_data_name].find('.progress').remove();
    				
    				// Trigger
    				jQuery.event.trigger({type: "cfom_file_uploaded",
                                file_meta:file_input,
                                file_resp: obj_resp,
                                time: new Date()
                                });
    			},
    
    			UploadProgress: function(up, file) {
    				
    				$filelist_DIV[file_data_name].find('.progress-bar').css('width', file.percent + '%');
    				
    				//disabling add to cart button for a while
    				if( ! isCartBlock ){
    				jQuery('form.cart').block({
    		                    message: null,
    		                    overlayCSS: {
    		                    background: "#fff",
    		                    opacity: .6,
    		                    onBlock: function() { 
    				                isCartBlock = true;
    				            } 
    					                    }
    			         });
    				}
    			},
    
    			Error: function(up, err) {
    				//document.getElementById('console').innerHTML += "\nError #" + err.code + ": " + err.message;
    				alert("\nError #" + err.code + ": " + err.message);
    			}
    		}
    		
    
    	});
    	
    	upload_instance[file_data_name].init();
    	uploaderInstances[file_data_name] = upload_instance[file_data_name];
}