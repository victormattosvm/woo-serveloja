<?php
/**
 * WooCommerce Serveloja Gateway class.
 *
 * Extende as funções de pagamento, utilizando os serviços da Serveloja.
 *
 * @class   WC_Serveloja_Gateway
 * @extends WC_Payment_Gateway
 * @version 2.7.0
 * @author  TiServeloja
 */

if (!defined( 'ABSPATH' )) {
    exit;
}

class WC_Serveloja_Gateway extends WC_Payment_Gateway {

    public function __construct() {
        $this->id                 = 'serveloja';
        $this->icon               = apply_filters('woocommerce_serveloja_icon', plugins_url( 'assets/images/logo-checkout.png', plugin_dir_path( __FILE__ )));
        $this->method_title       = __('Serveloja', 'woocommerce-serveloja');
        $this->method_description = __('Aceite pagamentos com cartões de crédito através da Serveloja em sua loja virtual.', 'woocommerce-serveloja');
        $this->title              = 'Pagamento com cartão de crédito protegido pela Serveloja.';
        $this->description        = 'Realize pagamentos com cartões de crédito através da Serveloja.';
        $this->order_button_text  = __('Realizar pagamento', 'woocommerce-serveloja');
        $this->method             = $this->get_option( 'method', 'direct' );

        // forms
        $this->init_form_fields();

        // settings
        $this->init_settings();

        // veriaveis do form
        $this->checkbox = $this->get_option('checkbox');

        // actions principais 
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_receipt_' . $this->id, array( $this, 'receipt_page'));

        if ( 'transparent' === $this->method ) {
            $this->description = $this->wcsvl_generate_serveloja_form($order);
            $this->order_button_text  = __('Finalizar pagamento', 'woocommerce-serveloja');
		}
    }

    
    function init_form_fields() {
        $this->form_fields = array(
            'integration' => array(
				'title'       => __('Configuração da aplicação', 'woocommerce-serveloja'),
				'type'        => 'title',
				'description' => '',
			),
            'enabled' => array(
                'title'       => __('Habilitar/Desabilitar', 'woocommerce-serveloja'),
                'type'        => 'checkbox',
                'label'       => __('Utilizar <b>WooCommerce Serveloja</b> para receber pagamentos', 'woocommerce-serveloja'),
                'default'     => 'yes'
            ),
            'method'               => array(
				'title'       => __( 'Método de integração', 'woocommerce-serveloja' ),
				'type'        => 'select',
				'description' => __( 'Selecione o método de integração com o seu e-commerce', 'woocommerce-pagseguro' ),
				'desc_tip'    => true,
				'default'     => 'transparent',
				'class'       => 'wc-enhanced-select',
				'options'     => array(
					'lightbox'    => __( 'Lightbox', 'woocommerce-serveloja' ),
					'transparent' => __( 'Checkout Transparente', 'woocommerce-serveloja' ),
                )
            )
        );
    }

