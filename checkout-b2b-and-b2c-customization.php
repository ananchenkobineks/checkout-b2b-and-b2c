<?php 

/**
* Plugin Name: 	Checkout: B2B and B2C Customization
* Version: 		1.1
* Author: 		Jack Ananchenko
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }


add_action( 'init', 'b2b_b2c_checkout_customization_init' );

function b2b_b2c_checkout_customization_init() {

	add_action( 'woocommerce_checkout_before_customer_details', 'b2b_b2c_customise_checkout_field');

	add_action( 'woocommerce_checkout_process', 'b2b_b2c_checkout_field_process');

	add_action( 'woocommerce_checkout_update_order_meta', 'b2b_b2c_checkout_field_update_order_meta' );

	add_action( 'woocommerce_checkout_after_order_review', 'b2b_b2c_after_order_review', 20);

	add_filter( 'woocommerce_available_payment_gateways','b2b_b2c_filter_gateways', 10, 1);

	add_filter( 'woocommerce_get_checkout_url', 'b2b_b2c_default_url_to_checkout', 20 );

	add_filter( 'woocommerce_ship_to_different_address_checked', '__return_true' );

	add_filter( 'woocommerce_email_order_meta_keys', 'b2b_b2c_checkout_field_order_meta_keys');
}

add_action( 'template_redirect', 'b2b_b2c_thankyou_page_redirect' );

function b2b_b2c_thankyou_page_redirect(){

	global $woocommerce;
    	
    if( is_cart() && WC()->cart->cart_contents_count == 0) {

		$b2b_b2c_checkout = get_b2b_b2c_wc_session_val();

		if( $b2b_b2c_checkout == 'true' ) {

			$order_id = WC()->session->get('b2b_b2c_order_id');

			if( !empty($order_id) ) {

		    	WC()->session->set('b2b_b2c_order_id', '');

		    	$url = get_permalink( get_page_by_path( 'bekraftelse' ) );
		    	wp_safe_redirect($url);
			}
		}
    }
}

add_action('wp_enqueue_scripts', 'callback_for_setting_up_scripts');

function callback_for_setting_up_scripts() {
    wp_register_style( 'b2b-b2c-style', plugins_url( 'checkout-b2b-and-b2c-customization/css/checkout.css' ) );

	if (is_checkout()) {
		wp_enqueue_style( 'b2b-b2c-style' );
		wp_enqueue_script('b2b-b2c-script', plugins_url( 'checkout-b2b-and-b2c-customization/js/checkout.js' ), array('jquery') );

		$checkout_obj = array(
			'ajaxurl' 			=> admin_url('admin-ajax.php'),
			'b2b_b2c_checkout'	=> get_b2b_b2c_wc_session_val()
		);

		wp_localize_script('b2b-b2c-script', 'checkout_obj', $checkout_obj);
	}
}


function b2b_b2c_customise_checkout_field() {

	$b2b_b2c_checkout = get_b2b_b2c_wc_session_val();

	if( $b2b_b2c_checkout == 'true') {
		$corporate = 'active ';
	} else {
		$privat = 'active ';
	}

	echo 
	'<div id="b2b-b2c-checkout-tab">
		<ul class="nav nav-tabs">
	  		<li class="'. $privat .'privat"><a href="#">'. __('Privat') .'</a></li>
			<li class="'. $corporate .'corporate"><a href="#">'. __('Företag') .'</a></li>
		</ul>
	</div>';
}


function b2b_b2c_checkout_field_process() {

	if(get_b2b_b2c_wc_session_val() == 'true') {
		if( !preg_match("/^[0-9]{6}[-][0-9]{4}$/", $_POST['b2b-b2c-organisation']) ) {
			
			$message = __("<strong>Organisationnummer</strong> måste fyllas i.");
			wc_add_notice( __( $message ), 'error' );
		}
	} else {
		$message = __("Felaktig betalningsmetod.");
		wc_add_notice( __( $message ), 'error' );
	}
}


function b2b_b2c_checkout_field_update_order_meta( $order_id ) {
    if ( ! empty( $_POST['b2b-b2c-organisation'] ) ) {
        add_post_meta( $order_id, '_b2b-b2c-organisation', $_POST['b2b-b2c-organisation'] );
        WC()->session->set('b2b_b2c_order_id', $order_id);
    }
}


add_action('wp_ajax_b2b_b2c_set_gateway','b2b_b2c_set_gateways');
add_action('wp_ajax_nopriv_b2b_b2c_set_gateway','b2b_b2c_set_gateways');

function b2b_b2c_set_gateways() {

	WC()->session->set('b2b_b2c_checkout', $_POST['b2b_b2c_checkout']);
	exit;
}

function b2b_b2c_filter_gateways($gateways) {

    $b2b_b2c_checkout = get_b2b_b2c_wc_session_val();



    if( $b2b_b2c_checkout == 'true' ) {

    	add_filter('woocommerce_cart_needs_payment', '__return_false');
    	return;
    }
    
    return $gateways;
}

function get_b2b_b2c_wc_session_val() {
	return WC()->session->get('b2b_b2c_checkout');
}



function b2b_b2c_after_order_review() {
	echo '<div class="b2b-b2c-free-shipping">Fri Frakt</div>';
}


function b2b_b2c_default_url_to_checkout( $url ) {
	
	return $link_to_checkout = get_home_url()."/checkout/";
}


function b2b_b2c_display_order_data_in_admin( $order ) {

	$organisation_number = get_post_meta( $order->id, '_b2b-b2c-organisation', true );

	if( !empty($organisation_number) ): ?>

		<div class="form-field form-field-wide">
	        <h3><?php _e( 'Organisationnummer' ); ?></h3>
	        <p><strong><?php echo $organisation_number; ?></strong></p>
	    </div>

	<?php endif;
}
add_action( 'woocommerce_admin_order_data_after_order_details', 'b2b_b2c_display_order_data_in_admin' );


function b2b_b2c_checkout_field_order_meta_keys( $keys ) {
    $keys['Organisationnummer'] = '_b2b-b2c-organisation';
    return $keys;
}
