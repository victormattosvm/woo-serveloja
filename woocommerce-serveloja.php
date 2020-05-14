<?php
/**
 * Plugin Name: WooCommerce Serveloja
 * Plugin URI: http://www.serveloja.com.br
 * Description: Plugin para realização de pagamentos via lojas virtuais com WooCommerce, utilizando soluções fornecidas pela Serveloja.
 * Version: 2.7.0
 * Author: TiServeloja
 * Author URI: http://www.serveloja.com.br
**/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    if (!class_exists('WC_Serveloja')) {
        class WC_Serveloja {

            const VERSION = '2.7.0';

            protected static $instance = null;

            private function __construct() {
                if (class_exists('WC_Payment_Gateway')) {
                    
                    $this->wcsvl_includes();

                    add_action('admin_enqueue_scripts', array("WC_Serveloja_Styles", 'wcsvl_styles_serveloja_admin'));

                    add_action('wp_enqueue_scripts', array("WC_Serveloja_Styles", 'wcsvl_styles_serveloja_gateway'));

                    add_filter('woocommerce_payment_gateways', array($this, 'wcsvl_add_gateway'));
                    add_filter('plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'wcsvl_plugin_action_links'));

                    // define o arquivo de desisntalação e executa funções
                    define('WP_UNINSTALL_PLUGIN', plugins_url('uninstall.php', __FILE__));

                    
                    // link no menu principal do wordpress
                    $this->wcsvl_menu();
                } else {
                    add_action('admin_notices', array( $this, 'woocommerce_missing_notice'));
                }
            }

            public static function wcsvl_get_templates_path() {
                return plugin_dir_path(__FILE__) . 'templates/';
            }

            public static function wcsvl_get_instance() {
                if ( null === self::$instance ) {
                    self::$instance = new self;
                }
                return self::$instance;
            }
            
            // adiciona link na página de edição de plugins
            public function wcsvl_plugin_action_links($links) {
                $plugin_links   = array();
                $plugin_links[] = '<a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=serveloja')) . '">' . __('Woocommerce', 'woocommerce-serveloja') . '</a>';
                $plugin_links[] = '<a href="' . esc_url(admin_url('admin.php?page=home')) . '">Configurações</a>';
                return array_merge($plugin_links, $links);
            }

            private function wcsvl_includes() {
                include_once dirname( __FILE__ ) . '/includes/class-wc-serveloja-gateway.php';
                include_once dirname( __FILE__ ) . '/includes/class-wc-serveloja-funcoes.php';
                include_once dirname( __FILE__ ) . '/includes/class-wc-serveloja-api.php';
                include_once dirname( __FILE__ ) . '/includes/class-wc-serveloja-modulos.php';
                include_once dirname( __FILE__ ) . '/includes/class-wc-serveloja-styles.php';
                include_once dirname( __FILE__ ) . '/templates/configuracoes.php';
                include_once dirname( __FILE__ ) . '/templates/cartoes.php';
                include_once dirname( __FILE__ ) . '/templates/home.php';
            }
            
            public function wcsvl_add_gateway($methods) {
                $methods[] = 'WC_Serveloja_Gateway';
                return $methods;
            }

            
            private function wcsvl_menu() {
                add_action('admin_menu', 'wcsvl_addCustomMenuItem');
                function wcsvl_addCustomMenuItem() {
                    add_menu_page('Serveloja', 'Serveloja', 'manage_options', 'home', 'wcsvl_function_home', 'dashicons-businessman', 6);
                    add_submenu_page('home', 'Serveloja', 'Configurações', 'manage_options', 'configuracoes', 'wcsvl_function_configuracoes');
                    add_submenu_page('home', 'Serveloja', 'Cartões', 'manage_options', 'cartoes', 'wcsvl_function_cartoes');
                }
            }
            
        }
    }

    function wcsvl_create_db_table() {
        global $wpdb;
        $tabela_aplicacao = $wpdb->prefix . 'serveloja_aplicacao';
        $tabela_cartoes = $wpdb->prefix . 'serveloja_cartoes';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $tabela_aplicacao (
            `apl_id` int(11) NOT NULL AUTO_INCREMENT,
            `apl_nome` varchar(32) NOT NULL,
            `apl_token` varchar(64) NOT NULL,
            `apl_op_teste` int(1),
            `apl_token_teste` varchar(64),
            `apl_token_producao` varchar(64),
            `apl_prefixo` varchar(50),
            `apl_email` varchar(100),
            PRIMARY KEY (`apl_id`)
        ) $charset_collate;
        CREATE TABLE $tabela_cartoes (
            `car_id` int(11) NOT NULL AUTO_INCREMENT,
            `car_cod` varchar(32) NOT NULL,
            `car_bandeira` varchar(50) NOT NULL,
            `car_parcelas` varchar(20) NOT NULL,
            PRIMARY KEY (`car_id`)
        ) $charset_collate;
        ";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }


    // Em caso de desativação do plugin
    function wcsvl_truncate_db_table() {
        global $wpdb;
        $tabela_aplicacao = $wpdb->prefix . 'serveloja_aplicacao';
        $tabela_cartoes = $wpdb->prefix . 'serveloja_cartoes';
        $wpdb->query("TRUNCATE TABLE $tabela_aplicacao");
        $wpdb->query("TRUNCATE TABLE $tabela_cartoes");
        delete_option("serveloja");
        delete_site_option('serveloja');
    }

    register_activation_hook(__FILE__, 'wcsvl_create_db_table');
    register_deactivation_hook(__FILE__, 'wcsvl_truncate_db_table');
    add_action('plugins_loaded', array('WC_Serveloja', 'wcsvl_get_instance'));
    
// cria tabelas no banco
}
