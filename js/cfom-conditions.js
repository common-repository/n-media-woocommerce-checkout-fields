/**
 * cfom Fields Conditions
 **/
"use strict"

var cfom_field_matched_rules    = {};
var cfom_hidden_fields          = [];
jQuery(function($) {
    
    $(".cfom-wrapper").on('change', 'select,input:radio,input:checkbox', function(e){
        
        cfom_check_conditions();
    });
    
    $(document).on('cfom_field_shown', function(e){
        
        // Remove from array
        $.each(cfom_hidden_fields, function(i, item){
            if( item === e.field){
                
                
                // Set checked/selected again
                cfom_set_default_option(item);
                
                cfom_hidden_fields.splice(i, 1);
                $.event.trigger({type: "cfom_hidden_fields_updated",
                                field: e.field,
                                time: new Date()
                                });
                                
            }
        });
        
        // Apply FileAPI to DOM
        var field_meta = cfom_get_field_meta_by_id( e.field );
        if( field_meta.type === 'file' || field_meta.type === 'cropper' ) {
            
            cfom_setup_file_upload_input(field_meta);
        }
        
        // Price Matrix
        if( field_meta.type == 'pricematrix' ) {
            // Resettin
            $(".cfom_pricematrix").removeClass('active');
            
            // Set Active
            var classname = "."+field_meta.data_name;
            // console.log(classname);
            $(classname).find('.cfom_pricematrix').addClass('active')
        }
        
    });
    
    $(document).on('cfom_hidden_fields_updated', function(e){
       
        
        $("#conditionally_hidden").val(cfom_hidden_fields);
        cfom_update_option_prices();
    });
    
    $(document).on('cfom_field_hidden', function(e) {
       
        var field_meta      = cfom_get_field_meta_by_id( e.field );
        var element_type    = field_meta.type;
        var field_section   = field_meta.section;
        
        switch( element_type ) {
        
            case 'select':
                $('select[name="cfom['+field_section+']['+e.field+']"]').val('');
                break;
            
            case 'checkbox':
                $('input[name="cfom['+field_section+']['+e.field+'][]"]').prop('checked', false);
                break;
            case 'radio':
                $('input[name="cfom['+field_section+']['+e.field+']"]').prop('checked', false);
                break;
                
            case 'file':
                $('#filelist-'+e.field).find('.u_i_c_box').remove();
                break;
                
            case 'image':
                 $('input[name="cfom['+field_section+']['+e.field+'][]"]').prop('checked', false);
                 break;
                 
            case 'imageselect':
                    var the_id    = 'cfom-imageselect'+e.field;
                    $("#"+the_id).remove();
                 break;
                
            default:
                // Reset text/textarea/date/email etc types
                $('#'+e.field).val('');
                break;
        }
        
        cfom_hidden_fields.push(e.field);
        $.event.trigger({type: "cfom_hidden_fields_updated",
                        field: e.field,
                        time: new Date()
                        });
    });
    
        
    setTimeout(function(){
        cfom_check_conditions();
    }, 500);
    
});

function cfom_set_default_option( field_id ) {
    
    
    var field = cfom_get_field_meta_by_id(field_id);
    switch( field.type ) {
     
        // Check if field is 
        case 'radio':
            jQuery.each(field.options, function(label, options){
                
               if( options.raw == field.selected ) {
                 jQuery("#"+options.option_id).prop('checked', true);
               } 
            });
            
        break;
        
        case 'select':
            jQuery("#"+field.data_name).val(field.selected);
        break;
        
        case 'image':
            jQuery.each(field.images, function(index, img){
                
               if( img.title == field.selected ) {
                 jQuery("#"+field.data_name+'-'+img.id).prop('checked', true);
               } 
            });
        break;
        
        case 'checkbox':
            jQuery.each(field.options, function(label, options){
                
                
                var default_checked = field.checked.split("\n");
                jQuery.each(default_checked, function(j, checked_option) {
                    
                   if( options.raw == checked_option ) {
                
                       jQuery("#"+options.option_id).prop('checked', true);
                   } 
                });
            });
            
            
        break;
    }
}

function cfom_check_conditions() {
    
    jQuery.each(cfom_input_vars.conditions, function(field, condition){
       
    
        // It will return rules array with True or False
        cfom_field_matched_rules[field] = cfom_get_field_rule_status(condition);

        // Now check if all rules are valid
        if( condition.bound === 'Any' && cfom_field_matched_rules[field] > 0) {
            cfom_unlock_field_from_condition( field, condition.visibility );
        } else if(condition.bound === 'All' && cfom_field_matched_rules[field] == condition.rules.length) {
            cfom_unlock_field_from_condition( field, condition.visibility );
        } else {
            cfom_lock_field_from_condition( field, condition.visibility );
        }
        
    });
}

