<?php
/**
 * Plugin Name:       Aditum Gateway
 * Plugin URI:        https://aditum.com.br/
 * Description:       Gateway de pagamento de boleto do Aditum para o WooCommerce
 * Version:           1.5.3
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Aditum Gateway
 * Author URI:        https://aditum.com.br
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       aditum-gateway-boleto
 */

require_once dirname( __FILE__, 1 ) . '/vendor/autoload.php';

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

register_activation_hook( __FILE__, function() {
	if ( ! is_plugin_active( 'woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php' ) and current_user_can( 'activate_plugins' ) ) {
		wp_die( 'Desculpe, mas este plugin requer que o plugin "Brazilian Market on WooCommerce" esteja instalado e ativo. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Voltar para os Plugins</a>' );
	}
} );

add_action( 'woocommerce_api_aditum', function() { 
  $logger = wc_get_logger();
  
  $logger->info( 'Iniciando processamento do webhook');

  $input = json_decode(file_get_contents('php://input'), true);
  
	if(count($input) == 0) {
		$logger->info('ID e status do pedido não encontrado na requisição', array( 'source' => 'webhook-aditum-error' ));
		wp_die();
	}
  
	$order_id = $input['MerchantChargeId'];
	$order = wc_get_order( $order_id );
	$logger->info( 'Atualizando pedido '.$order_id, array( 'source' => 'aditum-orders' ) );
	if( $order ){
		if( 1 == $input['ChargeStatus'] ) {
			$order->payment_complete();
			wc_reduce_stock_levels( $order_id );
		}
		else if( 2 == $input['ChargeStatus'] ) {
			$order->update_status( 'pending', __( 'Pagamento pre-autorizado.', 'wc-aditum-card' ) );
		}
		else { 
			$order->update_status( 'cancelled', __( 'Pagamento cancelado.', 'wc-aditum-card' ) );
		}
	}
	else{
		// LOG THE FAILED ORDER TO CUSTOM "failed-orders" LOG
		$logger->info( 'O pedido com o ID: ' . $input['MerchantChargeId'] . ' Não foi encontrado ', array( 'source' => 'failed-orders' ) );
  }
  
	exit();
} );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'aditum-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', [], time() );
	wp_enqueue_script( 'jquerymask', plugin_dir_url(__FILE__) . 'assets/js/jquery.mask.js', array( 'jquery' ), time(), false );
	wp_enqueue_script( 'clipboard.js', plugin_dir_url(__FILE__) . 'assets/js/clipboard.min.js', array( 'jquery' ), time(), false );
	wp_enqueue_script( 'main-scripts', plugin_dir_url(__FILE__) . 'assets/js/app.js', array(), time(), false );
	wp_add_inline_script( 'main-scripts', "window.antifraude_id = '".get_option('aditum_antifraude_id')."'" );
	wp_add_inline_script( 'main-scripts', "window.antifraude_type = '".get_option('aditum_antifraude_type')."'" );
	wp_add_inline_script( 'main-scripts', "window.aditum_plugin_url = '".plugin_dir_url(__FILE__)."'" );
	wp_enqueue_script( 'antifraude', plugin_dir_url(__FILE__) . 'assets/js/antifraud.js', array('main-scripts'), time(), false );
} );

add_filter('woocommerce_checkout_fields', function( $fields ) {
  $fields['billing']['billing_neighborhood']['required'] = true;
  return $fields;
}, 1000, 1);

/**
 * Add gateway class and register with woocommerce
 */
add_action( 'plugins_loaded', function() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}
	// ! Boleto Gateway Class Register
	include_once plugin_dir_path( __FILE__ ) . 'classes/AditumBoleto.class.php';
	add_filter( 'woocommerce_payment_gateways', function( $methods ) {
		$methods[] = 'WC_Aditum_Boleto_Pay_Gateway';
		return $methods;
	}, 1000 );
	
	// ! Credit Card Gateway Class Register
	include_once plugin_dir_path( __FILE__ ) . 'classes/AditumCard.class.php';
	add_filter( 'woocommerce_payment_gateways', function( $methods ) {
		$methods[] = 'WC_Aditum_Card_Pay_Gateway';
		return $methods;
	}, 1000 );
	
	// ! Pix Gateway Class Register
	include_once plugin_dir_path( __FILE__ ) . 'classes/AditumPix.class.php';
	add_filter( 'woocommerce_payment_gateways', function( $methods ) {
		$methods[] = 'WC_Aditum_Pix_Pay_Gateway';
		return $methods;
	}, 1000 );
	
} , 0 );   

/**
 * Thank You Page Content
 *
 * @param int $order_id Order Id.
 */
