<?php
/**
 * Correios Webservice.
 *
 * @package WooCommerce_Correios/Classes/Webservice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Correios Webservice integration class.
 */
class WC_Correios_Webservice {
 

	/**
	 * Shipping method ID.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Shipping zone instance ID.
	 *
	 * @var int
	 */
	protected $instance_id = 0;

	/**
	 * ID from Correios service.
	 *
	 * @var string|array
	 */
	protected $service = '';

	/**
	 * WooCommerce package containing the products.
	 *
	 * @var array
	 */
	protected $package = null;

	/**
	 * Origin postcode.
	 *
	 * @var string
	 */
	protected $origin_postcode = '';

	/**
	 * Destination postcode.
	 *
	 * @var string
	 */
	protected $destination_postcode = '';

	/**
	 * Login.
	 *
	 * @var string
	 */
	protected $login = '';

	/**
	 * Password.
	 *
	 * @var string
	 */
	protected $password = '';

	/**
	 * Package height.
	 *
	 * @var float
	 */
	protected $height = 0;

	/**
	 * Package width.
	 *
	 * @var float
	 */
	protected $width = 0;

	/**
	 * Package diameter.
	 *
	 * @var float
	 */
	protected $diameter = 0;

	/**
	 * Package length.
	 *
	 * @var float
	 */
	protected $length = 0;

	/**
	 * Package weight.
	 *
	 * @var float
	 */
	protected $weight = 0;

	/**
	 * Minimum height.
	 *
	 * @var float
	 */
	protected $minimum_height = 0.1;

	/**
	 * Minimum width.
	 *
	 * @var float
	 */
	protected $minimum_width = 0.1;

	/**
	 * Minimum length.
	 *
	 * @var float
	 */
	protected $minimum_length = 0.1;

	/**
	 * Extra weight.
	 *
	 * @var float
	 */
	protected $extra_weight = 0;

	/**
	 * Declared value.
	 *
	 * @var string
	 */
	protected $declared_value = '0';

	/**
	 * Own hands.
	 *
	 * @var string
	 */
	protected $own_hands = 'N';

	/**
	 * Receipt notice.
	 *
	 * @var string
	 */
	protected $receipt_notice = 'N';

	/**
	 * Package format.
	 *
	 * 1 – box/package
	 * 2 – roll/prism
	 * 3 - envelope
	 *
	 * @var string
	 */
	protected $format = '1';

	/**
	 * Debug mode.
	 *
	 * @var string
	 */
	protected $debug = 'no';

	/**
	 * Logger.
	 *
	 * @var WC_Logger
	 */
	protected $log = null;

	/**
	 * Initialize webservice.
	 *
	 * @param string $id Method ID.
	 * @param int    $instance_id Instance ID.
	 */
	public function __construct( $id = 'correios', $instance_id = 0 ) {
		$this->id           = $id;
		$this->instance_id  = $instance_id;
		$this->log          = new WC_Logger();
	}

	/**
	 * Set the service
	 *
	 * @param string|array $service Service.
	 */
	public function set_service( $service = '' ) {
		if ( is_array( $service ) ) {
			$this->service = implode( ',', $service );
		} else {
			$this->service = $service;
		}
	}

	/**
	 * Set shipping package.
	 *
	 * @param array $package Shipping package.
	 */
	public function set_package( $package = array() ) {
		$this->package = $package;
		$correios_package = new WC_Correios_Package( $package );

		if ( ! is_null( $correios_package ) ) {
			$data = $correios_package->get_data();

			$this->set_height( $data['height'] );
			$this->set_width( $data['width'] );
			$this->set_length( $data['length'] );
			$this->set_weight( $data['weight'] );
		}

		if ( 'yes' === $this->debug ) {
			if ( ! empty( $data ) ) {
				$data = array(
					'weight' => $this->get_weight(),
					'height' => $this->get_height(),
					'width'  => $this->get_width(),
					'length' => $this->get_length(),
				);
			}

			$this->log->add( $this->id, 'Weight and cubage of the order: ' . print_r( $data, true ) );
		}
	}

	/**
	 * Set origin postcode.
	 *
	 * @param string $postcode Origin postcode.
	 */
	public function set_origin_postcode( $postcode = '' ) {
		$this->origin_postcode = $postcode;
	}

	/**
	 * Set destination postcode.
	 *
	 * @param string $postcode Destination postcode.
	 */
	public function set_destination_postcode( $postcode = '' ) {
		$this->destination_postcode = $postcode;
	}

	/**
	 * Set login.
	 *
	 * @param string $login User login.
	 */
	public function set_login( $login = '' ) {
		$this->login = $login;
	}

