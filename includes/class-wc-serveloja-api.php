<?php
/**
 * WooCommerce Serveloja API class.
 *
 * API de comunicação com base Serveloja.
 *
 * @class   WC_Serveloja_API
 * @version 2.7.0
 * @author  TiServeloja
 */

if (!defined('ABSPATH')) {
	exit;
}

class WC_Serveloja_API {

	private static function wcsvl_servidor() {
		return "https://sistemaserveloja.com.br/gtw/webapi/";
		//return "http://desenvolvimento.redeserveloja.com/Novo/WebApi/";
	}

	public static function wcsvl_metodos_get($url, $param, $authorization, $applicationId) {
		$args = array(
			'blocking' => true,
			'timeout' => '5000',
			'headers' => array(
				'Authorization' => 'Basic ' . $authorization,
				'ApplicationId' => $applicationId,
				'Content-Type' => 'application/json',
				'User-Agent' => $_SERVER['HTTP_USER_AGENT']
			)
		);
		$response = wp_remote_get(WC_Serveloja_API::wcsvl_servidor() . $url, $args);
		if (is_wp_error($response)) {
			return $response->get_error_message();
		}else if(json_decode($response["body"])->HttpStatusCode == 400){
			return false;
		} else {
			return $response;
		}
	}

	public static function wcsvl_metodos_post($url, $param, $authorization, $applicationId) {
		$args = array(
			'blocking' => true,
			'timeout' => '5000',
			'headers' => array(
				'Authorization' => 'Basic ' . $authorization,
				'ApplicationId' => $applicationId,
				'Content-Type' => 'application/json',
				'User-Agent' => $_SERVER['HTTP_USER_AGENT']
			),
			'body' => json_encode($param)
		);
		$response = wp_remote_post(WC_Serveloja_API::wcsvl_servidor() . $url, $args);
		if (is_wp_error($response)) {
			return $response->get_error_message();
		} else {
			return $response;
		}
	}

} ?>