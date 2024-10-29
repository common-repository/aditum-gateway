=== Aditum Gateway ===
Tags: woocommerce, aditum, payment
Requires at least: 4.0
Tested up to: 6.4.2
Stable tag: 1.5.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Contributors: pluginsaditum
Requires PHP: 7.4

Adds Aditum gateway to the WooCommerce plugin

== Description ==

### Add Aditum gateway to WooCommerce ###

This plugin adds Aditum gateway to WooCommerce.

Please notice that WooCommerce must be installed and active.


### Descrição em Português: ###

Adicione o Aditum como método de pagamento em sua loja WooCommerce.

[Aditum](https://aditum.com.br/) é um método de pagamento brasileiro.

O plugin WooCommerce Aditum foi desenvolvido para integração de pagamento da sua loja virtual woocommerce com o nosso gateway com checkout transparente.

Estão disponíveis as seguintes modalidades de pagamento:

- **Cartão de Crédito:**
- **Boleto Bancário:**
- **Pix:**


= Compatibilidade =

Compatível com versões posteriores ao WooCommerce 3.0.

Este plugin também é compatível com o [WooCommerce Extra Checkout Fields for Brazil](https://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/), desta forma é possível enviar os campos de "CPF", "número do endereço" e "bairro" (para o Checkout Transparente é obrigatório o uso deste plugin).

= Instalação =

Confira o nosso [passo a passo de instalação e configuração](https://github.com/aditum-payments/aditum-woocommerce).

= Integração =

Este plugin funciona perfeitamente em conjunto com:

* [WooCommerce Extra Checkout Fields for Brazil](https://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/).
* [WooCommerce Multilingual](https://wordpress.org/plugins/woocommerce-multilingual/).

= Dúvidas? =

Para dúvidas podem acessar o link abaixo:
https://aditum.com.br/duvidas.php



== Installation ==

* Upload plugin files to your plugins folder, or install using WordPress built-in Add New Plugin installer;
* Activate the plugin;
* Navigate to WooCommerce -> Settings -> Payment Gateways, choose Aditum and fill in your CNPJ and Merchant Token:

### Instalação e configuração em Português: ###

= Instalação do plugin: =

* Envie os arquivos do plugin para a pasta wp-content/plugins, ou instale usando o instalador de plugins do WordPress.
* Ative o plugin.

= Requerimentos: =

É necessário possuir uma conta no [Aditum](https://aditum.com.br/) e ter instalado o [WooCommerce](https://wordpress.org/plugins/woocommerce/).

Apenas com isso já é possível receber os pagamentos e fazer o retorno automático de dados.

<blockquote>Atenção: Não é necessário configurar qualquer URL em "Página de redirecionamento" ou "Notificação de transação", pois o plugin é capaz de comunicar com api Aditum.</blockquote>

= Configurações do Plugin: =

Com o plugin instalado acesse o admin do WordPress e entre em "WooCommerce" > "Configurações" > "Finalizar compra" > "Aditum".

Habilite o Aditum, adicione o seu CNPJ e o Merchant Token:. Esses dados são utilizado para gerar os pagamentos e fazer o retorno de dados.

Você pode conseguir o seu Merchant Token no sua conta no site da Aditum" > "(https://aditum.com.br)".


= Checkout Transparente =
Para utilizar o checkout transparente é necessário utilizar o plugin 


Pronto, sua loja já pode receber pagamentos pelo Aditum.

== Frequently Asked Questions ==

= What is the plugin license? =

* This plugin is released under a GPL license.

= What is needed to use this plugin? =

* WooCommerce version 3.0 or latter installed and active.
* Only one account on [Aditum](https://aditum.com.br/ "Aditum").

### FAQ em Português: ###

= Qual é a licença do plugin? =

Este plugin esta licenciado como GPL.

= O que eu preciso para utilizar este plugin? =

* Ter instalado o plugin WooCommerce 3.0 ou mais recente.
* Possuir uma conta no Aditum.
* Gerar seu CNPJ e o Merchant Token da Aditum.


= Adimtum recebe pagamentos de quais países? =

No momento a Aditum recebe pagamentos apenas do Brasil.

Configuramos o plugin para receber pagamentos apenas de usuários que selecionarem o Brasil nas informações de pagamento durante o checkout.

= Quais são os meios de pagamento que o plugin aceita? =

São aceitos todos os meios de pagamentos que a Aditum disponibiliza.

Confira os [meios de pagamento e parcelamento](https://aditum.com.br).


== Changelog ==

= Aditum Gateway 1.5.3 =
* Update the version from wordpress.org

= Aditum Gateway 1.5.2 =
* Remove the Update URI field on aditum-payment

= Aditum Gateway 1.5.1 =
* Update the supported versions of wordpress in Readme

= Aditum Gateway 1.5.0 =
* Fix QR Code not showing on end screen

= Aditum Gateway 1.4.9 =
* Correção do webhook

= Aditum Gateway 1.4.8 =
* Bump version to 1.4.8

= Aditum Gateway 1.4.7 =
* Correção do valor do produto do carrinho no Pix

= Aditum Gateway 1.4.6 =
* Change variable to MerchantChargeId at check for Webhook Order

= Aditum Gateway 1.4.5 =
* Force new stable version

= Aditum Gateway 1.4.4 =
* Add billing address

= Aditum Gateway 1.4.3 =
* Change status from Order to processing after payment

= Aditum Gateway 1.4.2 =
* Change source of charge to Woocommerce

= Aditum Gateway 1.4.1 =
* Bug fix

= Aditum Gateway 1.4.0 =
* Bug fix

= Aditum Gateway 1.3.11 =
* Bug fix

= Aditum Gateway 1.3.10 =
* Bug fix

= Aditum Gateway 1.3.9 =
* Bug fix

= Aditum Gateway 1.3.8 =
* Bug fix

= Aditum Gateway 1.3.7 =
* Bug fix

= Aditum Gateway 1.3.6 =
* Bug fix

= Aditum Gateway 1.3.5 =
* Bug fix

= Aditum Gateway 1.3.4 =
* Bug fix

= Aditum Gateway 1.3.3 =
* Bug fix

= Aditum Gateway 1.3.2 =
* Bug fix

= Aditum Gateway 1.3.1 =
* Bug fix

= Aditum Gateway 1.3.0 =
* Adição de opção de debug

= Aditum Gateway 1.2.1 =
* Bug fix

= Aditum Gateway 1.2.0 =
* Envio de dados dos itens do pedido para a API

= Aditum Gateway 1.1.1 =
* Atualização da documentação

= Aditum Gateway 1.1.0 =
* Adicionado método de pagamento por Pix

= Aditum Gateway 1.0.4 =
* Ajuste antifraude

= Aditum Gateway 1.0.3 =
* Correção de bugs

= Aditum Gateway 1.0.2 =
* Correção de bugs

= Aditum Gateway 1.0.1 =
* Atualizações de informações

= Aditum Gateway 1.0.0 =
* Plublicação do plugin

= Aditum Gateway =
* Atualização dos screenshots

== Upgrade Notice ==

== Screenshots ==