function cfom_unlock_field_from_condition( field, unlock ) {
    
    var classname = '.cfom-input-'+field;
    if( unlock === 'Show') {
        jQuery(classname).show().removeClass('cfom-locked').addClass('cfom-unlocked')
        .trigger({
        	type: "cfom_field_shown",
        	field: field,
        	time: new Date()
        });
    } else {
        jQuery(classname).hide().removeClass('cfom-locked').addClass('cfom-unlocked')
        .trigger({
    	type: "cfom_field_hidden",
    	field: field,
    	time: new Date()
    });
    }
}

function cfom_lock_field_from_condition( field, lock) {
    
    var classname = '.cfom-input-'+field;
    if( lock === 'Show') {
        jQuery(classname).hide().removeClass('cfom-unlocked').addClass('cfom-locked')
        .trigger({
    	type: "cfom_field_hidden",
    	field: field,
    	time: new Date()
    });
    } else {
        jQuery(classname).show().removeClass('cfom-unlocked').addClass('cfom-locked')
        .trigger({
    	type: "cfom_field_shown",
    	field: field,
    	time: new Date()
    });
    }
    
    jQuery.event.trigger({
    	type: "cfom_field_locked",
    	field: field,
    	lock: lock,
    	time: new Date()
    });
}

// It will return rules array with True or False
function cfom_get_field_rule_status( condition ) {
    
    var cfom_rules_matched = 0;
    jQuery.each(condition.rules, function(i, rule){
        
        var element_type = cfom_get_field_type_by_id(rule.elements);
        
        switch ( rule.operators ) {
            case 'is':
                if( element_type === 'checkbox'){
                    var element_value = cfom_get_element_value(rule.elements);
                    jQuery(element_value).each(function(i, item){
                        if( item === rule.element_values ) {
                            cfom_rules_matched++;
                        }
                    });
                } else if( cfom_get_element_value(rule.elements) === rule.element_values ) {
                        cfom_rules_matched++;
                }
                break;
                
            case 'not':
                if( element_type === 'checkbox'){
                    var element_value = cfom_get_element_value(rule.elements);
                    jQuery(element_value).each(function(i, item){
                        if( item !== rule.element_values ) {
                            cfom_rules_matched++;
                        }
                    });
                } else if( cfom_get_element_value(rule.elements) !== rule.element_values ) {
                    cfom_rules_matched++;
                }
                break;
                
            case 'greater than':
                if( element_type === 'checkbox'){
                    var element_value = cfom_get_element_value(rule.elements);
                    jQuery(element_value).each(function(i, item){
                        if( parseFloat(item) > parseFloat(rule.element_values) ) {
                            cfom_rules_matched++;
                        }
                    });
                } else if( parseFloat(cfom_get_element_value(rule.elements)) > parseFloat(rule.element_values) ) {
                    cfom_rules_matched++;
                }
                break;
                
            case 'less than':
                if( element_type === 'checkbox'){
                    var element_value = cfom_get_element_value(rule.elements);
                    jQuery(element_value).each(function(i, item){
                        if( parseFloat(item) < parseFloat(rule.element_values) ) {
                            cfom_rules_matched++;
                        }
                    });
                } else if( parseFloat(cfom_get_element_value(rule.elements)) < parseFloat(rule.element_values) ) {
                    cfom_rules_matched++;
                }
                break;
            
            
        }
    });
    
    return cfom_rules_matched;
}

// Getting rule element value
function cfom_get_element_value( field_name ) {
    
    var field_meta      = cfom_get_field_meta_by_id( field_name );
    var element_type    = field_meta.type;
    var field_section   = field_meta.section;
    var value_found     = '';
    var value_found_cb  = [];
    
    switch( element_type ) {
        
        case 'select':
            value_found = jQuery('select[name="cfom['+field_section+']['+field_name+']"]').val();
            break;
            
        case 'radio':
            value_found = jQuery('input[name="cfom['+field_section+']['+field_name+']"]:checked').val();
            break;

        case 'checkbox':
                jQuery('input[name="cfom['+field_section+']['+field_name+'][]"]:checked').each(function(i){
                    value_found_cb[i] = jQuery(this).val();
                });
            break;
            
        case 'image':
            value_found = jQuery('input[name="cfom['+field_section+']['+field_name+'][]"]:checked').attr('data-label');
            break;
            
        case 'imageselect':
            value_found = jQuery('input[name="cfom['+field_section+']['+field_name+']"]:checked').attr('data-title');
            break;
            
    }
    
    if( element_type === 'checkbox') {
        return value_found_cb;
    }
    
    return value_found;
}