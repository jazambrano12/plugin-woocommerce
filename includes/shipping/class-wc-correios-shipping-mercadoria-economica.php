<?php
/**
 * Correios Mercadoria Econômica shipping method.
 *
 * @package WooCommerce_Correios/Classes/Shipping
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mercadoria Econômica shipping method class.
 */
class WC_Correios_ShippingMercadoriaEconomica extends WC_Correios_ShippingInternational {

	/**
	 * Service code.
	 * 128 - Mercadoria Econômica.
	 *
	 * @var string
	 */
	protected $code = '128';

	/**
	 * Initialize Mercadoria Econômica.
	 *
	 * @param int $instance_id Shipping zone instance.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id           = 'correios-mercadoria-economica';
		$this->method_title = __( 'Mercadoria Econ&ocirc;mica', 'woocommerce-correios' );
		$this->more_link    = '';

		parent::__construct( $instance_id );
	}
}
