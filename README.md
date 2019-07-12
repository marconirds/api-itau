# Itaú API - Atus publicações
API PHP de Registro On-line e leitura de Retornos de Boletos de Cobrança Itaú.


## Descrição
A biblioteca tem como objetivo, facilitar o registro online de boletos do Itaú, como a leitura de seus arquivos de retorno.

A documentação mais recente fornecido pelo Itaú, está na raiz do diretório da API.


## Variáveis de ambiente
Os seguintes parâmetros devem ser informados dentro do código da API (Linhas 31 à 35 do index.php) caso haja mudança:

Parâmetro | Obrigatório | Valores | Comentário
------------ | ------------- | ------------- | -------------
tipo_ambiente | Sim | teste / producao | Tipo de ambiente para registros de boletos
identificador | Sim | 99999999999999 | CPF/CNPJ do cliente junto ao banco
itau_chave | Sim | hash key | itau_chave passada pelo banco ao cliente
client_id | Sim | hash key | client_id passada pelo banco ao cliente
client_secret | Sim | hash key | client_secret passada pelo banco ao cliente

## Como usar?
Após definir as variáveis de ambiente acima, basta realizar um request em seu código passando um JSON em RAW via POST.
A API irá retornar um JSON com os dados do boleto no banco de dados do Itaú. Segue abaixo um template de JSON que será passado na request:  

```json
{"ambiente":"teste","tipo_registro":1,"tipo_cobranca":1,"tipo_produto":"6","subproduto":"8","titulo_aceite":"N","tipo_carteira_titulo":"109","nosso_numero":"4729","data_vencimento":"2019-07-19","valor_cobrado":"1969.15","seu_numero":"4729","especie":"1","data_emissao":"2019-07-11","data_limite_pagamento":"2019-07-19","tipo_pagamento":3,"indicador_pagamento_parcial":"false","informacoes":"NOTAFISCAL 6663","protesto":"","cpf_cnpj_pagador":"15016827000160","nome_pagador":"HOSPITAL E MATERNIDADE SAO MAT","logradouro_pagador":"AV ACLIMACAO 335","bairro_pagador":"BOSQUE DA SAUDE","cidade_pagador":"CUIABA","uf_pagador":"MT","cep_pagador":'78050040',"codigo_moeda_cnab":"9","cpf_cnpj_beneficiario":"08182332000146","agencia_beneficiario":"0288","conta_beneficiario":"88173","digito_verificador_conta_beneficiario":"3","tipo_desconto":"0","tipo_autorizacao_recebimento":"3","tipo_valor_percentual_recebimento":"V","valor_minimo_recebimento":"1969.15","percentual_minimo_recebimento":"","valor_maximo_recebimento":"1969.15","percentual_maximo_recebimento":""}
```
Vale ressaltar que, os formatos de dados do JSON enviado são estabelecidos pelo Itaú, conforme encontra-se na documentação fornecida. Os campos de percentuais de juros e multa já estão configurados no código da API (linhas 81 e 87), conforme realidade do cliente 1% ao mês e 2%, respectivamente. Caso queira modificá-los, basta apenas passar os novos valores no formato conforme documentação do Itaú. São eles:

* **tipo_juros**: Aqui será informado o código com o tipo do juros, no formato `9`.
* **percentual_juros**: Aqui será informado percentual do juros, no formato `999999900000`, onde 9 são casas inteiras, e 0 casas decimais.
* **tipo_multa**: Aqui será informado o código com o tipo da multa, no formato `9`.
* **percentual_multa**: Aqui será informado percentual da multa, no formato `999999900000`, onde 9 são casas inteiras, e 0 casas decimais.
* 
Também poderá ser ser passado informações próprias do cliente, que não serão enviadas ao Itaú, mas serão impressas no boleto pdf gerado:

* **informacoes**: Informar as instruções ou informações que você queira mostrar ao pagador do boleto.
* **protesto**: Informar as informações de protesto que você queira informar ao pagador do boleto.

Segue um modelo do JSON retornado na request:  

