<?php
/**
 * Módulo GalaxPay Pix para WHMCS
 * @copyright	2022 Gofas Software
 * @see			https://gofas.net/?p=14641
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=14644
 * @version		0.1.0
 */
use WHMCS\Database\Capsule;
require_once __DIR__.'/includes/config.php';
function gofasgalaxpaypix_link($params){
	if(stripos($_SERVER['REQUEST_URI'], 'viewinvoice.php') !== false ){
		require __DIR__.'/includes/functions.php';
		$log['params'] = $params;
		if($params['amount'] >= $params['minimunamount']){
			foreach( Capsule::table('tblconfiguration') -> where('setting', '=', 'ggppwhmcsurl') -> get( array( 'value','created_at') ) as $ggppwhmcsurl_ ){
				$ggppwhmcsurl					= $ggppwhmcsurl_->value;
			}
			$result .= '<script type="text/javascript" src="'.$ggppwhmcsurl.'modules/gateways/gofasgalaxpaypix/assets/js/copy2clipboard.js" charset="UTF-8"></script>';
			$result .= '<script type="text/javascript" src="'.$ggppwhmcsurl.'modules/gateways/gofasgalaxpaypix/assets/js/scripts.js" charset="UTF-8"></script>';
			$result .= '<input type="hidden" id="system_url" value="'.$ggppwhmcsurl.'">';
			$result .= '<input type="hidden" id="invoice_id" value="'.$params['invoiceid'].'">';
			$params_api = ggpp_api_connect();
			$customer = ggpp_customer($params['clientdetails']['id']);

			$saved_qr_code = ggpp_get_local_qrc($params['invoiceid']);
			if($saved_qr_code['image'] and (float)($saved_qr_code['amount']/100) === (float)$params['amount'] and $saved_qr_code['api_mode'] === $params_api['api_mode']){
				if($params['pix_logo']){
					$result .= '<img style="width: 140px;margin: 18px 10px 0px 0px;" src="'.$ggppwhmcsurl.'/modules/gateways/gofasgalaxpaypix/assets/img/pix.png"></a>';
				}
				if($params['top_message']){
					$result .= '<p style=" margin: 20px 0px 0px 0px; ">'.$params['top_message'].'</p>';
				}
				$result .= '<img style="width: 100%; max-width: 255px;" src="'. $saved_qr_code['image'].'" /><br>';
				$result .= '<p id="qrcodeforcopy" style="width: 0px;height: 0px;font-size: 0px;padding: 0px;margin: -60px 0px 60px 0px;">'.$saved_qr_code['qrcode'].'</p>';
				$button_func = "document.getElementById('qrcodeforcopy')";
				if($params['show_date']){
					$result .= '<p style=" margin: 0px 0px 10px 0px; ">Gerado em '.date('d/m/Y à\s H:i:s',strtotime($saved_qr_code['updated_at'])).'</p>';
				}
				if($params['show_total']){
					$result .= '<p style=" margin: -10px 0px 10px 0px; ">Total: R$ '.number_format( $params['amount'],  2, ',', '.').'</p>';
				}
				
				$result .= '<button id="copy_tooltip" class="btn btn-default" onclick="select_all_and_copy('.$button_func.')">Clique aqui para copiar</button>';
				$log['saved_qr_code'] = $saved_qr_code;
			}
			if(!$saved_qr_code['image'] || !$saved_qr_code['qrcode'] || (float)($saved_qr_code['amount']/100) !== (float)$params['amount'] || $saved_qr_code['api_mode'] !== $params_api['api_mode']){
				
				$access_token_ = ggpp_get_token();
				$access_token = $access_token_['result']['access_token'];

				 if($access_token_['result']['access_token']){
					 $access_token = $access_token_['result']['access_token'];
				 }
				 else{
					 $error .= $access_token_['response_code'].': '.json_encode($access_token_['result']);
				}
				$log['access_token_'] = $access_token_;
				$amount = ((int)$params['amount'])*100;
				$postfields = array(
					'access_token'=> $access_token,
					'charge'=> ['additionalInfo'=> substr( implode("\n",$line_items),  0, 400),
						'myId'=> $params['invoiceid'].time(),
						'value' => $amount,
						'payday'=>date("Y-m-d"),
						'payedOutsideGalaxPay' => false,
						'mainPaymentMethodId' => "pix",
						'Customer' => [
							'myId'=> $customer['id'],
							'name'=> $customer['name'],
							'document'=> $customer['document'],
							'emails'=> [
								$customer['email'],
							],
							'phones'=> [
								$customer['phone'],
							],
						],
    					'PaymentMethodPix'=> [
    					    'fine'=> 0,
    					    'interest'=> 0,
    					    'instructions'=> $params['top_message'],
    					    'Deadline'=> [
    					        'type'=> 'days',
    					        'value'=> 60
    					    ],
    					    'Discount'=> [
    					        'qtdDaysBeforePayDay'=> 1,
    					        'type'=> 'percent',
    					        'value'=> 0
    					   ]
    					],
					]
				);
				$qr_code_ = ggpp_charge($postfields);
				if((int)$qr_code_['result_code'] !== (int)200){
					$error .= $qr_code_['result']['error']['message'];
				}
				$log['qr_code_'] = $qr_code_;
				if($qr_code_['result']['Charge']['Transactions']['0']['Pix']['image']){
				
					if(!$saved_qr_code['image'] || !$saved_qr_code['qrcode']){
						$save_qrc = ggpp_save_qrc(
							[
								'invoice_id'=>$params['invoiceid'],
								'charge_id'=>$qr_code_['result']['Charge']['Transactions']['0']['chargeGalaxPayId'],
								'amount'=>$amount,
								'reference'=>$qr_code_['result']['Charge']['Transactions']['0']['Pix']['reference'],
								'qrcode'=>$qr_code_['result']['Charge']['Transactions']['0']['Pix']['qrCode'],
								'image'=>$qr_code_['result']['Charge']['Transactions']['0']['Pix']['image'],
								'api_mode'=>$params_api['api_mode'],
							]
						);
						if($save_qrc !== 'success'){
							$error .= $save_qrc;
						}
					}
					if($saved_qr_code['image']){
						$update_qrc = ggpp_update_qrc(
							[
								'invoice_id'=>$params['invoiceid'],
								'charge_id'=>$qr_code_['result']['Charge']['Transactions']['0']['chargeGalaxPayId'],
								'amount'=>$amount,
								'reference'=>$qr_code_['result']['Charge']['Transactions']['0']['Pix']['reference'],
								'qrcode'=>$qr_code_['result']['Charge']['Transactions']['0']['Pix']['qrCode'],
								'image'=>$qr_code_['result']['Charge']['Transactions']['0']['Pix']['image'],
								'api_mode'=>$params_api['api_mode'],
							]
						);
						//$update_qrc = ggpp_update_qrc($qr_code,$params['invoiceid'],$params['amount'],$params['clientdetails']['client_id'],$params_api['api_mode']);
						if($update_qrc !== 'success'){
							$error .= $update_qrc;
						}
					}
					if($params['pix_logo']){
						$result .= '<img style="width: 140px;margin: 18px 10px 0px 0px;" src="'.$ggppwhmcsurl.'/modules/gateways/gofasgalaxpaypix/assets/img/pix.png"></a>';
					}
					if(!$params['top_message']){
						$result .= '<p style=" margin: 20px 0px 0px 0px; ">Pague escaneando o QR code<br>ou copiando e colando a chave</p>';
					}
					if($params['top_message']){
						$result .= '<p style=" margin: 20px 0px 0px 0px; ">'.$params['top_message'].'</p>';
					}
					$result .= '<img style="width: 100%; max-width: 255px;" src="'.$qr_code_['result']['Charge']['Transactions']['0']['Pix']['image'].'" /><br>';
					$result .= '<p id="qrcodeforcopy" style="width: 0px;height: 0px;font-size: 0px;padding: 0px;margin: -60px 0px 60px 0px;">'.$qr_code_['result']['Charge']['Transactions']['0']['Pix']['qrCode'].'</p>';
					$button_func = "document.getElementById('qrcodeforcopy')";
					if($params['show_date']){
						$result .= '<p style=" margin: 0px 0px 10px 0px; ">Gerado em '.date('d/m/Y à\s H:i:s',strtotime(date("Y-m-d H:i:s"))).'</p>';
					}
					if($params['show_total']){
						$result .= '<p style=" margin: -10px 0px 10px 0px; ">Total: R$ '.number_format( $params['amount'],  2, ',', '.').'</p>';
					}
					$result .= '<button class="btn btn-default" onclick="select_all_and_copy('.$button_func.')">Clique aqui para copiar</button>';
				}
			}
			if($error){
		    	$result = '<b style="color:red;">Erro: '.$error.'</b>';
			}
			if($params['log']){
				foreach( Capsule::table('tblconfiguration') -> where('setting','=','ggpp_version') -> get(['value']) as $ggpp_version_ ){
					$ggpp_version			= $ggpp_version_->value;
				}
				logModuleCall('gofasgalaxpaypix','gofasgalaxpaypix_link',array('module_version'=>$ggpp_version,),'', $log );
				//echo '<pre style="height:250px;">',$url,'<br>',print_r($log),'</pre>';
			}
			return $result;
		}
		elseif( $params['amount'] < $params['minimunamount']){
			$error .= 'O valor mínimo para utilizar esse método de pagamento é '.number_format( $params['minimunamount'] ,  2, ',', '.').'.';
			return $error;
		}
	}
}
/*
function gofasgalaxpaypix_refund($params){
	require_once __DIR__.'/functions.php';
	$params_api = ggpp_api_connect();
	$access_token_ = ggpp_get_token();
	$access_token = $access_token_['result']['access_token'];
	$charge_id = ggpp_get_string_between($params['transid'], 'ggpp-', '-'.$params_api['api_mode']);
	$refund = ggpp_refund($charge_id,$access_token);

	$GetTransactions = localAPI('GetTransactions',array('transid' => $params['transid']), (int)$params['admin']);
	$dt = new DateTime($GetTransactions['transactions']['transaction']['0']['date']);
	$payment_date = $dt->format('Ymd');
	$today = date('Ymd');
	if((int)$today > (int)$payment_date){
		$fee = $GetTransactions['transactions']['transaction']['0']['fees'];
	}
	elseif((int)$today === (int)$payment_date){
		$fee = NULL;
	}
	if($params['log']){
		logModuleCall('gofasgalaxpaypix', 'refund_payment', array('module_version'=>ggpp_version(),'params'=>$params,'GetTransactions'=>$GetTransactions), 'post',  array('access_token'=> $access_token,'charge_id'=> $charge_id,'refund'=>$refund), 'replaceVars');
	}
	if( $refund['result']['error'] || (int)$refund['result_code'] !== 200){
		return array(
    	    'status' => 'error',
	        'rawdata' => $refund,
	    );
	}
	if((int)$refund['result_code'] === 200){
	    return array(
        	'status' => 'success',
        	'rawdata' => $refund,
        	'ggpp-'.$charge['result']['Charge']['galaxPayId'].'-'.$params_api['api_mode'].'-'.$charge_id.'.',
			'fee' => $fee,
    	);
	}
}
*/