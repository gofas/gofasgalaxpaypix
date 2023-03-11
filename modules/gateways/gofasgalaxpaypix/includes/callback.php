<?php
/**
 * Módulo GalaxPay Pix para WHMCS
 * @copyright	2022 Gofas Software
 * @see			https://gofas.net/?p=14685
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=14690
 * @version		0.1.0
 */
require_once __DIR__ . '/../../../../init.php';
require_once __DIR__ . '/../../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../../includes/invoicefunctions.php';

$params = getGatewayVariables('gofasgalaxpaypix');
//if(!$params['type']){die("Module Not Activated");}
if($_REQUEST['invoice_id']){
	require __DIR__.'/functions.php';
	$params_api = ggpp_api_connect();
	$invoice = localAPI('getinvoice',array('invoiceid'=> $_REQUEST['invoice_id']),(int)$params['admin']);
	if( $invoice['invoiceid']){
		$qrcode = ggpp_get_local_qrc($_REQUEST['invoice_id']);	
		$charge = ggpp_charge_verify($qrcode['charge_id']);
		
		if(($charge['result']['Transactions']['0']['status'] === 'payedPix' || $charge['result']['Transactions']['0']['status'] === 'captured')
			and $invoice['status'] !== 'Paid' and (float)$invoice['total'] === (float)($charge['result']['Transactions']['0']['value']/100)){
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
		if($charge['result']['Transactions']['0']['status']){
			echo $charge['result']['Transactions']['0']['status'];
		}
	}
	if($params['log']){
		//logModuleCall('gofasgalaxpaypix','receive_callback',array('module_version'=>'0.1.0','request'=>$_REQUEST),'', array( 'result'=>$result ) );
	}
	if($_REQUEST['debug4857yue7r623dg']){
		echo '<pre>';
		echo 'invoice:<br>',print_r($invoice);
		echo 'qrcode:<br>',print_r($qrcode);
		echo 'charge:<br>', print_r($charge);
		echo 'AddTransaction:<br>', print_r($AddTransaction);
		echo '</pre>';
		logModuleCall('gofasgalaxpaypix','receive_callback',array('module_version'=>ggpp_version(),'request'=>$_REQUEST),'', array( 'result'=>$result ) );
	}
}