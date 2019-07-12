<?php

require_once('../vendor/autoload.php');

$dir = '../arquivos/retorno/';

$files = scandir($dir); 
$l = count($files);

for($x=0;$x<$l;$x++){
    
    $s1 = array_search('.', $files);
    $s2 = array_search('..', $files);
    
    if($s1 !== false){
        unset($files[$s1]);
    }
    if($s2 !== false){
        unset($files[$s2]);
    }
    
} 

$files = array_values($files);

$argument = '../arquivos/retorno/'.$files[0];
    
$return = new \Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco\Itau($argument);

$return->processar();

foreach($return->getDetalhes() as $object) {

    $doc =  $object->getNumeroDocumento();
    $vlrRecebido = $object->getValorRecebido();
    $ocorrencia = $object->getOcorrencia();


    echo json_encode($object->toArray());

}

rename($argument, '../arquivos/retorno/lida/'.$files[0]);


?>