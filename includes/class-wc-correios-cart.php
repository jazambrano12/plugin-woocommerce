<?php
/**
 * WooCommerce cart integration
 *
 * @package WooCommerce_Correios/Classes/Cart
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cart integration.
 */
class WC_Correios_Cart {

	/**
	 * Init cart actions.
	 */
	public function __construct() {
		add_action( 'woocommerce_after_shipping_rate', array( $this, 'shipping_delivery_forecast' ), 100 );
	}

	/**
	 * Adds delivery forecast after method name.
	 *
	 * @param WC_Shipping_Rate $shipping_method Shipping method data.
	 */
	public function shipping_delivery_forecast( $shipping_method ) {
		$meta_data = $shipping_method->get_meta_data();
		$total     = isset( $meta_data['_delivery_forecast'] ) ? intval( $meta_data['_delivery_forecast'] ) : 0;

		if ( $total ) {
			/* translators: %d: days to delivery */
			echo '<p><small>' . esc_html( sprintf( _n( 'Entrega en %d dia(s)', 'Entrega en %d dia(s)', $total, 'woocommerce-correios' ), $total ) ) . '</small></p>';
		}
	}
}

new WC_Correios_Cart();
