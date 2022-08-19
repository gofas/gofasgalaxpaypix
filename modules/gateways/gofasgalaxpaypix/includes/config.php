<?php
/**
 * Módulo GalaxPay Pix para WHMCS
 * @copyright	2022 Gofas Software
 * @see			https://gofas.net/?p=14641
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=14644
 * @version		0.1.0
 */

if( !defined('WHMCS')){ die(''); }
use WHMCS\Database\Capsule;
function gofasgalaxpaypix_MetaData(){
    return array(
        'DisplayName' => 'Gofas GalaxPay - Pix',
        'APIVersion' => '1.1',
    );
}
function gofasgalaxpaypix_config(){
	$module_version = '0.1.0';
	$module_version_int = (int)preg_replace("/[^0-9]/", "", $module_version);
	if( !function_exists('ggpp_verifyInstall') ){
	function ggpp_verifyInstall(){
		if( !Capsule::schema()->hasTable('gofasgalaxpaypix') ){
    		try {
				Capsule::schema()->create('gofasgalaxpaypix', function($table){
					$table->string('invoice_id');
					$table->string('charge_id');
					$table->string('amount');
					$table->text('reference');
					$table->string('qrcode');
					$table->text('image');
					$table->string('api_mode');
					$table->string('created_at');
					$table->string('updated_at');
    			});
			}
			catch (\Exception $e){
    			$error .= "Não foi possível criar a tabela do módulo no banco de dados: {$e->getMessage()}";
			}
		}
		if(!$error){
			return array('sucess'=>1);
		}
		elseif($error){
			return array('error'=>$error);
		}
	}}
	$verifyInstall = ggpp_verifyInstall();
	if($verifyInstall['error']){
		$error = $verifyInstall['error'];
	}
	$actual_link		= (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	if( stripos( $actual_link, '/configgateways.php') ){
		$whmcs_url__ = str_replace("\\",'/',(isset($_SERVER['HTTPS']) ? "https://" : "http://").$_SERVER['HTTP_HOST'].substr(getcwd(),strlen($_SERVER['DOCUMENT_ROOT'])));
		$admin_url = $whmcs_url__.'/';
		$vtokens = explode('/', $actual_link);
		$whmcs_admin_path = '/'.$vtokens[sizeof($vtokens)-2].'/';
		$whmcs_url = str_replace( $whmcs_admin_path, '', $admin_url).'/';
		foreach( Capsule::table('tblconfiguration') -> where('setting', '=', 'ggppwhmcsurl') -> get( array( 'value','created_at') ) as $ggppwhmcsurl_ ){
			$ggppwhmcsurl					= $ggppwhmcsurl_->value;
			$ggppwhmcsurl_created_at			= $ggppwhmcsurl_->created_at;
		}
		foreach( Capsule::table('tblconfiguration') -> where('setting', '=', 'ggppwhmcsadminurl') -> get( array( 'value','created_at') ) as $ggppwhmcsadminurl_ ){
			$ggppwhmcsadminurl				= $ggppwhmcsadminurl_->value;
			$ggppwhmcsadminurl_created_at	= $ggppwhmcsurl_->created_at;
		}
		foreach( Capsule::table('tblconfiguration') -> where('setting', '=', 'ggppwhmcsadminpath') -> get( array( 'value','created_at') ) as $ggppwhmcsadminpath_ ){
			$ggppwhmcsadminpath				= $ggppwhmcsadminpath_->value;
			$ggppwhmcsadminpath_created_at	= $ggppwhmcsurl_->created_at;
		}
		if( !$ggppwhmcsurl ){
			try { Capsule::table('tblconfiguration')->insert(array('setting' => 'ggppwhmcsurl', 'value' => $whmcs_url, 'created_at' => date("Y-m-d H:i:s") , 'updated_at' => date("Y-m-d H:i:s")));}
			catch (\Exception $e){ $e->getMessage(); }
			try { Capsule::table('tblconfiguration')->insert(array('setting' => 'ggppwhmcsadminurl', 'value' => $admin_url, 'created_at' => date("Y-m-d H:i:s") , 'updated_at' => date("Y-m-d H:i:s")));}
			catch (\Exception $e){ $e->getMessage(); }
			try { Capsule::table('tblconfiguration')->insert(array('setting' => 'ggppwhmcsadminpath', 'value' => $whmcs_admin_path, 'created_at' => date("Y-m-d H:i:s") , 'updated_at' => date("Y-m-d H:i:s")));}
			catch (\Exception $e){ $e->getMessage(); }
		}
		if( $ggppwhmcsurl and ($whmcs_url !== $ggppwhmcsurl) ){
			try { Capsule::table('tblconfiguration')->where( 'setting', 'ggppwhmcsurl')->update(array('value' => $whmcs_url, 'created_at' =>  $ggppwhmcsurl_created_at , 'updated_at' => date("Y-m-d H:i:s")));}
			catch (\Exception $e){$e->getMessage();}
		}
		if( $ggppwhmcsadminurl and ($admin_url !== $ggppwhmcsadminurl) ){
			try { Capsule::table('tblconfiguration')->where( 'setting', 'ggppwhmcsadminurl')->update(array('value' => $admin_url, 'created_at' =>  $ggppwhmcsadminurl_created_at , 'updated_at' => date("Y-m-d H:i:s")));}
			catch (\Exception $e){$e->getMessage();}
		}
		if( $ggppwhmcsadminpath and ($whmcs_admin_path !== $ggppwhmcsadminpath) ){
			try { Capsule::table('tblconfiguration')->where( 'setting', 'ggppwhmcsadminpath')->update(array('value' => $whmcs_admin_path, 'created_at' =>  $ggppwhmcsadminpath_created_at , 'updated_at' => date("Y-m-d H:i:s")));}
			catch (\Exception $e){$e->getMessage();}
		}
		if( !$ggpp_version ){
			try { Capsule::table('tblconfiguration')->insert(array('setting' => 'ggpp_version', 'value' =>$module_version, 'created_at' => date("Y-m-d H:i:s") , 'updated_at' => date("Y-m-d H:i:s")));}
			catch (\Exception $e){ $e->getMessage(); }
		}
		if( $ggpp_version and (string)$ggpp_version !== (string)$module_version){
			try { Capsule::table('tblconfiguration')->where( 'setting', 'ggpp_version')->update(array('value' => $module_version, 'created_at' =>  $ggpp_version_created_at , 'updated_at' => date("Y-m-d H:i:s")));}
			catch (\Exception $e){$e->getMessage();}
		}
	}
	if( !function_exists('ggpp_verify_module_updates') ){
	function ggpp_verify_module_updates($page_id, $referer,$module_version){
   		$query = 'https://gofas.net/br/updates/?software='.$page_id.'&referer='.$referer.'&version='.$module_version;
    	$curl = curl_init();
    	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
    	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
    	curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
    	curl_setopt($curl, CURLOPT_URL, $query);
		$result = curl_exec($curl);
    	$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		return array(
			'http_status' => $http_status,
			'result' => $result,
		);
	}}
	$available_update_ = ggpp_verify_module_updates('14641',$whmcs_url,$module_version);
	if( (int)$available_update_['http_status'] === 200 ){
		$available_update = $available_update_['result'];
		$available_update_int = (int)preg_replace("/[^0-9]/", "", $available_update);
	}
	else {
		$available_update_int = 000;
	}
	if( $available_update_int === $module_version_int ){
		$available_update_message = '<p style="color: green"><i class="fas fa-check-square"></i> Você está executando a versão mais recente do módulo.</p>';
	}
	if( $available_update_int > $module_version_int ){
		$available_update_message = '<p style="font-size: 14px; color: red;"><i class="fas fa-exclamation-triangle"></i> Atualização disponível, verifique a <a style="color:#CC0000;text-decoration:underline;" href="https://gofas.net/?p=14641" target="_blank">versão '.$available_update.'</a>';
	}
	if( $available_update_int < $module_version_int ){
		$available_update_message = '<p style="font-size: 14px; color: orange;"><i class="fas fa-exclamation-triangle"></i> Você está executando uma versão Beta desse módulo.<br>Não recomendamos o uso dessa versão em produção.<br>Baixar versão estável: <a style="color:#CC0000;text-decoration:underline;" href="https://gofas.net/?p=14641" target="_blank">v'.$available_update.'</a>';
	}
	if( $available_update_int === 000 ){
		$available_update_message = '';
	}
	$tbladmins = array();
	foreach( Capsule::table('tbladmins') -> get() as $tbladmins_ ){
		$tbladmins[$tbladmins_->id] = $tbladmins_->id.' - '.$tbladmins_->firstname.' '.$tbladmins_->lastname.' ('.$tbladmins_->username.')';
	}
	$tblticketdepartments = array();
	$tblticketdepartments[] = '';
	foreach( Capsule::table('tblticketdepartments') -> get() as $tblticketdepartments_ ){
		$tblticketdepartments_id			= $tblticketdepartments_->id;
		$tblticketdepartments_name			= $tblticketdepartments_->name;
		$tblticketdepartments[]				= $tblticketdepartments_id.' - '.$tblticketdepartments_name;
	}
	$opt_num = 1;
	$renderize = array(
		'FriendlyName' => array(
			'Type' => 'System',
			'Value' => 'Gofas GalaxPay - Pix',
		),
		'separator_1' => array(
			'Description' => '
			<div class="ggpc_separator" style="padding: 1px 15px 9px;">
				<div style="float: right; padding: 0px;">
				<a target="_blank" href="https://app.galaxpay.com.br/abrir-conta?affiliateHash=34c8f0bb"><img style=" width: 300px;" src="'.$whmcs_url.'/modules/gateways/gofasgalaxpaycartao/assets/img/gofasgalaxpaycartao.png"></a>
				</div>
				<div style="margin-left: 10px;">
					<h4 style="padding-top: 5px;">Módulo Gofas GalaxPay - Pix para WHMCS v'.$module_version.'</h4>
					'.$available_update_message.'
					<p><a style="text-decoration:underline;" target="_blank" href="https://gofas.net/?p=14641#configuration">Documentação do módulo</a>.</p>
					<p><a style="text-decoration:underline;" target="_blank" href="https://docs.galaxpay.com.br/">Documentação da API GalaxPay</a>.</p>
					<p>Crie um <a style="text-decoration:underline;" target="_blank" href="'.$admin_url.'/configcustomfields.php">campo personalizado de cliente</a> para CPF e/ou CNPJ, ou se preferir, crie dois campos distintos, um campo apenas para CPF e outro campo para CNPJ. O módulo identifica os campos do perfil do cliente automaticamente.</p>
				</div>
			</div>',
		),
		'separator_2' => array(
			'Description' => '<h2>Credenciais API - Produção</h3>',
		),
		// Secret Token
		'galax_id' => array(
			'FriendlyName' => $opt_num++.'- Galax ID<span class="ggpc_required">*</span>',
			'Type' => 'text',
			'Size' => '50',
			'Default' => '',
			'Description' => '<span class="ggpc_required_txt">(Obrigatório)</span> Galax ID | Produção. <a target="_blank" style="text-decoration:underline;" href="https://docs.galaxpay.com.br/suporte">Obter Galax ID</a>',
		),
		'galax_hash' => array(
			'FriendlyName' => $opt_num++.'- Galax Hash<span class="ggpc_required">*</span>',
			'Type' => 'text',
			'Size' => '50',
			'Default' => '',
			'Description' => '<span class="ggpc_required_txt">(Obrigatório)</span> Galax Hash | Produção. <a target="_blank" style="text-decoration:underline;" href="https://docs.galaxpay.com.br/suporte">Obter Galax Hash</a>',
		),
		'separator_3' => array(
			'Description' => '<h2>Credenciais API - Testes</h2>',
		),
		'sandbox_galax_id' => array(
			'FriendlyName' => $opt_num++.'- Sandbox Galax ID<span class="ggpc_required">*</span>',
			'Type' => 'text',
			'Size' => '50',
			'Default' => '',
			'Description' => '<span class="ggpc_required_txt">(Obrigatório)</span> Galax ID | Testes. <a target="_blank" style="text-decoration:underline;" href="https://docs.galaxpay.com.br/autenticacao">Obter Galax ID</a>',
		),
		// Sandbox Secret Token
		'sandbox_galax_hash' => array(
			'FriendlyName' => $opt_num++.'- Sandbox Galax Hash<span class="ggpc_required">*</span>',
			'Type' => 'text',
			'Size' => '50',
			'Default' => '',
			'Description' => '<span class="ggpc_required_txt">(Obrigatório)</span> Galax Hash | Testes. <a target="_blank" style="text-decoration:underline;" href="https://docs.galaxpay.com.br/autenticacao">Obter Galax Hash</a>',
		),
		// All others settings
		'separator_4' => array(
			'Description' => '<h2>Configurações gerais</h2>',
		),
		'admin' => array(
			'FriendlyName' => $opt_num++.'- Administrador do WHMCS<span class="ggpp_required">*</span>',
			'Type'          => 'dropdown',
			'Default' 		=> key(reset($tbladmins)),
            'Options'       => $tbladmins,
			'Description' => 'Defina o administrador com permissões para utilizar a API interna do WHMCS.',
		),
		// Sandbox
		'sandbox' => array(
			'FriendlyName' => $opt_num++.'- <i>Sandbox</i>',
			'Type' => 'yesno',
			'Default' => 'yes',
			'Description' => 'Ative essa opção para gerar cobranças em modo de testes.',
		),
		// Log
		'log' => array(
			'FriendlyName' => $opt_num++.'- Salvar Logs',
			'Type' => 'yesno',
			'Default' => 'yes',
			'Description' => 'Salva informações de diagnóstico em <a target="_blank" style="text-decoration: underline;" href="'.$admin_url.'/systemmodulelog.php">Utilitários > Logs > Log de Módulo</a>. Para funcionar, antes é necessário ativar o debug de módulo clicando em "Ativar Log de Debug". <a target="_blank" style="text-decoration: underline;" href="'.$admin_url.'/systemmodulelog.php">VER LOG</a>.',
		),
		// minimum amount
		'minimunamount' => array(
			'FriendlyName' => $opt_num++.'- Valor mínimo',
			'Type' => 'text',
			'Size' => '10',
			'Default' => '5',
			'Description' => 'Insira o valor total mínimo da fatura para permitir pagamento via Cartão. Formato: Decimal, separado por ponto. Maior ou igual a sua tarifa (a partir de 2.50) e menor ou igual a 1000000.00.',
		),
		// fee
		'fee' => array(
			'FriendlyName' => $opt_num++.'- Tarifa Pix',
			'Type' => 'text',
			'Size' => '10',
			'Default' => '0.99',
			'Description' => 'Insira o valor da tarifa paga à GalaxPay por cada Pix recebido. Formato: Decimal, separado por ponto (0.99)',
		),
		// top message
		'top_message' => array(
			'FriendlyName' => $opt_num++.'- Mensagem acima do código QR',
			'Type' => 'text',
			'Size' => '50',
			'Default' => 'Pague escaneando o QR code<br>ou copiando e colando a chave',
			'Description' => 'Permitido HTML',
		),
		// Logo
		'pix_logo' => array(
			'FriendlyName' => $opt_num++.'- Exibir Logo PIX',
			'Type' => 'yesno',
			'Default' => 'yes',
			'Description' => 'Exibe logotipo "PIX powered by Banco Central" na fatura, acima do <i>QR Code</i>',
		),
		// Data e hora
		'show_date' => array(
			'FriendlyName' => $opt_num++.'- Exibir data e hora do código QR',
			'Type' => 'yesno',
			'Default' => 'yes',
			'Description' => 'Exemplo: "Gerado em 08/01/2022 às 08:06:30"',
		),
		// Log
		'show_total' => array(
			'FriendlyName' => $opt_num++.'- Exibir valor total do código QR',
			'Type' => 'yesno',
			'Default' => 'yes',
			'Description' => 'Exemplo: "Total: R$ 24.800,00"',
		),
	);
	$footer = array('footer' => array(
			'Description' => '<div class="ggpp_section">
			<p>&copy; '.date('Y').' <a style="text-decoration:underline;" target="_blank" title="↗ Gofas.net" href="https://gofas.net">Gofas.net</a> | <a style="text-decoration:underline;" target="_blank" title="↗ Gofas.net" href="https://gofas.net/?p=14641#changelog">'.$module_version.'</a> | <a  style="text-decoration:underline;"target="_blank" title="↗ Documentação" href="https://gofas.net/?p=14641">Documentação</a> | <a style="text-decoration:underline;" target="_blank" title="↗ Fórum de Suporte" href="https://gofas.net/foruns/">Suporte</a>.</p>
			<p style="font-size: 11px;">
			Ao utilizar esse módulo você concorda com nosso <a style="text-decoration:underline;" target="_blank" title="↗ Contrato de licença de uso de software" href="https://gofas.net/?p=9340">contrato de licença de uso de software</a>.
			</p>
			'.$available_update_message.'
			</div>',
		),
	);
	return array_merge($renderize,$footer);
}