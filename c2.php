<?php
include("protecao.php");
if(!isset($_SESSION)){
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script language="JavaScript" src="static/jquery-3.6.0.js"></script>
    <title>Cadastro de funcionários</title>
</head>
<body>
<?php ob_start(); 
require_once("conexao.php");
if (isset($_SESSION['cnpj']) || isset($_SESSION['nome'])) {
$email_empresa = $_SESSION['email'];
//print_r($_FILES);die();
$nome = $_POST['nome'];
$email = $_POST['email'];
$ctps = $_POST['ctps'];
$foto = $_FILES['foto'];
$foto1 = $_FILES['foto1'];
$sql0 = "SELECT * FROM refape_web.funcionario WHERE email_empresa='$email_empresa' and ctps='$ctps' ;";
$resultado0=pg_query($conexao, $sql0);
$linha = pg_fetch_assoc($resultado0);
if ($linha > 0) {
    echo "Funcionário já cadastrado.";
    echo "<br/><br/>";
    echo "<input type='button' value='Voltar' onClick='javascript:history.back();'/>";
    } else {
if($foto['size']>20971520||$foto1['size']>20971520){
    echo "<input type='button' value='Voltar' onClick='javascript:history.back();'/>";
    echo "<br/><br/>";
    die("O tamanho máximo da foto é 20 MB.");
}else{
    if(file_exists('./pasta')){
        echo ('');
    }else{
        mkdir('./pasta');
    }
    if(file_exists('./pasta/'.$email_empresa)){
        echo ('');
         }else{
        mkdir("./pasta/$email_empresa");
                }
        mkdir("./pasta/$email_empresa/$ctps");
$pasta="pasta/$email_empresa/$ctps/";
//foto1
$nomearquivo=$foto['name'];
$extensao= strtolower(pathinfo($nomearquivo, PATHINFO_EXTENSION));
if ($extensao!="png"){
    echo "Apenas o formato png é aceito.";
    echo "<br/><br/>";
    echo "<input type='button' value='Voltar' onClick='javascript:history.back();'/>";
    die;
}
$o=move_uploaded_file($foto["tmp_name"], "./".$pasta.'1'. "." . $extensao);

//foto2
$nomearquivo3=$foto1['name'];
$extensao1= strtolower(pathinfo($nomearquivo3, PATHINFO_EXTENSION));
if ($extensao1!="png"){
    echo "Apenas o formato png é aceito.";
    echo "<br/><br/>";
    echo "<input type='button' value='Voltar' onClick='javascript:history.back();'/>";
    die;
}
$g=move_uploaded_file($foto1["tmp_name"], "./".$pasta. '2' . "." . $extensao1);
$sql = "INSERT INTO refape_web.funcionario (nome, email, ctps, email_empresa) VALUES ( '$nome', '$email', '$ctps', '$email_empresa');";
    $resultado=pg_query($conexao, $sql);
    if(!$resultado){
    echo "Ocorreu um erro no cadastro, tente novamente.";
}else{
    echo "Cadastro realizado com sucesso!";
$sql3 = "SELECT * FROM refape_web.funcionario WHERE ctps='$ctps'and email_empresa='$email_empresa' ;";
  $resultado3=pg_query($conexao,$sql3);
  if($linha3 = pg_fetch_assoc($resultado3)){
    $id1=$linha3['id'];
    $id= str_pad($id1, 16, 0, STR_PAD_LEFT);
  }
  $sql2="UPDATE refape_web.funcionario SET foto='$id/$email_empresa/1.png', foto1='$id/$email_empresa/2.png' WHERE id='$id'";
  $resultado2=pg_query($conexao, $sql2);
  $comando=exec("python3 app.py $id $ctps $email_empresa");
  function deletar($apagar){ 

    $iterator     = new RecursiveDirectoryIterator($apagar,FilesystemIterator::SKIP_DOTS);
    $rec_iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
  
    foreach($rec_iterator as $file){ 
      $file->isFile() ? unlink($file->getPathname()) : rmdir($file->getPathname()); 
    } 
  
    rmdir($apagar); 
  }
  
  // EXEMPLO DE UTILIZACAO
  deletar("pasta/$email_empresa/$ctps");
}
pg_close($conexao);
echo "<br/><br/>";
echo "<input type='button' value='Voltar' onClick='javascript:history.back();'/>";
        }
    }
}
?>
</body>
</html>