	/**
	 * Set password.
	 *
	 * @param string $password User login.
	 */
	public function set_password( $password = '' ) {
		$this->password = $password;
	}

	/**
	 * Set shipping package height.
	 *
	 * @param float $height Package height.
	 */
	public function set_height( $height = 0 ) {
		$this->height = (float) $height;
	}

	/**
	 * Set shipping package width.
	 *
	 * @param float $width Package width.
	 */
	public function set_width( $width = 0 ) {
		$this->width = (float) $width;
	}

	/**
	 * Set shipping package diameter.
	 *
	 * @param float $diameter Package diameter.
	 */
	public function set_diameter( $diameter = 0 ) {
		$this->diameter = (float) $diameter;
	}

	/**
	 * Set shipping package length.
	 *
	 * @param float $length Package length.
	 */
	public function set_length( $length = 0 ) {
		$this->length = (float) $length;
	}

	/**
	 * Set shipping package weight.
	 *
	 * @param float $weight Package weight.
	 */
	public function set_weight( $weight = 0 ) {
		$this->weight = (float) $weight;
	}

	/**
	 * Set minimum height.
	 *
	 * @param float $minimum_height Package minimum height.
	 */
	public function set_minimum_height( $minimum_height = 1 ) {
		$this->minimum_height = 1 <= $minimum_height ? $minimum_height : 1;
	}

	/**
	 * Set minimum width.
	 *
	 * @param float $minimum_width Package minimum width.
	 */
	public function set_minimum_width( $minimum_width = 1 ) {
		$this->minimum_width = 1 <= $minimum_width ? $minimum_width : 1;
	}

	/**
	 * Set minimum length.
	 *
	 * @param float $minimum_length Package minimum length.
	 */
	public function set_minimum_length( $minimum_length = 1 ) {
		$this->minimum_length = 1 <= $minimum_length ? $minimum_length : 1;
	}

	/**
	 * Set extra weight.
	 *
	 * @param float $extra_weight Package extra weight.
	 */
	public function set_extra_weight( $extra_weight = 0 ) {
		$this->extra_weight = (float) wc_format_decimal( $extra_weight );
	}

	/**
	 * Set declared value.
	 *
	 * @param string $declared_value Declared value.
	 */
	public function set_declared_value( $declared_value = '0' ) {
		$this->declared_value = $declared_value;
	}

