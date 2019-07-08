<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting( E_ALL ^E_NOTICE );
require_once('vendor/autoload.php');
require_once('envia_remessa.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die('Request method is not allowed on this server!');
}

$raw = json_decode(file_get_contents("php://input"), true);

$ambiente = $raw['ambiente'];
if($ambiente == 'teste'){
    $ambiente = 1;
}
elseif($ambiente == 'producao'){
    $ambiente = 2;
}

$calculoDV = new Eduardokum\LaravelBoleto\CalculoDV;
$dvNossoNumero = $calculoDV->itauNossoNumero($raw['agencia_beneficiario'],$raw['conta_beneficiario'].$raw['digito_verificador_conta_beneficiario'],$raw['tipo_carteira_titulo'],$raw['nosso_numero']);

$jurosCalculado = round($raw['valor_cobrado']*(0.03/100),2);
$multaCalculada = (2/100)*$raw['valor_cobrado'];

$arrJuros = explode(".",$jurosCalculado);
$arrMulta = explode(".",$multaCalculada);

$config = [
	'tipo_ambiente' => $ambiente, //tipo de ambiente: 1- TESTES | 2 - PRODUÇÃO
	'identificador' => str_pad($raw['cpf_cnpj_beneficiario'], 14, '0', STR_PAD_LEFT), 
	'itau_chave' 	=> '9a6a013b-54df-49a5-bf99-f674761f5775',
	'client_id'		=> 'uV1fJf6iL7Cu0',
	'client_secret'	=> 'THWZYB8tT3ob2sJlrjgUXZUanLPv-Z0KBA7YLpaRikgfyDhswoW_AbNWHwfjMh0wPa1qhfaXcSiv5GRiIMEwHg2'
];

$remessa = new EnviaRemessa($config);

$boleto = [
	'tipo_registro'								=> $raw['tipo_registro'],
	'tipo_cobranca'								=> $raw['tipo_cobranca'],
	'tipo_produto'								=> str_pad($raw['tipo_produto'], 5, '0', STR_PAD_LEFT),
	'subproduto'								=> str_pad($raw['subproduto'], 5, '0', STR_PAD_LEFT),
	'titulo_aceite'								=> $raw['titulo_aceite'],
	'tipo_carteira_titulo'						=> $raw['tipo_carteira_titulo'],
	'nosso_numero'								=> str_pad($raw['nosso_numero'], 8, '0', STR_PAD_LEFT),
	'digito_verificador_nosso_numero'			=> $dvNossoNumero,
	'data_vencimento'							=> $raw['data_vencimento'],
	'valor_cobrado'								=> str_pad(number_format($raw['valor_cobrado'], 2, '', ''), 17, '0', STR_PAD_LEFT),
	'seu_numero'								=> str_pad($raw['seu_numero'], 6, '0', STR_PAD_LEFT),
	'especie'									=> str_pad($raw['especie'], 2, '0', STR_PAD_LEFT),
	'data_emissao'								=> $raw['data_emissao'],
	'data_limite_pagamento'						=> $raw['data_limite_pagamento'],
	'tipo_pagamento'							=> $raw['tipo_pagamento'],
	'indicador_pagamento_parcial'				=> $raw['indicador_pagamento_parcial'],
	//'codigo_barras'								=> '3419109008221031508134347167000047260000043831',

	
	//pagador
	'cpf_cnpj_pagador'							=> $raw['cpf_cnpj_pagador'],
	'nome_pagador'								=> $raw['nome_pagador'],	//precisa reduzir o tamanho
	'logradouro_pagador'						=> $raw['logradouro_pagador'],
	'bairro_pagador'							=> $raw['bairro_pagador'],		//precisa reduzir o tamanho
	'cidade_pagador'							=> $raw['cidade_pagador'],
	'uf_pagador'								=> $raw['uf_pagador'],
	'cep_pagador'								=> $raw['cep_pagador'],
	
	//moeda
	'codigo_moeda_cnab'							=> str_pad($raw['codigo_moeda_cnab'], 2, '0', STR_PAD_LEFT),
	
	//beneficiario
	'cpf_cnpj_beneficiario'						=> $raw['cpf_cnpj_beneficiario'],
	'agencia_beneficiario'						=> $raw['agencia_beneficiario'],
	'conta_beneficiario'						=> str_pad($raw['conta_beneficiario'], 7, '0', STR_PAD_LEFT),
	'digito_verificador_conta_beneficiario'		=> $raw['digito_verificador_conta_beneficiario'],
	
	//juros
	'juros' =>[
		'tipo_juros'							=> 8,
		'percentual_juros'						=> '000000100000',
	],

	//multa
	'multa' =>[
		'tipo_multa'							=> 2,
		'percentual_multa'						=> '000000200000',
	],

	//desconto
	'tipo_desconto'								=> $raw['tipo_desconto'],
	
	//recebimento divergente
	'tipo_autorizacao_recebimento'				=> $raw['tipo_autorizacao_recebimento'],
	'tipo_valor_percentual_recebimento'			=> $raw['tipo_valor_percentual_recebimento'],
	'valor_minimo_recebimento'					=> str_pad(number_format($raw['valor_minimo_recebimento'], 2, '', ''), 17, '0', STR_PAD_LEFT),
	'percentual_minimo_recebimento'				=> $raw['percentual_minimo_recebimento'],
	'valor_maximo_recebimento'					=> str_pad(number_format($raw['valor_maximo_recebimento'], 2, '', ''), 17, '0', STR_PAD_LEFT),
	'percentual_maximo_recebimento'				=> $raw['percentual_maximo_recebimento'],

	'instrucao_cobranca_1' 						=> 10
];