    private function wcsvl_mascaraValor() {
        wc_enqueue_js('
            function wcsvl_mascaraValor(value) {
                return value.formatMoney(2, ",", ".");
            }

            Number.prototype.formatMoney = function (c, d, t) {
                var n = this,
                    c = isNaN(c = Math.abs(c)) ? 2 : c,
                    d = d == undefined ? "." : d,
                    t = t == undefined ? "," : t,
                    s = n < 0 ? "-" : "",
                    i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
                    j = (j = i.length) > 3 ? j % 3 : 0;
                return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
            };
        ');
    }


    private function wcsvl_modal() {
        echo $this->wcsvl_mascaraValor();
       
        wc_enqueue_js('
        $("#bgModal").hide();
            
        function wcsvl_htmlModal(tipo, titulo, mensagem, url) {
            var reply = "";
            reply += "<div id=\'modal\' class=\'modal modal_" + tipo +  " sombra\'>" +
                "<div class=\'cabecalho cabecalho_" + tipo +  "\'>" + titulo + "</div>" +
                "<div id=\'icone\'><img src=\'' . plugins_url( 'assets/images/" + tipo + ".png', plugin_dir_path( __FILE__ )) . '\' alt=\'icone\' /></div>" +
                "<div class=\'resposta\'>" + mensagem + "</div>";
            if (tipo == "duvida") {
                reply += "<div class=\'ok\' id=\'cancela\'>Não</div>" +
                    "<div class=\'ok\' id=\'okConf\' style=\'margin-right: 120px;\'>Sim</div>";
            } else {
                reply += "<div class=\'ok\' id=\'ok\'>Ok</div>";
            }
            reply += "</div>";
            return reply;
        }

        function wcsvl_modal(tipo, titulo, mensagem, url, bg) {
            $("#" + bg).fadeIn();
            setTimeout(function () {
                $("#" + bg).html(wcsvl_htmlModal(tipo, titulo, mensagem, url));
            }, 300);
            $("#ok, #okConf, #cancela").live("click", function () {
                $("#" + bg).html("");
                $("#" + bg).fadeOut();
                if (url != "") {
                    jQuery(window.document.location).attr("href", url);
                }
            });
        }

        function wcsvl_htmlModalCancelar() {
            var reply = "";
            reply += "<form method=\'POST\' action=\'\' name=\'dados_pagamento\'>" +
            "<div class=\'modalCancelar\'>" +
                        "<div class=\'modalCancelarConteudo\'>" +
                            "<div class=\'modalCancelarTitulo\'>Deseja realmente fazer esta ação?</div>"+
                            "<div class=\'modalCancelarBotoes\'>"+
                                "<input type=\'submit\' id=\'modalCancelarBotaoSim\' name=\'cancelarSim\' value=\'Sim\'/>" +
                                "<button id=\'modalCancelarBotaoNao\' name=\'cancelarNao\' value=\'Não\'>Não</button>" +
                            "</div>"+
                        "</div>"+
                    "</div>"+
                    "</form>";
            return reply;
        }

        function wcsvl_modalCancelar(url) {
            $("#bgModal_interno").fadeIn();
            setTimeout(function () {
                $("#bgModal_interno").html(wcsvl_htmlModalCancelar());
            }, 300);
            $("#modalCancelarBotaoNao").live("click", function () {
                $("#bgModal_interno").html("");
                $("#bgModal_interno").fadeOut();
                $("#finalizarCompraModal").show();
                $("#cancelar").show();
            });
        }

        function wcsvl_htmlModalSucesso() {
            var reply = "";
            reply += 
                    "<a href=\''.get_option('home').'\'>" +
                        "<div id=\'sair\' title=\'Cancelar e voltar para o carrinho\'>" +
                        "</div>" +
                    "</a>" +
                    "<div class=\'modalSucesso\'>" +
                        "<div class=\'modalSucessoConteudo\'>" +
                            "<img style=\'height: 50px;\' src=\'' . plugins_url( 'assets/images/checkPagamento.png', plugin_dir_path( __FILE__ )) . '\' alt=\'check\' />"+
                            "<div class=\'modalSucessoTitulo\'>"+
                            "Seu pagamento foi realizado com Sucesso!</div>"+

                            /*TODO concluir essa parte (retirar)*/
                            "<div class=\'modalSucessoInfo\'>"+
                                "<p class=\'tituloModalSucessoInfo\'>Cliente</p>"+
                                "<input disabled style=\'width: 80%;\' type=\'text\' id=\'nomeSucesso\' class=\'descricaoModalSucessoInfo\' value=\'\'>"+
                                
                                "<p class=\'tituloModalSucessoInfo\'>Valor total</p>"+
                                "<input disabled style=\'width: 80%;\' type=\'text\' id=\'totalSucesso\' class=\'descricaoModalSucessoInfo\' value=\'\'>"+
                                
                                "<p class=\'tituloModalSucessoInfo\'>Forma de pagamento</p>"+
                                "<input disabled style=\'width: 80%;\' type=\'text\' id=\'cartaoSucesso\' class=\'descricaoModalSucessoInfo\' value=\'\'>"+

                                "<p class=\'tituloModalSucessoInfo\'>Parcela</p>"+
                                "<input disabled style=\'width: 80%;\' type=\'text\' id=\'parcelaSucesso\' class=\'descricaoModalSucessoInfo\' value=\'\'>"+

                                // "<img style=\'width: 100px; margin: 40px 0px 10px 0px;\' src=\'' . plugins_url( 'assets/images/serveloja-preto.png', plugin_dir_path( __FILE__ )) . '\' alt=\'check\' />"+    
                                "<input disabled style=\'width: 80%; text-align: center;border: none;background-color: white;margin: 0px;\' type=\'text\' id=\'dataSucesso\' class=\'rodapeModalSucesso\' value=\'\'>"+
                                "<p style=\'margin-bottom: 0px !important;\' class=\'rodapeModalSucesso\'>Código da compra:</p>"+
                                "<input disabled style=\'color: #24B24B;\' type=\'text\' id=\'codigoCompraSucesso\' class=\'descricaoModalSucessoInfo\' value=\'\'>"+

                            "</div>"+

                            "<div class=\'modalSucessoRodape\'>"+
                                
                            "</div>"+

                        "</div>"+
                    "</div>";
            return reply;
        }

        function wcsvl_modalSucesso(url, bg, nome, valorTotal, bandeira, numeroCarao, parcela, codigoCompra) {

            valorTotal = valorTotal.replace(".","");
            valorTotal = valorTotal.replace(",",".");
            var infoCartao = String(bandeira+": **** "+numeroCarao.substr(-4));
            console.log(valorTotal);
            var valorParcela = wcsvl_mascaraValor(valorTotal / parcela);
            console.log(parcela);
            console.log(valorParcela);
            var infoParcela = String(parcela+"x R$"+valorParcela);
            var infoCodigo =  String("#"+codigoCompra);
            valorTotal = valorTotal.replace(".",",");
            var total = String("R$"+valorTotal);

            now = new Date;

            if(now.getMonth() == 0){
                var mes = "JAN";
            }
            if(now.getMonth() == 1){
                var mes = "FEV";
            }
            if(now.getMonth() == 2){
                var mes = "MAR";
            }
            if(now.getMonth() == 3){
                var mes = "ABR";
            }
            if(now.getMonth() == 4){
                var mes = "MAI";
            }
            if(now.getMonth() == 5){
                var mes = "JUN";
            }
            if(now.getMonth() == 6){
                var mes = "JUL";
            }
            if(now.getMonth() == 7){
                var mes = "AGO";
            }
            if(now.getMonth() == 8){
                var mes = "SET";
            }
            if(now.getMonth() == 9){
                var mes = "OUT";
            }
            if(now.getMonth() == 10){
                var mes = "NOV";
            }
            if(now.getMonth() == 11){
                var mes = "DEZ";
            }
            if(now.getDate() < 10){
                var dia = String("0"+now.getDate());
            }else{
                var dia = now.getDate();
            }
            if(now.getHours() < 10){
                var hora = String("0"+now.getHours());
            }else{
                var hora = now.getHours();
            }
            if(now.getMinutes() < 10){
                var minuto = String("0"+now.getMinutes());
            }else{
                var minuto = now.getMinutes();
            }

            var infoData = String("Pagamento efetuado em: " + dia +" "+ mes +" "+ now.getFullYear() + " às " + hora +":"+minuto);

            $("#" + bg).fadeIn();
            setTimeout(function () {
                $("#" + bg).html(wcsvl_htmlModalSucesso());
                document.getElementById("nomeSucesso").value = nome;

                document.getElementById("totalSucesso").value = total;
                document.getElementById("cartaoSucesso").value = infoCartao;
                document.getElementById("parcelaSucesso").value = infoParcela;
                document.getElementById("codigoCompraSucesso").value = infoCodigo;
                document.getElementById("dataSucesso").value = infoData;
            }, 300);
            $("#ok, #okConf, #cancela").live("click", function () {
                $("#" + bg).html("");
                $("#" + bg).fadeOut();
                if (url != "") {
                    jQuery(window.document.location).attr("href", url);
                }
            });
        }


        ');
    }
    private function wcsvl_cpf_cnpj() {
        wc_enqueue_js('
            function wcsvl_cpf_cnpj (id) {
                $(document).ready(function() {
                    $("#" + id).mask("999.999.999-99?99999");
                    $("#" + id).live("keyup", function (e) {
                        var query = $(this).val().replace(/[^a-zA-Z 0-9]+/g,"");
                        if (query.length == 11) {
                            $("#" + id).mask("999.999.999-99?99999");
                        }
                        if (query.length == 14) {
                            $("#" + id).mask("99.999.999/9999-99");
                        }
                    });
                });
            }
        ');
    }

    private function wcsvl_mascarasValidacao() {
        wc_enqueue_js('
        function MascaraCPF(cpf){
            var tirar = /[a-zA-Z\s]+/g;
            str = String(cpf.value);
            cpf.value = str.replace(tirar,"");
            if(mascaraInteiro(cpf)==false){
              event.returnValue = false;
            }
            if(cpf.value.length <= 14){
              return formataCampo(cpf, "000.000.000-00", event);
            }else{
              return formataCampo(cpf, "00.000.000/0000-00", event);
            }
            
          }
      
          function ValidaNome(nome) {
            exp = /^(([a-zA-Z ]|[é])*)$/
            if((nome.value.length < 7)&&(nome.value.length > 0)){
                $("#nmTitular").addClass("hasError");   
                $("#msgErroNome").show();     
                return false;
            }else{
                if (!exp.test(nome.value)) {
                    $("#nmTitular").addClass("hasError");   
                    $("#msgErroNome").show();       
                    return false;
                }else{
                    $("#nmTitular").removeClass("hasError");   
                    $("#msgErroNome").hide();      
                    return true;
                }  
            }
          }
      
          function MascaraCartao(cartao) {
            $(document).ready(function() {
                var tirar = /[a-zA-Z\s]+/g;
                str = String(cartao.value);
                cartao.value = str.replace(tirar,"");
                str = str.replace("-","");
                str = str.replace("-","");
                str = str.replace("-","");
                str = str.replace("-","");
                str = str.replace(" ","");
                str = str.replace(" ","");
                str = str.replace(" ","");
                str = str.replace(" ","");
                var regexAmex = /^3[47][0-9]{13}/;
                var regexDiners = /^3(?:0[0-5]|[68][0-9])[0-9]{11}/;
                if (mascaraInteiro(cartao) == false) {
                event.returnValue = false;
                }
                if(str.match(regexAmex)){
                    return formataCampo(cartao, "0000-000000-00000", event);
                }
                if(str.match(regexDiners)){
                    return formataCampo(cartao, "0000-000000-0000", event);
                }else{
                    return formataCampo(cartao, "0000-0000-0000-0000", event);
                }
            });
          }

          function ValidaCartao(cartao) {
            exp = /(^\d{4}\-?\d{4}\-?\d{4}\-?\d{4}$)|(^\d{4}\s\d{4}\s\d{4}\s\d{4}$)/
            if (!exp.test(cartao.value)) {
                return false;                 
            }else{
                return true;
            }
          }
      
          function MascaraValidade(validade) {
            var tirar = /[a-zA-Z\s]+/g;
            str = String(validade.value);
            validade.value = str.replace(tirar,"");
            if (mascaraInteiro(validade) == false) {
              event.returnValue = false;
            }
            return formataCampo(validade, "00/0000", event);
          }
      
          function MascaraCCV(ccv) {
            var tirar = /[a-zA-Z\s]+/g;
            str = String(ccv.value);
            ccv.value = str.replace(tirar,"");
            if (mascaraInteiro(ccv) == false) {
              event.returnValue = false;
            }
            return true;
          }
      
          function ValidaCCV(ccv) {
            exp = /\d{3,4}/
            if (ccv.value.length > 0) {
                if (!exp.test(ccv.value)) {
                    $("#CodSeguranca").addClass("hasError");
                    $("#msgErroCCV").show();      
                    return false;
                }else{
                    $("#CodSeguranca").removeClass("hasError");
                    $("#msgErroCCV").hide();      
                    return true;
                }
            }else{
                $("#CodSeguranca").removeClass("hasError");
                $("#msgErroCCV").hide();     
                return true;
            }
          }
      
          function MascaraDDD(ddd) {
            var tirar = /[a-zA-Z\s]+/g;
            str = String(ddd.value);
            ddd.value = str.replace(tirar,"");
            if (mascaraInteiro(ddd) == false) {
              event.returnValue = false;
            }
            return true;
          }
      
          function ValidaDDD(ddd) {
            exp = /\d{2,3}/
            if (ddd.value.length > 0) {
                if (!exp.test(ddd.value)) {
                    $("#DDDCelular").addClass("hasError");   
                    $("#msgErroDDD").show();            
                    return false;
                }else{
                    $("#DDDCelular").removeClass("hasError");
                    $("#msgErroDDD").hide();      
                    return true;
                }
            }else{
                $("#DDDCelular").removeClass("hasError");
                $("#msgErroDDD").hide();    
                return true;
            }
          }
      
          function MascaraCelular(celular) {
            var tirar = /[a-zA-Z\s]+/g;
            str = String(celular.value);
            celular.value = str.replace(tirar,"");
            if (mascaraInteiro(celular) == false) {
              event.returnValue = false;
            }
            return formataCampo(celular, "00000-0000", event);
          }
      
          function ValidaCelular(celular) {
            exp = /\d{5}\-\d{4}/
            if (celular.value.length > 0) {
                if(celular.value == "00000-0000"||
                celular.value == "11111-1111"||
                celular.value == "22222-2222"||
                celular.value == "33333-3333"||
                celular.value == "44444-4444"||
                celular.value == "55555-5555"||
                celular.value == "66666-6666"||
                celular.value == "77777-7777"
                ){
                    $("#NrCelular").addClass("hasError");
                    $("#msgErroCelular").show();      
                    return false;
                }else{
                    if (!exp.test(celular.value)) {
                        $("#NrCelular").addClass("hasError");    
                        $("#msgErroCelular").show();           
                        return false;
                    }else{
                        $("#NrCelular").removeClass("hasError");
                        $("#msgErroCelular").hide(); 
                        return true;
                    }
                }
            }else{
                $("#NrCelular").removeClass("hasError");
                $("#msgErroCelular").hide(); 
                return true;
            }
          }
      
          function mascaraInteiro() {
            if (event.keyCode < 48 || event.keyCode > 57) {
                event.returnValue = false;
                return false;
            }
            return true;
          }
      
          function formataCampo(campo, Mascara, evento) {
            var boleanoMascara;
            var Digitado = evento.keyCode;
            exp = /\-|\.|\/|\(|\)| /g
            campoSoNumeros = campo.value.toString().replace(exp, "");
            var posicaoCampo = 0;
            var NovoValorCampo = "";
            var TamanhoMascara = campoSoNumeros.length;
      
            if (Digitado != 8) { // backspace 
              for (i = 0; i <= TamanhoMascara; i++) {
                boleanoMascara = ((Mascara.charAt(i) == "-") || (Mascara.charAt(i) == ".") || (Mascara.charAt(i) == "/"))
                boleanoMascara = boleanoMascara || ((Mascara.charAt(i) == "(") || (Mascara.charAt(i) == ")") || (Mascara.charAt(i) == " "))
                if (boleanoMascara) {
                    NovoValorCampo += Mascara.charAt(i);
                    TamanhoMascara++;
                } else {
                    NovoValorCampo += campoSoNumeros.charAt(posicaoCampo);
                    posicaoCampo++;
                }
              }
      
              campo.value = NovoValorCampo;
      
              return true;
      
            } else {
                return true;
            }
      
          } 
    ');
    }

    private function wcsvl_detalhes_cartao($total) {
        echo $this->wcsvl_mascaraValor();
        wc_enqueue_js('
            $(document).ready(function () {
                $("#bgModal_interno").hide();
                $("#checkoutTransparentFormInterno").hide();
                $("#exibeCartao").hide();
                $("input[name=bandeira_cartao]:checked").live("click", function() {
                    var isChecked = $(this).val();
                    if (isChecked) {
                        $("#exibeCartao").show();
                        var valor = ' . $total . ';
                        var qtd = isChecked.split("-");
                        var parcela = valor / qtd[0];
                        var imagem = "<img class=\'img_detalhes\' src=\'' . plugins_url( 'assets/images/" + qtd[1].toLowerCase() + ".png', plugin_dir_path( __FILE__ )) . '\' alt=\'" + qtd[1].toLowerCase() + "\' />";
                        var select = "<div class=\'dir_detalhes\'>";
                            select += "<div class=\'tituloInputFinalizarCompraLightbox\' style=\'margin-top:0px;\'>Selecione a quantidade de parcelas</div>";
                            select += "<select class=\'select input_maior select_sborda coluna100\' id=\'QtParcela\' name=\'QtParcela\'>";
                                for (var i = 1; i <= qtd[0]; i++) {
                                    select += "<option value=\'" + i + "\'>" + i + "x - R$ " + wcsvl_mascaraValor(valor / i) + "</option>";
                                }
                            select += "</select>";
                        select += "</div>";
                        select += "<input type=\'hidden\' id=\'Bandeira\' name=\'Bandeira\' value=\'" + qtd[1] + "\' />";
                        select += "<input type=\'hidden\' id=\'Valor\' name=\'Valor\' value=\'" + wcsvl_mascaraValor(valor) + "\' />";
                        $("#exibeCartao").html(imagem + select);

                        var input_senha = "";
                        if (qtd[1] == "assomise") {
                            input_senha = "<div class=\'tituloInputFinalizarCompraLightbox margin_top\'>Senha do cartão</div>";
                            input_senha += "<input type=\'password\' name=\'SenhaCartao\' class=\'exibeSenha\' id=\'SenhaCartao\' value=\'\' />";
                            $("#senha").html(input_senha);
                        } else {
                            input_senha += "<input type=\'hidden\' name=\'SenhaCartao\' value=\'\' />";
                            $("#senha").html(input_senha);
                        }
                        return true;
                    }
                });
            });
        ');
    }

    private function wcsvl_valida_cpf() {
        wc_enqueue_js('
            function wcsvl_validarCPF (cpf) {
                var Soma;
                var Resto;
                Soma = 0;
                cpf = cpf.replace(/[^\d]+/g,\'\');
                if (cpf == \'\') { return false; }
                if (cpf == "00000000000" ||
                    cpf == "11111111111" ||
                    cpf == "22222222222" ||
                    cpf == "33333333333" ||
                    cpf == "44444444444" ||
                    cpf == "55555555555" ||
                    cpf == "66666666666" ||
                    cpf == "77777777777" ||
                    cpf == "88888888888" ||
                    cpf == "99999999999") {
                    return false;
                }

                for (i=1; i<=9; i++) Soma = Soma + parseInt(cpf.substring(i-1, i)) * (11 - i);
                Resto = (Soma * 10) % 11;

                if ((Resto == 10) || (Resto == 11))  Resto = 0;
                if (Resto != parseInt(cpf.substring(9, 10)) ) return false;

                Soma = 0;
                for (i = 1; i <= 10; i++) Soma = Soma + parseInt(cpf.substring(i-1, i)) * (12 - i);
                Resto = (Soma * 10) % 11;

                if ((Resto == 10) || (Resto == 11))  Resto = 0;
                if (Resto != parseInt(cpf.substring(10, 11) ) ) return false;
                
                $("#msgErroCpf").hide();
                $("#CpfCnpjComprador").removeClass("hasError");
                //$("#submit-serveloja-payment-form").show();
                //$("#submit-serveloja-payment-form-false").hide();
                return true;
                
            }
        ');
    }

    private function wcsvl_valida_cnpj() {
        wc_enqueue_js('
            function wcsvl_validarCNPJ (cnpj) {
                cnpj = cnpj.replace(/[^\d]+/g,\'\');
                if (cnpj == \'\') { return false; }
                if (cnpj.length != 14) { return false; }
                if (cnpj == "00000000000000" ||
                    cnpj == "11111111111111" ||
                    cnpj == "22222222222222" ||
                    cnpj == "33333333333333" ||
                    cnpj == "44444444444444" ||
                    cnpj == "55555555555555" ||
                    cnpj == "66666666666666" ||
                    cnpj == "77777777777777" ||
                    cnpj == "88888888888888" ||
                    cnpj == "99999999999999") {
                    return false;
                }
                // Valida DVs
                tamanho = cnpj.length - 2
                numeros = cnpj.substring(0, tamanho);
                digitos = cnpj.substring(tamanho);
                soma = 0;
                pos = tamanho - 7;
                for (i = tamanho; i >= 1; i--) {
                    soma += numeros.charAt(tamanho - i) * pos--;
                    if (pos < 2) { pos = 9; }
                }
                resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
                if (resultado != digitos.charAt(0)) { return false; }
                tamanho = tamanho + 1;
                numeros = cnpj.substring(0,tamanho);
                soma = 0;
                pos = tamanho - 7;
                for (i = tamanho; i >= 1; i--) {
                    soma += numeros.charAt(tamanho - i) * pos--;
                    if (pos < 2) { pos = 9; }
                }
                resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
                if (resultado != digitos.charAt(1)) { return false; }

                $("#msgErroCpf").hide();
                $("#CpfCnpjComprador").removeClass("hasError");
                return true;
            }
        ');
    }

    private function wcsvl_compare_data() {
        wc_enqueue_js('
            function wcsvl_compareDatas(valCartao) {
                var hoje = new Date();
                var hoje = new Date(hoje.getMonth()+ "/01/" +hoje.getFullYear());
                var dadosData = valCartao.split("/");
                var validade = new Date(dadosData[0] + "/01/" + dadosData[1]);
                if (validade > hoje) {
                    if(valCartao.length<6){
                        $("#msgErroValidade").show();
                        $("#DataValidade").addClass("hasError");
                        return false;
                    }else{
                        $("#msgErroValidade").hide();
                        $("#DataValidade").removeClass("hasError");
                        return true;
                    }
                } else {
                    $("#msgErroValidade").show();
                    $("#DataValidade").addClass("hasError");
                    return false;
                }
            }
        ');
    }

    private function wcsvl_verifica_cpf_cnpj() {
        echo $this->wcsvl_valida_cpf();
        echo $this->wcsvl_valida_cnpj();
        wc_enqueue_js('
            setTimeout(function(){ 
                $("#CpfCnpjComprador").change(function() {
                    var cpfCnpj = $("#CpfCnpjComprador").val();
                    if (cpfCnpj != "") {
                        var cpfCnpj = cpfCnpj.replace(/[^\d]+/g,\'\');
                        if (cpfCnpj.length == 11) {
                            if (wcsvl_validarCPF(cpfCnpj) == false) {
                                $("#msgErroCpf").show();
                                $("#CpfCnpjComprador").addClass("hasError");
                                //$("#submit-serveloja-payment-form").hide();
                                //$("#submit-serveloja-payment-form-false").show();
                                /*  wcsvl_modal("erro", "Algo está errado...", "Informe um número de CPF válido", "", "bgModal_interno");
                                wcsvl_modal("erro", "Algo está errado...", "Informe um número de CPF válido", "", "checkoutTransparentFormInterno");
                                $("#submit-serveloja-payment-form").hide(); */
                            }
                        } else if (cpfCnpj.length == 14) {
                            if (wcsvl_validarCNPJ(cpfCnpj) == false) {
                                $("#msgErroCpf").show();
                                $("#CpfCnpjComprador").addClass("hasError");
                                //$("#submit-serveloja-payment-form").hide();
                                //$("#submit-serveloja-payment-form-false").show();
                                /* wcsvl_modal("erro", "Algo está errado...", "Informe um número de CNPJ válido", "", "bgModal_interno");
                                wcsvl_modal("erro", "Algo está errado...", "Informe um número de CNPJ válido", "", "checkoutTransparentFormInterno");
                                $("#submit-serveloja-payment-form").hide(); */
                            }
                        } else if (cpfCnpj.length != 11 || cpfCnpj.length != 14) {
                            $("#msgErroCpf").show();
                            $("#CpfCnpjComprador").addClass("hasError");
                            //$("#submit-serveloja-payment-form").hide();
                            //$("#submit-serveloja-payment-form-false").show();
                            /* wcsvl_modal("erro", "Algo está errado...", "Informe um número de CPF ou CNPJ válido", "", "bgModal_interno");
                            wcsvl_modal("erro", "Algo está errado...", "Informe um número de CPF ou CNPJ válido", "", "checkoutTransparentFormInterno");
                            $("#submit-serveloja-payment-form").hide(); */
                        }
                    }
                });
            }, 5001);
        ');
    }

    private function wcsvl_validade_cartao() {
        echo $this->wcsvl_compare_data();
        wc_enqueue_js('
            setTimeout(function(){ 
                $("#DataValidade").change(function() {
                    var DataValidade = $("#DataValidade").val();
                    if (DataValidade != "") {
                        if (wcsvl_compareDatas(DataValidade) == false) {
                            $("#msgErroValidade").show();
                            $("#DataValidade").addClass("hasError");
                            $("#submit-serveloja-payment-form").hide();
                            $("#submit-serveloja-payment-form-false").show();
                           /*  wcsvl_modal("erro", "Algo está errado...", "A data de validade do cartão <b>(" + DataValidade + ")</b>, aparentemente já experiou", "", "bgModal_interno");
                            wcsvl_modal("erro", "Algo está errado...", "A data de validade do cartão <b>(" + DataValidade + ")</b>, aparentemente já experiou", "", "checkoutTransparentFormInterno"); */
                        }
                    }
                });
            }, 5002);
        ');
    }

    
    //TODO bandeiras
    private function wcsvl_apiBandeiraCartao() {
        wc_enqueue_js('
            function wcsvl_apiBandeiraCartao(numeroCartao) {
                
                var str = String(numeroCartao);
                str = str.replace("-","");
                str = str.replace("-","");
                str = str.replace("-","");
                str = str.replace("-","");
                str = str.replace(" ","");
                str = str.replace(" ","");
                str = str.replace(" ","");
                str = str.replace(" ","");
                var regexMaster = /^5[1-5][0-9]{14}/;
                var regexVisa = /^4[0-9]{12}(?:[0-9]{3})/;
                var regexAmex = /^3[47][0-9]{13}/;
                var regexDiners = /^3(?:0[0-5]|[68][0-9])[0-9]{11}/;
                var regexElo = /^((((636368)|(438935)|(504175)|(451416)|(636297))\d{0,10})|((5067)|(4576)|(4011))\d{0,12})/;
                var regexHipercard = /^(606282\d{10}(\d{3})?)|(3841\d{15})/;
                var regexAssomise = /^639595|^608732/;
                var regexFortBrasil = /^628167/;
                var regexSorocred = /^627892|^606014|^636414|^9555[0-9]{2}/;
                var regexBanese = /^6361[0-9]{2}|^6374[0-9]{2}/;

                if(str.match(regexMaster)){
                    return "mastercard";
                }
                if(str.match(regexVisa)){
                    return "visa";
                }
                if(str.match(regexAmex)){
                    return "amex";
                }
                if(str.match(regexDiners)){
                    return "diners";
                }
                if(str.match(regexElo)){
                    return "elo";
                }
                if(str.match(regexHipercard)){
                    return "hipercard";
                }
                if(str.match(regexAssomise)){
                    return "assomise";
                }
                if(str.match(regexFortBrasil)){
                    return "fortbrasil";
                }
                if(str.match(regexSorocred)){
                    return "sorocred";
                }
                if(str.match(regexBanese)){
                    return "nenhuma";
                }
                             
                else{
                    return "nenhuma";
                }


            }
        ');
        
    }

    //TODO bandeiras
    private function wcsvl_obter_mascaras($total) {
        echo $this->wcsvl_mascaraValor();
        $cartoes_banco = WC_Serveloja_Funcoes::wcsvl_cartoes_salvos();
        wc_enqueue_js(' var cartoes = "cartoes"; ');
        foreach ($cartoes_banco as $row) {
            wc_enqueue_js(' cartoes = cartoes+","+"'.strtolower($row->car_parcelas).'"; ');
        }
        wc_enqueue_js('
            setTimeout(function(){ 
                $("#DataValidade").change(function() {
                    MascaraValidade(document.getElementById("DataValidade"));
                }); 
                $("#CodSeguranca").change(function() {
                    MascaraCCV(document.getElementById("CodSeguranca"));
                });
                $("#CpfCnpjComprador").change(function() {
                    MascaraCPF(document.getElementById("CpfCnpjComprador"));
                }); 
                $("#DDDCelular").change(function() {
                    MascaraDDD(document.getElementById("DDDCelular"));
                }); 
                $("#NrCelular").change(function() {
                    MascaraCelular(document.getElementById("NrCelular"));
                });
                $("#NrCartao").val("");
                $("#exibeBandeira").hide();
                $("#NrCartao").change(function() {
                    if(document.getElementById("NrCartao").value == ""){
                        var bandeiraCartao = cartoes.split(",");
                        for(var j=1; j<bandeiraCartao.length; j++){
                            var bandeira = bandeiraCartao[j].split("-");
                            $("#imagem-"+bandeira[1]).removeClass("imgBandeiraSelecionada");
                            $("#imagem-"+bandeira[1]).removeClass("opacidade");
                        }
                    }
                    MascaraCartao(document.getElementById("NrCartao"));
                    var numeroCartao = $("#NrCartao").val();
                    if (numeroCartao.length >= 1) {
                        var nomeBandeira = wcsvl_apiBandeiraCartao(numeroCartao);
                        if(nomeBandeira === "nenhuma"){
                            $("#NrCartao").addClass("hasError");
                            $("#msgErroCartao").show();
                            $("#exibeParcelas").hide();
                            $("#exibeSelectVazio").show();
                            var bandeiraCartao = cartoes.split(",");
                            for(var j=1; j<bandeiraCartao.length; j++){
                                var bandeira = bandeiraCartao[j].split("-");
                                $("#imagem-"+bandeira[1]).removeClass("imgBandeiraSelecionada");
                                $("#imagem-"+bandeira[1]).removeClass("opacidade");
                            }
                            var imagem = "<img class=\'imgBandeira\' src=\'' . plugins_url( 'assets/images/" + nomeBandeira.toLowerCase() + ".png', plugin_dir_path( __FILE__ )) . '\' alt=\'" + nomeBandeira.toLowerCase() + "\' />";
                            return false;
                        }
                        else{
                            $("#NrCartao").removeClass("hasError");
                            $("#msgErroCartao").hide();
                            var imagem = "<img class=\'imgBandeira\' src=\'' . plugins_url( 'assets/images/" + nomeBandeira.toLowerCase() + ".png', plugin_dir_path( __FILE__ )) . '\' alt=\'" + nomeBandeira.toLowerCase() + "\' />";
                            var cartao = cartoes.split(",");
                            for(var j=1; j<cartao.length; j++){
                                var bandeira = cartao[j].split("-");
                                $("#imagem-"+bandeira[1]).removeClass("imgBandeiraSelecionada");
                                $("#imagem-"+bandeira[1]).addClass("opacidade");
                                if(String(nomeBandeira).toLowerCase() === bandeira[1]){
                                    $("#imagem-"+bandeira[1]).addClass("imgBandeiraSelecionada");
                                    var totalParcelas = bandeira[0];
                                    var valor = ' . $total . ';
                                    var select = "<div class=\'coluna100\'>";
                                    select += "<div class=\'tituloInputFinalizarCompraLightbox margin_top\'>Selecione a quantidade de parcelas</div>";
                                    select += "<select class=\'selectFinalizarCompraLightbox\' id=\'QtParcela\' name=\'QtParcela\'>";
                                        for (var i = 1; i <= totalParcelas; i++) {
                                            if(i == 1){
                                                select += "<option value=\'" + i + "\'>" + i + "x - R$ " + wcsvl_mascaraValor(valor / i) + "</option>";
                                            }else if((valor/i) >= 5){
                                                select += "<option value=\'" + i + "\'>" + i + "x - R$ " + wcsvl_mascaraValor(valor / i) + "</option>";
                                            }
                                            
                                        }
                                    select += "</select>";
                                    select += "</div>";
                                    select += "<input type=\'hidden\' id=\'Bandeira\' name=\'Bandeira\' value=\'" + bandeira[1] + "\' />";
                                    select += "<input type=\'hidden\' id=\'Valor\' name=\'Valor\' value=\'" + wcsvl_mascaraValor(valor) + "\' />";
                                
                                    var input_senha = "";
                                    if (String(nomeBandeira).toLowerCase() == "assomise") {
                                        input_senha = "<div class=\'tituloInputFinalizarCompraLightbox margin_top\'>Senha do cartão</div>";
                                        input_senha += "<input type=\'password\' maxlength=\'8\' name=\'SenhaCartao\' class=\'exibeSenha\' id=\'SenhaCartao\' value=\'\' />";
                                        $("#senha").html(input_senha);
                                    } else {
                                        input_senha += "<input type=\'hidden\' name=\'SenhaCartao\' value=\'\' />";
                                        $("#senha").html(input_senha);
                                    }
                                
                                }
                            }

                            $("#exibeParcelas").show();
                            $("#exibeSelectVazio").hide();
                            $("#exibeParcelas").html(select);
                            
                        }
                        
                    }if(numeroCartao.length < 1){
                        var imagem = "<img class=\'imgBandeira\' src=\'' . plugins_url( 'assets/images/nenhuma.png', plugin_dir_path( __FILE__ )) . '\' alt=\'nenhuma\' />";
                        $("#exibeParcelas").hide();
                        $("#exibeSelectVazio").show();
                        $("#NrCartao").removeClass("hasError");
                        $("#msgErroCartao").hide();
                        //return true;
                    }

                    $("#exibeBandeira").show();
                    $("#exibeBandeira").html(imagem);
                    return true;

                });
            }, 4999);
        ');

    }
    
    private function wcsvl_lista_cartoes_cliente() {
        $cartoes_banco = WC_Serveloja_Funcoes::wcsvl_cartoes_salvos();
        $lista = "";
            if (count($cartoes_banco) > 0) {
                foreach ($cartoes_banco as $row) {
                    if('transparent' === $this->method){
                        $lista .= "<img id='imagem-". strtolower($row->car_bandeira) ."' class='imgBandeirasCheckout' src='" . plugins_url("assets/images/" . strtolower($row->car_bandeira) . ".png", plugin_dir_path( __FILE__ )) . "' title='" . ucfirst(strtolower($row->car_bandeira)) . "' alt='" . strtolower($row->car_bandeira) . "' />";
                    }else{
                        $lista .= "<img id='imagem-". strtolower($row->car_bandeira) ."' class='imgBandeiras' src='" . plugins_url("assets/images/" . strtolower($row->car_bandeira) . ".png", plugin_dir_path( __FILE__ )) . "' title='" . ucfirst(strtolower($row->car_bandeira)) . "' alt='" . strtolower($row->car_bandeira) . "' />";
                    }
                    
                } 
            } else {
                $lista .= "<div id='msgCartoesIndisponiveis'>Nenhuma bandeira está disponível para uso.</div>";
            }
        return $lista;
    }

    private function wcsvl_exibe_titulo_checkout() {
        $cartoes_banco = WC_Serveloja_Funcoes::wcsvl_cartoes_salvos();
        
        $lista = "<img id='logo_checkout' class='logoCheckout' src='" . plugins_url("assets/images/serveloja-preto.png", plugin_dir_path( __FILE__ )) . "'/>";
          
        // if (count($cartoes_banco) > 0) {
        //         foreach ($cartoes_banco as $row) {
        //              if((strtolower($row->car_bandeira) == "visa")||
        //              (strtolower($row->car_bandeira) == "mastercard")||
        //              (strtolower($row->car_bandeira) == "hiper")||
        //              (strtolower($row->car_bandeira) == "elo")){
        //                 $lista .= "<img id='imagem-checkout-". strtolower($row->car_bandeira) ."' class='imgBandeirasCheckout' src='" . plugins_url("assets/images/" . strtolower($row->car_bandeira) . ".png", plugin_dir_path( __FILE__ )) . "' title='" . ucfirst(strtolower($row->car_bandeira)) . "' alt='" . strtolower($row->car_bandeira) . "' />";
        //             }
        //         }
        //         $lista .= "...";
        // } 
         

        return $lista;
    }

   private function wcsvl_valida_campos() {
        wc_enqueue_js('
            $(document).ready(function() {
                $("#submit-serveloja-payment-form").hide();
                $("#submit-serveloja-payment-form-false").show();
                $("#nmTitular, #NrCartao, #DataValidade, #CodSeguranca, #colunaEsq, #DDDCelular, #NrCelular, #CpfCnpjComprador").live("mousemove", function() {
                    if($("#DataValidade").val().length > 1){
                        var validouData = wcsvl_compareDatas($("#DataValidade").val());
                    }
                    if($("#NrCartao").val().length > 1){
                        var nomeBandeira = wcsvl_apiBandeiraCartao($("#NrCartao").val());
                        if(nomeBandeira === "nenhuma"){
                            var validouCartao = false;
                        }else{
                            var validouCartao = true;
                        }
                    }

                    if (ValidaCelular(document.getElementById("NrCelular")) &&
                    ValidaDDD(document.getElementById("DDDCelular")) &&
                    ValidaCCV(document.getElementById("CodSeguranca")) &&
                    ValidaCartao(document.getElementById("NrCartao")) &&
                    ValidaNome(document.getElementById("nmTitular")) &&
                    (wcsvl_validarCPF ($("#CpfCnpjComprador").val()) ||
                    wcsvl_validarCNPJ ($("#CpfCnpjComprador").val())) && 
                    validouData && validouCartao
                    ) {
                        $("#submit-serveloja-payment-form").show();
                        $("#submit-serveloja-payment-form-false").hide();
                        $("#place_order").attr("disabled", false);
                    }else{
                        $("#submit-serveloja-payment-form").hide();
                        $("#submit-serveloja-payment-form-false").show();
                        $("#place_order").attr("disabled", true);
                    }
                    if ($("#nmTitular").val() == "" ||
                        $("#NrCartao").val() == "" ||
                        $("#DataValidade").val() == "" ||
                        $("#CodSeguranca").val() == "" ||
                        $("#DDDCelular").val() == "" ||
                        $("#NrCelular").val() == "" ||
                        $("#CpfCnpjComprador").val() == ""
                    ) {
                        $("#submit-serveloja-payment-form").hide();
                        $("#submit-serveloja-payment-form-false").show();
                        $("#place_order").attr("disabled", true);
                    } 
                });
            });
        ');
    } 

    private function wcsvl_cancelar($order) {
        wc_enqueue_js('
            $(document).ready(function() {
                $("#cancelar").live("click", function () {
                    $("#finalizarCompraModal").fadeOut();
                    $("#cancelar").fadeOut();
                    wcsvl_modalCancelar("' . get_option('sair') . esc_url($order->get_cancel_order_url()) . '"); //wc_get_page_permalink( "myaccount" ) 
                });
            });
        ');
    }

    public function wcsvl_apl_authorization() {
        $authorization = (WC_Serveloja_Funcoes::wcsvl_aplicacao() == "0") ? "" : WC_Serveloja_Funcoes::wcsvl_aplicacao()[0]->apl_token;
        $applicatioId = (WC_Serveloja_Funcoes::wcsvl_aplicacao() == "0") ? "" : WC_Serveloja_Funcoes::wcsvl_aplicacao()[0]->apl_nome;
        if(WC_Serveloja_API::wcsvl_metodos_get('Cartao/ObterBandeirasValidas', "", $authorization, $applicatioId) == false){
            return false;
        }else{
            return $authorization;
        }
    }

    public function wcsvl_apl_applicatioId() {
        $applicatioId = (WC_Serveloja_Funcoes::wcsvl_aplicacao() == "0") ? "" : WC_Serveloja_Funcoes::wcsvl_aplicacao()[0]->apl_nome;
        return $applicatioId;
    }

    private function wcsvl_form_payment_lightbox($order_id, $https) {
        $order = wc_get_order($order_id);
        echo $this->wcsvl_modal();
        echo $this->wcsvl_mascarasValidacao();
        echo $this->wcsvl_cancelar($order);
        if (($this->wcsvl_apl_authorization() != false)&&($https)) {
            $nmTitular = isset($_POST['nmTitular']) ? sanitize_text_field($_POST['nmTitular']) : '';
            $NrCartao = isset($_POST['NrCartao']) ? sanitize_text_field($_POST['NrCartao']) : '';
            $DataValidade = isset($_POST['DataValidade']) ? sanitize_text_field($_POST['DataValidade']) : '';
            $CpfCnpjComprador = isset($_POST['CpfCnpjComprador']) ? sanitize_text_field($_POST['CpfCnpjComprador']) : '';
            $CodSeguranca = isset($_POST['CodSeguranca']) ? sanitize_text_field($_POST['CodSeguranca']) : '';
            $DDDCelular = isset($_POST['DDDCelular']) ? sanitize_text_field($_POST['DDDCelular']) : '';
            $NrCelular = isset($_POST['NrCelular']) ? sanitize_text_field($_POST['NrCelular']) : '';
            // form
            wc_enqueue_js('
            $(document).ready(function() {

                $("#NrCartao").keypress(function() {
                    MascaraCartao(document.getElementById("NrCartao"));
                }); 

                $("#DataValidade").keypress(function() {
                    MascaraValidade(document.getElementById("DataValidade"));
                }); 
                $("#CodSeguranca").keypress(function() {
                    MascaraCCV(document.getElementById("CodSeguranca"));
                });
                $("#CpfCnpjComprador").keypress(function() {
                    MascaraCPF(document.getElementById("CpfCnpjComprador"));
                }); 
                $("#DDDCelular").keypress(function() {
                    MascaraDDD(document.getElementById("DDDCelular"));
                }); 
                $("#NrCelular").keypress(function() {
                    MascaraCelular(document.getElementById("NrCelular"));
                });

                $("#nmTitular").blur(function() {
                    ValidaNome(document.getElementById("nmTitular"));
                }); 
                $("#CodSeguranca").blur(function() {
                    ValidaCCV(document.getElementById("CodSeguranca"));
                });
                $("#DDDCelular").blur(function() {
                    ValidaDDD(document.getElementById("DDDCelular"));
                });
                $("#NrCelular").blur(function() {
                    ValidaCelular(document.getElementById("NrCelular"));
                });  
            });
            ');
            $retorno = '
            "<link rel=\'stylesheet\' type=\'text/css\' href=\'//fonts.googleapis.com/css?family=Nunito\' />"+
            "<div id=\'formularioLightbox\' class=\'formularioLightbox\' ondragstart=\'return false;\' ondrop=\'return false;\'>" +
                "<form method=\'POST\' action=\'\' name=\'dados_pagamento\'>" +
                    "<input type=\'hidden\' name=\'_nonce_payment\' value=\'' . wp_create_nonce('payment_user') . '\' />" +

                    "<div id=\'exibeFormulario\'>" +
                        "<div class=\'clear\'></div>" +
                        
                        "<div class=\'coluna100\'>" +
                            "<div class=\'tituloInputFinalizarCompraLightbox margin_top\'>Número do cartão</div>" +
                            "<span>"+
                                "<input required type=\'tel\' name=\'NrCartao\' maxlength=\'19\' class=\'inputFormFinalizarCompraLightbox\' id=\'NrCartao\' value=\'' . $NrCartao . '\' />" +
                                "<div id=\'exibeBandeira\'></div>" +
                            "</span>" +
                            "<div id=\'msgErroCartao\' class=\'tituloInputFinalizarCompraLightbox vermelho\'>Cartão inválido.</div>" +
                        "</div>" +

                        "<div class=\'coluna100\'>" +
                            "<div class=\'tituloInputFinalizarCompraLightbox margin_top\'>Nome do titular impresso no cartão</div>" +
                            "<input required type=\'text\' name=\'nmTitular\' maxlength=\'100\' class=\'inputFormFinalizarCompraLightbox caixa_alta\' id=\'nmTitular\' value=\'' . $nmTitular . '\' />" +
                            "<div id=\'msgErroNome\' class=\'tituloInputFinalizarCompraLightbox vermelho\'>Nome inválido.</div>" +
                        "</div>" +

                        "<div class=\'coluna50_left\'>" +
                            "<div class=\'tituloInputFinalizarCompraLightbox margin_top\'>Validade</div>" +
                            "<input required type=\'tel\' name=\'DataValidade\' maxlength=\'7\' class=\'inputFormFinalizarCompraLightbox\' placeholder=\'MM/AAAA\' id=\'DataValidade\' value=\'' . $DataValidade . '\' />" +
                            "<div id=\'msgErroValidade\' class=\'tituloInputFinalizarCompraLightbox vermelho\'>Data inválida.</div>" +
                        "</div>" +

                        "<div class=\'coluna50_right\'>" +
                            "<div class=\'tituloInputFinalizarCompraLightbox margin_top\'>CCV</div>" +
                            "<input required type=\'tel\' name=\'CodSeguranca\' maxlength=\'4\' class=\'inputFormFinalizarCompraLightbox\' id=\'CodSeguranca\' maxlength=\'4\' value=\'' . $CodSeguranca . '\' />" +
                            "<div id=\'msgErroCCV\' class=\'tituloInputFinalizarCompraLightbox vermelho\'>CCV inválido.</div>" +
                        "</div>" +
                        "<div class=\'clear\'></div>" +

                        "<div class=\'coluna100\'>" +
                            "<div class=\'inputLightbox\' id=\'senha\'></div>" +
                        "</div>" +

                        "<div id=\'exibeParcelas\'></div>" +

                        "<div id=\'exibeSelectVazio\' style=\'opacity: 0.3;\'>" +
                            "<div class=\'coluna100\'>"+
                                "<div class=\'tituloInputFinalizarCompraLightbox margin_top\'>Selecione a quantidade de parcelas</div>"+
                                "<select disabled class=\'selectFinalizarCompraLightbox\' id=\'QtParcela\' name=\'QtParcela\'>"+
                                    "<option value=\'mensagem\'>Selecionar</option>"+
                                "</select>"+
                            "</div>"+
                        "</div>"+

                        "<div class=\'coluna100\'>" +
                            "<div class=\'tituloInputFinalizarCompraLightbox margin_top\'>CPF ou CNPJ do comprador</div>" +
                            "<input required type=\'tel\' name=\'CpfCnpjComprador\' maxlength=\'18\' class=\'inputFormFinalizarCompraLightbox\' id=\'CpfCnpjComprador\' value=\'' . $CpfCnpjComprador . '\' />" +
                            "<div id=\'msgErroCpf\' class=\'tituloInputFinalizarCompraLightbox\'>CPF/CNPJ inválido.</div>" +
                        "</div>"+
                        
                        "<div class=\'coluna25_left\'>" +
                            "<div class=\'tituloInputFinalizarCompraLightbox margin_top\'>DDD</div>" +
                            "<input required type=\'tel\' name=\'DDDCelular\' maxlength=\'3\' class=\'inputFormFinalizarCompraLightbox\' id=\'DDDCelular\' maxlength=\'2\' value=\'' . $DDDCelular . '\' />" +
                            "<div id=\'msgErroDDD\' class=\'tituloInputFinalizarCompraLightbox vermelho\'>DDD inválido.</div>" +
                        "</div>" +
                        
                        "<div class=\'coluna75\'>" +
                            "<div class=\'tituloInputFinalizarCompraLightbox margin_top\'>Celular</div>" +
                            "<input required type=\'tel\' name=\'NrCelular\' maxlength=\'10\' class=\'inputFormFinalizarCompraLightbox\' id=\'NrCelular\' value=\'' . $NrCelular . '\' />" +
                            "<div id=\'msgErroCelular\' class=\'tituloInputFinalizarCompraLightbox vermelho\'>Número inválido.</div>" +
                        "</div>" +

                        "<br />" +
                        "<input type=\'submit\' id=\'submit-serveloja-payment-form\' name=\'finalizar\' value=\'Finalizar pagamento\'/>" +
                        "<input type=\'submit\' disabled id=\'submit-serveloja-payment-form-false\' name=\'finalizarFalso\' value=\'Finalizar pagamento\'/>" +
                    "</div>" +
                "</form>" +
                "<div class=\'coluna100\'>" +
                        "<div class=\'exibeImagensBandeirasDisponiveis\'>"+
                            "' . $this->wcsvl_lista_cartoes_cliente() . '" +
                        "</div>" +
                    "</div>" +
                /*"<input type=\'submit\' id=\'sucesso\' name=\'finalizarTeste\' value=\'Finalizar Pagamento Teste\'/>" + 
                */
            "</div>"';
        } else {
            
            $retorno = '"<div class=\'erro\'>"+
            "Falha ao validar as credenciais da aplicação.</br>"+
            "</br>O pagamento via Serveloja ainda não está liberado. Entre em contato com o proprietário da loja.</div>"';
            
        }
        return $retorno;
    }

    private function wcsvl_modal_payment($order_id, $https) {
        $order = wc_get_order($order_id);
        echo $this->wcsvl_cpf_cnpj();
        echo $this->wcsvl_detalhes_cartao($order->get_total());
        echo $this->wcsvl_modal();
        echo $this->wcsvl_verifica_cpf_cnpj();
        echo $this->wcsvl_validade_cartao();
        echo $this->wcsvl_obter_mascaras($order->get_total()); 
        echo $this->wcsvl_apiBandeiraCartao(); 
        echo $this->wcsvl_valida_campos();
        echo $this->wcsvl_cancelar($order);
        wc_enqueue_js('
            $("#bgModal").fadeIn();
            var reply = "";
            reply += 
            "<div id=\'cancelar\' title=\'Cancelar e voltar para o carrinho\'>" +
            "</div>" +
            "<div id=\'bgModal_interno\'></div>" +
            "<div id=\'finalizarCompraModal\' class=\'sombra\'>" +
                "<div class=\'cabecalhoFinalizarCompraModal\' id=\'cabecalho_pagamento\'>" +
                    "<div id=\'logo\'><img src=\'' . plugins_url( 'assets/images/serveloja-preto.png', plugin_dir_path( __FILE__ )) . '\' alt=\'serveloja\' /></div>" +
                    "<div class=\'valorTotalFinalizarCompraLightbox\' id=\'valor_total\'><label class=\'textoValorTotal\'>Valor total: </label>R$ " + wcsvl_mascaraValor(' . $order->get_total() . ') + "</div>" +
                "</div>" +
                "<div class=\'clear\'></div>" +
                    ' . $this->wcsvl_form_payment_lightbox($order_id, $https) . ' + 
                "</div>" +
            "</div>";
            $("#bgModal").html(reply);
        ');
    }
    
    private function wcsvl_form_payment_checkout_inpage($order_id, $https) {
        $order = wc_get_order($order_id);
        echo $this->wcsvl_cpf_cnpj();
        echo $this->wcsvl_detalhes_cartao($this->get_order_total());//$order->get_total()
        echo $this->wcsvl_mascarasValidacao();
        echo $this->wcsvl_modal();
        echo $this->wcsvl_verifica_cpf_cnpj();
        echo $this->wcsvl_validade_cartao();
        echo $this->wcsvl_obter_mascaras($this->get_order_total()); 
        echo $this->wcsvl_apiBandeiraCartao(); 
        echo $this->wcsvl_valida_campos();
        echo $this->wcsvl_mascarasValidacao();
        
        if (($this->wcsvl_apl_authorization() != false)&&($https)) {
            $nmTitular = isset($_POST['nmTitular']) ? sanitize_text_field($_POST['nmTitular']) : '';
            $NrCartao = isset($_POST['NrCartao']) ? sanitize_text_field($_POST['NrCartao']) : '';
            $DataValidade = isset($_POST['DataValidade']) ? sanitize_text_field($_POST['DataValidade']) : '';
            $CpfCnpjComprador = isset($_POST['CpfCnpjComprador']) ? sanitize_text_field($_POST['CpfCnpjComprador']) : '';
            $CodSeguranca = isset($_POST['CodSeguranca']) ? sanitize_text_field($_POST['CodSeguranca']) : '';
            $DDDCelular = isset($_POST['DDDCelular']) ? sanitize_text_field($_POST['DDDCelular']) : '';
            $NrCelular = isset($_POST['NrCelular']) ? sanitize_text_field($_POST['NrCelular']) : '';
            // form
            wc_enqueue_js('
            setTimeout(function(){ 
                $("#NrCartao").keypress(function() {
                    MascaraCartao(document.getElementById("NrCartao"));
                }); 
                $("#DataValidade").keypress(function() {
                    MascaraValidade(document.getElementById("DataValidade"));
                }); 
                $("#CodSeguranca").keypress(function() {
                    MascaraCCV(document.getElementById("CodSeguranca"));
                });
                $("#CpfCnpjComprador").keypress(function() {
                    MascaraCPF(document.getElementById("CpfCnpjComprador"));
                }); 
                $("#DDDCelular").keypress(function() {
                    MascaraDDD(document.getElementById("DDDCelular"));
                }); 
                $("#NrCelular").keypress(function() {
                    MascaraCelular(document.getElementById("NrCelular"));
                });
                $("#nmTitular").blur(function() {
                    ValidaNome(document.getElementById("nmTitular"));
                }); 
                $("#CodSeguranca").blur(function() {
                    ValidaCCV(document.getElementById("CodSeguranca"));
                });
                $("#DDDCelular").blur(function() {
                    ValidaDDD(document.getElementById("DDDCelular"));
                });
                $("#NrCelular").blur(function() {
                    ValidaCelular(document.getElementById("NrCelular"));
                });

                if($( "#payment_method_serveloja" ).is( ":checked" )){
                    $("#place_order").attr("disabled", true);
                }else{
                    $("#place_order").attr("disabled", false);
                }

                $(".input-radio").change(function() {
                    if($( "#payment_method_serveloja" ).is( ":checked" )){
                        $("#place_order").attr("disabled", true);
                    }else{
                        $("#place_order").attr("disabled", false);
                    }
                });
                $("#dropdownBandeiras").click(function() {
                    if($("#mostrarBandeiras").is(":visible")){
                        $("#mostrarBandeiras").fadeOut();
                    }else{
                        $("#mostrarBandeiras").fadeIn();
                    }
                }); 

            },5000);
            ');
           
            $retorno = '
            <div class=\'formFinalizarCompraCheckout \' ondragstart=\'return false;\' ondrop=\'return false;\'>
                    <input type=\'hidden\' name=\'_nonce_payment\' value=\'' . wp_create_nonce('payment_user') . '\' />
                    <div class=\'clear\'></div>
                    
                    <div class=\'coluna100\'>
                        <a id=\'dropdownBandeiras\' class=\'dropdownFinalizarCompraLightbox margin_top\'>Quais cartões posso usar nesta compra? ⌵</a>
                        <div id=\'mostrarBandeiras\' class=\'exibeImagensBandeirasDisponiveis\'>
                            ' . $this->wcsvl_lista_cartoes_cliente() . '
                        </div>
                    </div>

                    <div id=\'exibeFormulario\'>

                        <div class=\'coluna100\'>

                            <div class=\'colunaDinamica_left\'>
                                <div class=\'tituloInputFinalizarCompraLightbox margin_top\'>Número do cartão</div>
                                    <input required type=\'tel\' name=\'NrCartao\' maxlength=\'19\' class=\'inputFormFinalizarCompraCheckout coluna100\' id=\'NrCartao\' value=\'' . $NrCartao . '\' />
                                    <div style=\'margin-top: -20px;\' id=\'exibeBandeira\'></div>
                                    <div id=\'msgErroCartao\' class=\'tituloInputFinalizarCompraLightbox vermelho coluna100\'>Cartão inválido.</div>
                                
                            </div>

                            <div class=\'colunaDinamica_right\'>
                                <div class=\'tituloInputFinalizarCompraLightbox margin_top\'>Nome do titular impresso no cartão</div>
                                <input required type=\'text\' name=\'nmTitular\' maxlength=\'100\' class=\'inputFormFinalizarCompraCheckout caixa_alta coluna100\' id=\'nmTitular\' value=\'' . $nmTitular . '\' />
                                <div id=\'msgErroNome\' class=\'tituloInputFinalizarCompraLightbox vermelho coluna100\'>Nome inválido.</div>
                            </div>

                        </div>

                        <div class=\'coluna100\'>

                            <div class=\'colunaDinamica_left\'>
                                <div class=\'coluna50_left\'>
                                    <div class=\'tituloInputFinalizarCompraLightbox margin_top\'>Validade</div>
                                    <input required type=\'tel\' name=\'DataValidade\' maxlength=\'7\' class=\'inputFormFinalizarCompraCheckout coluna100\' placeholder=\'MM/AAAA\' id=\'DataValidade\' value=\'' . $DataValidade . '\' />
                                    <div id=\'msgErroValidade\' class=\'tituloInputFinalizarCompraLightbox vermelho coluna100\'>Data inválida.</div>
                                </div>    
                                <div class=\'coluna50_right\'>
                                    <div class=\'tituloInputFinalizarCompraLightbox margin_top\'>CCV</div>
                                    <input required type=\'tel\' name=\'CodSeguranca\' maxlength=\'4\' class=\'inputFormFinalizarCompraCheckout coluna100\' id=\'CodSeguranca\' maxlength=\'4\' value=\'' . $CodSeguranca . '\' />
                                    <div id=\'msgErroCCV\' class=\'tituloInputFinalizarCompraLightbox vermelho coluna100\'>CCV inválido.</div>
                                </div>
                            </div>
                            
                            <div id=\'exibeParcelas\' class=\'colunaDinamica_right\'></div>

                            <div id=\'exibeSelectVazio\' style=\'opacity: 0.3;\'>
                                <div class=\'colunaDinamica_right\'>
                                    <div class=\'tituloInputFinalizarCompraLightbox margin_top\'>Selecione a quantidade de parcelas</div>
                                    <select disabled class=\'selectFinalizarCompraLightbox\' id=\'QtParcela\' name=\'QtParcela\'>
                                        <option value=\'mensagem\'>Selecionar</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class=\'coluna100\'>
                        
                            <div class=\'colunaDinamica_left\'>
                                <div class=\'tituloInputFinalizarCompraLightbox margin_top\'>CPF ou CNPJ do comprador</div>
                                <input required type=\'tel\' name=\'CpfCnpjComprador\' maxlength=\'18\' class=\'inputFormFinalizarCompraCheckout coluna100\' id=\'CpfCnpjComprador\' value=\'' . $CpfCnpjComprador . '\' />
                                <div id=\'msgErroCpf\' class=\'tituloInputFinalizarCompraLightbox vermelho coluna100\'>CPF/CNPJ inválido.</div>
                            </div>
                            
                            <div class=\'colunaDinamica_right\'>
                                <div class=\'coluna23\'>
                                    <div class=\'tituloInputFinalizarCompraLightbox margin_top\'>DDD</div>
                                    <input required type=\'tel\' name=\'DDDCelular\' maxlength=\'3\' class=\'inputFormFinalizarCompraCheckout coluna100\' id=\'DDDCelular\' maxlength=\'2\' value=\'' . $DDDCelular . '\' />
                                    <div id=\'msgErroDDD\' class=\'tituloInputFinalizarCompraLightbox vermelho coluna100\'>DDD inválido.</div>
                                </div>
                                <div class=\'coluna75\'>
                                    <div class=\'tituloInputFinalizarCompraLightbox margin_top\'>Celular</div>
                                    <input required="required" type=\'tel\' name=\'NrCelular\' maxlength=\'10\' class=\'inputFormFinalizarCompraCheckout coluna100\' id=\'NrCelular\' value=\'' . $NrCelular . '\' />
                                    <div id=\'msgErroCelular\' class=\'tituloInputFinalizarCompraLightbox vermelho coluna100\'>Número inválido.</div>
                                </div>
                            </div>

                        </div>

                        <div class=\'colunaDinamica_left\'>
                            <div class=\'inputCheckout\' id=\'senha\'></div>
                        </div>
                        <br/>
                    </div>
                <p style=\'margin: 0px; color: white\'>.</p>
            </div>';
        } else {
            $retorno = '<div class=\'formFinalizarCompraCheckout\'><div class=\'erro\'>';
            if (!($https)) {
                $retorno .= 'Erro de ativação de protocolo HTTPS.</br>';
            }
            if (($this->wcsvl_apl_authorization() == false)) {
                $retorno .= 'Erro ao validar as credenciais da aplicação.</br>';
            }
            $retorno .= '</br>O pagamento via Serveloja ainda não está liberado. Entre em contato com o proprietário da loja.</div>
            </div>';
        }
        return $retorno;
    }

    public function wcsvl_generate_serveloja_form($order_id) {
        global $woocommerce;
        $order = wc_get_order($order_id);
        if ( 'transparent' === $this->method ) {
            
            if($_SERVER['HTTPS'] == "on"){
                return $this->wcsvl_form_payment_checkout_inpage($order_id, true);
            }else{
                return $this->wcsvl_form_payment_checkout_inpage($order_id, false);
            }
            
        }else{
            if($_SERVER['HTTPS'] == "on"){
                echo $this->wcsvl_modal_payment($order_id, true);
                return '<div id="bgModal"></div>';
            }else{
                echo $this->wcsvl_modal_payment($order_id, false);
                return '<div id="bgModal"></div>';
            }
            
        }

        
       
    }

    // processa pagamento
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);

        if ( 'transparent' == $this->method ) {

            if (wp_verify_nonce($_POST['_nonce_payment'], 'payment_user')) {

                $dados = array(
                    "Bandeira"         => strtoupper(sanitize_text_field($_POST['Bandeira'])),
                    "CpfCnpjComprador" => preg_replace("/[^0-9]/", "", sanitize_text_field($_POST['CpfCnpjComprador'])),
                    "nmTitular"        => strtoupper(sanitize_text_field($_POST['nmTitular'])),
                    "NrCartao"         => preg_replace("/[^0-9]/", "", sanitize_text_field($_POST['NrCartao'])),
                    "CodSeguranca"     => sanitize_text_field($_POST['CodSeguranca']),
                    "DataValidade"     => sanitize_text_field($_POST['DataValidade']),
                    "Valor"            => sanitize_text_field($_POST['Valor']),
                    "QtParcela"        => sanitize_text_field($_POST['QtParcela']),
                    "SenhaCartao"      => sanitize_text_field($_POST['SenhaCartao']),
                    "DDDCelular"       => sanitize_text_field($_POST['DDDCelular']),
                    "NrCelular"        => preg_replace("/[^0-9]/", "", sanitize_text_field($_POST['NrCelular'])),
                    "DsObservacao"     => "Venda de produtos na loja utilizando WooCommerce Serveloja. Número do pedido: #" . $order->get_order_number()
                );

                wc_enqueue_js('
                    var nomeModalSucesso = "'. $dados['nmTitular'] .'";
                    var valorModalSucesso = "'. $dados['Valor'] .'";
                    var numeroCartaoModalSucesso = "'. $dados['NrCartao'] .'";
                    var bandeiraCartaoModalSucesso = "'. $dados['Bandeira'] .'";
                    var parcelaModalSucesso = "'. $dados['QtParcela'] .'";
                    var codigoModalSucesso = "'. $order->get_order_number() .'";
                ');

                // envia dados via API
                $resposta = WC_Serveloja_API::wcsvl_metodos_post('Vendas/EfetuarVendaCredito', $dados, $this->wcsvl_apl_authorization(), $this->wcsvl_apl_applicatioId());
                $resultado = json_decode($resposta["body"], true);

                if ($resultado['HttpStatusCode'] == 200) {
                    // adiciona status na loja
                    $order->update_status('completed', __('Pagamento realizado com cartão ' . strtoupper(sanitize_text_field($_POST['Bandeira'])) . ' através do WooCommerce Serveloja. Código da transação: ' . $resultado['Container'] . '.', 'woocommerce-serveloja' ));
                    return array(
                        'result'   => 'success',
                        'redirect' => $this->get_return_url($order)
                    );    
                }else{
                    $message = $resultado["Mensagem"];
                    wc_add_notice(  $message,  'error' ); 
                    return array(
                        'result'   => 'fail'
                    );    
                }

            }
        }else{
            return array(
                'result'   => 'success',
                'redirect' => $order->get_checkout_payment_url(true)
            );
        }

    }

    public function receipt_page($order_id) {
        global $woocommerce;
        $order = wc_get_order( $order_id );
        if ( 'transparent' != $this->method ) {
            echo $this->wcsvl_generate_serveloja_form($order_id); 

            if (isset($_POST['finalizar'])) {
                if (wp_verify_nonce($_POST['_nonce_payment'], 'payment_user')) {
                    $dados = array(
                        "Bandeira"         => strtoupper(sanitize_text_field($_POST['Bandeira'])),
                        "CpfCnpjComprador" => preg_replace("/[^0-9]/", "", sanitize_text_field($_POST['CpfCnpjComprador'])),
                        "nmTitular"        => strtoupper(sanitize_text_field($_POST['nmTitular'])),
                        "NrCartao"         => preg_replace("/[^0-9]/", "", sanitize_text_field($_POST['NrCartao'])),
                        "CodSeguranca"     => sanitize_text_field($_POST['CodSeguranca']),
                        "DataValidade"     => sanitize_text_field($_POST['DataValidade']),
                        "Valor"            => sanitize_text_field($_POST['Valor']),
                        "QtParcela"        => sanitize_text_field($_POST['QtParcela']),
                        "SenhaCartao"      => sanitize_text_field($_POST['SenhaCartao']),
                        "DDDCelular"       => sanitize_text_field($_POST['DDDCelular']),
                        "NrCelular"        => preg_replace("/[^0-9]/", "", sanitize_text_field($_POST['NrCelular'])),
                        "DsObservacao"     => "Venda de produtos na loja utilizando WooCommerce Serveloja. Número do pedido: #" . $order->get_order_number()
                    );

                    wc_enqueue_js('
                                var nomeModalSucesso = "'. $dados['nmTitular'] .'";
                                var valorModalSucesso = "'. $dados['Valor'] .'";
                                var numeroCartaoModalSucesso = "'. $dados['NrCartao'] .'";
                                var bandeiraCartaoModalSucesso = "'. $dados['Bandeira'] .'";
                                var parcelaModalSucesso = "'. $dados['QtParcela'] .'";
                                var codigoModalSucesso = "'. $order->get_order_number() .'";
                    ');

                    // envia dados via API
                    $resposta = WC_Serveloja_API::wcsvl_metodos_post('Vendas/EfetuarVendaCredito', $dados, $this->wcsvl_apl_authorization(), $this->wcsvl_apl_applicatioId());
                    $resultado = json_decode($resposta["body"], true);

                    if ($resultado['HttpStatusCode'] == 200) {
                        // adiciona status na loja
                        $order->update_status('completed', __('Pagamento realizado com cartão ' . strtoupper(sanitize_text_field($_POST['Bandeira'])) . ' através do WooCommerce Serveloja. Código da transação: ' . $resultado['Container'] . '.', 'woocommerce-serveloja' ));
                        // reduz estoque, se houver
                        $order->reduce_order_stock();
                        // limpa carrinho
                        $woocommerce->cart->empty_cart();
                            wc_enqueue_js('
                            $(document).ready(function () {
                                $("#formPagamentoModal").hide();
                                wcsvl_modalSucesso("' . get_option('home') . esc_url('index.php/loja') . '", "bgModal", nomeModalSucesso, valorModalSucesso, bandeiraCartaoModalSucesso, numeroCartaoModalSucesso, parcelaModalSucesso, codigoModalSucesso);
                            });
                        ');
                            
                    } else {
                        wc_enqueue_js('
                            $(document).ready(function () {
                                wcsvl_modal("erro", "Algo está errado...", "' . trim(preg_replace('/\s\s+/', ' ', $resultado['Mensagem'])) . '", "", "bgModal_interno");
                            });
                        ');
                    }
                } else {
                    wc_enqueue_js('
                        $(document).ready(function () {
                            wcsvl_modal("erro", "Algo está errado...", "Não foi possível verificar o código de autorização. Tente novamente", "", "bgModal_interno");
                        });
                    ');
                }
             }if(isset($_POST['cancelarSim'])) {
                wc_enqueue_js('
                    $("#bgModal_interno").html("");
                    $("#bgModal_interno").hide();
                    jQuery(window.document.location).attr("href", "' . get_option('home') . '");
                ');
                wp_delete_post($order_id,true);
            }//get_option('sair') . esc_url($order->get_cancel_order_url())

        }


    }

} ?>