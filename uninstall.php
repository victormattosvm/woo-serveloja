<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit();

global $wpdb;
$tabela_aplicacao = $wpdb->prefix . 'serveloja_aplicacao';
$tabela_cartoes = $wpdb->prefix . 'serveloja_cartoes';
$wpdb->query("DROP TABLE IF EXISTS $tabela_aplicacao");
$wpdb->query("DROP TABLE IF EXISTS $tabela_cartoes");

// Deleta as opções
delete_option( 'serveloja' );
delete_site_option( 'serveloja' ); ?>