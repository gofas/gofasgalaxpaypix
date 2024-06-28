<?php
/**
 * Módulo GalaxPay Pix para WHMCS
 * @copyright	2022 Gofas Software
 * @see			https://gofas.net/?p=14685
 * @license		https://gofas.net/?p=9340
 * @support		https://gofas.net/?p=14690
 * @version		1.2.1
 */

if( !defined('WHMCS')){ die(''); }
use WHMCS\Database\Capsule;
function gofasgalaxpaypix_MetaData(){
    return array(
        'DisplayName' => 'Gofas GalaxPay Pix',
        'APIVersion' => '1.1',
    );
}
function gofasgalaxpaypix_config(){
	if(stripos($_SERVER['REQUEST_URI'], '/configgateways.php')!==false){
		require __DIR__.'/functions.php';
		$module_version = '1.2.1';
		$module_page	= '14685';
		$verify_install = ggpp_verify_install();
		$whmcs_url = ggpp_whmcs_url();
		$check_updates = ggpp_verify_module_updates($module_page,$whmcs_url['url'],$module_version);
		$tbladmins = ggpp_tbladmins();
		$opt_num = 1;
		$renderize = array(
			'FriendlyName' => array(
				'Type' => 'System',
				'Value' => 'Gofas GalaxPay Pix',
			),
			'separator_1' => array(
				'Description' => '
				<div class="ggpc_separator" style="padding: 1px 15px 9px;">
					<div style="float: right; padding: 0px;">
					'.ggpp_decrypt($check_updates['check']).'
					</div>
					<div style="margin-left: 10px;">
						<h4 style="padding-top: 5px;">Módulo Gofas GalaxPay Pix para WHMCS v'.$module_version.'</h4>
						'.$check_updates['message'].'
						<p><a style="text-decoration:underline;" target="_blank" href="https://gofas.net/?p=14685#configuration">Documentação do módulo</a>.</p>
						<p><a style="text-decoration:underline;" target="_blank" href="https://docs.galaxpay.com.br/">Documentação da API GalaxPay</a>.</p>
						<p>Crie um <a style="text-decoration:underline;" target="_blank" href="'.$whmcs_url['admin_url'].'/configcustomfields.php">campo personalizado de cliente</a> para CPF e/ou CNPJ, ou se preferir, crie dois campos distintos, um campo apenas para CPF e outro campo para CNPJ. O módulo identifica os campos do perfil do cliente automaticamente.</p>
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
				'Default' 		=> array_shift(array_values($tbladmins)),
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
				'Description' => 'Salva informações de diagnóstico em <a target="_blank" style="text-decoration: underline;" href="'.$whmcs_url['admin_url'].'/systemmodulelog.php">Utilitários > Logs > Log de Módulo</a>. Para funcionar, antes é necessário ativar o debug de módulo clicando em "Ativar Log de Debug". <a target="_blank" style="text-decoration: underline;" href="'.$whmcs_url['admin_url'].'/systemmodulelog.php">VER LOG</a>.',
			),
			// minimum amount
			'minimunamount' => array(
				'FriendlyName' => $opt_num++.'- Valor mínimo',
				'Type' => 'text',
				'Size' => '10',
				'Default' => '5',
				'Description' => 'Insira o valor total mínimo da fatura para permitir pagamento via Pix. Formato: Decimal, separado por ponto. Maior ou igual a sua tarifa (a partir de 2.50) e menor ou igual a 1000000.00.',
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
				<p>&copy; '.date('Y').' <a style="text-decoration:underline;" target="_blank" title="↗ Gofas.net" href="https://gofas.net">Gofas.net</a> | <a style="text-decoration:underline;" target="_blank" title="↗ Gofas.net" href="https://gofas.net/?p=14685#changelog">'.$module_version.'</a> | <a  style="text-decoration:underline;"target="_blank" title="↗ Documentação" href="https://gofas.net/?p=14685">Documentação</a> | <a style="text-decoration:underline;" target="_blank" title="↗ Fórum de Suporte" href="https://gofas.net/foruns/">Suporte</a>.</p>
				<p style="font-size: 11px;">
				Ao utilizar esse módulo você concorda com nosso <a style="text-decoration:underline;" target="_blank" title="↗ Contrato de licença de uso de software" href="https://gofas.net/?p=9340">contrato de licença de uso de software</a>.
				</p>
				'.$check_updates['message'].'
				</div>',
			),
		);
	}
	return array_merge($renderize,$footer);
}