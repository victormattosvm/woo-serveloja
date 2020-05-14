<?php
/**
 * WooCommerce Serveloja Gateway class.
 *
 * Funções utilizadas em WooCommerce Serveloja.
 *
 * @class   WC_Serveloja_Funcoes
 * @version 2.7.0
 * @author  TiServeloja
 */

if (!defined( 'ABSPATH' )) {
    exit;
}

class WC_Serveloja_Funcoes {

    // verifica se token informado por usuário é válido
    private static function wcsvl_valida_token($url, $method, $param) {
        return WC_Serveloja_Api::wcsvl_metodos_acesso_api($url, $method, $param);
    }

    // fecha div via javascript após alguns segundos
    private static function wcsvl_script($div) {
        return '<script type="text/javascript">Fecha_mensagem("' . $div . '");</script>';
    }

    // exibe a mensagem e classe conforme setado
    private static function wcsvl_div_resposta($class, $titulo, $mensagem) {
        return array("class" => $class, "titulo" => $titulo, "mensagem" => $mensagem);
    }

    // ações no banco para aplicação
    private static function wcsvl_insert_aplicacao($apl_nome, $apl_token_teste, $apl_op_teste, $apl_token_producao, $apl_token, $apl_prefixo, $apl_email) {
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . "serveloja_aplicacao",
            array('apl_nome' => $apl_nome,
                    'apl_token_teste' => $apl_token_teste,
                    'apl_op_teste' => $apl_op_teste,
                    'apl_token_producao' => $apl_token_producao,
                    'apl_token' => $apl_token,
                    'apl_prefixo' => $apl_prefixo,
                    'apl_email' => $apl_email
            ),
            array('%s', '%s', '%s', '%s', '%s')
        );
        if ($wpdb->last_error) {
            return WC_Serveloja_Funcoes::wcsvl_div_resposta("erro", "Ocorreram falhas", "Erro: " . $wpdb->last_error);
        } else {
            return WC_Serveloja_Funcoes::wcsvl_div_resposta("sucesso", "Tudo certo!", "Os dados foram adicionados com sucesso");
        }
    }

    private static function wcsvl_update_aplicacao($apl_nome, $apl_token_teste, $apl_op_teste, $apl_token_producao, $apl_token, $apl_prefixo, $apl_email, $apl_id) {
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . "serveloja_aplicacao",
            array('apl_nome' => $apl_nome,
                    'apl_token_teste' => $apl_token_teste,
                    'apl_op_teste' => $apl_op_teste,
                    'apl_token_producao' => $apl_token_producao,
                    'apl_token' => $apl_token,
                    'apl_prefixo' => $apl_prefixo,
                    'apl_email' => $apl_email
            ),
            array('apl_id' => $apl_id),
            array('%s', '%s', '%s', '%s', '%s'),
            array('%s')
        );
        if ($wpdb->last_error) {
            return WC_Serveloja_Funcoes::wcsvl_div_resposta("erro", "Ocorreram falhas", "Ocorreu um erro: " . $wpdb->last_error);
        } else {
            return WC_Serveloja_Funcoes::wcsvl_div_resposta("sucesso", "Tudo certo!", "Os dados foram modificados com sucesso");
        }
    }

    // salva dados aplicação
    public static function wcsvl_save_configuracoes($apl_nome, $apl_token_teste, $apl_op_teste, $apl_token_producao, $apl_token, $apl_prefixo, $apl_email, $apl_id, $nonce) {
        global $reg_errors;
        $reg_errors = new WP_Error;

        // valida campos
        if (empty($apl_nome)) {
            $reg_errors->add("nome-vazio", "Você precisa informar o <b>Nome da Aplicação</b> da aplicação antes de prosseguir");
        }
        if (empty($apl_token)) {
            $reg_errors->add("token-vazio", "Você precisa informar o <b>Token da Aplicação</b> da aplicação antes de prosseguir");
        }
        if (!is_email($apl_email)) {
            $reg_errors->add("email-invalido", "O e-mail informado não é válido");
        }
        if (!wp_verify_nonce($nonce, "config_user")) {
            $reg_errors->add("cod-invalido", "O código de verificação é inválido. Operação não concluída");
        }

        // processamento e retorno
        $retorno = array();
        if (is_wp_error($reg_errors)) {
            if (count($reg_errors->get_error_messages()) > 0) {
                $class = "erro";
                $titulo = "Antes de prosseguir, você precisa resolver os seguintes problemas:";
                $mensagem = "";
                for ($i = 0; $i < count($reg_errors->get_error_messages()); $i++) {
                    $mensagem .= "<b>" . ($i + 1) . ")</b> " . $reg_errors->get_error_messages()[$i] . "<br />";
                }
                return array("class" => $class, "titulo" => $titulo, "mensagem" => $mensagem);
            } else {
                if ($apl_id == 0) {
                    return WC_Serveloja_Funcoes::wcsvl_insert_aplicacao($apl_nome, $apl_token_teste, $apl_op_teste, $apl_token_producao, $apl_token, $apl_prefixo, $apl_email);
                } else {
                    return WC_Serveloja_Funcoes::wcsvl_update_aplicacao($apl_nome, $apl_token_teste, $apl_op_teste, $apl_token_producao, $apl_token, $apl_prefixo, $apl_email, $apl_id);
                }
            }
        }
    }

    // lista dados da aplicação
    public static function wcsvl_aplicacao() {
        global $wpdb;
        $rows = $wpdb->get_results("SELECT apl_id, apl_nome, apl_token_teste, apl_op_teste, apl_token_producao, apl_token, apl_prefixo, apl_email FROM " . $wpdb->prefix . "serveloja_aplicacao ORDER BY apl_id DESC LIMIT 1");
        if ($wpdb->last_error) {
            return WC_Serveloja_Funcoes::wcsvl_div_resposta("erro", "Ocorreram falhas", "Erro: " . $wpdb->last_error);
        } else {
            if (count($rows) == 0) {
                return "0";
            } else {
                return $rows;
            }
        }
    }

    // cartões
    public static function wcsvl_lista_cartoes() {
        $authorization = (WC_Serveloja_Funcoes::wcsvl_aplicacao() == "0") ? "" : WC_Serveloja_Funcoes::wcsvl_aplicacao()[0]->apl_token;
        $applicatioId = (WC_Serveloja_Funcoes::wcsvl_aplicacao() == "0") ? "" : WC_Serveloja_Funcoes::wcsvl_aplicacao()[0]->apl_nome;
        if(WC_Serveloja_API::wcsvl_metodos_get('Cartao/ObterBandeirasValidas', "", $authorization, $applicatioId) == false){
            return false;
        }else{
            return WC_Serveloja_API::wcsvl_metodos_get('Cartao/ObterBandeirasValidas', "", $authorization, $applicatioId);
        }
    }

    public static function wcsvl_insert_cartoes($posicao, $car_cod, $car_bandeira, $car_parcelas, $nonce) {
        if (wp_verify_nonce($nonce, 'cartoes_user')) {
            global $wpdb;
            $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix . "serveloja_cartoes");
            for ($i = 0; $i < count($posicao); $i++) {
                $pos = $posicao[$i];
                $wpdb->insert(
                    $wpdb->prefix . "cartoes",
                    array('car_cod' => $car_cod[$pos],
                        'car_bandeira' => $car_bandeira[$pos],
                        'car_parcelas' => $car_parcelas[$pos]
                    ),
                    array('%s', '%s', '%s')
                );
            }
            if ($wpdb->last_error) {
                return WC_Serveloja_Funcoes::wcsvl_div_resposta("erro", "Ocorreram falhas", "Erro: " . $wpdb->last_error);
            } else {
                return WC_Serveloja_Funcoes::wcsvl_div_resposta("sucesso", "Tudo certo!", "Os dados foram modificados com sucesso");
            }
        } else {
            return WC_Serveloja_Funcoes::wcsvl_div_resposta("erro", "Não verificado", "Ocorreu um erro na validação da ação (NONCE WP). O sistema não pode continuar");
        }
    }

    public static function wcsvl_cartoes_salvos() {
        global $wpdb;
        $rows = $wpdb->get_results("SELECT car_cod, car_bandeira, car_parcelas FROM " . $wpdb->prefix . "serveloja_cartoes");
        if ($wpdb->last_error) {
            return WC_Serveloja_Funcoes::div_resposta("erro", "Ocorreram falhas", "Erro: " . $wpdb->last_error);
        } else {
            return $rows;
        }
    }

    // cartões de crédito
    public static function wcsvl_lista_cartoes_api($url, $method, $param) {
        return WC_Serveloja_Funcoes::wcsvl_metodos_acesso_api($url, $method, $param);
    }

    // verifica se existem configurações salvas
    public static function wcsvl_configuracoes() {
        global $wpdb;
        $rows = $wpdb->get_results("SELECT COUNT(apl_id) AS total FROM " . $wpdb->prefix . "serveloja_aplicacao");
        if ($wpdb->last_error) {
            return WC_Serveloja_Funcoes::div_resposta("erro", "Ocorreram falhas", "Erro: " . $wpdb->last_error);
        } else {
            foreach ($rows as $row) {
                return (int)$row->total;
            }
        }
    }

    // tabela
    private static function wcsvl_parcelas_padrao($quant, $parcelas) {
        $retorno = '<select name="car_parcelas_padrao" onchange="alterarParcelas(this.options[this.selectedIndex].value)" class="select_menor_total_parcelas" style="margin-top: 0px;">'.
        '<option value="0" selected>Selecione</option>';
        
        for ($i = 1; $i <= intval($quant); $i++) {
            $selected = '';
            if (in_array($i, $parcelas)) {
                $selected = 'selected';
            }
            $retorno .= '<option value="' . $i . '" ' . $selected . '>Em ' . $i . 'x</option>';
        }
        $retorno .= '</select>';

        return $retorno;
    }
    private static function wcsvl_parcelas($quant, $bandeira, $parcelas, $idAtual) {
        $retorno = '<select id="'. $bandeira .'" name="car_parcelas[]" class="select_menor" style="margin-top: 0px;">';
        for ($i = 1; $i <= intval($quant); $i++) {
            $selected = '';
            if (in_array($i . '-' . $bandeira, $parcelas)) {
                $selected = 'selected';
            }
            if ($i == 1) {
                $retorno .= '<option value="' . $i . '-' . $bandeira . '" ' . $selected . '>Apenas uma vez</option>';
            }else {
                // if($bandeira == 'visa'){//colocar bandeira certa aqui
                //     if($i>6){//colocar quantidade certa aqui
                //         //não faz nada
                //     }else{
                //         $retorno .= '<option value="' . $i . '-' . $bandeira . '" ' . $selected . '>Em ' . $i . ' vezes</option>';
                //     }
                // }else{
                    $retorno .= '<option value="' . $i . '-' . $bandeira . '" ' . $selected . '>Em ' . $i . ' vezes</option>';
                // }
            }
        }
        $retorno .= '</select>';
        return $retorno;
    }

    public static function wcsvl_tabela_cartoes() {
        $retorno = '';
        if (WC_Serveloja_Funcoes::wcsvl_configuracoes() == 0) {
            $retorno .= '<div Style="width: 80%; margin-left: auto !important;margin-right: auto !important;" class="alerta">
            Antes de selecionar os cartões, você precisar informar um Nome e Token da aplicação em Configurações.
            </div>';
        } else if(WC_Serveloja_Funcoes::wcsvl_lista_cartoes() == false){
            $retorno .= '<div Style="width: 80%; margin-left: auto !important;margin-right: auto !important;" class="erro">
            Erro ao listar cartões! Token e/ou nome da aplicação inválido(s). Por favor, confira os dados inseridos na tela de configurações.</br>
            Em caso de dúvidas sobre o token ou nome da aplicação, entre em contato com a Serveloja.
            </div>';
        } else {
            $lista_cartoes = WC_Serveloja_Funcoes::wcsvl_lista_cartoes();
            $cartoes = json_decode($lista_cartoes["body"], true);
            $cartoes_banco = WC_Serveloja_Funcoes::wcsvl_cartoes_salvos();
            $quant_parcelas = 12;
            $array_cod = array();
            $array_parcelas = array();
            foreach ($cartoes_banco as $row) {
                array_push($array_cod, $row->car_cod);
                array_push($array_parcelas, $row->car_parcelas);
            }
            $retorno .= 
            
            '<script>'. 
                'function verificaStatus(nome){
                    if(nome.form.marcaTudo.checked == 0){
                        nome.form.marcaTudo.checked = 0;
                        desmarcarTodos(nome);
                    }
                    if(nome.form.marcaTudo.checked == 1){
                        nome.form.marcaTudo.checked = 1;
                        marcarTodos(nome);
                    }
                }
                
                function marcarTodos(nome){
                for (i=0;i<nome.form.elements.length;i++)
                    if(nome.form.elements[i].type == "checkbox")
                        nome.form.elements[i].checked=1;
                        desabilitaBandeiras(1, nome.form.elements.length);
                }
                
                function desmarcarTodos(nome){
                for (i=0;i<nome.form.elements.length;i++)
                    if(nome.form.elements[i].type == "checkbox")
                        nome.form.elements[i].checked=0;
                        desabilitaBandeiras(0, nome.form.elements.length);
                }

                function desabilitaBandeiras(valor, tamanho){
                    if(valor == 0){
                        for (i=0;i<tamanho;i++){
                            var id = String("checkBoxCartoes."+i);
                            var id2 = String("selectCartoes."+i);
                            document.getElementById(id).classList.add("fora_do_banco");
                            document.getElementById(id2).classList.add("desabilitar");
                        }
                    }
                    if(valor == 1){
                       for (i=0;i<tamanho;i++){
                            var id = String("checkBoxCartoes."+i);
                            var id2 = String("selectCartoes."+i);
                            document.getElementById(id).classList.remove("fora_do_banco");
                            document.getElementById(id2).classList.remove("desabilitar");
                        }
                        
                    } 
                }

                function desabilitaBandeira(elemento){
                    var id = String("checkBoxCartoes."+elemento.value);
                    var id2 = String("selectCartoes."+elemento.value);
                    var estado = document.getElementsByClassName("inputCheck")[elemento.value].checked;
                    if(estado == 0){
                        document.getElementById(id).classList.add("fora_do_banco");
                        document.getElementById(id2).classList.add("desabilitar");
                    }
                    if(estado == 1){
                        document.getElementById(id).classList.remove("fora_do_banco");
                        document.getElementById(id2).classList.remove("desabilitar");
                        
                    } 
                }

                function alterarParcelas(qtd){
                    var tamanho = document.getElementsByClassName("select_menor").length;
                    for (var i = 0; i < tamanho; i++) {
                        var bandeira = document.getElementsByClassName("select_menor")[i].id;
                        // if(bandeira == "visa"){//colocar bandeira certa aqui
                        //     if(qtd>6){//colocar quantidade certa aqui
                        //         //não faz nada
                        //     }else{
                        //         document.getElementsByClassName("select_menor")[i].value = qtd+"-"+bandeira;
                        //     }
                        // }else{
                            document.getElementsByClassName("select_menor")[i].value = qtd+"-"+bandeira;
                        // }
                        
                    }
                }'. 
            '</script>'.

            '<form name="tabela">'.
            '<table cellspacing="0" cellpadding="0" class="tabelanova">' .
            '<thead>' .
            '<tr>' .
            '<td class="celulacheck celulacentralizar"><input class="inputCheckTitulo" type="checkbox" name="marcaTudo" id="checkVerde" onclick="verificaStatus(this)"/> </td>' .
            '<td class="celulabandeiras">Bandeiras</td>' .
            '<td class="celulaselect celulacentralizar">Quantidade de parcelas? '.  '</br>'. 
            '<p style="margin: 6px 0px 10px 0px;font-size: .8rem; color: #404040;" class="inputFormConfigText"> Alterar em todos: '. 
                WC_Serveloja_Funcoes::wcsvl_parcelas_padrao($quant_parcelas, $array_parcelas) .
            '</p>'.
            '</td>' .
            '</tr>' .
            '</thead>';

            for ($i = 0; $i < count($cartoes["Container"]); $i++) {
                if (in_array($cartoes["Container"][$i]['CodigoBandeira'], $array_cod)) {
                    $css = '';
                    $checado = 'checked';
                    $desabilitar = '';
                    $mensagem = '';
                } else {
                    $css = 'fora_do_banco';
                    $desabilitar = 'desabilitar';
                    $mensagem = ' (bandeira desabilitada: selecione-a para habiltar, em seguida clique em salvar)';
                    $checado = '';
                }
                $retorno .= '<tr id="checkBoxCartoes.'. $i .'" class="' . $css . '">' .
                '<td  class="celulacheck celulacentralizar"><input ' . $checado . ' class="inputCheck" type="checkbox" name="posicao[]" value="' . $i .'" onchange="desabilitaBandeira(this)"/>' .
                '<input type="hidden" name="car_bandeira[]" value="' . $cartoes["Container"][$i]["NomeBandeira"] . '" />' .
                '<input type="hidden" name="car_cod[]" value="' . $cartoes["Container"][$i]["CodigoBandeira"] . '" />' .
                '</td>' .
                '<td class="celulabandeiras">' .
                '<img class="img_tabela" src="' . plugins_url('assets/images/' . strtolower($cartoes["Container"][$i]["NomeBandeira"]) . '.png', dirname(__FILE__)) . '" title="' . $cartoes["Container"][$i]["NomeBandeira"] .'" alt="' . strtolower($cartoes["Container"][$i]["NomeBandeira"]) . '" />' .
                '<span class="nomeBandeira">' .
                    $cartoes["Container"][$i]["NomeBandeira"] . $mensagem.
                '</span>' .
                '</td>' .
                '<td id="selectCartoes.'. $i .'" class="celulaselect celulacentralizar ' . $desabilitar .'">' .
                WC_Serveloja_Funcoes::wcsvl_parcelas($quant_parcelas, strtolower($cartoes["Container"][$i]["NomeBandeira"]), $array_parcelas, $i) .
                '</td>' .
                '</tr>';
                $teste = 'tudo';
            }
            $retorno .= '</table>' .
            '<div class="clear"></div>' .
            '<input type="submit" id="botaoSalvar" class="submit" name="salvar_cartoes" value="Salvar"/>'.
            '</form>';
        }
        return $retorno;
    }

    // limpeza de dados de array
    static function sanitize_text_or_array($dados) {
        if (is_string($dados)){
            $dados = sanitize_text_field($dados);
        } else if (is_array($dados)) {
            foreach ($dados as $key => &$value) {
                if (is_array($value)) {
                    $value = sanitize_text_or_array_field($value);
                } else {
                    $value = sanitize_text_field($value);
                }
            }
        }
        return $dados;
    }

} ?>