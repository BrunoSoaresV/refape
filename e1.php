<?php
include("protecao.php");
if(!isset($_SESSION)){
    session_start();
}
require_once("conexao.php");
$nome = $_POST['nome'];
$email = $_POST['email'];
$ctps = $_POST['ctps'];
$id = $_POST['id'];
$status=$_POST['s'];
$email_empresa = $_POST['email_empresa'];
$sql = "UPDATE refape_web.funcionario SET nome='$nome', status='$status', email='$email', ctps='$ctps', email_empresa='$email_empresa' WHERE id='$id';";
$resultado=pg_query($conexao, $sql);
if(!$resultado){
echo "Ocorreu um erro na edição, tente novamente.";
}else{
echo "Edição realizada com sucesso!";
}
pg_close($conexao);
echo "<br/><br/>";
echo "<a href='listagem.php'><input type='button' value='Voltar'></a>";
?>


