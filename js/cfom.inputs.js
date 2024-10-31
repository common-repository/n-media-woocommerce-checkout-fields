/**
 * cfom input scripts
 * 
 **/
 
 "use strict"
 
 var cfom_bulkquantity_meta = '';
 var cfom_pricematrix_discount_type = '';
 
 jQuery(function($){
     
    // $('[data-toggle="tooltip"]').tooltip({container:'body'});
    
    // Measure
    $('.cfom-measure').on('change', '.cfom-measure-unit', function(e){
        
        e.preventDefault();
        // console.log($(this).text());
        
        $(this).closest('.cfom-measure').find('.cfom-measure-input').trigger('change');
    });
    
    // Disable ajax add to cart
    $(document).ready(function() {
         $(".add_to_cart_button").removeClass("ajax_add_to_cart")
     });
     
    // Range slider updated
    $(document).on('cfom_range_slider_updated', function(e){ 
        
        wc_product_qty.val(e.qty);
        cfom_update_option_prices();
    });
    
    // move modals to body bottom
    if( $('.cfom-modals').length > 0 ) {
         $('.cfom-modals').appendTo('body');
    }
    
    $.each(cfom_input_vars.cfom_inputs, function(index, input){
         
        // console.log(input.type);
        var InputSelector = $("#"+input.data_name);
        
        // Applying JS on inputs
        switch( input.type ) {
            
            // masking
            case 'text':
                if(input.type === 'text' && input.input_mask !== undefined && input.input_mask !== '') {
                    InputSelector.inputmask( input.input_mask  );
                }
                break;
                
            case 'date':
                if( input.jquery_dp === 'on'){
                
                    InputSelector.datepicker("destroy");
                    InputSelector.datepicker({
                        changeMonth: true,
        				changeYear: true,
        				dateFormat: input.date_formats,
        				yearRange: input.year_range,
                    });
                    
                    if( input.past_dates === 'on' ) {
                        var date_today = new Date();
                        InputSelector.datepicker('option', 'minDate', date_today);
                    }
                    if( input.no_weekends === 'on' ) {
                        InputSelector.datepicker('option', 'beforeShowDay', $.datepicker.noWeekends);
                    }
                }
                break;
                
            case 'image':
                // Image Tooltip
                if( input.show_popup === 'on') {
                    $('.cfom-zoom').imageTooltip({
    							  xOffset: 5,
    							  yOffset: 5
    						    });
                }
						    
				// Data Tooltip
				// $(".pre_upload_image").tooltip({container: 'body'});
                break;
            // date_range
            case 'daterange':
                InputSelector.daterangepicker({
                    autoApply: (input.auto_apply == 'on') ? true : false,
                    locale: {
                      format: (input.date_formats !== '') ? input.date_formats : "YYYY-MM-DD"
                    },
                    showDropdowns: (input.drop_down == 'on') ? true : false,
                    showWeekNumbers: (input.show_weeks == 'on') ? true : false,
                    timePicker: (input.time_picker == 'on') ? true : false,
                    timePickerIncrement: (input.tp_increment !== '') ? parseInt(input.tp_increment) : '',
                    timePicker24Hour: (input.tp_24hours == 'on') ? true : false,
                    timePickerSeconds: (input.tp_seconds == 'on') ? true : false,
                    drops: (input.open_style !== '') ? input.open_style : 'down',
                    startDate: input.start_date,
                    endDate: input.end_date,
                    minDate: input.min_date,
                    maxDate: input.max_date,
                });
                break;
                
            // color: iris
            case 'color':
                
                InputSelector.css( 'background-color', input.default_color);
                var iris_options = {
                    'palettes': cfom_get_palette_setting(input),
                    'hide'  : input.show_onload == 'on' ? false : true,
                    'color' : input.default_color,
                    'mode' : input.palettes_mode != '' ? input.palettes_mode : 'hsv',
                    'width': input.palettes_width != '' ? input.palettes_width : 200,
                    change: function(event, ui) {
                        
                        InputSelector.css( 'background-color', ui.color.toString());
                        InputSelector.css( 'color', '#fff');   
                    }
                }
    
                InputSelector.iris(iris_options);
                break;
                
            // Palettes
            case 'palettes':
                
                // Nothing so far.
                break;
            // Bulk quantity
            case 'bulkquantity':
                
                setTimeout(function() { $('.quantity.buttons_added').hide(); }, 50);
                $('form.cart').find('.quantity').hide();
				
				// setting formatter
				/*if ($('form.cart').closest('div').find('.price').length > 0){
					wc_price_DOM = $('form.cart').closest('div').find('.price');
				}*/

                cfom_bulkquantity_meta = input.options;				
				// Starting value
				cfom_bulkquantity_price_manager(1);
                break;
                
            case 'pricematrix':
                
                cfom_pricematrix_discount_type = input.discount_type;
               
                if( input.show_slider === 'on' ) {
                    var slider = new Slider('.cfom-range-slide', {
                       formatter: function(value){
                           jQuery.event.trigger({
                            	type: "cfom_range_slider_updated",
                            	qty: value,
                            	time: new Date()
                            });
                           return cfom_input_vars.text_quantity+": "+value;
                       }
                    });
                }
                break;
        }
        
        
     });
       
 });
 

 
 function cfom_get_palette_setting(input){
     
     var palettes_setting = false;
     // first check if palettes is on
     if(input.show_palettes === 'on'){
         palettes_setting = true;
     }
     if(palettes_setting && input.palettes_colors !== ''){
         palettes_setting = input.palettes_colors.split(',');
     }
     
     return palettes_setting;
 }
 
function cfom_get_field_type_by_id( field_id ) {
 
 var field_type = '';
 jQuery.each(cfom_input_vars.cfom_inputs, function(i, field){
    
     if( field.data_name === field_id ) {
         field_type = field.type;
         return;
     }
 });
 
 return field_type;
}

// Get all field meta by id
function cfom_get_field_meta_by_id( field_id ) {
 
 var field_meta = '';
 jQuery.each(cfom_input_vars.cfom_inputs, function(i, field){
    
     if( field.data_name === field_id ) {
         field_meta = field;
         return;
     }
 });
 
 return field_meta;
}

function cfom_bulkquantity_price_manager( quantity ){
			         
    // 	console.log(cfom_bulkquantity_meta);
	jQuery('.cfom-bulkquantity-qty').val(quantity);

	var cfom_base_price = 0;
	jQuery.each(JSON.parse(cfom_bulkquantity_meta), function(idx, obj) {
	    
		var qty_range       = obj['Quantity Range'].split('-');
		var qty_range_from  = qty_range[0];
		var qty_range_to    = qty_range[1];
		
		if (quantity >= parseInt(qty_range_from) && quantity <= parseInt(qty_range_to)) {

			// Setting Initial Price to 0 and taking base price
			var price = 0;
			cfom_base_price = (obj['Base Price'] == undefined || obj['Base Price'] == '') ? 0 : obj['Base Price'];
			jQuery('.cfom-bulkquantity-options option:selected').attr('data-baseprice', cfom_base_price);

			// Taking selected variation price
			var variation = jQuery('.cfom-bulkquantity-options').val();
			var var_price = obj[variation];
			jQuery('.cfom-bulkquantity-options option:selected').attr('data-price', var_price);
		}
		
	});
	
	cfom_update_option_prices();
}