<?php
/**
 * WooCommerce Serveloja styles e scripts class.
 *
 * Carregamento dos scripts e styles do plugin.
 *
 * @class   WC_Serveloja_Styles
 * @version 2.7.0
 * @author  TiServeloja
 */

if (!defined('ABSPATH')) {
	exit;
}

class WC_Serveloja_Styles {

    // admin
    public static function wcsvl_styles_serveloja_admin() {
        wp_enqueue_style('wcsvl_serveloja', plugins_url('assets/css/serveloja.css', dirname(__FILE__)));
        wp_enqueue_style('wcsvl_form', plugins_url('assets/css/forms.css', dirname(__FILE__)));
        wp_enqueue_style('wcsvl_tabelas', plugins_url('assets/css/tabelas.css', dirname(__FILE__)));
        wp_enqueue_script('wcsvl_scripts', plugins_url('assets/scripts/scripts.js', dirname(__FILE__)));
    }

    // tema
    public static function wcsvl_styles_serveloja_gateway() {
        wp_enqueue_style('wcsvl_cliente', plugins_url('assets/css/cliente.css', dirname(__FILE__)));
        wp_enqueue_style('wcsvl_form', plugins_url('assets/css/forms.css', dirname(__FILE__)));
        wp_enqueue_script('wcsvl_masked', plugins_url('assets/scripts/maskedinput.js', dirname(__FILE__)));
    }

} ?>