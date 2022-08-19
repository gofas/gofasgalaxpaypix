<?php
/**
 * Módulo GalaxPay Pix para WHMCS
 * @copyright	2022 Gofas Software
 * @see			https://gofas.net/?p=14641
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=14644
 * @version		0.1.0
 */
require_once __DIR__ . '/../../../../init.php';
require_once __DIR__ . '/../../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../../includes/invoicefunctions.php';

$params = getGatewayVariables('gofasgalaxpaypix');
//if(!$params['type']){die("Module Not Activated");}
if( $_REQUEST['invoice_id'] ){
	require __DIR__.'/functions.php';
	$params_api = ggpp_api_connect();
	$invoice = localAPI('getinvoice',array('invoiceid'=> $_REQUEST['invoice_id']),(int)$params['admin']);
	if( $invoice['invoiceid']){
		$qrcode = ggpp_get_local_qrc($_REQUEST['invoice_id']);
		$access_token_ = ggpp_get_token();
		$access_token = $access_token_['result']['access_token'];
		
		$charge = ggpp_charge_verify([
			'galaxPayIds'=>$qrcode['charge_id'],
		]);
		
		if(($charge['result']['Transactions']['0']['status'] === 'payedPix' || $charge['result']['Transactions']['0']['status'] === 'captured') and $invoice['status'] !== 'Paid' and (float)$invoice['total'] === (float)($charge['result']['Transactions']['0']['value']/100)){
			$AddTransaction = localAPI(
				'AddTransaction', 
				array(
					'invoiceid' =>  $_REQUEST['invoice_id'],
					'transid' => 'ggpp-'.$qrcode['charge_id'].'-'.$params_api['api_mode'],
					'paymentmethod' => 'gofasgalaxpaypix',
					'date' => date("d/m/Y"),
					'description' => 'Pagamento aprovado',
					'amountin'=> (float)($qrcode['amount']/100),
					'fees' => $params['fee'],
				),
				(int)$params['admin']
			);
		}
		echo $charge['result']['Transactions']['0']['status'];
	}
	if($params['log'] and $_REQUEST['debug']){
		echo '<pre>';
		echo 'invoice:<br>',print_r($invoice);
		echo 'access_token_:<br>',print_r($access_token);
		echo 'charge:<br>', print_r($charge);
		echo '</pre>';
		logModuleCall('gofasgalaxpaypix','receive_callback',array('module_version'=>'0.1.0','request'=>$_REQUEST),'', array( 'result'=>$result ) );
	}
}