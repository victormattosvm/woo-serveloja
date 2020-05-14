<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit();

global $wpdb;
$tabela_aplicacao = $wpdb->prefix . 'aplicacao';
$tabela_cartoes = $wpdb->prefix . 'cartoes';
$wpdb->query("DROP TABLE IF EXISTS $tabela_aplicacao");
$wpdb->query("DROP TABLE IF EXISTS $tabela_cartoes");

// Deleta as opções
delete_option( 'serveloja' );
delete_site_option( 'serveloja' ); ?>