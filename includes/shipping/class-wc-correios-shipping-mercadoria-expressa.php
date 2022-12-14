<?php
/**
 * Correios Mercadoria Expressa shipping method.
 *
 * @package WooCommerce_Correios/Classes/Shipping
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mercadoria Expressa shipping method class.
 */
class WC_Correios_ShippingMercadoriaExpressa extends WC_Correios_ShippingInternational {

	/**
	 * Service code.
	 * 110 - Mercadoria Expressa.
	 *
	 * @var string
	 */
	protected $code = '110';

	/**
	 * Initialize Mercadoria Expressa.
	 *
	 * @param int $instance_id Shipping zone instance.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id           = 'correios-mercadoria-expressa';
		$this->method_title = __( 'Mercadoria Expressa', 'woocommerce-correios' );
		$this->more_link    = '';

		parent::__construct( $instance_id );
	}
}