```json
{"beneficiario":{"codigo_banco_beneficiario":"341","digito_verificador_banco_beneficiario":"7","agencia_beneficiario":"0288","conta_beneficiario":"88173","digito_verificador_conta_beneficiario":"3","cpf_cnpj_beneficiario":"08182332000146","nome_razao_social_beneficiario":"TELEMAKO FRAGERIS PUBLICIDADE","logradouro_beneficiario":"AV HISTOR RUBENS DE MENDONCA","bairro_beneficiario":"JD ACLIMACAO","complemento_beneficiario":"ED AMERCIAN BUS","cidade_beneficiario":"CUIABA","uf_beneficiario":"MT","cep_beneficiario":"78050000"},"pagador":{"cpf_cnpj_pagador":"12049631000184","nome_razao_social_pagador":"MOURA DUBEUX ENGENHARIA S/A","logradouro_pagador":"AV ENGENHEIRO DOMINGOS FERREIRA 467","complemento_pagador":"","bairro_pagador":"PINA","cidade_pagador":"RECIFE","uf_pagador":"PE","cep_pagador":"51011050"},"sacador_avalista":{"cpf_cnpj_sacador_avalista":"00000000000000","nome_razao_social_sacador_avalista":""},"moeda":{"sigla_moeda":"R$","quantidade_moeda":0,"cotacao_moeda":0},"especie_documento":"DM","vencimento_titulo":"2019-07-20","tipo_carteira_titulo":"109","nosso_numero":"000055554","seu_numero":"005555","codigo_barras":"34191795600000444001090000555540288881733000","numero_linha_digitavel":"34191090080055554028588817330009179560000044400","local_pagamento":"ATE O VENCIMENTO PAGUE EM QUALQUER BANCO OU CORRESPONDENTE NAO BANCARIO. APOS O VENCIMENTO, ACESSE ITAU.COM.BR/BOLETOS E PAGUE EM QUALQUER BANCO OU CORRESPONDENTE NAO BANCARIO.","data_processamento":"2019-07-12","data_emissao":"2019-07-12","uso_banco":"","valor_titulo":444,"valor_desconto":0,"valor_outra_deducao":0,"valor_juro_multa":0,"valor_outro_acrescimo":0,"valor_total_cobrado":0,"lista_texto_informacao_cliente_beneficiario":[{"texto_informacao_cliente_beneficiario":""},{"texto_informacao_cliente_beneficiario":""},{"texto_informacao_cliente_beneficiario":""},{"texto_informacao_cliente_beneficiario":""},{"texto_informacao_cliente_beneficiario":""},{"texto_informacao_cliente_beneficiario":""},{"texto_informacao_cliente_beneficiario":""},{"texto_informacao_cliente_beneficiario":""},{"texto_informacao_cliente_beneficiario":""}]}
```

## Retorno
Para se realizar uma requisição de retorno, bastar inserir na pasta `arquivos/retorno/` um único arquivo de retorno, logo em seguida chamar `retorno/index.php`. A request trará como resposta um JSON. 
Segue modelo:  
```json
{"carteira":"109","nossoNumero":"000000000011","numeroDocumento":"1","numeroControle":"0000000000000000000000000",  
"ocorrencia":"02","ocorrenciaTipo":3,"ocorrenciaDescricao":"Entrada Confirmada","dataOcorrencia":"20\/10\/2016",  
"dataVencimento":"20\/10\/2016","dataCredito":"21\/10\/2016","valor":"100.00","valorTarifa":"4.20","valorIOF":0,"valorAbatimento":0,  
"valorDesconto":0,"valorRecebido":"100.00","valorMora":0,"valorMulta":0,"error":null,"trash":[]}
```
## Formatação dos campos
Os campos devem ser passados nos formatos conforme abaixo:

* Valores de nome e endereço do pagador são recortados automaticamente para a quantidade máxima de caracteres.
* As datas devem ser passadas no formato padrão internacional `AAAA-MM-DD`.
* Moedas deverão ser passadas no formato internacional `9999.99`.

PS: Os arquivos de retorno gerados devem estar dentro do diretório `arquivos/retorno/`, e só poderá conter até um por vez. Após serem processados, serão movidos para o diretório `arquivos/retorno/lida`.
