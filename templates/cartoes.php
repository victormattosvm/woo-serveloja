<?php function wcsvl_function_cartoes() {
// cabeçalho
WC_Serveloja_Modulos::wcsvl_cabecalho();

//Barra de ferramentas
WC_Serveloja_Modulos::wcsvl_ferramentas(2);

$funcoes = new WC_Serveloja_Funcoes;
if (isset($_POST["salvar_cartoes"])) {
    // campos 'sanatizados' antes da inserção no banco
    $salvar = $funcoes::wcsvl_insert_cartoes(
        $funcoes::sanitize_text_or_array($_POST["posicao"]),
        $funcoes::sanitize_text_or_array($_POST["car_cod"]),
        $funcoes::sanitize_text_or_array($_POST["car_bandeira"]),
        $funcoes::sanitize_text_or_array($_POST["car_parcelas"]),
        $_POST["_nonce_cartoes"]
    );

    if (!is_null($salvar["class"])) {
        echo "<div class='" . $salvar["class"] . "'>" .
            "<h3>" . $salvar["titulo"] . "</h3>" . $salvar["mensagem"] .
        "</div>";
    } else {
        echo $salvar;
    }
} ?>

<?php if($_SERVER['HTTPS'] == "on"){ ?>
<?php }else{ ?>
    <p class="erro" style="font-family: Nunito, sans-serif;">É necessário habilitar o protocolo HTTPS em seu site para utilizar o plugin da Serveloja.</p>
<?php } ?>

<h1 style="color: #24B24B; font-family: Nunito, sans-serif;">Cartões de Crédito</h1>
<p style="color: #808080; font-family: Nunito, sans-serif;">Configure as bandeiras com as quais você irá receber os pagamentos dos seus clientes.</p>

<form method="post" action="" name="cartoes">
    <input type="hidden" name="_nonce_cartoes" value="<?php echo wp_create_nonce('cartoes_user'); ?>" />
    <?php echo $funcoes::wcsvl_tabela_cartoes(); ?>
</form>

<div class="clear"></div>
</div>
<?php } ?>