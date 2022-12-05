<?php
require_once("conexao.php");
$email = $_POST['email'];
$senha = $_POST['senha'];
if(isset($_POST['email'])||isset($_POST['senha'])) {
$email=pg_escape_string($_POST['email']);
$senha=pg_escape_string($_POST['senha']);
//echo password_hash($senha,PASSWORD_DEFAULT);die();
//$sql="SELECT * FROM refape_web.empresa WHERE email='$email' AND senha='".password_hash($senha,PASSWORD_DEFAULT)."'";
$sql="SELECT * FROM refape_web.empresa WHERE email='$email'";

$q=pg_query($conexao, $sql);
//$quantidade=pg_num_rows($q);
//if($quantidade==1){
if ( $linha = pg_fetch_assoc($q) ) {
     $usuario = $linha['email'];
     $cnpj = $linha['cnpj'];
     $senhaDoBanco = $linha['senha'];
     if(password_verify($senha,$senhaDoBanco)){
         if (!isset($_SESSION)){
            session_start();
        }
        $_SESSION['cnpj']=$linha['cnpj'];
        $_SESSION['nome']=$linha['nome'];
        $_SESSION['email'] = $linha['email'];
        header("Location: home.php");
     }else{
	echo "<br/>";
        echo "E-mail ou senha errados. Tente novamente.";
        echo "<br/><br/>";
        echo "<input type='button' value='Voltar' onClick='javascript:history.back();'/>";
     }
}else{
    echo "E-mail ou senha incorretos, por favor tente novamente.";
    echo "<br/><br/>";
    echo "<input type='button' value='Voltar' onClick='javascript:history.back();'/>";
}
}
?>
