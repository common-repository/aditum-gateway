<?php
/**
 * Aditum Gateway Payment Pix Class
 * Description: Pix Class
 *
 * @package Aditum/Payments
 */

/**
 * Class Init WooCommerce Gateway
 */
class WC_Aditum_Pix_Pay_Gateway extends WC_Payment_Gateway {

	/**
	 * Whether or not logging is enabled
	 *
	 * @var bool
	 */
	public static $log_enabled = true;

	/**
	 * Logger instance
	 *
	 * @var WC_Logger
	 */
	public static $log = true;

	/**
	 * Merchant Key Credentials
	 *
	 * @var string
	 */
	public $merchant_key = '';

	/**
	 * Merchant CNPJ Credentials
	 *
	 * @var string
	 */
	public $merchant_cnpj = '';

	/**
	 * Ambient Environment
	 *
	 * @var string
	 */
	public $environment = '';

	/**
	 * Ambient Deadline
	 *
	 * @var string
	 */
	public $deadline = 0;


	/**
	 * Ambient Initial Status
	 *
	 * @var string
	 */
	public $initial_status = '';

	/**
	 * Ambient Expiry Date
	 *
	 * @var string
	 */
	public $expiry_date = '';

	/**
	 * Ambient Max Installment
	 *
	 * @var string
	 */
	public $max_installment = '';

	/**
	 * Function Plugin constructor
	 */
	public function __construct() {
		$this->id = 'aditum_pix';
		// $this->icon               = apply_filters( 'woocommerce_aditum_pix_icon', plugins_url() . '/../plugins/aditum-boleto-gateway/assets/icon.png' );
		$this->has_fields         = true;
		$this->method_title       = __( 'Aditum Pix', 'wc-aditum-pix' );
		$this->method_description = __( 'Aditum Pagamento por Pix', 'wc-aditum-pix' );

		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->instructions = $this->get_option(
			'instructions',
			$this->description
		);

		$this->supports = array(
			'products',
		);

		$this->merchant_key   = $this->get_option( 'aditum_pix_merchantKey' );
		$this->merchant_cnpj  = $this->get_option( 'aditum_pix_cnpj' );
		$this->environment    = $this->get_option( 'aditum_pix_environment' );
		$this->debug          = $this->get_option( 'aditum_pix_debug' );
		$this->initial_status = $this->get_option( 'aditum_pix_initial_status' );

		$this->init_form_fields();
		$this->init_settings();

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thank_you_' . $this->id, array( $this, 'thankyou_page' ) );
		
	}

	/**
	 * Init init_form_fields form fields
	 */
	public function init_form_fields() {

		$inputs_address = array();
		$wc_address     = WC()->countries->get_address_fields( null, $type = 'billing_' );
		foreach ( $wc_address as $key => $address ) {
			$inputs_address[ $key ] = $key;
		}
		$this->form_fields = apply_filters(
			'woo_aditum_pix_pay_fields',
			array(
				'enabled'                    => array(
					'title'   => __( 'Habilitar/Desabilitar:', 'wc-aditum_pix' ),
					'type'    => 'checkbox',
					'label'   => __( 'Habilitar ou desabilitar o Módulo de Pagamento', 'wc-aditum_pix' ),
					'default' => 'no',
				),
				'aditum_pix_debug'           => array(
					'title'   => __( 'Habilitar debug:', 'wc-aditum_pix' ),
					'type'    => 'checkbox',
					'label'   => __( 'Habilitar ou desabilitar debug', 'wc-aditum_pix' ),
					'default' => 'no',
				),
				'aditum_pix_environment'    => array(
					'title'   => __( 'Ambiente do Gateway:', 'wc-aditum_pix' ),
					'type'    => 'select',
					'options' => array(
						'production' => __( 'Produção', 'wc-aditum_pix' ),
						'sandbox'    => __( 'Sandbox', 'wc-aditum_pix' ),
					),
				),
				'title'                      => array(
					'title'       => __( 'Título do Gateway:', 'wc-aditum_pix' ),
					'type'        => 'text',
					'description' => __( 'Adicione um novo título ao aditum, os clientes vão visualizar ese título no checkout.', 'wc-aditum_pix' ),
					'default'     => __( 'Aditum Gateway - Pix', 'wc-aditum_pix' ),
					'desc_tip'    => true,
				),
				'description'                => array(
					'title'       => __( 'Descrição do Gateway:', 'wc-aditum_pix' ),
					'type'        => 'textarea',
					'description' => __( 'Adicione uma nova descrição para o aditum.', 'wc-aditum_pix' ),
					'default'     => __( 'Pague com total segurança através do Pix.', 'wc-aditum_pix' ),
					'desc_tip'    => true,
				),
				'instructions'               => array(
					'title'       => __( 'Instruções Após o Pedido:', 'wc-aditum_pix' ),
					'type'        => 'textarea',
					'description' => __( 'As instruções iram aparecer na página de Obrigado & Email após o pedido ser feito.', 'wc-aditum_pix' ),
					'desc_tip'    => true,
				),
				'aditum_pix_initial_status' => array(
					'title'       => __( 'Status do Pedido criado:', 'wc-aditum_pix' ),
					'type'        => 'select',
					'options'     => wc_get_order_statuses(),
					'description' => __( 'Status do pedido criado.', 'wc-aditum_pix' ),
					'desc_tip'    => true,
					'default'     => 'wc-pending'
				),
				'aditum_pix_cnpj'           => array(
					'title'       => __( 'CNPJ:', 'wc-aditum_pix' ),
					'type'        => 'text',
					'description' => __( 'Insira o CNPJ cadastrado no Aditum.', 'wc-aditum_pix' ),
					'desc_tip'    => true,
				),
				'aditum_pix_merchantKey'    => array(
					'title'       => __( 'Merchant Token:', 'wc-aditum_pix' ),
					'type'        => 'text',
					'description' => __( 'Insira o Merchant Key cadastrado no Aditum.', 'wc-aditum_pix' ),
					'desc_tip'    => true,
				),
				'def_endereco_rua'           => array(
					'title'   => __( 'Definições do Endereço - Rua:', 'wc-aditum_pix' ),
					'type'    => 'select',
					'options' => $inputs_address,
					'default' => 'billing_address_1'
				),
				'def_endereco_numero'        => array(
					'title'   => __( 'Definições do Endereço - Número:', 'wc-aditum_pix' ),
					'type'    => 'select',
					'options' => $inputs_address,
					'default' => 'billing_number'
				),
				'def_endereco_comp'          => array(
					'title'   => __( 'Definições do Endereço - Complemento:', 'wc-aditum_pix' ),
					'type'    => 'select',
					'options' => $inputs_address,
					'default' => 'billing_address_2'
				),
				'def_endereco_bairro'        => array(
					'title'   => __( 'Definições do Endereço - Bairro:', 'wc-aditum_pix' ),
					'type'    => 'select',
					'options' => $inputs_address,
					'default' => 'billing_neighborhood'
				)
			
			)
		);
	}
	

