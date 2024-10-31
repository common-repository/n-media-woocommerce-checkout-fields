"use strict"

var cfomWrapper = jQuery(".cfom-wrapper ");
var cfomPriceListContainer = '';
var cfomPriceListContainerRow = '';
// Quantity update capture/update price change
var wc_product_qty  = jQuery('form.cart').find('input[name="quantity"]');
var cfom_product_base_price = cfom_input_vars.wc_product_price;

jQuery(function($){
    
    cfom_update_option_prices();
    
    $('.cfom-wrapper').on('change', 'select,input:checkbox,input:radio', function(e){
       
        cfom_update_option_prices();
   });
   
   cfomWrapper.find('input[type="number"]').on('keyup change', function(e){
       
        cfom_update_option_prices();
   });

    // Measue input creating price-checkbox on change
    $('.cfom-wrapper').on('click change keyup', '.cfom-measure-input', function() {
       
       var data_name = $(this).attr('id');
       var m_qty    = $(this).val();
       
        // console.log(use_units);
        $('input:radio[name="cfom[unit]['+data_name+']"]:checked').attr('data-qty', m_qty);
        cfom_update_option_prices();
    });
    
    
});


function cfom_update_option_prices() {
    
    // Set hidden input
    var cfom_all_option_prices = cfom_update_get_prices();
    jQuery("#cfom_option_fees").val( JSON.stringify(cfom_all_option_prices) );
    // Triggering Checkout Update
    jQuery('body').trigger('update_checkout');
    return;
   
}