//adicionando boleto
$remessa->addBoleto($boleto);

$result = $remessa->enviar();

$getJson = json_decode(json_encode($result), true);

 //BOLETO ENGINE
$beneficiario = new \Eduardokum\LaravelBoleto\Pessoa([
    'nome' => $getJson['beneficiario']['nome_razao_social_beneficiario'],
    'endereco' => $getJson['beneficiario']['logradouro_beneficiario'].' '.$getJson['beneficiario']['complemento_beneficiario'],
    'bairro' => $getJson['beneficiario']['bairro_beneficiario'],
    'cep' => $getJson['beneficiario']['cep_beneficiario'],
    'uf' => $getJson['beneficiario']['uf_beneficiario'],
    'cidade' => $getJson['beneficiario']['cidade_beneficiario'],
    'documento' => $getJson['beneficiario']['cpf_cnpj_beneficiario']
]);

$pagador = new \Eduardokum\LaravelBoleto\Pessoa([
    'nome' => $getJson['pagador']['nome_razao_social_pagador'],
    'endereco' => $getJson['pagador']['logradouro_pagador'].' '.$getJson['pagador']['complemento_pagador'],
    'bairro' => $getJson['pagador']['bairro_pagador'],
    'cep' => $getJson['pagador']['cep_pagador'],
    'uf' => $getJson['pagador']['uf_pagador'],
    'cidade' => $getJson['pagador']['cidade_pagador'],
    'documento' => $getJson['pagador']['cpf_cnpj_pagador']
]);

$boletoPdf = new Eduardokum\LaravelBoleto\Boleto\Banco\Itau([
    'logo' => realpath(__DIR__ . '/logo_atus.png'),
    'dataVencimento' => new  \Carbon\Carbon($getJson['vencimento_titulo']),
    'valor' => number_format($getJson['valor_titulo'], 2, '.', ''),
    'multa' => $getJson['valor_juro_multa'],
    'juros' => $getJson['valor_juro_multa'],
    'numero' => str_pad($raw['nosso_numero'], 8, '0', STR_PAD_LEFT),
    'numeroDocumento' => str_pad($raw['seu_numero'], 6, '0', STR_PAD_LEFT),
    'pagador' => $pagador,
    'beneficiario' => $beneficiario,
    'carteira' => $getJson['tipo_carteira_titulo'],
    'agencia' => $getJson['beneficiario']['agencia_beneficiario'],
    'conta' => $getJson['beneficiario']['conta_beneficiario'].'-'.$getJson['beneficiario']['digito_verificador_conta_beneficiario'],
    'descricaoDemonstrativo' => ['Após vencimento, cobrar Multa de 2% + Juros de 0,033% ao dia.', $raw['informacoes'], $raw['protesto'] ],
    'instrucoes'  => ['Após vencimento, cobrar Multa de 2% + Juros de 0,033% ao dia.', $raw['informacoes'], $raw['protesto']  ],
    'aceite' => 'N',
    'especieDoc' => $getJson['especie_documento']
]);

$arquivo = intval($getJson['seu_numero']).'.pdf';

$pdf = new Eduardokum\LaravelBoleto\Boleto\Render\Pdf();
$pdf->addBoleto($boletoPdf);
$pdf->gerarBoleto($pdf::OUTPUT_SAVE, __DIR__ . DIRECTORY_SEPARATOR . '/arquivos' . DIRECTORY_SEPARATOR .$arquivo);

echo json_encode($result, true);

?>