	/**
	 * Logging method.
	 *
	 * @param string $message Log message.
	 * @param string $level Optional. Default 'info'. Possible values:
	 *                      emergency|alert|critical|error|warning|notice|info|debug.
	 */
	public static function log( $message, $level = 'info' ) {
		if ( self::$log_enabled ) {
			if ( empty( self::$log ) ) {
				self::$log = wc_get_logger();
			}
			self::$log->log( $level, $message, array( 'source' => 'wc-aditum-pix' ) );
		}
	}

	public function validateInputs( $data ) {

		$keys = array(
		);

		foreach ( $data as $key => $input ) {
			if ( in_array( $key, $keys ) ) {
				if ( empty( $data[ $key ] ) ) {
					return false;
				}
			}
		}

		return true;

	}
	/**
	 * Process_payment method.
	 *
	 * @param int $order_id Id of order.
	 */
	public function process_payment( $order_id ) {

		global $woocommerce;
		$order = new WC_Order( $order_id );

		$address_1    = $order->get_meta( '_billing_address_1' );
        $address_2    = $order->get_meta( '_billing_address_2' );
        $address_number =  $order->get_meta( '_billing_number' );
        $address_neightboorhood = $order->get_meta( '_billing_neighborhood' );

		AditumPayments\ApiSDK\Configuration::initialize();
		if ( 'sandbox' === $this->environment ) {
			AditumPayments\ApiSDK\Configuration::setUrl( AditumPayments\ApiSDK\Configuration::DEV_URL );
		}
		// wp_send_json([$this->merchant_cnpj, $this->merchant_key]);
		$merchant_numeric_cnpj = preg_replace('/[^0-9]/', '', $this->merchant_cnpj);
        AditumPayments\ApiSDK\Configuration::setCnpj($merchant_numeric_cnpj);
		AditumPayments\ApiSDK\Configuration::setMerchantToken( $this->merchant_key );
		AditumPayments\ApiSDK\Configuration::setlog( false );
		AditumPayments\ApiSDK\Configuration::login();

		if ( ! $this->validateInputs( $_POST ) ) {
			return wc_add_notice( 'Preencha todos os campos.', 'error' );
		}

		$gateway       = new AditumPayments\ApiSDK\Gateway();
		$pix = new AditumPayments\ApiSDK\Domains\Pix;

		foreach($order->get_items() as $item) {
			$product = new WC_Product($item->get_product_id());
			$total_product_amount = preg_replace( '/[^0-9]/', '', number_format($item->get_subtotal(),2));
			$product_amount = round($total_product_amount / $item->get_quantity(), 0);
			$pix->products->add(
				// name
				$item->get_name(),  
				// sku
				$product->get_sku(), 
				// price
				$product_amount,
				// quantity
				$item->get_quantity()
			);
		}

		$phone = preg_replace( '/[^\d]+/', '', $order->get_billing_phone() );

		$customer_phone_area_code = substr( $phone, 0, 2 );
		$customer_phone           = substr( $phone, 2 );
		$amount                   = str_replace( '.', '', $order->get_total() );

		$pix->setMerchantChargeId($order_id);

		// ! Customer
		$pix->customer->setName( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
		$pix->customer->setEmail( $order->get_billing_email() );
		$pix->customer->setId( "$order_id" );

		if (!empty($order->get_meta('_billing_cpf'))) {
            $pix->customer->setDocumentType(AditumPayments\ApiSDK\Enum\DocumentType::CPF);
            $documento = preg_replace('/[^\d]+/', '', $order->get_meta('_billing_cpf'));
        } else if (!empty($order->get_meta('_billing_cnpj'))) {
            $pix->customer->setDocumentType(AditumPayments\ApiSDK\Enum\DocumentType::CNPJ);
            $documento = preg_replace('/[^\d]+/', '', $order->get_meta('_billing_cnpj'));
        }
		
		$pix->customer->setDocument( $documento );

		// ! Customer->address
		$pix->customer->address->setStreet( $address_1 );
		$pix->customer->address->setNumber( $address_number  );
		$pix->customer->address->setNeighborhood( $address_neightboorhood );
		$pix->customer->address->setCity( $order->get_billing_city() );
		$pix->customer->address->setState( $order->get_billing_state() );
		$pix->customer->address->setCountry( $order->get_billing_country() );
		$pix->customer->address->setZipcode( str_replace( '-', '', $order->get_billing_postcode() ) );
		$pix->customer->address->setComplement( $address_2 );

		// ! Customer->phone
		$pix->customer->phone->setCountryCode( '55' );
		$pix->customer->phone->setAreaCode( $customer_phone_area_code );
		$pix->customer->phone->setNumber( $customer_phone );
		$pix->customer->phone->setType( AditumPayments\ApiSDK\Enum\PhoneType::MOBILE );

		// ! Transactions
		$pix->transactions->setAmount( $amount );

		$res = $gateway->charge( $pix );

		wc_get_logger()->info( wc_print_r( $res, true ), array( 'source' => 'aditum-pix-orders' ) );

		//Compress QrCode
		$qrCodeImg = base64_decode($res['charge']->transactions[0]->qrCodeBase64);

		ob_start();
		imagejpeg (imagecreatefromstring($qrCodeImg));
		$compressedQrCodeImg = ob_get_contents();
		ob_end_clean();

		if ( isset( $res['status'] ) ) {

			$order->update_meta_data(
				'_params_aditum',
				array(
					'order_id'                     => $order_id,
					'chargeId'                => $res['charge']->id,
					'chargeStatus'            => $res['charge']->chargeStatus,
					'qrCode'          => $res['charge']->transactions[0]->qrCode,
					'qrCodeBase64'          => base64_encode($compressedQrCodeImg),
					'transaction_id'          => $res['charge']->transactions[0]->transactionId,
					'transaction_amount'      => $res['charge']->transactions[0]->amount,
					'transaction_transactionStatus' => $res['charge']->transactions[0]->transactionStatus,
				)
			);

			$order->save();

			wc_get_logger()->info( wc_print_r( $order->get_meta( '_params_aditum' ), true ), array( 'source' => 'aditum-pix-orders' ) );

			if ( AditumPayments\ApiSDK\Enum\ChargeStatus::NOT_AUTHORIZED === $res['status'] ) {
				if($this->debug == '1') {
					return wc_add_notice( json_encode($res) , 'error' );
				}
				return wc_add_notice( 'Transação não autorizada.', 'error' );
			}
			else if ( AditumPayments\ApiSDK\Enum\ChargeStatus::AUTHORIZED === $res['status'] ) {

					$order->update_status( 'processing', __( 'Pagamento Concluído', 'wc-aditum-pix' ) );

					$woocommerce->cart->empty_cart();

					return array(
						'result'   => 'success',
						'redirect' => $this->get_return_url( $order ),
					);

			} else {
				if($res['charge']->chargeStatus === "Denied")
				{
					return wc_add_notice( $res['charge']->transactions[0]->errorMessage, 'error' );
				}
				else if($res['charge']->chargeStatus === "PreAuthorized"){

					$order->update_status( 'pending', __( 'Aguardando autorização do pagamento', 'wc-aditum-pix' ) );
					
					return array(
						'result'   => 'success',
						'redirect' => $this->get_return_url( $order ),
					);

				}else{
					if($this->debug == '1') {
						return wc_add_notice( json_encode($res) , 'error' );
					}
					return wc_add_notice( $res['charge']->transactions[0]->errorMessage, 'error' );
				}
			}
		} else {
			if ( null !== $res ) {
				if($this->debug == '1') {
					return wc_add_notice( json_encode($res) , 'error' );
				}

				if($res['httpMsg'] === ''){
					return wc_add_notice( 'Verifique as credenciais de acesso ao Aditum e tente novamente. <br/>' . json_encode($res), 'error' );
				}

				$messages = json_decode($res['httpMsg'], true);
				$erros = '';
				foreach($messages['errors'] as $errors){
					$erros .= $errors['message'].'<br>';
				}
				return wc_add_notice( $erros , 'error' );
			}
		}
	}

	/**
	 * Thankyou_page method.
	 */
	public function thankyou_page() {
		if ( $this->instructions ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
		}
	}
}
