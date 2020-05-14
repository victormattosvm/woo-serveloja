<?php function wcsvl_function_configuracoes() {

  $funcoes = new WC_Serveloja_Funcoes;

  // verifica se já existem informações sobre a aplicação
  $dados = $funcoes::wcsvl_aplicacao();
  $apl_id = ($dados == "0") ? "0" : $dados[0]->apl_id;
  $apl_nome = ($dados == "0") ? "" : $dados[0]->apl_nome;
  $apl_token_teste = ($dados == "0") ? "" : $dados[0]->apl_token_teste;
  $apl_op_teste = ($dados == "0") ? "" : $dados[0]->apl_op_teste;
  $apl_token_producao = ($dados == "0") ? "" : $dados[0]->apl_token_producao;
  $apl_token = ($dados == "0") ? "" : $dados[0]->apl_token;
  $apl_prefixo = ($dados == "0") ? "" : $dados[0]->apl_prefixo;
  $apl_email = ($dados == "0") ? "" : $dados[0]->apl_email;

  // cabeçalho
  WC_Serveloja_Modulos::wcsvl_cabecalho();

  //Barra de ferramentas
  WC_Serveloja_Modulos::wcsvl_ferramentas(1);

  wc_enqueue_js('
  if(document.getElementById("opTeste").value == 1){
    $("#inputTokenTeste").show();
    $("#inputTokenProducao").hide();
    document.getElementsByName("token")[1].checked = true;
  }else{
    $("#inputTokenTeste").hide();
    $("#inputTokenProducao").show();
    document.getElementsByName("token")[0].checked = true;
  }

  
  $(".botaoCopia").click(function(){
    if(document.getElementsByName("token")[0].checked == true){
      if($("#copiaTokenProducao").val().length>0){
        $("#mensagemCopia").show();
        setTimeout(function(){ 
          $("#mensagemCopia").hide();
        }, 2000);
      }
    }else{
      if($("#copiaTokenTeste").val().length>0){
        $("#mensagemCopia").show();
        setTimeout(function(){ 
          $("#mensagemCopia").hide();
        }, 2000);
      }
    }
    
  });
  



  ');

  echo "<script>
  function alterarToken(valor){
    if(valor == 1){
      document.getElementById('tokenAplicacao').value = document.getElementById('copiaTokenTeste').value;
    }else{
      document.getElementById('tokenAplicacao').value = document.getElementById('copiaTokenProducao').value;
    }
    document.getElementById('opTeste').value = valor;
    //console.log(document.getElementById('tokenAplicacao').value);
    //console.log(document.getElementById('opTeste').value);
  }
  </script>";

  // post
  if (isset($_POST["salvar_config"])) {

    // tratamento
    $apl_nome               = $funcoes::sanitize_text_or_array($_POST["apl_nome"]);
    $apl_token_teste        = $funcoes::sanitize_text_or_array($_POST["apl_token_teste"]);
    $apl_op_teste           = intval($funcoes::sanitize_text_or_array($_POST["apl_op_teste"]));
    $apl_token_producao  = $funcoes::sanitize_text_or_array($_POST["apl_token_producao"]);
    $apl_token              = $funcoes::sanitize_text_or_array($_POST["apl_token"]);
    $apl_prefixo            = $funcoes::sanitize_text_or_array($_POST["apl_prefixo"]);
    $apl_email              = $funcoes::sanitize_text_or_array($_POST["apl_email"]);
    $apl_id                 = intval($funcoes::sanitize_text_or_array($_POST["apl_id"]));
    $nonce                  = $_POST["_nonce_config"];

    $salvar = WC_Serveloja_Funcoes::wcsvl_save_configuracoes(
      $apl_nome, $apl_token_teste, $apl_op_teste, $apl_token_producao, $apl_token, $apl_prefixo, $apl_email, $apl_id, $nonce
    );

    // retorno
    echo "<div class='" . $salvar["class"] . "'>" .
      "<h3>" . $salvar["titulo"] . "</h3>" . $salvar["mensagem"] .
    "</div>";

    //var_dump($apl_token); 

    $dados = WC_Serveloja_Funcoes::wcsvl_aplicacao();
  } ?>
  
  <?php if($_SERVER['HTTPS'] == "on"){ ?>
    <?php }else{ ?>
        <p class="erro" style="font-family: Nunito, sans-serif;">É necessário habilitar o protocolo HTTPS em seu site para utilizar o plugin da Serveloja.</p>
  <?php } ?>

  <h1 style="color: #24B24B; font-family: Nunito, sans-serif;">Configurações</h1>


  <p style="color: #808080; font-family: Nunito, sans-serif; margin: 10px 0px;">
      Caso você ainda não seja cliente Serveloja, entre em contato com um de nossos consultores. <a class="linkContato" target="_blank" href="https://site.serveloja.com.br/fale-conosco">Clique aqui</a>.
  </P>

  <p style="color: #808080; font-family: Nunito, sans-serif; font-size: 0.60rem; margin: 0px;">Todos os campos marcados com <b>(*)</b> são de preenchimento obrigatório.</p>

  <br/>

  <form name="configuracoes" method="post" action="">

  <input type="hidden" name="_nonce_config" value="<?php echo wp_create_nonce('config_user'); ?>" />

    <div class="inputFormConfigText">Nome da Aplicação (*)</div>
    <input type="text" class="inputFormConfigBox" name="apl_nome" value="<?php echo $apl_nome; ?>" maxlength="30" />
    <br/>

    <div class="inputFormConfigText">
      <b style="color: #000;">Token</b>
      <div class="radios">
        <div style="margin-top: 10px;">
          <input type="radio" name="token" class="inputFormConfigRadio" value="tokenProducao" onclick="alterarToken(0); document.getElementById('inputTokenProducao').style.display='block';document.getElementById('inputTokenTeste').style.display='none';"/>
          <label style="color: #000; cursor: default !important;" for="tokenTeste">Token para ambiente de produção</label>
        </div>
        <div style="margin-top: 10px;">
          <input name="token" type="radio" class="inputFormConfigRadio" value="tokenTeste" onclick="alterarToken(1); document.getElementById('inputTokenTeste').style.display='block';document.getElementById('inputTokenProducao').style.display='none';"/>
          <label style="color: #000; cursor: default !important;" for="tokenTeste">Token para ambiente de homologação</label>
          <span id="mensagemCopia">Token copiado!</span>
        </div>
      </div>
    </div>

    <label id="inputTokenProducao">
      <input id="copiaTokenProducao" type="text" class="inputFormConfigBox" name="apl_token_producao" value="<?php echo $apl_token_producao; ?>" onchange="alterarToken(0);" maxlength="100" />
      <button type="button" class="botaoCopia" onClick="document.getElementById('copiaTokenProducao').select(); document.execCommand('Copy');"></button>
      <br/>
    </label>

    <label id="inputTokenTeste" style="display:none">
      <input id="copiaTokenTeste" type="text" class="inputFormConfigBox" name="apl_token_teste" value="<?php echo $apl_token_teste; ?>" onchange="alterarToken(1);" maxlength="100" />
      <button type="button" class="botaoCopia" onClick="document.getElementById('copiaTokenTeste').select(); document.execCommand('Copy');"></button>
      <div class="alertaToken">Atenção: Este token deve ser usado apenas para testes!</div>
    </label>

    <label id="inputTokenAplicacao" style="display:none">
      <input id="tokenAplicacao" type="text" name="apl_token" value="<?php echo $apl_token; ?>" maxlength="60" />
      <input id="opTeste" type="text" name="apl_op_teste" value="<?php echo $apl_op_teste; ?>" maxlength="1" />
      <br/>
    </label>

    <div class="inputFormConfigText">Prefixo das transações</div>
    <input type="text" class="inputFormConfigBox" name="apl_prefixo" value="<?php echo $apl_prefixo; ?>" />
    <br/>

    <div class="inputFormConfigText">Informe um e-mail para receber notificações sobre compras realizadas em seu site/loja (*)</div>
    <input type="text" class="inputFormConfigBox" name="apl_email" value="<?php echo $apl_email; ?>" />
    <br/>

    <input type="hidden" name="apl_id" value="<?php echo $apl_id; ?>" />

    <input type="submit" id="botaoSalvar" class="submit" name="salvar_config" value="Salvar"/>

  </form>
</div>

<?php } ?>