function cfom_update_get_prices() {
    
    var options_price_added = [];
    cfomWrapper.find('select,input:checkbox,input:radio').each(function(i, input){
        
        // if fixedprice (addon) then return
        if( jQuery("option:selected", this).attr('data-unitprice') !== undefined ) return;
        
        var selected_option_price = jQuery("option:selected", this).attr('data-price');
        var selected_option_label = jQuery("option:selected", this).attr('data-label');
        var selected_option_title = jQuery("option:selected", this).attr('data-title');
        var selected_option_apply = jQuery("option:selected", this).attr('data-onetime') !== 'on' ? 'variable' : 'onetime';
        var selected_option_taxable = jQuery("option:selected", this).attr('data-taxable');
        var selected_option_without_tax = jQuery("option:selected", this).attr('data-without_tax');
        var selected_option_optionid = jQuery("option:selected", this).attr('data-optionid');
        var selected_option_data_name = jQuery("option:selected", this).attr('data-data_name');
        
        var checked_option_price = jQuery(this).attr('data-price');
        var checked_option_label = jQuery(this).attr('data-label');
        var checked_option_title = jQuery(this).attr('data-title');
        var checked_option_apply = jQuery(this).attr('data-onetime') !== 'on' ? 'variable' : 'onetime';
        var checked_option_taxable = jQuery(this).attr('data-taxable');
        var checked_option_without_tax = jQuery(this).attr('data-without_tax');
        var checked_option_optionid = jQuery(this).attr('data-optionid');
        var checked_option_data_name = jQuery(this).attr('data-data_name');
        
        // apply now being added from data-attribute for new prices
        if( jQuery(this).attr('data-apply') !== undefined ) {
            checked_option_apply = jQuery(this).attr('data-apply');
            selected_option_apply = jQuery(this).attr('data-apply');
        }
        
            
        var does_option_has_price = true;
        
        if( (checked_option_price == undefined || checked_option_price == '') && 
            (selected_option_price == undefined || selected_option_price == '') ) {
            return;
        }
            
        var option_price = {};
        if( jQuery(this).prop("checked") ){
            
            if( checked_option_title !== undefined ) {
                option_price.label = checked_option_title+' '+checked_option_label;
            } else {
                option_price.label = checked_option_label;
            }
            option_price.price = checked_option_price;
            option_price.apply = checked_option_apply;
            
            option_price.product_title  = cfom_input_vars.product_title;
            option_price.taxable        = checked_option_taxable;
            option_price.without_tax    = checked_option_without_tax;
            option_price.option_id      = checked_option_optionid;
            option_price.data_name      = checked_option_data_name;
            
            // More data attributes
            if( checked_option_apply === 'measure' ) {
                option_price.qty = jQuery(this).attr('data-qty');
                option_price.use_units = jQuery(this).attr('data-use_units');
            }
            
            options_price_added.push( option_price );
            
    	} else if(selected_option_price !== undefined && is_option_calculatable(this) ) {
    	    
    	    if( selected_option_title !== undefined ) {
                option_price.label = selected_option_title+' '+selected_option_label;
            } else {
                option_price.label = selected_option_label;
            }
    	    option_price.price = selected_option_price;
            option_price.apply = selected_option_apply;
            
            option_price.product_title  = cfom_input_vars.product_title;
            option_price.taxable        = selected_option_taxable;
            option_price.without_tax    = selected_option_without_tax;
            option_price.option_id      = selected_option_optionid;
            option_price.data_name      = selected_option_data_name;
            
            options_price_added.push( option_price );
    	} else {
    	    
    	    
    	    /*if( jQuery(this).data('type') == 'measure' ) {
    	        
    	        var product_qty = cfom_get_order_quantity();
    	        var measure_price = checked_option_price * jQuery(this).val();
    	        console.log(checked_option_price);
    	        console.log(measure_price);
    	        checked_option_title = checked_option_title+' - '+
    	                                cfom_get_formatted_price(checked_option_price)+'x'
    	                                +jQuery(this).val();
    	        
        	    option_price.label = checked_option_title;
        	    option_price.price = measure_price;
        	    option_price.measure = jQuery(this).val();
                option_price.apply = 'variable';
                
                option_price.product_title  = cfom_input_vars.product_title;
                option_price.taxable        = true;
                option_price.without_tax    = '';
                
                options_price_added.push( option_price );
    	    }*/
    	}
    	
    });
    
    
    // Price matrix
    var cfom_pricematrix = jQuery(".cfom_pricematrix.active").val();
    var cfom_pricematrix_discount = jQuery(".cfom_pricematrix.active").attr('data-discount');
    var cfom_pricematrix_id = jQuery(".cfom_pricematrix.active").data('dataname');
    
    var cfom_matrix_array = Array();
    var apply_as_discount = cfom_pricematrix_discount == 'on' ? true : false;
    
    if( cfom_pricematrix !== undefined) {
        jQuery.each( JSON.parse(cfom_pricematrix), function(range, meta){
            var option_price = {};
            
            var range_break = range.split("-");
            var range_from  = parseInt(range_break[0]);
            var range_to    = parseInt(range_break[1]);
            var product_qty = cfom_get_order_quantity();
            
            // console.log(meta);
            
            if( product_qty >= range_from && product_qty <= range_to ) {
                
                option_price.label = meta.label;
                option_price.price = meta.price;
                option_price.percent = meta.percent;
                option_price.range = range;
                option_price.apply = (apply_as_discount) ? 'matrix_discount' : 'matrix';
                option_price.data_name = cfom_pricematrix_id;
                options_price_added.push( option_price );
            }
            
        });
    }
    
    // Variation quantities
    var cfom_quantities_qty = 0;
    jQuery('.cfom-input-quantities').each(function(){
        
		// Checking if quantities is hidden
		if( jQuery(this).hasClass('cfom-locked') ) {
		    // Resetting quantity to one
		    wc_product_qty.val(1);
		    return;
		}
        
        jQuery(this).find('.cfom-quantity').each(function(){
		
    		
    		var option_price = {};
    		
    		option_price.price      = jQuery(this).attr('data-price');
            option_price.label      = jQuery(this).attr('data-label');
            option_price.quantity   = (jQuery(this).val() === '' ) ? 0 :  jQuery(this).val();
            option_price.include    = jQuery(this).attr('data-includeprice');
            option_price.apply      = 'quantities';
            cfom_quantities_qty     += parseInt(option_price.quantity);
            
            options_price_added.push( option_price );
            wc_product_qty.val(cfom_quantities_qty);
        });
    });
    
    // Bulkquantity
    if( jQuery('#cfom-input-bulkquantity').length > 0 ){
        
		var option_price = {};
		
		var cfom_bq_container = jQuery('#cfom-input-bulkquantity');
		
		option_price.price      = cfom_bq_container.find('.cfom-bulkquantity-options option:selected').attr('data-price');
		option_price.base       = cfom_bq_container.find('.cfom-bulkquantity-options option:selected').attr('data-baseprice');
        option_price.label      = cfom_bq_container.find('.cfom-bulkquantity-options option:selected').attr('data-label');
        option_price.quantity   = cfom_bq_container.find('.cfom-bulkquantity-qty').val();
        // option_price.include    = jQuery(this).attr('data-includeprice');
        option_price.apply      = 'bulkquantity';
        options_price_added.push( option_price );
        
        /*var option_price = {};
        // Base price
        option_price.price      = cfom_bq_container.find('.cfom-bulkquantity-baseprice').attr('data-price');
        option_price.label      = cfom_bq_container.find('.cfom-bulkquantity-baseprice').attr('data-label');
        // option_price.include    = jQuery(this).attr('data-includeprice');
        option_price.apply      = 'onetime';
        
        options_price_added.push( option_price );*/
    }
    
    // Fixedprice addon
     if( jQuery('.cfom-input-fixedprice').length > 0 ){
        
		var option_price = {};
		
		var cfom_fp_container = jQuery('.cfom-input-fixedprice.cfom-unlocked');
		
		option_price.price      = cfom_fp_container.find('select option:selected').attr('data-price') || 0;
		option_price.unitprice  = cfom_fp_container.find('select option:selected').attr('data-unitprice') || 0;
        option_price.label      = cfom_fp_container.find('select option:selected').attr('data-label') || '';
        option_price.quantity   = cfom_fp_container.find('select option:selected').attr('data-qty') || 0;
        // option_price.include    = jQuery(this).attr('data-includeprice');
        option_price.apply      = 'fixedprice';
        options_price_added.push( option_price );
		
     }
    
    // console.log(options_price_added);
    return options_price_added;
}

