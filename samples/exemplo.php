<?php
/**Exemplo de uso da classe para processamento de arquivo de retorno de cobranças em formato FEBRABAN/CNAB240,
* testado com arquivo de retorno do Santander.
*/

//Adiciona a classe strategy RetornoBanco que vincula um objeto de uma sub-classe
//de RetornoBase, e assim, executa o processamento do arquivo de uma determinada
//carteira de um banco específico.

use retornoBanco\RetornoBanco;
use retornoBanco\RetornoFactory;


/**Função handler a ser associada ao evento aoProcessarLinha de um objeto da classe
* RetornoBase. A função será chamada cada vez que o evento for disparado.
*
* A coluna do tipo DETALHE em retorno CNAB240 tem 2 segmentos(duas linhas) "T" e "U"
* este exemplo lista no nome da empresa
* e alguns dados do DETALHE de cada boleto pago.
* Nota: o Segmento "U" sempre é continuação do Segmento "T" que o precedeu
* @param RetornoBase $self Objeto da classe RetornoBase que está processando o arquivo de retorno
* @param $numLn Número da linha processada.
* @param $vlinha Vetor contendo a linha processada, contendo os valores da armazenados
* nas colunas deste vetor. Nesta função o usuário pode fazer o que desejar,
* como setar um campo em uma tabela do banco de dados, para indicar
* o pagamento de um boleto de um determinado cliente.
* @see linhaProcessada1
*/

function linhaProcessada($self, $numLn, $vlinha) {
  if($vlinha) {
	  if($vlinha["registro"] == $self::HEADER_ARQUIVO)
		  echo "<b>".$vlinha['nome_empresa']."</b><br />";
		//O registro detalhe U são dados adicionais do registro de pagamento
		//e não necessariamente precisa ser usado.
		//Pode ser que o arquivo de retorno não tenha o registro detalhe separado em 
		//duas linhas (T e U). Assim, nestes casos, pode-se fazer apenas um 
		//if($vlinha["registro"] == $self::DETALHE)
	  else if($vlinha["registro"] == $self::DETALHE && $vlinha["segmento"] == "T") {
		  echo get_class($self) . ": Nosso N&uacute;mero: <b>".$vlinha['nosso_numero']."</b> - 
		  Venc: <b>".$vlinha['vencimento']."</b>".
		  " Vlr Titulo: <b>R\$ ".number_format($vlinha['valor'], 2, ',', '')."</b> - ".
		  " Vlr Tarifa: <b>R\$ ".number_format($vlinha['valor_tarifa'], 2, ',', '')."</b><br/>";
	  }
  } else echo "Tipo da linha n&atilde;o identificado<br/>\n";
}

/**Outro exemplo de função handler, a ser associada ao evento
* aoProcessarLinha de um objeto da classe RetornoBase.
* Neste exemplo, é utilizado um laço foreach para percorrer
* o vetor associativo $vlinha, mostrando os nomes das chaves
* e os valores obtidos da linha processada.
* @see linhaProcessada
*/
function linhaProcessada1($self, $numLn, $vlinha) {
  printf("%08d) ", $numLn);
  if($vlinha) {
    foreach($vlinha as $nome_indice => $valor)
      echo get_class($self) . ": $nome_indice: <b>$valor</b><br/>\n ";
  } else echo "Tipo da linha n&atilde;o identificado<br/>\n";
  echo "<br/>\n";
  
}

//--------------------------------------INÍCIO DA EXECUÇÃO DO CÓDIGO-----------------------------------------------------

$fileName = "SIGCB.RET20150508013704969.RET";
//$fileName = "retorno_santander.ret";
//$fileName = "CBR64302.RET";

//Use uma das duas instrucões abaixo (comente uma e descomente a outra)
//$cnab240 = RetornoFactory::getRetorno($fileName, "linhaProcessada");
$cnab240 = RetornoFactory::getRetorno($fileName, "linhaProcessada1");

$retorno = new RetornoBanco($cnab240);
$retorno->processar();
?>
