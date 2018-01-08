jQuery( document ).ready(function( $ ) {

	var b2b_b2c_checkout = checkout_obj.b2b_b2c_checkout;

	if( b2b_b2c_checkout == 'true' ) {
		add_organisation_field();
		$('.checkout-sidebar').addClass('b2b-b2c-corporate');
	} else {
		$('.checkout-sidebar').addClass('b2b-b2c-private');
	}

    $('#b2b-b2c-checkout-tab a').click(function(e){
    	e.preventDefault();

    	var $link_parent = $(this).parent(),
    		$form = $("form.woocommerce-checkout"),
    		checkout = false;

    	$.blockUI.defaults.overlayCSS.cursor = 'default';
		
		$form.addClass("processing");

		$form.block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});

		if( $link_parent.hasClass("corporate") ) {
			checkout = true;
		} else {
			checkout = false;
		}

    	jQuery.ajax({
	        type: "post",
	        dataType: "json",
	        url: checkout_obj.ajaxurl,
	        data: {action: 'b2b_b2c_set_gateway',b2b_b2c_checkout: checkout},
	        success: function(msg){
	        	$form.unblock();
	            $('body').trigger('update_checkout');
	        }
	    });

    	$("#b2b-b2c-checkout-tab li").removeClass("active");
        $link_parent.addClass("active");

        if( checkout ) {

        	add_organisation_field();

        	$('.checkout-sidebar').addClass('b2b-b2c-corporate');
        	$('.checkout-sidebar').removeClass('b2b-b2c-private');
        } else {

        	remove_organisation_field();

        	$('.checkout-sidebar').addClass('b2b-b2c-private');
        	$('.checkout-sidebar').removeClass('b2b-b2c-corporate');
        }
        
    });

    function add_organisation_field() {

    	$("#klarna-part-payment-get-address").remove();

    	organisation_field = 
		'<p class="form-row form-row-wide" id="b2b-b2c-get-address">\
			<label for="b2b-b2c-organisation">Organisationnummer <abbr class="required" title="required">*</abbr></label>\
			<input type="text" class="input-text " name="b2b-b2c-organisation" id="b2b-b2c-organisation" placeholder="YYMMDD-XXXX" value="">\
		</p>';

		if ( !$( "#b2b-b2c-get-address" ).length ) {
			$(".woocommerce-billing-fields").prepend(organisation_field);    
		}

    }

    function remove_organisation_field() {
    	$("#b2b-b2c-get-address").remove();
    }

});