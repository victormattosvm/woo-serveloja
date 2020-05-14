<?php function wcsvl_function_home() {

    // cabeçalho
    WC_Serveloja_Modulos::wcsvl_cabecalho(); ?>

    <?php if($_SERVER['HTTPS'] == "on"){ ?>
    <?php }else{ ?>
        <p class="erro" style="font-family: Nunito, sans-serif;">É necessário habilitar o protocolo HTTPS em seu site para utilizar o plugin da Serveloja.</p>
    <?php } ?>

    <div class="conteudo">

        <h1 style="color: #24B24B; font-family: Nunito, sans-serif;">WooCommerce Serveloja</h1>
        <p style="color: #808080; font-family: Nunito, sans-serif;">Clique em uma das opções abaixo para iniciar</p>

        <div class="centralizarDiv">
        <div class="box">

            <a class="links" href="admin.php?page=configuracoes">
                <div class="cardHome">
                    <img src="<?php echo plugins_url('assets/images/configuracoes.png', dirname(__FILE__)); ?>" alt="configuracoes" />
                    <br/>
                    <h2>Configurações</h2>  
                    <div class="subtitulo">
                        Configure sua aplicação antes de começar a usar
                    </div>
                </div>
            </a>

            <a class="links" href="admin.php?page=cartoes">
                <div class="cardHome">
                    <img src="<?php echo plugins_url('assets/images/cartoes.png', dirname(__FILE__)); ?>" alt="cartoes" />
                    <br/>
                    <h2>Cartões de Crédito</h2>
                    <div class="subtitulo">
                        Informe os cartões de crédito com os quais deseja receber pagamentos
                    </div>
                </div>
            </a>

            <a class="links" href="admin.php?page=wc-settings&tab=checkout&section=serveloja">
                <div class="cardHome">
                    <img src="<?php echo plugins_url('assets/images/woo.png', dirname(__FILE__)); ?>" alt="woocommerce" />
                    <br/>
                    <h2>WooCommerce</h2>
                    <div class="subtitulo">
                        Configurações da Serveloja no WooCommerce
                    </div>
                </div>
            </a>
        </div>
    </div>
    </div>  
        

<?php } ?>