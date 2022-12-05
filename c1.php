<?php
require_once("conexao.php");
$nome = $_POST['nome'];
$email = $_POST['email'];
$cnpj = $_POST['cnpj'];
$senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
$sql0 = "SELECT * FROM refape_web.empresa WHERE email='$email' ;";
$resultado0=pg_query($conexao, $sql0);
$linha = pg_fetch_assoc($resultado0);
if ($linha > 0) {
    echo "Empresa já cadastrada.";
    echo "<br/><br/>";
    echo "<input type='button' value='Voltar' onClick=\"window.location.href='home.php'\"/> ";
    echo "<br/><br/>";
    echo "<input type='button' value='Login' onClick=\"window.location.href='login.php'\"/> ";
    } else {
$sql = "INSERT INTO refape_web.empresa (nome, email, cnpj, senha) VALUES ( '$nome', '$email', '$cnpj', '$senha');";
$resultado=pg_query($conexao, $sql);
if(!$resultado){
echo "Ocorreu um erro no cadastro, tente novamente.";
echo "<br/><br/>";
echo "<input type='button' value='Voltar' onClick=\"window.location.href='home.php'\"/> ";
echo "<br/><br/>";
echo "<input type='button' value='Login' onClick=\"window.location.href='login.php'\"/> ";
}else{
echo "Cadastro realizado com sucesso!";
echo "<br/><br/>";
echo "<input type='button' value='Voltar' onClick=\"window.location.href='home.php'\"/> ";
echo "<br/><br/>";
echo "<input type='button' value='Login' onClick=\"window.location.href='login.php'\"/> ";
}
pg_close($conexao);
 }

?>