add_action( 'woocommerce_thankyou', function( $order_id ) {
	$order = new WC_Order( $order_id );
	$aditum_data = $order->get_meta( '_params_aditum' );
	if ( $order->get_payment_method() === 'aditum_boleto' ) {
		if ( ! empty( $aditum_data ) ) {
			echo ('Código de Barras:');
			$generator = new Picqer\Barcode\BarcodeGeneratorHTML();
			echo ($generator->getBarcode( $aditum_data['transaction_barcode'], $generator::TYPE_CODE_128 ));
			echo ('<p>' . $aditum_data['transaction_digitalLine'] . '</p>');
			echo ('<div style="text-align: center">');
			if($aditum_data['environment'] === 'sandbox')
			{
				echo ('<a href="https://payment-dev.aditum.com.br' . $aditum_data['transaction_bankSlipUrl'] . '" class="button button-primary download-boleto" >Clique aqui para baixar o boleto</a>');
			}else{
				echo ('<a href="https://payment.aditum.com.br' . $aditum_data['transaction_bankSlipUrl'] . '" class="button button-primary download-boleto">Clique aqui para baixar o boleto</a>');
			}
			echo ('</div>');
		}
	}
	else if ( $order->get_payment_method() === 'aditum_card' ) {
		if ( ! empty( $aditum_data ) ) {
			if($aditum_data['transaction_transactionStatus'] === 'PreAuthorized') {
				echo ('<div class="woocommerce-info"><b>Pagamento Pré-Autorizado</b> recebemos o seu pedido mas o seu pagamento ainda não foi totalmente aprovado, assim que a compra for totalmente aprovada te notificaremos por e-mail.	</div>');
			}
			else if($aditum_data['transaction_transactionStatus'] === 'Captured') {
				echo ('<div class="woocommerce-message"><b>Pagamento Feito!</b> recebemos o seu pagamento com sucesso.</div>');
			}
		}
	}
	else if ( $order->get_payment_method() === 'aditum_pix' ) { 
		if ( ! empty( $aditum_data ) ) {
			include __DIR__ . '/templates/thankyou/' . $order->get_payment_method() . '.php';
		}else{
			echo ('<div class="woocommerce-message"><b>Não foi possível carregar os dados da transação.</b> Entre em contato com o lojista.</div>');
		}
	}
} );


/**
 * Alert if checkbox not checked
 */ 


/**
 * Card Fields Checkout
 *
 * @param int $description Description.
 * @param int $payment_id  Payment Id.
 */
add_filter( 'woocommerce_gateway_description', function( $description, $payment_id ) {
	if ( 'aditum_card' === $payment_id ) {
    $total = WC()->cart->get_total(null);
		$installment_options = ['' => 'Selecione a quantidade de parcelas'];
		$card = new WC_Aditum_Card_Pay_Gateway();
    if($total < $card->min_installments_amount) {
      $installment_options[1] = '1 parcela de R$'.number_format($total, 2, ',', '.');
    }
    else {
  		$installment_count = $card->max_installments ? $card->max_installments : 20;
  		for($i = 1; $i <= $installment_count;$i++) {
  			$installment_total = $total/$i;
  			$installment_plural = ($i > 1 ? 'Parcelas' : 'Parcela');
  			if($installment_total < $card->min_installments_amount) {
  				continue;
  			}
  			$installment_options[$i] = $i.' '.$installment_plural.' de R$'.number_format($installment_total, 2, ',', '.');
  		}
		}
		ob_start(); // ! Start buffering
		echo ('<div  class="aditum-card-fields" style="padding:10px 0;">');
		woocommerce_form_field(
			'card_holder_name',
			array(
				'type'     => 'text',
				'label'    => __( 'Nome do Titular do Cartão', 'woocommerce' ),
				'class'    => array( 'form-row form-row-wide' ),
				'required' => true,
			),
			''
		);
		woocommerce_form_field(
			'card_holder_document',
			array(
				'type'     => 'text',
				'label'    => __( 'CPF', 'woocommerce' ),
				'class'    => array( 'form-row form-row-wide' ),
				'required' => true,
			),
			''
		);
		woocommerce_form_field(
			'aditum_card_number',
			array(
				'type'     => 'text',
				'label'    => __( 'Número do Cartão', 'woocommerce' ),
				'class'    => array( 'form-row form-row-wide' ),
				'required' => true,
			),
			''
		);
		echo ('<span id="card-brand"></span>');
		woocommerce_form_field(
			'aditum_card_expiration_month',
			array(
				'type'     => 'text',
				'label'    => __( 'Data de validade', 'woocommerce' ),
				'class'    => array( 'form-row form-row-first' ),
				'required' => true,
				'placeholder' => 'MM',
			),
			''
		);
		woocommerce_form_field(
			'aditum_card_year_month',
			array(
				'type'     => 'text',
				'label'    => __( 'Ano Expiração', 'woocommerce' ),
				'class'    => array( 'form-row form-row-last' ),
				'required' => true,
				'placeholder' => 'YY',
			),
			''
		);
		woocommerce_form_field(
			'aditum_card_cvv',
			array(
				'type'     => 'text',
				'label'    => __( 'Número de Verificação do Cartão', 'woocommerce' ),
				'class'    => array( 'form-row form-row-wide' ),
				'input_class'   => array('card_cvv'),
				'required' => true,
			),
			''
		);
		woocommerce_form_field(
			'aditum_card_installment',
			array(
				'type'     => 'select',
				'options'  => $installment_options,
				'label'    => __( 'Quantidade de parcelas', 'woocommerce' ),
				'class'    => array( 'form-row form-row-wide installment_aditum_card' ),
				'required' => true,
			),
			''
		);    
		echo ('</div>');
		$description .= ob_get_clean(); // ! Append buffered content
	}
	elseif( 'aditum_boleto' === $payment_id ){
		ob_start(); // ! Start buffering
		echo ('<div  class="aditum-pix-fields" style="padding:10px 0;">');    
		echo ('<div>');
		$description .= ob_get_clean(); // ! Append buffered content
	}
	elseif( 'aditum_pix' === $payment_id ){
		ob_start(); // ! Start buffering   
		echo ('<div  class="aditum-boleto-fields" style="padding:10px 0;">');   
		echo ('<div>');
		$description .= ob_get_clean(); // ! Append buffered content
	}
	return $description;
}, 20, 2 );


