<?php
/**
 * WooCommerce Serveloja módulos.
 *
 * Barras de ferramentas da aplicação na área administrativa
 *
 * @class   WC_Serveloja_Modulos
 * @extends WC_Serveloja_Modulos
 * @version 2.7.0
 * @author  TiServeloja
 */

if (!defined( 'ABSPATH' )) {
    exit;
}

class WC_Serveloja_Modulos {

    public static function wcsvl_cabecalho() {
        $html = '<div class="conteudo">' .
        '<div class="barraFundo" id="headerPlugin">' .
            '<a class="links" href="admin.php?page=home">' .
                '<div id="logo">' .
                    '<img src="' . plugins_url('assets/images/serveloja.png', dirname(__FILE__)) . '" alt="servloja" border="0" />' .
                '</div>' .
            '</a>' .
            
            '<a class="links" target="_blank" href="https://play.google.com/store/apps/details?id=br.com.serveloja&hl=pt_BR">' . //link aqui
                '<div id="linkGooglePlay">' .    
                    '<img src="' . plugins_url('assets/images/selo-android.png', dirname(__FILE__)) . '" alt="servloja" border="0" />' .
                '</div>' .
            '</a>' .
            
            '<a class="links" target="_blank" href="https://apps.apple.com/br/app/serveloja/id777645411">' . //link aqui
                '<div id="linkAppStore">' .    
                    '<img src="' . plugins_url('assets/images/selo-apple.png', dirname(__FILE__)) . '" alt="servloja" border="0" />' .
                '</div>' .
            '</a>' .
            
            '<div id="texto">' .
                '<p id="texto" href="admin.php?page=home">' .
                'Baixe o nosso aplicativo no seu </br> smartphone e acompanhe suas vendas</p>' .
            '</div>' .
        '</div>';
        echo $html;
    }

    public static function wcsvl_ferramentas($var) {
        if($var == 1){
            $html = '<div class="conteudo">' .
        '<div class="barraFerramentas">' .
            '<div class="botao" id="links">' .
                '<a class="links" href="admin.php?page=home">' .
                    'Página Inicial' .
                '</a>' .
            '</div>' .

            
            '<a class="links" style="color: #24B24B;" href="admin.php?page=configuracoes">' .
                '<div class="botao" id="links">' .
                    'Configurações' .
                '</div>' .
            '</a>' .
            

            '<div class="botao" id="links">' .
                '<a class="links" href="admin.php?page=cartoes">' .
                'Cartões' .
                '</a>' .
            '</div>' .

            '<div class="botao" id="links">' .
                '<a class="links" href="admin.php?page=wc-settings&tab=checkout&section=serveloja">' .
                'WooCommerce' .
                '</a>' .
            '</div>' .
        '</div>';
        }if($var == 2){
            $html = '<div class="conteudo">' .
        '<div class="barraFerramentas">' .
            '<div class="botao" id="links">' .
                '<a class="links" href="admin.php?page=home">' .
                    'Página Inicial' .
                '</a>' .
            '</div>' .

            '<div class="botao" id="links">' .
                '<a class="links" href="admin.php?page=configuracoes">' .
                'Configurações' .
                '</a>' .
            '</div>' .

            '<div class="botao" id="links">' .
                '<a class="links" style="color: #24B24B;" href="admin.php?page=cartoes">' .
                'Cartões' .
                '</a>' .
            '</div>' .

            '<div class="botao" id="links">' .
                '<a class="links" href="admin.php?page=wc-settings&tab=checkout&section=serveloja">' .
                'WooCommerce' .
                '</a>' .
            '</div>' .
        '</div>';
    }
        
        echo $html;
    }

} ?>