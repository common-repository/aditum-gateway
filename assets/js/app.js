jQuery.noConflict();
   (function( $ ) {
       $(function() {

            $(document).ready(function(){
                // $('#aditum_card_installment').hide();
            });

            $(document).on('focus', 'input#aditum_card_number', function(){
                $('input#aditum_card_number').mask('0000 0000 0000 0000');
            });

            $(document).on('click', '#payment_method_aditum_debitcard', function(){
                $('span#card-brand').html();
            });

            $(document).on('focusout', 'input#aditum_card_number', function(){

                $.post('/wp-admin/admin-ajax.php?action=get_card_brand', {bin:$('input#aditum_card_number').val()}, function(response){ 
                    if(response.status == 'success'){

                        //$('span#card-brand').text('Bandeira: ' + response.brand);
                        if(response.brand.toLowerCase() == "mastercard"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/mastercard.svg"/>');
                        } else if(response.brand.toLowerCase() == "visa"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/visa.svg"/>');
                        } else if(response.brand.toLowerCase() == "alelo"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/alelo.svg"/>');
                        } else if(response.brand.toLowerCase() == "vr"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/vr.svg"/>');
                        } else if(response.brand.toLowerCase() == "vero"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/vero.svg"/>');
                        } else if(response.brand.toLowerCase() == "verdecard"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/verdecard.svg"/>');
                        } else if(response.brand.toLowerCase() == "ticket"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/ticket.svg"/>');
                        } else if(response.brand.toLowerCase() == "sorocred"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/sorocred.svg"/>');
                        } else if(response.brand.toLowerCase() == "sodexo"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/sodexo.svg"/>');
                        } else if(response.brand.toLowerCase() == "maestro"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/maestro.svg"/>');
                        } else if(response.brand.toLowerCase() == "jcb"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/jcb.svg"/>');
                        } else if(response.brand.toLowerCase() == "hipercard"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/hipercard.svg"/>');
                        } else if(response.brand.toLowerCase() == "hiper"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/hiper.svg"/>');
                        } else if(response.brand.toLowerCase() == "elo"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/elo.svg"/>');
                        } else if(response.brand.toLowerCase() == "discover"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/discover.svg"/>');
                        } else if(response.brand.toLowerCase() == "dinerclub"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/dinerclub.svg"/>');
                        } else if(response.brand.toLowerCase() == "cabal"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/cabal.svg"/>');
                        } else if(response.brand.toLowerCase() == "banricard"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/banricard.svg"/>');
                        } else if(response.brand.toLowerCase() == "amex"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/amex.svg"/>');
                        }else{
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/outras.svg"/>');
                        }
                    }else{
                        $('span#card-brand').text('');
                    }
                });
            });

            $(document).on('click', 'input#aditum_card_cvv', function(){
                $('input#aditum_card_cvv').mask('000');
            });

            $(document).on('focus', 'input#card_holder_document', function(){
                $("#card_holder_document").mask("999.999.999-99");
            });

            $(document).on('click', 'input#aditum_card_expiration_month', function(){
                $('input#aditum_card_expiration_month').mask('00');
            });

            $(document).on('click', 'input#aditum_card_year_month', function(){
                $('input#aditum_card_year_month').mask('00');
            });

            $(document).on('focus', 'input#aditum_debitcard_number', function(){
                $('input#aditum_debitcard_number').mask('0000 0000 0000 0000');
            });
            
            $(document).on('focusout', 'input#aditum_debitcard_number', function(){
                $.post('/wp-admin/admin-ajax.php?action=get_card_brand', {bin:$('input#aditum_debitcard_number').val()}, function(response){ 
                    if(response.status == 'success'){
                       // $('span#card-brand').text('Bandeira: ' + response.brand);
                        if(response.brand == "Mastercard"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/mastercard.svg"/>');
                        } else if(response.brand == "Visa"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/visa.svg"/>');
                        } else if(response.brand == "Alelo"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/alelo.svg"/>');
                        } else if(response.brand == "Vr"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/vr.svg"/>');
                        } else if(response.brand == "Vero"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/vero.svg"/>');
                        } else if(response.brand == "VerdeCard"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/verdecard.svg"/>');
                        } else if(response.brand == "Ticket"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/ticket.svg"/>');
                        } else if(response.brand == "Sorocred"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/sorocred.svg"/>');
                        } else if(response.brand == "Sodexo"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/sodexo.svg"/>');
                        } else if(response.brand == "Maestro"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/maestro.svg"/>');
                        } else if(response.brand == "Jcb"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/JCB.svg"/>');
                        } else if(response.brand == "Hipercard"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/hipercard.svg"/>');
                        } else if(response.brand == "Hiper"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/hiper.svg"/>');
                        } else if(response.brand == "Elo"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/elo.svg"/>');
                        } else if(response.brand == "Discover"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/discover.svg"/>');
                        } else if(response.brand == "Dinerclub"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/dinerclub.svg"/>');
                        } else if(response.brand == "Cabal"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/cabal.svg"/>');
                        } else if(response.brand == "Banricard"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/banricard.svg"/>');
                        } else if(response.brand == "Amex"){
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/amex.svg"/>');
                        }else{
                            $('span#card-brand').html('<img src="' + window.aditum_plugin_url + 'assets/bandeiras/outras.svg"/>');
                        }
                    }else{
                        $('span#card-brand').text('');
                    }
                });
            });
            
            $(document).on('click', 'input#aditum_debitcard_cvv', function(){
                $('input#aditum_debitcard_cvv').mask('000');
            });
            
            $(document).on('focus', 'input#debitcard_holder_document', function(){
                $("#debitcard_holder_document").mask("999.999.999-99");
            });
            
            $(document).on('click', 'input#aditum_debitcard_expiration_month', function(){
                $('input#aditum_debitcard_expiration_month').mask('00');
            });
            
            $(document).on('click', 'input#aditum_debitcard_year_month', function(){
                $('input#aditum_debitcard_year_month').mask('00');
            });
       });
   })(jQuery);