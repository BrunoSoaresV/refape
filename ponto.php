<?php
include("protecao.php");
if (!isset($_SESSION)) {
    session_start();
}
define('AJAX_REQUEST', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
if(!AJAX_REQUEST) {
  die("Acesso negado.");
}
require_once("conexao.php");
  if(isset($_GET['data'])){
    $label1 = $_GET['data'];
  }
  $label=ltrim($label1, "0"); 
if (isset($_SESSION['cnpj']) || isset($_SESSION['nome'])) {
  $email_empresa1 = $_SESSION['email'];
if($label=="Rosto desconhecido" or $label=="")die("");
  date_default_timezone_set('America/Sao_Paulo');
  $horario_atual = date("Y-m-d H:i:s");
  $data_atual=date("Y-m-d");
  $dateOBJ = new DateTime();
  $minutos_anteriores=0;
  isset($_SESSION["$label1"]) ? $minutos_anteriores=$_SESSION["$label1"] : $minutos_anteriores=0;
  $minutos_atuais = $dateOBJ->getTimeStamp(); 
  if(($minutos_atuais-$minutos_anteriores)<=300)die("");
  $_SESSION["$label1"]=$minutos_atuais;
  $sql3 = "SELECT * FROM refape_web.funcionario WHERE id='$label';";
  $resultado3=pg_query($conexao,$sql3);
  if($linha3 = pg_fetch_assoc($resultado3)){
    $nome=$linha3['nome'];
    $email=$linha3['email'];
    $email_empresa=$linha3['email_empresa'];
    $ctps=$linha3['ctps'];
  }
  $sql = "SELECT * FROM refape_web.ponto WHERE ctps='$ctps' and email_empresa='$email_empresa1'ORDER BY id DESC LIMIT 1;";
  $resultado=pg_query($conexao,$sql);
  if ($email_empresa1!=$email_empresa){
    die;
  }
  if($linha = pg_fetch_assoc($resultado)){
    $hora_saida=$linha['hora_saida'];
    $id=$linha['id'];
    $hora_entrada=$linha['hora'];
  }
    if (($resultado) and (pg_num_rows($resultado) != 0)) {
      if ($hora_saida == null){
          $ponto= "saida";
        } else { 
          $ponto = "entrada";
      }
  } else {
      $ponto = "entrada";
  }
  switch ($ponto) {
  case "saida":
    $hora_entrada1 = new DateTime($hora_entrada);
    $hora_saida1 = new DateTime($horario_atual);
    $tempo_permanencia=$hora_entrada1->diff($hora_saida1);
    $a=$tempo_permanencia->format('%d dias:%H horas:%I minutos:%S segundos');
    $saida = "UPDATE refape_web.ponto SET hora_saida ='$horario_atual', tempo_permanencia='$a'WHERE id='$id;";
    $resultado_saida=pg_query($conexao, $saida);
    echo("Ponto de saída realizado com sucesso, ".$nome."!");
    break;
 default:
    $entrada= "INSERT INTO refape_web.ponto (nome, email, ctps, email_empresa, hora, data) VALUES ('$nome', '$email', '$ctps','$email_empresa', '$horario_atual', '$data_atual');";
    $resultado_entrada=pg_query($conexao,$entrada);
    echo("Ponto de entrada realizado com sucesso, ".$nome."!");
    break;
  }
  pg_close($conexao);
}
?>