	/**
	 * Set own hands.
	 *
	 * @param string $own_hands Use 'N' for no and 'S' for yes.
	 */
	public function set_own_hands( $own_hands = 'N' ) {
		$this->own_hands = $own_hands;
	}
	public function set_receipt_notice( $receipt_notice = 'N' ) {
		$this->receipt_notice = $receipt_notice;
	}
	public function set_format( $format = '1' ) {
		$this->format = $format;
	}
	public function set_debug( $debug = 'no' ) {
		$this->debug = $debug;
	}
	public function get_origin_postcode() {
		return apply_filters( 'woocommerce_correios_origin_postcode', $this->origin_postcode, $this->id, $this->instance_id, $this->package );
	}
	public function get_height() {
		return $this->float_to_string( $this->minimum_height <= $this->height ? $this->height : $this->minimum_height );
	}
	public function get_width() {
		return $this->float_to_string( $this->minimum_width <= $this->width ? $this->width : $this->minimum_width );
	}
	public function get_diameter() {
		return $this->float_to_string( $this->diameter );
	}
	public function get_length() {
		return $this->float_to_string( $this->minimum_length <= $this->length ? $this->length : $this->minimum_length );
	}
	public function get_weight() {
		return $this->float_to_string( $this->weight + $this->extra_weight );
	}
	protected function float_to_string( $value ) {
		$value = str_replace( '.', ',', $value );
		return $value;
	}
	public function get_shipping() { 
		$shipping = null;

		$result = json_decode('{"cServico":{"Codigo":"EX","Valor":"0,00","PrazoEntrega":"0","ValorSemAdicionais":"0,00","ValorMaoPropria":"0,00","ValorAvisoRecebimento":"0,00","ValorValorDeclarado":"0,00","EntregaDomiciliar":{},"EntregaSabado":{},"obsFim":{},"Erro":"-888","MsgErro":"Erro ao calcular tarifa. Tente novamente mais tarde. Servidores indispon\u00edveis."}}');

		$shipping = $result->cServico;
		$bultos = array();
		
		foreach($this->package['contents'] as $indice=>$itens){
			$itensOrder = $itens['quantity'];  
			
			if($itens['data']->get_width() == 0 ){
				$ancho = 10;
			}else {
				$ancho = $itens['data']->get_width();
			}
			
			if($itens['data']->get_length() == 0 ){
				$largo = 10;
			}else {
				$largo = $itens['data']->get_length();
			}
			
			if($itens['data']->get_height() == 0 ){
				$alto = 10;
			}else {
				$alto = $itens['data']->get_height();
			}
			
			$bultos[] = [ 
				"ancho"=> $ancho,
				"largo"=> $largo,
				"alto"=> $alto,
				"pesoFisico"=> $itens['quantity'],
				"cantidad"=>$itens['quantity']
			];
		} 
		
		$userData = get_option('woocommerce_correios-integration_settings');

		//Busco la comuna seleccionada por el cliente 
		$comunasGeo = wp_remote_get('https://bx-tracking.bluex.cl/bx-geo/states', array( 
			'headers' => array(
				'Content-Type' 		=> 'application/json',
				'BX-CLIENT_ACCOUNT' => $userData['tracking_password'],
				'BX-TOKEN' 			=> $userData['tracking_token'],
				'BX-USERCODE' 		=> $userData['tracking_login']
			)
		));  
        $bxGeo = json_decode($comunasGeo['body']);  
		
		$cadena = str_replace(
            array('Á', 'À', 'Â', 'Ä', 'á', 'à', 'ä', 'â', 'ª','É', 'È', 'Ê', 'Ë', 'é', 'è', 'ë', 'ê','Í', 'Ì', 'Ï', 'Î', 'í', 'ì', 'ï', 'î','Ó', 'Ò', 'Ö', 'Ô', 'ó', 'ò', 'ö', 'ô','Ú', 'Ù', 'Û', 'Ü', 'ú', 'ù', 'ü', 'û','Ñ', 'ñ', 'Ç', 'ç'),
            array('A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a','E', 'E', 'E', 'E', 'e', 'e', 'e', 'e','I', 'I', 'I', 'I', 'i', 'i', 'i', 'i','O', 'O', 'O', 'O', 'o', 'o', 'o', 'o','U', 'U', 'U', 'U', 'u', 'u', 'u', 'u','N', 'n', 'C', 'c'),
            $this->package['destination']['city'] 
        );

		
		foreach($bxGeo->data[0]->states as $indice=>$bxData){ 
				foreach($bxData->ciudades as $indiceC=>$bxDataC){
					if(strtolower($bxDataC->name)==strtolower($cadena)){
						$dadosGeo['regionCode'] 	= $bxData->code;
						$dadosGeo['cidadeName'] 	= $bxDataC->name;
						$dadosGeo['cidadeCode'] 	= $bxDataC->code;
						$dadosGeo['districtCode']   = $bxDataC->defaultDistrict;
					}
				}
				if($dadosGeo['cidadeName'] == ''){
					foreach($bxData->ciudades as $indiceC=>$bxDataC){
						foreach($bxDataC->districts as $indiceD=>$bxDataD){
							if(strtolower($bxDataD->name)==strtolower($cadena)){
								$dadosGeo['regionCode'] 	= $bxData->code;
								$dadosGeo['cidadeName'] 	= $bxDataC->name;
								$dadosGeo['cidadeCode'] 	= $bxDataC->code;
								$dadosGeo['districtCode']   = $bxDataC->defaultDistrict;
							}
						}
					}
				} 
		}

		//Consulto el precio para la comuna seleccionada 
		
		$postPrice = wp_remote_post('https://apigw.bluex.cl/api/legacy/pricing/v1', array(
			'method'  => 'POST',
			'headers' => array(
				'Content-Type' => 'application/json',
				'apikey' => $userData['tracking_bxkey'],
				'BX-TOKEN' => $userData['tracking_token']
			),
			'body'        => '{
				"from": {
					"country": "CL",
					"district": "'.$userData['districtCode'].'"
				},
				"to": {
					"country": "CL",
					"state": '.$dadosGeo['regionCode'].',
					"district": "'.$dadosGeo['districtCode'].'"
				},
				"serviceType": "'.$this->service.'",
				"datosProducto": {
					"producto": "P",
					"familiaProducto": "PAQU",
					"bultos": '.json_encode($bultos).'
				  }
			}'
		));  
  
		$response = json_decode($postPrice['body']);  

		$shipping->Codigo = $this->service;
		$shipping->Valor = (int) $response->data->flete;
		$shipping->PrazoEntrega = str_replace('-','/',$response->data->fechaEstimadaEntrega);
		$shipping->Erro = 0;
		$shipping->MsgErro = '';


		unset($shipping->EntregaDomiciliar);
		unset($shipping->EntregaSabado);
		unset($shipping->obsFim);
		unset($shipping->ValorSemAdicionais);
		unset($shipping->ValorMaoPropria);
		unset($shipping->ValorAvisoRecebimento);
		unset($shipping->ValorValorDeclarado);
		return $shipping;
	}
}