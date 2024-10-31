"use strict";
jQuery(function($){
    
    /*********************************
    *       CFOM Form Design JS       *
    **********************************/


    /*-------------------------------------------------------
        
        ------ Its Include Following Function -----

        1- Submit cfom Form Fields
        3- Get Last Field Index
        4- Show And Hide Visibility Role Field
        5- Remove Unsaved Fields
        6- Check And Uncheck All Fields
        7- Remove Check Fields
        8- On Fields Options Handle Add Option Last
        9- Edit Existing Fields
        10- Add New Fields
        11- Update Existing Fields
        12- Clone New Fields
        16- Handle Fields Tabs
        17- Handle Media Images Of Following Inputs Types
        18- Add Fields Conditions
        19- Add Fields Options
        20- Auto Generate Option IDs
        21- Create Field data_name By Thier Title
        22- Fields Sortable
        23- Fields Option Sortable
        24- Fields Dataname Must Be Required
        25- Fields Add Option Index Controle Funtion
        26- Fields Add Condition Index Controle Function
        27- Get All Fields Title On Condition Element Value After Click On Condition Tab
        28- validate API WooCommerce Product
        29- Toggle Switch Yes And No
        30- Reset Selected Section To Defualt
    ------------------------------------------------------------*/


    // $('#cfom-meta-table').DataTable();


    /**
        CFOM Model
    **/
    var appendthis =  ("<div class='cfom-modal-overlay cfom-js-modal-close'></div>");

    $(document).on('click', '[data-modal-id]', function(e){
        e.preventDefault();
        $("body").append(appendthis);
        var modalBox = $(this).attr('data-modal-id');
        $('#'+modalBox).fadeIn();
    });  
    
    cfom_close_popup();
    function cfom_close_popup(){

        $(".cfom-js-modal-close, .cfom-modal-overlay").click(function(e) {
            
            var target = $( e.target );
            if (target.hasClass("cfom-modal-overlay")) {
                return false;
            }
            $(".cfom-modal-box, .cfom-modal-overlay").fadeOut('fast', function() {
                $(".cfom-modal-overlay").remove();
            });
         
        });
    }



    /*
    **============= Submit PDF settings ================ 
    */
    $(".cfom-save-pdf-settings").submit(function(e){
        e.preventDefault();
        
        $('.cfom-pdf-save-btn input[type="submit"]').prop('disabled', true);
        var data = $(this).serialize();

        $.post(ajaxurl, data, function(resp){

            $('.cfom-pdf-setting-alert').html(resp.message).show();
            setTimeout(function(){ 
                $('.cfom-pdf-setting-alert').hide();
                $('.cfom-pdf-save-btn input[type="submit"]').prop('disabled', false);
            }, 1000);

        }, 'json');
        
    });

 
    
    /**
        1- Submit cfom Form Fields
    **/
    $(".cfom-save-fields-meta").submit(function(e){
        e.preventDefault();
        
        jQuery(".cfom-meta-save-notice").html('<img src="' + cfom_vars.loader + '">').show();

        $('.cfom-unsave-data').remove();
        
        var data = $(this).serialize();

        $.post(ajaxurl, data, function(resp){

            jQuery(".cfom-meta-save-notice").html(resp.message).css({'background-color': '#4e694859','padding': '8px','border-left': '5px solid #008c00'});
            if(resp.status == 'success'){
                
                if(resp.cfom_id != ''){
                    window.location = cfom_vars.plugin_admin_page + '&cfom_id=' + resp.cfom_id+'&do_meta=edit';
                }else{
                    window.location.reload(true);   
                }
            }
        }, 'json');
        
    });


    /**
        3- Get Last Field Index
    **/
    var field_no = $('#field_index').val();


    /**
        4- Show And Hide Visibility Role Field
    **/
    $('.cfom-slider').find('[data-meta-id="visibility_role"]').removeClass('cfom-handle-all-fields').hide();
    $('.cfom_save_fields_model .cfom-slider').each(function(i, div){
        var visibility_value = $(div).find('[data-meta-id="visibility"] select').val();     
        if (visibility_value == 'roles') {
            $(div).find('[data-meta-id="visibility_role"]').show();
        }
    });    
    $(document).on('change', '[data-meta-id="visibility"] select', function(e) {
        e.preventDefault();

        var div = $(this).closest('.cfom-slider');
        var visibility_value = $(this).val();
        console.log(visibility_value);
        if (visibility_value == 'roles') {
            div.find('[data-meta-id="visibility_role"]').show();
        }else{
            div.find('[data-meta-id="visibility_role"]').hide();
        }
    });


    /**
        5- Remove Unsaved Fields
    **/
    $(document).on('click', '.cfom-close-fields', function(event) {
        event.preventDefault();

        $(this).closest('.cfom-slider').addClass('cfom-unsave-data');

    });
    

    /**
        6- Check And Uncheck All Fields
    **/
    $('.cfom-main-field-wrapper').on('click', '.cfom-check-all-field input', function(event) {
        if($(this).prop('checked')){
            $('.cfom_field_table input[type="checkbox"]').prop('checked',true);
        }
        else{
            $('.cfom_field_table input[type="checkbox"]').prop('checked',false);
        }
    });
    $('.cfom-main-field-wrapper').on('click', '.cfom_field_table tbody input[type="checkbox"]', function(event) {
        if($('.cfom_field_table tbody input[type="checkbox"]:checked').length == $('.cfom_field_table tbody input[type="checkbox"]').length){
             $('.cfom-check-all-field input').prop('checked',true);
        }
        else{
             $('.cfom-check-all-field input').prop('checked',false);
        }
    });
    

    /**
        7- Remove Check Fields
    **/
    $('.cfom-main-field-wrapper').on('click', '.cfom_remove_field', function(e){
        e.preventDefault();
        
        var check_field = $('.cfom-check-one-field input[type="checkbox"]:checked');
        
        if (check_field.length > 0 ) {
            swal({
                title: "Are you sure?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55 ",
                cancelButtonColor: "#DD6B55",
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                closeOnConfirm: true
                }, function (isConfirm) {
                    if (!isConfirm) return;

                    $('.cfom_field_table').find('.cfom-check-one-field input').each(function(i, meta_field){

                        if (this.checked) {
                            var field_id = $(meta_field).val();
                            console.log(field_id)
                            $(meta_field).parent().parent().parent('.row_no_'+field_id+'').remove();
                        }
                        $('.cfom_save_fields_model').find('#cfom_field_model_'+field_id+'').remove();
                    });
            });
        }else{
            swal("Please at least check one field!", "", "error");
        }
    });


    /**
        8- On Fields Options Handle Add Option Last
    **/
    $('.webcontact-rules').each(function(i, meta_field){

        var selector_btn = $(this).closest('.cfom-slider');
        selector_btn.find('.cfom-add-rule').not(':last').removeClass('cfom-add-rule').addClass('cfom-remove-rule')
       .removeClass('btn-success').addClass('btn-danger')
       .html('<i class="fa fa-minus" aria-hidden="true"></i>');
            
    });
    $('.data-options').each(function(i, meta_field){

        var selector_btn = $(this).closest('.cfom-slider');
        selector_btn.find('.cfom-add-option').not(':last').removeClass('cfom-add-option').addClass('cfom-remove-option')
       .removeClass('btn-success').addClass('btn-danger')
       .html('<i class="fa fa-minus" aria-hidden="true"></i>');
            
    });


    /**
        9- Edit Existing Fields
    **/
    $('.cfom_field_table').on('click', '.cfom-edit-field', function(event) {
        event.preventDefault();

        var the_id = $(this).attr('id');
        $('#cfom_field_model_'+the_id+'').find('.cfom-close-checker').removeClass('cfom-close-fields');
    });


    /**
        10- Add New Fields
    **/
    $(document).on('click', '.cfom-add-field', function(event) {
        event.preventDefault();

        var $this = $(this);
        var ui = cfom_required_data_name($this);
        if (ui == false) {
            return;
        }

        var id = $(this).attr('data-field-index');
            id = Number(id);

        // console.log(id);    
        var field_title = $('#cfom_field_model_'+id+'').find('.modal-body .cfom-fields-actions').attr('data-table-id'); 
        var data_name   = $('#cfom_field_model_'+id+'').find('[data-meta-id="data_name"] input').val();
        var title       = $('#cfom_field_model_'+id+'').find('[data-meta-id="title"] input').val();
        var placeholder = $('#cfom_field_model_'+id+'').find('[data-meta-id="placeholder"] input').val();
        var required    = $('#cfom_field_model_'+id+'').find('[data-meta-id="required"] input').prop('checked');
        var condition_enabled    = $('#cfom_field_model_'+id+'').find('[data-meta-id="logic"] input').prop('checked');
        var type        = $(this).attr('data-field-type');
        
        if (required == true) {
            var _ok = 'Yes';
        }else{
            _ok = 'No';
        }
        if (placeholder == null) {
            placeholder = '-';
        }
        
        if (condition_enabled == true) {
            var condition_enabled = 'Yes';
        }else{
            condition_enabled     = 'No';
        }
        
        var html  = '<tr class="row_no_'+id+'" id="cfom_sort_id_'+id+'">';
                html += '<td class="cfom-sortable-handle"><i class="fa fa-arrows" aria-hidden="true"></i></td>';
                html += '<td class="cfom-check-one-field cfom-checkboxe-style">';
                    html += '<label>';
                        html += '<input type="checkbox" value="'+id+'">';
                        html += '<span></span>';
                    html += '</label>';
                html += '</td>';
                html += '<td class="cfom_meta_field_id">'+data_name+'</td>';
                html += '<td class="cfom_meta_field_type">'+type+'</td>';
                html += '<td class="cfom_meta_field_title">'+title+'</td>';
                html += '<td class="cfom_meta_field_plchlder">'+placeholder+'</td>';
                html += '<td class="cfom_meta_field_condition">'+condition_enabled+'</td>';
                html += '<td class="cfom_meta_field_req">'+_ok+'</td>';
                html += '<td>';
                    html += '<button class="cfom-edit-field btn" id="'+id+'" data-modal-id="cfom_field_model_'+id+'"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
                html += '</td>';
            html += '</tr>';

        $(html).appendTo('.cfom_field_table tbody');

        $(".cfom-modal-box, .cfom-modal-overlay").fadeOut('fast', function() {
            $(".cfom-modal-overlay").remove();
        });

        $(this).removeClass('cfom-add-field').addClass('cfom-update-field');
        $(this).html('Update Field');
    });


    /**
        11- Update Existing Fields
    **/
    $(document).on('click', '.cfom-update-field', function(event) {
        event.preventDefault();

        var $this = $(this);
        var ui = cfom_required_data_name($this);
        
        if (ui == false) {
            return;
        }

        var id = $(this).attr('data-field-index');
            id = Number(id);

        var data_name   = $('#cfom_field_model_'+id+'').find('[data-meta-id="data_name"] input').val();
        var title       = $('#cfom_field_model_'+id+'').find('[data-meta-id="title"] input').val();
        var placeholder = $('#cfom_field_model_'+id+'').find('[data-meta-id="placeholder"] input').val();
        var required    = $('#cfom_field_model_'+id+'').find('[data-meta-id="required"] input').prop('checked');
        var condition_enabled    = $('#cfom_field_model_'+id+'').find('[data-meta-id="logic"] input').prop('checked');
        
        var type        = $(this).attr('data-field-type');
        
        if (required == true) {
            var _ok = 'Yes';
        }else{
            _ok = 'No';
        }
        
        if (condition_enabled == true) {
            var condition_enabled = 'Yes';
        }else{
            condition_enabled = 'No';
        }

        var row = $('.cfom_field_table tbody').find('.row_no_'+id);

        row.find(".cfom_meta_field_title").html(title);
        row.find(".cfom_meta_field_id").html(data_name);
        row.find(".cfom_meta_field_type").html(type);
        row.find(".cfom_meta_field_plchlder").html(placeholder);
        row.find(".cfom_meta_field_req").html(_ok);
        row.find(".cfom_meta_field_condition").html(condition_enabled);

        $(".cfom-modal-box, .cfom-modal-overlay").fadeOut('fast', function() {
            $(".cfom-modal-overlay").remove();
        });
    });


    /**
        12- Clone New Fields
    **/
    var option_index = 0;
    $(document).on('click', '.cfom_select_field', function(event) {
        event.preventDefault();

        var field_type      = $(this).data('field-type');
        var clone_new_field = $(".cfom-field-"+field_type+":last").clone();
        
        // field attr name apply on all fields meta with cfom-meta-field class
        clone_new_field.find('.cfom-meta-field').each(function(i, meta_field){
            var field_name = 'cfom['+field_no+']['+$(meta_field).attr('data-metatype')+']';
            $(meta_field).attr('name', field_name);
        });

        // fields options sortable
        clone_new_field.find(".cfom-options-sortable").sortable();
        
        // add fields index in data-field-no
        clone_new_field.find(".cfom-fields-actions").attr('data-field-no',  field_no);
        
        // fields conditions handle name attr
        clone_new_field.find('.cfom-condition-visible-bound').each(function(i, meta_field){
             var field_name = 'cfom['+field_no+'][conditions]['+$(meta_field).attr('data-metatype')+']';
            $(meta_field).attr('name', field_name);
        });
       
        clone_new_field.find('.cfom-fields-actions [data-meta-id="visibility_role"]').hide();
        
        var field_model_id = 'cfom_field_model_'+field_no+'';

        clone_new_field.find('.cfom_save_fields_model').end().appendTo('.cfom_save_fields_model').attr('id', field_model_id);
        clone_new_field.find('.cfom-field-checker').attr('data-field-index', field_no);

        clone_new_field.addClass('cfom_sort_id_'+field_no+'');
        var field_index = field_no;

        // handle multiple options
        var cfom_option_type = '';
        var option_selector   = clone_new_field.find('.cfom-option-keys');  
        var add_cond_selector = clone_new_field.find('.cfom-conditional-keys'); 


        cfom_create_option_index(option_selector, field_index , option_index, cfom_option_type );
        cfom_add_condition_set_index(add_cond_selector, field_index, field_type, option_index);
        
        // popup fields on model
        cfom_close_popup();
        $('#cfom_field_model_'+field_no+'').fadeIn();

        field_no++;
    });


    /**
        16- Handle Fields Tabs
    **/
    if ($('#tab1').hasClass('cfom-active-tab')) {
        $('.cfom-handle-all-fields').show();
    }
    $(document).on('click', '.cfom-tabs-label', function(){

        var div = $(this).closest('.cfom-slider');
        div.find('.cfom-tabs-label').removeClass('cfom-active-tab');
        $(this).addClass('cfom-active-tab');
        var id = $(this).attr('id');
        if (id == 'tab1') {
            div.find('.cfom-handle-condition').hide();
            div.find('.cfom-handle-paired').hide();
            div.find('.cfom-handle-all-fields').show();
        }else if(id == 'tab3'){
            div.find('.cfom-handle-condition').hide();
            div.find('.cfom-handle-all-fields').hide();
            div.find('.cfom-handle-paired').show();
        }else{
            div.find('.cfom-handle-all-fields').hide();
            div.find('.cfom-handle-paired').hide();
            div.find('.cfom-handle-condition').show();
        }
    });


    /**
        17- Handle Media Images Of Following Inputs Types
            17.1- Pre-Images Type
            17.2- Audio Type
            17.3- Imageselect Type
    **/
    var $uploaded_image_container;
    $(document).on('click', '.cfom-pre-upload-image-btn', function(e){
        
        e.preventDefault();
        var meta_type = $(this).attr('data-metatype');
        $uploaded_image_container = $(this).closest('div');
        var image_append = $uploaded_image_container.find('ul');
        var option_index = parseInt($uploaded_image_container.find('#cfom-meta-opt-index').val());
        $uploaded_image_container.find('#cfom-meta-opt-index').val( option_index + 1 );
        var main_wrapper  = $(this).closest('.cfom-slider');
        var field_index   = main_wrapper.find('.cfom-fields-actions').attr('data-field-no');
        var price_placeholder = 'Price (fix or %)';
        wp.media.editor.send.attachment = function(props, attachment){
            // console.log(attachment);
            var existing_images;
            var fileurl = attachment.url;
            var fileid  = attachment.id;
            var img_icon = '<img width="60" src="'+fileurl+'" style="width: 34px;">';
            var url_field = '<input placeholder="url" type="text" name="cfom['+field_index+']['+meta_type+']['+option_index+'][url]" class="form-control">';
            
            if (attachment.type !== 'image') {
                var img_icon = '<img width="60" src="'+attachment.icon+'" style="width: 34px;">';
                url_field = '';
            }
            
            // Set name key for imageselect addon
            if (meta_type == 'imageselect') {
                meta_type = 'images';
                price_placeholder = 'Price';
                url_field = '<input placeholder="Description" type="text" name="cfom['+field_index+']['+meta_type+']['+option_index+'][description]" class="form-control">';
            }

            if(fileurl){
                var image_box = '';
                image_box += '<li>';
                    image_box += '<span class="dashicons dashicons-move" style="margin-bottom: 7px;margin-top: 2px;"></span>';
                    image_box += '<span class="cfom-uploader-img-title"></span>';
                    image_box += '<div style="display: flex;">';
                        image_box += '<div class="cfom-uploader-img-center">';
                            image_box += img_icon;
                        image_box += '</div>';
                        image_box += '<input type="hidden" name="cfom['+field_index+']['+meta_type+']['+option_index+'][link]" value="'+fileurl+'">';
                        image_box += '<input type="hidden" name="cfom['+field_index+']['+meta_type+']['+option_index+'][id]" value="'+fileid+'" >';
                        image_box += '<input type="text" placeholder="Title" name="cfom['+field_index+']['+meta_type+']['+option_index+'][title]" class="form-control">';
                        image_box += '<input class="form-control" type="text" placeholder="'+price_placeholder+'" name="cfom['+field_index+']['+meta_type+']['+option_index+'][price]" class="form-control">';
                        image_box += url_field;
                        image_box += '<button class="btn btn-danger cfom-pre-upload-delete" style="height: 35px;"><i class="fa fa-times" aria-hidden="true"></i></button>';
                    image_box += '</div>';
                image_box += '</li>';
                
                $(image_box).appendTo(image_append);
            }
        }

        wp.media.editor.open(this);

        return false;
    });
    $(document).on('click', '.cfom-pre-upload-delete', function(e){
    
        e.preventDefault();
        $(this).closest('li').remove();
    });


    /**
        18- Add Fields Conditions
    **/
    $(document).on('click','.cfom-add-rule' , function(e){
        
        e.preventDefault();

        var div = $(this).closest('.cfom-slider');
        var option_index = parseInt(div.find('.cfom-condition-last-id').val());
        div.find('.cfom-condition-last-id').val( option_index + 1 );

        var field_index   = div.find('.cfom-fields-actions').attr('data-field-no');
        var condition_clone = $('.webcontact-rules:last').clone();
        
        var append_item = div.find('.cfom-condition-clone-js');
        condition_clone.find(append_item).end().appendTo(append_item);

        var field_type = '';
        var add_cond_selector = condition_clone.find('.cfom-conditional-keys');
        cfom_add_condition_set_index(add_cond_selector, field_index, field_type, option_index);

        $('.cfom-slider').find('.webcontact-rules:not(:last) .cfom-add-rule')
       .removeClass('cfom-add-rule').addClass('cfom-remove-rule')
       .removeClass('btn-success').addClass('btn-danger')
       .html('<i class="fa fa-minus" aria-hidden="true"></i>');
    }).on('click', '.cfom-remove-rule', function(e){

        $(this).parents('.webcontact-rules:first').remove();
        e.preventDefault();
        return false;
    });


    /**
        19- Add Fields Options
    **/
    $(document).on('click','.cfom-add-option' , function(e){
        
        e.preventDefault();

        var main_wrapper     = $(this).closest('.cfom-slider');
        var cfom_option_type = $(this).attr('data-option-type');

        var li = $(this).closest('li');
        var ul = li.closest('ul');
        var clone_item = li.clone();

        clone_item.find(ul).end().appendTo(ul);
        
        var option_index = parseInt(ul.find('#cfom-meta-opt-index').val());
        ul.find('#cfom-meta-opt-index').val( option_index + 1 );
        console.log(option_index);

        var field_index     = main_wrapper.find('.cfom-fields-actions').attr('data-field-no');
        var option_selector = clone_item.find('.cfom-option-keys');

        cfom_create_option_index(option_selector, field_index, option_index, cfom_option_type);
        
        $('.cfom-slider').find('.data-options:not(:last) .cfom-add-option')
       .removeClass('cfom-add-option').addClass('cfom-remove-option')
       .removeClass('btn-success').addClass('btn-danger')
       .html('<i class="fa fa-minus" aria-hidden="true"></i>');
    }).on('click', '.cfom-remove-option', function(e){

        $(this).parents('.data-options:first').remove();
        e.preventDefault();
        return false;
    });


    /**
        20- Auto Generate Option IDs
    **/
    $(document).on('keyup', '.option-title', function(){
    
        var closes_id = $(this).closest('li').find('.option-id');
        var option_id = $(this).val().replace(/[^A-Z0-9]/ig, "_");
        option_id = option_id.toLowerCase();
        $(closes_id).val( option_id );
    });


    /**
        21- Create Field data_name By Thier Title
    **/
    $('[data-section-id="core_field_meta"] [data-meta-id="data_name"] input').prop('readonly', true);
    $(document).on('keyup','[data-meta-id="title"] input[type="text"]', function() {

        var $this = $(this);
        var field_id = $this.val().toLowerCase().replace(/[^A-Za-z\d]/g,'_');
        var selector = $this.closest('.cfom-slider');

        var $is_core_fields = selector.find('.cfom-fields-actions').attr('data-section-id');
        if ($is_core_fields == 'core_field_meta') {
            return;
        }
        selector.find('[data-meta-id="data_name"] input[type="text"]').val(field_id);
    });


    /**
        22- Fields Sortable
    **/
    function insertAt(parent, element, index, dir) {
        var el = parent.children().eq(index);
        
        element[dir == 'top' ? 'insertBefore' : 'insertAfter'](el);
    }
    $(".cfom_field_table tbody").sortable({
        stop: function(evt, ui) {
                
            let parent = $('.cfom_save_fields_model'),
                el = parent.find('.' + ui.item.attr('id')),
                dir = 'top';
            if (ui.offset.top > ui.originalPosition.top) {
                dir = 'bottom';
            }
            insertAt(parent, el, ui.item.index(), dir);
        }
    });


    /**
        23- Fields Option Sortable
    **/
    $(".cfom-options-sortable").sortable();

    $("ul.cfom-options-container").sortable({
        revert : true
    });


    /**
        24- Fields Dataname Must Be Required
    **/
    function cfom_required_data_name($this){
        var selector  = $this.closest('.cfom-slider');
        var data_name = selector.find('[data-meta-id="data_name"] input[type="text"]').val();
        if (data_name == '') {
            var msg = 'Data Name must be required';
            var is_ok = false;    
        }else{
            msg = '';
            is_ok = true;   
        }
        selector.find('.cfom-req-field-id').html(msg);
        return is_ok;
    }


    /**
        25- Fields Add Option Index Controle Funtion
    **/
    function  cfom_create_option_index(option_selector, field_index , option_index, cfom_option_type ){

        option_selector.each(function(i, meta_field){
            var field_name = 'cfom['+field_index+'][options]['+option_index+']['+$(meta_field).attr('data-metatype')+']';
            $(meta_field).attr('name', field_name);
        });
    }


    /**
        26- Fields Add Condition Index Controle Function
    **/
    function  cfom_add_condition_set_index(add_c_selector, opt_field_no, field_type , opt_no ){
       add_c_selector.each(function(i, meta_field){
            // var field_name = 'cfom['+field_no+']['+$(meta_field).attr('data-metatype')+']';
            var field_name = 'cfom['+opt_field_no+'][conditions][rules]['+opt_no+']['+$(meta_field).attr('data-metatype')+']';
            $(meta_field).attr('name', field_name);
        });
    }


    /**
        27- Get All Fields Title On Condition Element Value After Click On Condition Tab
    **/
    // populate_conditional_elements();
    $(document).on('click', '.cfom-condition-tab-js', function(e){
        e.preventDefault();

        var div      = $(this).closest('.cfom-slider');
        var elements = div.find('select[data-metatype="elements"]');
        
        elements.each(function(i, item) {

           var conditional_elements = item.value;
           var exiting_meta = $(item).attr('data-existingvalue', conditional_elements);
        });
        
        populate_conditional_elements(elements);

    });

    function populate_conditional_elements(elements) {

        // resetting
        jQuery('select[data-metatype="elements"]').html('');

        jQuery(".cfom-slider").each(function(i, item) {

            var conditional_elements = jQuery(item).find(
                    'input[data-metatype="title"]').val();
            var conditional_elements_value = jQuery(item).find(
                    'input[data-metatype="data_name"]').val();

            if ($.trim(conditional_elements_value) !== '') {

                var $html = '';
                $html += '<option value="'
                            + conditional_elements_value + '">'
                            + conditional_elements
                        + '</option>';

                 $($html).appendTo('select[data-metatype="elements"]');
            }
                        
        });

        // setting the existing conditional elements
        $(".cfom-slider").each(function(i, item) {
                    
            $(item).find('select[data-metatype="elements"]').each(function(i, condition_element){
            
                var existing_value1 = $(condition_element).attr("data-existingvalue");

                if ($.trim(existing_value1) !== '') {
                    jQuery(condition_element).val(existing_value1);
                }
                
            });    
        });
    }


    /**
        28- validate API WooCommerce Product
    **/
    function validate_api_wooproduct(form){
    
        jQuery(form).find("#nm-sending-api").html(
                '<img src="' + nm_personalizedproduct_vars.doing + '">');
        
        var data = jQuery(form).serialize();
        data = data + '&action=nm_personalizedproduct_validate_api';
        
        jQuery.post(ajaxurl, data, function(resp) {

            //console.log(resp);
            jQuery(form).find("#nm-sending-api").html(resp.message);
            if( resp.status == 'success' ){
                window.location.reload(true);           
            }
        }, 'json');
        
        
        return false;
    }

    $('[data-section-id="Shipping"] [data-meta-id="data_name"] input').prop('readonly', true);

    /**
        29- Toggle Switch Yes And No
    **/
    if ($('.cfom-toggle-switch-js:input[type=radio]:checked')) {
        
        var edit_field = $('.cfom-toggle-switch-js:input[type=radio]:checked');
        if (edit_field.val() == 'yes') {
            edit_field.parent().addClass('btn-default active');
        }else{
            edit_field.parent().addClass('btn-default active');
        }
    }


    /**
        30- Reset Selected Section To Defualt
    **/
    $('body').on('click','a.cfom-reset-section-js', function(e){
        e.preventDefault();

        var section_type = $(this).attr('data-section-type');

        swal({
            title: "Are you sure?",
            text: "It will replace all existing fields to defualt",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55 ",
            cancelButtonColor: "#DD6B55",
            confirmButtonText: "Yes",
            cancelButtonText: "No",
            closeOnConfirm: true
            }, function (isConfirm) {
                if (!isConfirm) return;
                $("#cfom-reset-loader-" + section_type).html('<img src="' + cfom_vars.loader + '">');

                var data = {
                    action              : 'cfom_set_default_fields',
                    cfom_reset_section  : section_type
                };

                $.post(ajaxurl, data, function(resp){

                    $("#cfom-reset-loader-" + section_type).html('<span class="dashicons dashicons-controls-repeat"></span>');
                    swal({title: "Done", text: resp.message, type: "success" ,confirmButtonColor: '#217ac8'},
                        function(){ 
                            location.reload();
                    });
                });
        });
    });
    
    // Show hide section fields
    $('.cfom-section-meta-title').on('click', function(e){
        e.preventDefault();
        
        $(this).closest('td').find('.cfom-section-meta').toggle();
    });

});