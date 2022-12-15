<?php
/**
 * Plugin Name:          BlueX for WooCommerce
 * Plugin URI:           https://bluex.cl/
 * Description:          Add Blue Express shipping methods to your WooCommerce store.
 * Author:               Blue Express
 * Author URI:           https://bluex.cl/
 * Version:              1.0.1
 * License:              GPLv2 or later
 * Text Domain:          woocommerce-bluex
 * Domain Path:          /languages
 * WC requires at least: 3.0
 * WC tested up to:      4.4
 *
 */

defined( 'ABSPATH' ) || exit;

define( 'WC_CORREIOS_VERSION', '3.8.0' );
define( 'WC_CORREIOS_PLUGIN_FILE', __FILE__ );

if ( ! class_exists( 'WC_Correios' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-wc-correios.php';

	add_action( 'plugins_loaded', array( 'WC_Correios', 'init' ) );
}

function wpblog_wc_register_post_statuses() {
	register_post_status( 'wc-shipping-progress', array(
		'label' => _x( 'Listo para enviar', 'WooCommerce Order status', 'text_domain' ),
		'public' => true,
		'exclude_from_search' => false,
		'show_in_admin_all_list' => true,
		'show_in_admin_status_list' => true,
		'label_count' => _n_noop( 'Approved (%s)', 'Approved (%s)', 'text_domain' )
	));
}
add_filter( 'init', 'wpblog_wc_register_post_statuses' );
function wpblog_wc_add_order_statuses( $order_statuses ) {
	$order_statuses['wc-shipping-progress'] = _x( 'Listo para enviar', 'WooCommerce Order status', 'text_domain' );
	return $order_statuses;
}
add_filter( 'wc_order_statuses', 'wpblog_wc_add_order_statuses' );

add_action('save_post','sendBluexData');
function sendBluexData(){
	global $post, $wpdb;

	$idBlueXIntegration	= get_post_meta($post->ID,'id-post-bluex',true);
	if(!$idBlueXIntegration){
		if($post->post_type == 'shop_order' && sanitize_text_field($_POST['order_status']) =='wc-shipping-progress'){
		$destinatario = get_post_meta($post->ID);
		$dadospedido["orderId"]             = $post->ID;
		$dadospedido["storeId"]             = "https://blueexpresslogistica.com/";
		$dadospedido["receiveName"]         = $destinatario['_shipping_first_name'][0].' '.$destinatario['_shipping_last_name'][0];
		$dadospedido["receiveAddress"]      = $destinatario['_shipping_address_1'][0];
		$dadospedido["receiveCity"]         = $destinatario['_shipping_city'][0];
		$dadospedido["receiveState"]        = $destinatario['_shipping_state'][0];
		$dadospedido["receiveComplement"]   = $destinatario['_shipping_address_2'][0];
		$dadospedido["receiveEmail"]        = $destinatario['_billing_email'][0];
		$dadospedido["receiveTelefone"]     = $destinatario['_billing_phone'][0];
		$dadospedido['shippingMethod']      = sanitize_text_field($_POST['shipping_method_title'][$_POST['shipping_method_id'][0]]);
		switch (sanitize_text_field($_POST['shipping_method'][$_POST['shipping_method_id'][0]]) ) {
            case 'bluex-ex':
                $dadospedido['type_carrier'] ='EX';
                break;
            case 'bluex-py':
                $dadospedido['type_carrier'] ='PY';
                break;
            case 'bluex-md':
                $dadospedido['type_carrier'] ='MD';
                break;
            default:
                    $dadospedido['type_carrier'] ='';
                break;
        }
			
		$dadospedido['seller']              = get_option('woocommerce_correios-integration_settings');

		$order = wc_get_order( sanitize_text_field($_POST['ID']));
		foreach ( $order->get_items() as $item_id => $item ) {
			   $produtos[$item_id]['productId'] = $item->get_product_id();
			   $produtos[$item_id]['variationId'] = $item->get_variation_id();
			   $produtos[$item_id]['productName'] = $item->get_name();
			   $produtos[$item_id]['quantity'] = $item->get_quantity();
			   $produtos[$item_id]['total'] = sanitize_text_field($_POST['line_total'][$_POST['order_item_id'][0]]);

			 $product = $item->get_product();
				if($product->get_height() == 0 ){
					$alto = 10;
				}else {
					$alto = $product->get_height();
				}

				if($product->get_width() == 0 ){
					$ancho = 10;
				}else {
					$ancho = $product->get_width();
				}

				if($product->get_length() == 0 ){
					$largo = 10;
				}else {
					$largo = $product->get_length();
				}
			
			 $produtos[$item_id]['height'] = $alto;
			 $produtos[$item_id]['width']  = $ancho;
			 $produtos[$item_id]['length'] = $largo;
			 $produtos[$item_id]['weight'] = $product->get_weight();
		}

		$dadospedido['items'] = $produtos;
		$userData = get_option('woocommerce_correios-integration_settings');

		$comunasGeo = wp_remote_post('https://bx-tracking.bluex.cl/bx-geo/states', array(
			'method'      => 'GET',
			'headers' => array(
				'Content-Type' => 'application/json',
				'BX-CLIENT_ACCOUNT' => $userData['tracking_password'],
				'BX-TOKEN' => $userData['tracking_token'],
				'BX-USERCODE' => $userData['tracking_login']
			)
		)); 

		$bxGeo = json_decode($comunasGeo['body']);
		foreach($bxGeo->data[0]->states as $indice=>$bxData){
			foreach($bxData->ciudades as $indiceC=>$bxDataC){
				if(strtolower($bxDataC->name)==strtolower($destinatario['_shipping_city'][0])){
					$dadospedido['regionCode'] 		= $bxData->code;
					$dadospedido['cidadeName'] 		= $bxDataC->name;
					$dadospedido['cidadeCode'] 		= $bxDataC->code;
					$dadospedido['districtCode']   	= $bxDataC->defaultDistrict;
				}
			}
			if($dadospedido['cidadeName'] == ''){
				foreach($bxData->ciudades as $indiceC=>$bxDataC){
					foreach($bxDataC->districts as $indiceD=>$bxDataD){
						if(strtolower($bxDataD->name)==strtolower($destinatario['_shipping_city'][0])){
							$dadospedido['regionCode'] 		= $bxData->code;
							$dadospedido['cidadeName'] 		= $bxDataC->name;
							$dadospedido['cidadeCode'] 		= $bxDataC->code;
							$dadospedido['districtCode']   	= $bxDataC->defaultDistrict;
						}
					}
				}
			} 
		}

		$data_string = json_encode($dadospedido);

		$returnwebhook = wp_remote_post('https://qaapigw.bluex.cl/api/integrations/woocommerce/v1', array(
			'method'  => 'POST',
			'headers' => array(
				'Content-Type' => 'application/json',
				'apikey: ktngyRaE2LRcBTQHXv8DzHISLsexqchX'
			),
			'body'        => $data_string
		));   

		$dados = json_decode($returnwebhook);
	}
}
}

add_action('rest_api_init', function () {
register_rest_route( 'customapi/v1', '/trackingCode', array(
'methods' => 'PUT',
'callback' => 'updateTrackingCode',
) );
} );
function updateTrackingCode(WP_REST_Request $request) {
$orderId = sanitize_text_field($request['orderId']);
$trackingCode = sanitize_text_field($request['trackingCode']);

if($orderId == '') return false;
if($trackingCode == '') return false;

update_post_meta($orderId,'_correios_tracking_code',$trackingCode);
update_post_meta($orderId,'id-post-bluex',true);
return true;
}


