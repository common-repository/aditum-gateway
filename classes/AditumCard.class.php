<?php
/**
 * Aditum Gateway Payment Card Class
 * Description: Card Class
 *
 * @package Aditum/Payments
 */

/**
 * Class Init WooCommerce Gateway
 */
class WC_Aditum_Card_Pay_Gateway extends WC_Payment_Gateway {

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
		$this->id = 'aditum_card';
		// $this->icon               = apply_filters( 'woocommerce_aditum_card_icon', plugins_url() . '/../plugins/aditum-boleto-gateway/assets/icon.png' );
		$this->has_fields         = true;
		$this->method_title       = __( 'Aditum Cartão de Crédito', 'wc-aditum-card' );
		$this->method_description = __( 'Aditum Pagamento por Cartão de Crédito', 'wc-aditum-card' );

		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->instructions = $this->get_option(
			'instructions',
			$this->description
		);

		$this->supports = array(
			'products',
		);

		$this->merchant_key   = $this->get_option( 'aditum_card_merchantKey' );
		$this->merchant_cnpj  = $this->get_option( 'aditum_card_cnpj' );
		$this->debug          = $this->get_option( 'aditum_card_debug' );
		$this->environment    = $this->get_option( 'aditum_card_environment' );
		$this->initial_status = $this->get_option( 'aditum_card_initial_status' );

		$this->expiry_date = $this->get_option( 'aditum_card_order_expiry' );