// Return formatted price with decimal and seperator
function cfom_get_formatted_price( price ) {
    
    var decimal_separator= cfom_input_vars.wc_decimal_sep;
	var no_of_decimal    = cfom_input_vars.wc_no_decimal;
	
	var formatted_price = parseFloat(price);
	formatted_price = formatted_price.toFixed(no_of_decimal);
	formatted_price = formatted_price.toString().replace('.', decimal_separator);
	formatted_price = cfom_add_thousand_seperator(formatted_price);
		
	return formatted_price;
}

function cfom_add_thousand_seperator(n){

    var rx= /(\d+)(\d{3})/;
    return String(n).replace(/^\d+/, function(w){
        if (cfom_input_vars.wc_thousand_sep) {
            while(rx.test(w)){
                w= w.replace(rx, '$1'+cfom_input_vars.wc_thousand_sep+'$2');
            }
        }
        return w;
    });
}

// sometime options should not be calculated like in case of bulkquantity
function is_option_calculatable( selector ) {
    
    var option_calculatable = true;
    if( jQuery(selector).attr('data-type') === 'bulkquantity' ) {
        option_calculatable = false;
    }
    
    return option_calculatable;
}

// Return quantity
function cfom_get_order_quantity(){
    
    var quantity = cfom_input_vars.is_shortcode === 'yes' ? 1 : wc_product_qty.val();
    quantity = quantity || 1;
    return parseInt(quantity);
    
}

// Set quantity
function cfom_set_order_quantity(qty){
    
    wc_product_qty.val(qty);
}