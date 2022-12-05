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
if($status=="false"){
    if(!file_exists('./desativados/'.$email_empresa)){
        mkdir("./desativados/$email_empresa");
         }
    rename("pasta/$email_empresa/$ctps", "desativados/$email_empresa/$ctps");
    $sql2="UPDATE refape_web.funcionario SET foto='desativados/$email_empresa/$ctps/1.png', foto1='desativados/$email_empresa/$ctps/2.png' WHERE id='$id'";
    $resultado2=pg_query($conexao, $sql2);
    }else if ($status=="true"){
        if(!file_exists('./desativados/'.$email_empresa)){
            mkdir("./desativados/$email_empresa");
             }
    rename("desativados/$email_empresa/$ctps", "pasta/$email_empresa/$ctps");
    $sql1="UPDATE refape_web.funcionario SET foto='pasta/$email_empresa/$ctps/1.png', foto1='pasta/$email_empresa/$ctps/2.png' WHERE id='$id'";
    $resultado1=pg_query($conexao, $sql1);
}
}
pg_close($conexao);
echo "<br/><br/>";
echo "<a href='listagem.php'><input type='button' value='Voltar'></a>";
?>