		$this->max_installments = $this->get_option( 'aditum_card_max_installments' );
		$this->min_installments_amount = $this->get_option( 'aditum_card_min_installment_amount' );
		$this->installment_type = $this->get_option( 'aditum_card_installment_type' );

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
			'woo_aditum_card_pay_fields',
			array(
				'enabled'                    => array(
					'title'   => __( 'Habilitar/Desabilitar:', 'wc-aditum_card' ),
					'type'    => 'checkbox',
					'label'   => __( 'Habilitar ou desabilitar o Módulo de Pagamento', 'wc-aditum_card' ),
					'default' => 'no',
				),
				'aditum_card_debug'          => array(
					'title'   => __( 'Habilitar debug:', 'wc-aditum_card' ),
					'type'    => 'checkbox',
					'label'   => __( 'Habilitar ou desabilitar o debug', 'wc-aditum_card' ),
					'default' => 'no',
				),
				'aditum_card_environment'    => array(
					'title'   => __( 'Ambiente do Gateway:', 'wc-aditum_card' ),
					'type'    => 'select',
					'options' => array(
						'production' => __( 'Produção', 'wc-aditum_card' ),
						'sandbox'    => __( 'Sandbox', 'wc-aditum_card' ),
					),
				),
				'title'                      => array(
					'title'       => __( 'Título do Gateway:', 'wc-aditum_card' ),
					'type'        => 'text',
					'description' => __( 'Adicione um novo título ao aditum, os clientes vão visualizar ese título no checkout.', 'wc-aditum_card' ),
					'default'     => __( 'Aditum Gateway - Cartão de Crédito', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'description'                => array(
					'title'       => __( 'Descrição do Gateway:', 'wc-aditum_card' ),
					'type'        => 'textarea',
					'description' => __( 'Adicione uma nova descrição para o aditum.', 'wc-aditum_card' ),
					'default'     => __( 'Pague com total segurança através do seu cartão de crédito.', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'instructions'               => array(
					'title'       => __( 'Instruções Após o Pedido:', 'wc-aditum_card' ),
					'type'        => 'textarea',
					'description' => __( 'As instruções iram aparecer na página de Obrigado & Email após o pedido ser feito.', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'aditum_card_min_installment_amount' => array(
					'title'       => __( 'Valor mínimo da parcela:', 'wc-aditum_card' ),
					'type'        => 'text',
					'description' => __( 'Valor mínimo da parcela.', 'wc-aditum_card' ),
					'default'     => __( '5', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'aditum_card_max_installments' => array(
					'title'       => __( 'Número máximo de parcelas:', 'wc-aditum_card' ),
					'type'        => 'text',
					'description' => __( 'Número máximo de parcelas.', 'wc-aditum_card' ),
					'default'     => __( '2', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'aditum_card_order_expiry' => array(
					'title'       => __( 'Tempo de expiração do Pedido:', 'wc-aditum_card' ),
					'type'        => 'number',
					'description' => __( 'Depois de quanto tempo o pedido pendente de pagamento deve ser cancelado, define em dias.', 'wc-aditum_card' ),
					'default'     => __( '0', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'aditum_card_initial_status' => array(
					'title'       => __( 'Status do Pedido criado:', 'wc-aditum_card' ),
					'type'        => 'select',
					'options'     => wc_get_order_statuses(),
					'description' => __( 'Status do pedido criado.', 'wc-aditum_card' ),
					'desc_tip'    => true,
					'default'     => 'wc-pending'
				),
				'aditum_card_cnpj'           => array(
					'title'       => __( 'CNPJ:', 'wc-aditum_card' ),
					'type'        => 'text',
					'description' => __( 'Insira o CNPJ cadastrado no Aditum.', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'aditum_card_merchantKey'    => array(
					'title'       => __( 'Merchant Token:', 'wc-aditum_card' ),
					'type'        => 'text',
					'description' => __( 'Insira o Merchant Key cadastrado no Aditum.', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'def_endereco_rua'           => array(
					'title'   => __( 'Definições do Endereço - Rua:', 'wc-aditum_card' ),
					'type'    => 'select',
					'options' => $inputs_address,
					'default' => 'billing_address_1'
				),
				'def_endereco_numero'        => array(
					'title'   => __( 'Definições do Endereço - Número:', 'wc-aditum_card' ),
					'type'    => 'select',
					'options' => $inputs_address,
					'default' => 'billing_number'
				),
				'def_endereco_comp'          => array(
					'title'   => __( 'Definições do Endereço - Complemento:', 'wc-aditum_card' ),
					'type'    => 'select',
					'options' => $inputs_address,
					'default' => 'billing_address_2'
				),
				'def_endereco_bairro'        => array(
					'title'   => __( 'Definições do Endereço - Bairro:', 'wc-aditum_card' ),
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
			self::$log->log( $level, $message, array( 'source' => 'wc-aditum-card' ) );
		}
	}

	public function validateInputs( $data ) {

		$keys = array(
			'card_holder_name',
			'card_holder_document',
			'aditum_card_number',
			'aditum_card_cvv',
			'aditum_card_expiration_month',
			'aditum_card_year_month',
			'aditum_checkbox',
			'aditum_card_installments'
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
			return wc_add_notice( 'Preencha todos os campos do cartão de crédito.', 'error' );
		}

		$antifraud_token = sanitize_text_field($_POST['antifraud_token']);
		$card_holder_document = sanitize_text_field($_POST['card_holder_document']);
		$aditum_card_installment = sanitize_text_field($_POST['aditum_card_installment']);
		$aditum_card_number = sanitize_text_field($_POST['aditum_card_number']);
		$aditum_card_cvv = sanitize_text_field($_POST['aditum_card_cvv']);
		$card_holder_name = sanitize_text_field($_POST['card_holder_name']);
		$aditum_card_expiration_month = sanitize_text_field($_POST['aditum_card_expiration_month']);
		$aditum_card_year_month = sanitize_text_field($_POST['aditum_card_year_month']);

		$gateway       = new AditumPayments\ApiSDK\Gateway();
		$authorization = new AditumPayments\ApiSDK\Domains\Authorization();

		foreach($order->get_items() as $item) {
			$product = new WC_Product($item->get_product_id());
			$total_product_amount = preg_replace( '/[^0-9]/', '', number_format($item->get_subtotal(),2));
			$product_amount = round($total_product_amount / $item->get_quantity(), 0);
			
			$authorization->products->add(
				// name
				$item->get_name(),
				// skup
				$product->get_sku(),
				//product amount
				$product_amount,
				// quantity
				$item->get_quantity()
			);
		}

		$phone = preg_replace( '/[^\d]+/', '', $order->get_billing_phone() );

		$customer_phone_area_code = substr( $phone, 0, 2 );
		$customer_phone           = substr( $phone, 2 );
		$amount                   = str_replace( '.', '', $order->get_total() );

		$authorization->setMerchantChargeId($order_id);

		$authorization->setSessionId($antifraud_token);

		// ! Customer
		$authorization->customer->setName( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
		$authorization->customer->setEmail( $order->get_billing_email() );
		$authorization->customer->setId( "$order_id" );

		if (!empty($order->get_meta('_billing_cpf'))) {
            $authorization->customer->setDocumentType(AditumPayments\ApiSDK\Enum\DocumentType::CPF);
            $documento = preg_replace('/[^\d]+/', '', $order->get_meta('_billing_cpf'));
        } else if (!empty($order->get_meta('_billing_cnpj'))) {
            $authorization->customer->setDocumentType(AditumPayments\ApiSDK\Enum\DocumentType::CNPJ);
            $documento = preg_replace('/[^\d]+/', '', $order->get_meta('_billing_cnpj'));
        }

		$authorization->customer->setDocument( $documento );

		$cpf_card_holder = preg_replace( '/[^\d]+/', '', $card_holder_document  );

		$authorization->transactions->card->setCardholderDocument($cpf_card_holder);

		// ! Customer->address
		$authorization->customer->address->setStreet( $address_1 );
		$authorization->customer->address->setNumber( $address_number  );
		$authorization->customer->address->setNeighborhood( $address_neightboorhood );
		$authorization->customer->address->setCity( $order->get_billing_city() );
		$authorization->customer->address->setState( $order->get_billing_state() );
		$authorization->customer->address->setCountry( $order->get_billing_country() );
		$authorization->customer->address->setZipcode( str_replace( '-', '', $order->get_billing_postcode() ) );
		$authorization->customer->address->setComplement( $address_2 );

		// ! Customer->phone
		$authorization->customer->phone->setCountryCode( '55' );
		$authorization->customer->phone->setAreaCode( $customer_phone_area_code );
		$authorization->customer->phone->setNumber( $customer_phone );
		$authorization->customer->phone->setType( AditumPayments\ApiSDK\Enum\PhoneType::MOBILE );

		// ! Transactions
		$authorization->transactions->setAmount( $amount );
		$parcelas = $aditum_card_installment ? $aditum_card_installment : 1;

		if(empty($parcelas)) {
			return wc_add_notice( "Selecione as parcelas", 'error' );
		}

		$authorization->transactions->setPaymentType( AditumPayments\ApiSDK\Enum\PaymentType::CREDIT );
		$authorization->transactions->setInstallmentNumber( $parcelas ); // Só pode ser maior que 1 se o tipo de transação for crédito.

		$authorization->transactions->setInstallmentType($parcelas > 1 ? AditumPayments\ApiSDK\Enum\InstallmentType::MERCHANT : AditumPayments\ApiSDK\Enum\InstallmentType::NONE);

		$authorization->transactions->card->setCardNumber( preg_replace( '/[^\d]+/', '', $aditum_card_number ) );
		$authorization->transactions->card->setCVV( $aditum_card_cvv );
		$authorization->transactions->card->setCardholderName( $card_holder_name );
		$authorization->transactions->card->setExpirationMonth( $aditum_card_expiration_month );
		$authorization->transactions->card->setExpirationYear( 20 . $aditum_card_year_month );

		$authorization->transactions->card->billingAddress->setStreet( $address_1 );
		$authorization->transactions->card->billingAddress->setNumber( $address_number );
		$authorization->transactions->card->billingAddress->setNeighborhood($address_neightboorhood );
		$authorization->transactions->card->billingAddress->setCity($order->get_billing_city());
		$authorization->transactions->card->billingAddress->setState($order->get_billing_state());
		$authorization->transactions->card->billingAddress->setCountry($order->get_billing_country());
		$authorization->transactions->card->billingAddress->setZipcode(preg_replace( '/[^\d]+/', '', $order->get_billing_postcode() ));
		$authorization->transactions->card->billingAddress->setComplement( $address_2 );

		$res = $gateway->charge( $authorization );

		if ( isset( $res['status'] ) ) {

			$order->update_meta_data(
				'_params_aditum',
				array(
					'order_id'                     => $order_id,
					'chargeId'                => $res['charge']->id,
					'chargeStatus'            => $res['charge']->chargeStatus,
					'transaction_id'          => $res['charge']->transactions[0]->transactionId,
					'transaction_amount'      => $res['charge']->transactions[0]->amount,
					'transaction_transactionStatus' => $res['charge']->transactions[0]->transactionStatus,
				)
			);

			$order->save();

			if ( AditumPayments\ApiSDK\Enum\ChargeStatus::NOT_AUTHORIZED === $res['status'] ) {
				if($this->debug == '1') {
					return wc_add_notice( json_encode($res) , 'error' );
				}
				return wc_add_notice( 'Transação não autorizada.', 'error' );
			}
			else if ( AditumPayments\ApiSDK\Enum\ChargeStatus::AUTHORIZED === $res['status'] ) {

					$order->update_status( 'processing', __( 'Pagamento Concluído', 'wc-aditum-card' ) );

					$woocommerce->cart->empty_cart();

					return array(
						'result'   => 'success',
						'redirect' => $this->get_return_url( $order ),
					);

			} else {
				if($res['charge']->transactions[0]->transactionStatus === "Denied")
				{
					if($this->debug == '1') {
						return wc_add_notice( json_encode($res) , 'error' );
					}
					return wc_add_notice( $res['charge']->transactions[0]->errorMessage, 'error' );
				}else if($res['charge']->transactions[0]->transactionStatus === "PreAuthorized"){

					$order->update_status( 'pending', __( 'Aguardando autorização do pagamento', 'wc-aditum-card' ) );

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