add_action( 'wp_ajax_get_card_brand', 'aditum_get_card_brand' );
add_action( 'wp_ajax_nopriv_get_card_brand', 'aditum_get_card_brand' );
/**
 * Get card number
 *
 * @param int $bin card number.
 */
function aditum_get_card_brand() {
  if (!isset($_POST['bin'])) {
    wp_send_json( array(
      'status' => 'error',
      'brand'  => 'O número do cartão é obrigatório',
    ) );
  }
  $bin = sanitize_text_field($_POST['bin']);
  if(!$bin) {
    wp_send_json( array(
      'status' => 'error',
      'brand'  => 'O número do cartão é inválido',
    ) );
  }
	$credentials = new WC_Aditum_Card_Pay_Gateway();
	AditumPayments\ApiSDK\Configuration::initialize();
	if ( 'sandbox' === $credentials->environment ) {
		AditumPayments\ApiSDK\Configuration::setUrl( AditumPayments\ApiSDK\Configuration::DEV_URL );
	}
	$merchant_numeric_cnpj = preg_replace('/[^0-9]/', '', $this->merchant_cnpj);
    AditumPayments\ApiSDK\Configuration::setCnpj($merchant_numeric_cnpj);
	AditumPayments\ApiSDK\Configuration::setMerchantToken( $credentials->merchant_key );
	AditumPayments\ApiSDK\Configuration::setlog( false );
	AditumPayments\ApiSDK\Configuration::login();
	$brand_name = AditumPayments\ApiSDK\Helper\Utils::getBrandCardBin( str_replace( ' ', '', $bin ) );
	if ( $brand_name === null ) {
		$array_result = array(
			'status' => 'error',
			'brand'  => 'null',
		);
	} else {
		if ( true === $brand_name['status'] ) {
			$array_result = array(
				'status' => 'success',
				'brand'  => $brand_name['brand'],
			);
		} else {
			$array_result = array(
				'status' => 'error',
				'brand'  => 'null',
			);
		}
	}
	wp_send_json( $array_result );
}

add_option( 'woocommerce_pay_page_id', get_option( 'woocommerce_thanks_page_id' ) );

add_action( 'woocommerce_after_order_notes', function(){
	echo ('<input type="hidden" class="input-hidden" name="antifraud_token" id="antifraud_token" />');
} );

add_filter( 'woocommerce_settings_tabs_array', function($settings_tabs){
	$settings_tabs['settings_tab_aditum_antifraude'] = __( 'Antifraude', 'woocommerce-settings-tab-aditum-antifraude' );
    return $settings_tabs;
}, 50 );

function get_aditum_antifraude_settings() {
	$settings = array(
    'section_title' => array(
        'name'     => __( 'Antifraude', 'woocommerce-settings-tab-aditum-antifraude' ),
        'type'     => 'title',
        'desc'     => '',
        'id'       => 'wc_settings_tab_aditum_antifraude_section_title'
    ),
		'aditum_antifraude_type' => array(
			'title'   => __( 'Tipo de Antifraude:', 'wc-aditum' ),
			'type'    => 'select',
			'options' => ['konduto' => 'Konduto', 'clearsale' => 'Clear Sale', 'aditum' => 'Aditum'],
			'id'   => 'aditum_antifraude_type'
		),
		'aditum_antifraude_id'   => array(
			'title'       => __( 'Token:', 'wc-aditum' ),
			'type'        => 'text',
			'description' => __( 'Token.', 'wc-aditum' ),
			'desc_tip'    => true,
			'id'   => 'aditum_antifraude_id'
		),
    'section_end' => array(
         'type' => 'sectionend',
         'id' => 'wc_settings_settings_tab_aditum_antifraude_section_end'
    )
	);
	return apply_filters( 'wc_settings_settings_tab_aditum_antifraude_settings', $settings );
}
add_action( 'woocommerce_settings_tabs_settings_tab_aditum_antifraude', function(){
	woocommerce_admin_fields(get_aditum_antifraude_settings());
});
add_action( 'woocommerce_update_options_settings_tab_aditum_antifraude', function() {
    woocommerce_update_options( get_aditum_antifraude_settings() );
} );
