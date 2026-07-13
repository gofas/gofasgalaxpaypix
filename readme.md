# Módulo GalaxPay Pix para WHMCS

Módulo gratuito de integração que gera cobranças Pix com código QR direto nas faturas do WHMCS através da API GalaxPay, com confirmação automática do pagamento. Desenvolvido pela Gofas Software.

## Download

Baixe a versão mais recente:

https://github.com/gofas/gofasgalaxpaypix/releases/latest/download/gofasgalaxpaypix.zip

## Funcionalidades

- **Código QR na fatura** do WHMCS, com copia e cola em um clique
- **Confirmação automática de pagamento** e baixa nas faturas
- **Mensagem personalizada** acima do código QR
- **Exibição opcional** do logo Pix, da data e hora e do valor total no código QR
- **Valor mínimo** da fatura para permitir pagamento via Pix
- **Cálculo da tarifa** por transação confirmada, preenchendo o campo "Taxas" (fee) da lista de transações do WHMCS
- **Dispensa configuração de campos CPF/CNPJ**: o módulo detecta automaticamente os campos personalizados de clientes
- **Suporte a produção e a testes (sandbox)**
- **Logs de diagnóstico** configuráveis
- **Aviso de atualização** e verificação de versão na própria tela de configuração do módulo

## Requisitos

- WHMCS >= 8.0
- PHP >= 7.3
- Conta GalaxPay com o módulo Webservice ativo e chave Pix cadastrada
- Credenciais: Galax ID e Galax Hash (produção e testes)

## Instalação

1. Baixe o arquivo pelo link de download e descompacte. Será criada a pasta `gofasgalaxpaypix`.
2. Copie as pastas `includes` e `modules` de dentro de `gofasgalaxpaypix` para a raiz da instalação do WHMCS, mesclando com as pastas existentes.
3. Ative o módulo em `Opções > Pagamentos > Portais para Pagamentos > aba All Payment Gateways`.
4. Informe o Galax ID e o Galax Hash.

## Configuração

### Pré configuração na GalaxPay

1. No painel administrativo, em `Módulos`, ative o módulo Webservice.
2. Em `Módulos > Webservice > Configurar`, copie as credenciais Galax ID e Galax Hash de produção.
3. Repita o processo no painel do modo de testes para obter as credenciais de sandbox.
4. Cadastre a chave Pix na sua conta GalaxPay.

### Pré configuração no WHMCS

Crie um campo personalizado de cliente para CPF e/ou CNPJ, ou dois campos distintos, um para cada documento. O módulo identifica os campos automaticamente.

### Opções do módulo

<img src="https://raw.githubusercontent.com/gofas/gofasgalaxpaypix/master/docs/img/tela-configuracoes-modulo.png" alt="Tela de configuracoes do modulo" width="640">

- **Galax ID** e **Galax Hash**: credenciais do Webservice em produção.
- **Sandbox Galax ID** e **Sandbox Galax Hash**: credenciais do Webservice em modo de testes.
- **Administrador do WHMCS**: administrador com permissão para usar a API interna do WHMCS.
- **Sandbox**: gera cobranças em modo de testes.
- **Salvar Logs**: grava informações de diagnóstico em `Utilitários > Logs > Log de Módulo`.
- **Valor mínimo**: valor mínimo da fatura para permitir pagamento via Pix.
- **Tarifa Pix**: valor em % pago por transação, usado para preencher o campo "Taxas" (fee) da transação no WHMCS.
- **Mensagem acima do código QR**: texto exibido na fatura, acima do código QR.
- **Exibir Logo PIX**: exibe o logo do Pix junto ao código QR.
- **Exibir data e hora do código QR**: exibe a data e a hora de geração do código.
- **Exibir valor total do código QR**: exibe o valor total da cobrança junto ao código QR.
- **Enviar estatísticas de uso (opcional)**: controla o envio identificado das estatísticas de confirmação de pagamento. Desmarcado, as confirmações continuam sendo contabilizadas de forma anônima.

## Informações importantes

- A tarifa do Pix é paga separadamente à GalaxPay, conforme o plano da sua conta.
- Sempre faça backup antes de mudar algo no seu sistema.

## Suporte

Fórum de suporte gratuito: https://gofas.net/foruns/

## Licença

Software proprietário da Gofas Software. O código é público apenas para transparência e consulta; isso não concede licença de uso, modificação ou redistribuição. É vedado modificar, redistribuir, sublicenciar ou realizar engenharia reversa sem autorização prévia por escrito. Veja [LICENSE](LICENSE) e o contrato completo em https://gofas.net/contrato-de-venda-de-licenca-de-uso-de-software/.
