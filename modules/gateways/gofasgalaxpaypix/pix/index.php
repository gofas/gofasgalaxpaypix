<?php
/**
 * Módulo Juno PIX para WHMCS
 * @copyright	2020 Gofas Software
 * @see			https://gofas.net/?p=14641
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=14299
 * @version		1.0.2
 */
use WHMCS\Database\Capsule;
require_once __DIR__.'/includes/config.php';
function gofasgalaxpaypix_link($params){
	if(stripos($_SERVER['REQUEST_URI'], 'viewinvoice.php') !== false ){
		require __DIR__.'/includes/functions.php';		
		require __DIR__.'/includes/params.php';
		$log['params'] = $params;
		if($params['amount'] >= $params['minimunamount']){
			foreach( Capsule::table('tblconfiguration') -> where('setting', '=', 'ggppwhmcsurl') -> get( array( 'value','created_at') ) as $ggppwhmcsurl_ ){
				$ggppwhmcsurl					= $ggppwhmcsurl_->value;
			}
			$result .= '<script type="text/javascript" src="'.$ggppwhmcsurl.'modules/gateways/gofasgalaxpaypix/assets/js/copy2clipboard.js" charset="UTF-8"></script>';
			$result .= '<script type="text/javascript" src="'.$ggppwhmcsurl.'modules/gateways/gofasgalaxpaypix/assets/js/scripts.js" charset="UTF-8"></script>';
			$result .= '<input type="hidden" id="system_url" value="'.$ggppwhmcsurl.'">';
			$result .= '<input type="hidden" id="invoice_id" value="'.$params['invoiceid'].'">';
			$access_token_ = ggpp_get_token($api_url.'authorization-server/oauth/token',$client_id,$client_secret);
			 if($access_token_['response']['access_token']){
				 $access_token = $access_token_['response']['access_token'];
			 }
			 else{
				 $error .= $access_token_['response_code'].': '.json_encode($access_token_['response']);
			}
			$log['access_token_'] = $access_token_;

			/// Webhooks
			$webhook_url = $ggppwhmcsurl.'modules/gateways/gofasgalaxpaypix/includes/callback.php';
			$url=$charge_url.'notifications/webhooks';
			$header = [
				'content-type: application/json;charset=UTF-8',
				'accept-charset: utf-8',
				'x-platform: gofaspixparawhmcs',
				'X-Api-Version: 2',
				'X-Resource-Token: '.$private_token,
				'Authorization: Bearer '.$access_token,
			];
			/// Check
			$check_webhook_request = 'GET';
			$check_webhook = ggpp_charge($url,$access_token,$private_token,$header,$check_webhook_request);
			$log['check_webhook'] = $check_webhook;
			if($check_webhook['_embedded']['webhooks']){
				foreach($check_webhook['_embedded']['webhooks'] as $key => $value){
					if ((string)$webhook_url === (string)$value['url'] //and
						//$value['status'] === 'ACTIVE' and 
						//$value['eventTypes']['0']['name'] === 'PAYMENT_NOTIFICATION' and
						//$value['eventTypes']['0']['status'] === 'ENABLED' and
						//$value['eventTypes']['1']['name'] === 'CHARGE_STATUS_CHANGED' and
						//$value['eventTypes']['1']['status'] === 'ENABLED'
						){
						$check_webhook_list[$key] = $value;
					}
					if ((string)$webhook_url !== (string)$value['url'] //and
						//$value['status'] === 'ACTIVE' and 
						//$value['eventTypes']['0']['name'] === 'PAYMENT_NOTIFICATION' and
						//$value['eventTypes']['0']['status'] === 'ENABLED' and
						//$value['eventTypes']['1']['name'] === 'CHARGE_STATUS_CHANGED' and
						//$value['eventTypes']['1']['status'] === 'ENABLED'
						){
						$webhooks_to_exclude[$key] = $value;
					}
				}
			}
			$log['check_webhook_list'] = $check_webhook_list;
			$log['webhooks_to_exclude'] = $webhooks_to_exclude;

			if($webhooks_to_exclude['0']['id']){
				$del_header = [
					'content-type: application/json;charset=UTF-8',
					'accept-charset: utf-8',
					'x-platform: gofaspixparawhmcs',
					'X-Api-Version: 2',
					'X-Resource-Token: '.$private_token,
					'Authorization: Bearer '.$access_token,
				];
			
				$del_url=$charge_url.'notifications/webhooks/'.$webhooks_to_exclude['0']['id'];
				$delete_webhook = ggpp_charge($del_url,$access_token,$private_token,$del_header,'DELETE');
				$log['delete_webhook'] = $delete_webhook;
			}

			if($check_webhook_list['0']['url'] === $webhook_url){
				$webhook = $check_webhook_list['0'];
			}
			else{
				// Create webhook
				$webhook_request = ['url'=>$webhook_url,'eventTypes'=>['PAYMENT_NOTIFICATION','CHARGE_STATUS_CHANGED']];
				$webhook = ggpp_charge($url,$access_token,$private_token,$header,$webhook_request);
			}
			if($webhook['error']){
				$error .= $webhook['details']['0']['message'];
			}
			$log['webhook_request'] = $webhook_request;
			$log['webhook'] = $webhook;
				
			if($pix_key){
				$log['pix_key'] = $pix_key;
			}
			if(!$pix_key){
				$ggpp_ramdom_key_name = 'ggpp_ramdom_key_'.$api_mode;
				foreach( Capsule::table('tblconfiguration') -> where('setting', '=', $ggpp_ramdom_key_name) -> get( array( 'value','created_at') ) as $ggpp_ramdom_key_ ){
					$ggpp_ramdom_key = json_decode($ggpp_ramdom_key_->value, true);
					$log[$ggpp_ramdom_key_name] = $ggpp_ramdom_key;
				}
				if($ggpp_ramdom_key and ((string)$ggpp_ramdom_key['api_mode'] === (string)$api_mode)){
					$pix_key = $ggpp_ramdom_key['key'];
				}
				if(!$ggpp_ramdom_key || ((string)$ggpp_ramdom_key['api_mode'] !== (string)$api_mode)){			
					$url=$charge_url.'pix/keys';
					$header = [
						'content-type: application/json;charset=UTF-8',
						'accept-charset: utf-8',
						'x-platform: gofaspixparawhmcs',
						'X-Api-Version: 2',
						'X-Resource-Token: '.$private_token,
						'X-Idempotency-Key: '.ggpp_gen_uuid(),
						'Authorization: Bearer '.$access_token,
					];
					$ramdom_key = ggpp_charge($url,$access_token,$private_token,$header,['type'=>'RANDOM_KEY']);
					if($ramdom_key['error']){
						$error .= $ramdom_key['details']['0']['message'];
					}
					$ramdom_key['api_mode'] = $api_mode;
					$log['ramdom_key'] = $ramdom_key;
					if(!$error){
						$pix_key = $ramdom_key['key'];
					}
					if(!$error and !$ggpp_ramdom_key){
						try { Capsule::table('tblconfiguration')->insert(array('setting' => $ggpp_ramdom_key_name, 'value' => json_encode($ramdom_key), 'created_at' => date("Y-m-d H:i:s") , 'updated_at' => date("Y-m-d H:i:s")));}
						catch (\Exception $e){ $e->getMessage(); }
					}
				}
			}
			$saved_qr_code = ggpp_get_local_qrc($params['invoiceid']);
			if($saved_qr_code['imageInBase64'] and (float)$saved_qr_code['amount'] === (float)$params['amount'] and $saved_qr_code['api_mode'] === $api_mode){
				if($params['pix_logo']){
					$result .= '<img style="width: 140px;margin: 18px 10px 0px 0px;" src="'.$ggppwhmcsurl.'/modules/gateways/gofasgalaxpaypix/assets/img/pix.png"></a>';
				}
				if($params['top_message']){
					$result .= '<p style=" margin: 20px 0px 0px 0px; ">'.$params['top_message'].'</p>';
				}
				$result .= '<img src="data:image/gif;base64,' . $saved_qr_code['imageInBase64'] . '" /><br>';
				$result .= '<p id="qrcodeforcopy" style="width: 0px;height: 0px;font-size: 0px;padding: 0px;margin: -60px 0px 60px 0px;">'.base64_decode($saved_qr_code['payloadInBase64']).'</p>';
				$button_func = "document.getElementById('qrcodeforcopy')";
				if($params['show_date']){
					$result .= '<p style=" margin: -15px 0px 10px 0px; ">Gerado em '.date('d/m/Y à\s H:i:s',strtotime($saved_qr_code['updated_at'])).'</p>';
				}
				if($params['show_total']){
					$result .= '<p style=" margin: -10px 0px 10px 0px; ">Total: R$ '.number_format( $params['amount'],  2, ',', '.').'</p>';
				}
				//$result .= '<div id="copy_tooltip"></div>';
				$result .= '<button id="copy_tooltip" class="btn btn-default" onclick="select_all_and_copy('.$button_func.')">Clique aqui para copiar</button>';
				$log['saved_qr_code'] = $saved_qr_code;
			}
			if(!$saved_qr_code['imageInBase64'] || !$saved_qr_code['payloadInBase64'] || (float)$saved_qr_code['amount'] !== (float)$params['amount'] || $saved_qr_code['api_mode'] !== $api_mode){
				$header = [
					'content-type: application/json;charset=UTF-8',
					'accept-charset: utf-8',
					'x-platform: gofaspixparawhmcs',
					'X-Api-Version: 2',
					'X-Resource-Token: '.$private_token,
					'X-Idempotency-Key: '.ggpp_gen_uuid(),
					'Authorization: Bearer '.$access_token,
				];
				$customer = ggpp_customer($params['clientdetails']['id']);
				$qr_code_request = [
					"charge"=>[
						"pixKey"=> $pix_key,
						"pixIncludeImage"=> true,
						"references"=>[$params['invoiceid']],
						"amount"=> (float)$params['amount'],
						"description"=> 'Fatura #'.$params['invoiceid'],
						"paymentTypes"=> ['BOLETO_PIX'],
					],
					"billing"=>[
						'name'=>$customer['name'],
						'document'=>$customer['document'],
						'email'=>$customer['email'],
						'address'=>[
							'street'=>$customer['address'],
							'number'=>$customer['number'],
							'city'=>$customer['city'],
							'state'=>$customer['state'],
							'postCode'=>$customer['postcode'],
						],
					],
				];
				$log['qr_code_request'] = $qr_code_request;
				$log['header'] = $header;
				$url=$charge_url.'charges';
				$qr_code_ = ggpp_charge($url,$access_token,$private_token,$header,$qr_code_request);
				if($qr_code_['error']){
					$error .= $qr_code_['details']['0']['message'];
				}
				$log['qr_code_'] = $qr_code_;
				if($qr_code_['_embedded']['charges']['0']['pix']['imageInBase64']){
					$qr_code = $qr_code_['_embedded']['charges']['0']['pix'];
					$qr_code['charge_id'] = $qr_code_['_embedded']['charges']['0']['id'];
					$payloadInBase64 = base64_decode($qr_code['payloadInBase64']);
					$log['payloadInBase64'] = $payloadInBase64;
					
					if(!$saved_qr_code['imageInBase64'] || !$saved_qr_code['payloadInBase64']){
						$save_qrc = ggpp_save_qrc($qr_code,$params['invoiceid'],$params['amount'],$params['clientdetails']['client_id'],$api_mode);
						if($save_qrc !== 'success'){
							$error .= $save_qrc;
						}
					}
					if($saved_qr_code['imageInBase64']){
						$update_qrc = ggpp_update_qrc($qr_code,$params['invoiceid'],$params['amount'],$params['clientdetails']['client_id'],$api_mode);
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
					$result .= '<img src="data:image/gif;base64,' . $qr_code['imageInBase64'] . '" /><br>';
					$result .= '<p id="qrcodeforcopy" style="width: 0px;height: 0px;font-size: 0px;padding: 0px;margin: -60px 0px 60px 0px;">'.base64_decode($qr_code['payloadInBase64']).'</p>';
					$button_func = "document.getElementById('qrcodeforcopy')";
					if($params['show_date']){
						$result .= '<p style=" margin: -15px 0px 10px 0px; ">Gerado em '.date('d/m/Y à\s H:i:s',strtotime(date("Y-m-d H:i:s"))).'</p>';
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
				//echo '<pre>',$url,'<br>',print_r($log),'</pre>';
			}
			return $result;
		}
		elseif( $params['amount'] < $params['minimunamount']){
			$error .= 'O valor mínimo para utilizar esse método de pagamento é '.number_format( $params['minimunamount'] ,  2, ',', '.').'.';
			return $error;
		}
	}
}