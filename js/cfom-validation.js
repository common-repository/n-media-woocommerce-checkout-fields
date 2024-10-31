"use strict"
 
jQuery(function($){
    
//   console.log('loaded cart');

    var cfom_cart_validated = false;
    
    if($.blockUI !== undefined){ 
        $.blockUI.defaults.message = "";
    }
   
   $('form.cart').on('submit', function(e) { 
      
       if( cfom_cart_validated ) return true;
       
       e.preventDefault();
       
       // Removing validation div 
       $(".cfom-ajax-validation").remove();
       $('form.cart').block();
       
       var data = $(this).serialize();
       $.post(cfom_input_vars.ajaxurl, data, function( notices ) {
           
           $('form.cart').unblock();
           if( notices.length > 0 ) {
               
               var show_notice = $('<div/>')
                                .addClass('alert alert-danger cfom-ajax-validation')
                                .css('clear','both')
                                .html( notices.join("<br>"))
                                .appendTo('form.cart');
                                
           } else {
               
                cfom_cart_validated = true;
                $('button[name="add-to-cart"]').trigger('click');
           }
           
       });
       
   }); 
});