<?php
include("protecao.php");
if(!isset($_SESSION)){
    session_start();
}
if(isset($_POST['dados'])){ 
  $mensagem = "";
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
      $mensagem= "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Funcionário já cadastrado.
      <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
      </div>";
        } else {
    if($foto['size']>20971520||$foto1['size']>20971520){
      $mensagem= "<div class='alert alert-danger alert-dismissible fade show' role='alert'>O tamanho máximo da foto é 20 MB.
      <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
      </div>";
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
               if(file_exists("./pasta/$email_empresa/$ctps")){
                echo ('');
                 }else{
            mkdir("./pasta/$email_empresa/$ctps");
    $pasta="pasta/$email_empresa/$ctps/";
    //foto1
    $nomearquivo=$foto['name'];
    $extensao= strtolower(pathinfo($nomearquivo, PATHINFO_EXTENSION));
    $nomearquivo3=$foto1['name'];
    $extensao1= strtolower(pathinfo($nomearquivo3, PATHINFO_EXTENSION));
    if ($extensao1!="png"){
      $mensagem= "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Apenas o formato png é aceito.
      <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
      </div>";
    if ($extensao!="png"){
      $mensagem= "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Apenas o formato png é aceito.
      <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
      </div>";
      }else if (($extensao=="png") && ($extensao1=="png")){
    $o=move_uploaded_file($foto["tmp_name"], "./".$pasta.'1'. "." . $extensao);
    //foto2
    }else if (($extensao1=="png") && ($extensao=="png")) {
    $g=move_uploaded_file($foto1["tmp_name"], "./".$pasta. '2' . "." . $extensao1);
    $sql = "INSERT INTO refape_web.funcionario (nome, email, ctps, email_empresa) VALUES ( '$nome', '$email', '$ctps', '$email_empresa');";
        $resultado=pg_query($conexao, $sql);
        if(!$resultado){
          $mensagem= "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Ocorreu um erro no cadastro, tente novamente.
          <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
          </div>";
    }else{
        $mensagem= "<div class='alert alert-success alert-dismissible fade show' role='alert'>Cadastro realizado com sucesso!
          <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
          </div>";
    $sql3 = "SELECT * FROM refape_web.funcionario WHERE ctps='$ctps'and email_empresa='$email_empresa' ;";
      $resultado3=pg_query($conexao,$sql3);
      if($linha3 = pg_fetch_assoc($resultado3)){
        $id1=$linha3['id'];
        $id= str_pad($id1, 16, 0, STR_PAD_LEFT);
      }
      $sql2="UPDATE refape_web.funcionario SET foto='$id/$email_empresa/1.png', foto1='$id/$email_empresa/2.png' WHERE id='$id1'";
      $resultado2=pg_query($conexao, $sql2);
      //No linux
      $executar=exec("python3 app.py $id $ctps $email_empresa");
      //No windows
      //$executar=exec("C:/Users/bruno/AppData/Local/Programs/Python/Python310/python.exe app.py $id $ctps $email_empresa");
      function deletar($apagar){ 
    
        $iterator     = new RecursiveDirectoryIterator($apagar,FilesystemIterator::SKIP_DOTS);
        $rec_iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
      
        foreach($rec_iterator as $file){ 
          $file->isFile() ? unlink($file->getPathname()) : rmdir($file->getPathname()); 
        } 
      
        rmdir($apagar); 
      }
      deletar("pasta/$email_empresa");
    }
    pg_close($conexao);
            }
        }
    }
  }
}
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Cadastro de funcionários</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="static/imagem1.png" rel="icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="static/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="static/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="static/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="static/quill/quill.snow.css" rel="stylesheet">
  <link href="static/quill/quill.bubble.css" rel="stylesheet">
  <link href="static/remixicon/remixicon.css" rel="stylesheet">
  <link href="static/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="static/style1.css" rel="stylesheet">
  <!-- =======================================================
  * Template Name: NiceAdmin - v2.2.2
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>

  <main>
    <div class="container">

      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

              <div class="d-flex justify-content-center py-4">
                <a href="home.php" class="logo d-flex align-items-center w-auto">
                  <span class="d-none d-lg-block">Cadastro de funcionários</span>
                </a>
              </div><!-- End Logo -->

              <div class="card mb-3">

                <div class="card-body">

                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">Realizar cadastro de funcionários</h5>
                    <p class="text-center small"> Insira os dados do funcionário</p>
                  </div>

                  <form class="row g-3 needs-validation" novalidate method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>" name="cadastro" enctype='multipart/form-data'>
                    <div class="col-12">
                      <label for="nome" class="form-label">Nome do funcionário</label>
                      <input type="text" name="nome" class="form-control" placeholder="Informe o nome do funcionário" id="nome" required>
                      <div class="invalid-feedback">Por favor, coloque o nome do funcionário.</div>
                    </div>

                    <div class="col-12">
                      <label for="email" class="form-label">E-mail</label>
                      <div class="input-group has-validation">
                      <input type="email" name="email" class="form-control" placeholder="Informe o e-mail do funcionário" id="email" required>
                      <div class="invalid-feedback">Por favor, coloque um e-mail válido!</div>
                    </div>
                    </div>
                    
                    <div class="col-12">
                      <label for="ctps" class="form-label">CTPS</label>
                        <input type="text" name="ctps" maxlength="14" placeholder="Informe a CTPS do funcionário" class="form-control" id="ctps" required>
                        <div class="invalid-feedback">Por favor, coloque a CTPS do funcionário.</div>
                      </div>
                      
                    <div class="col-12">
                      <label for="foto" class="form-label">Primeira foto do funcionário em png e em boa qualidade</label>
                      <input type="file" name="foto" class="form-control" id="foto" required>
                      <div class="invalid-feedback">Por favor, coloque a primeira foto do funcionário em png.</div>
                    </div>

                    <div class="col-12">
                      <label for="foto1" class="form-label">Segunda foto do funcionário em png e em boa qualidade</label>
                      <input type="file" name="foto1" class="form-control" id="foto1" required>
                      <div class="invalid-feedback">Por favor, coloque a segunda foto do funcionário em png.</div>
                    </div>
                    </div>
                    <div class="col-12">
                    <a href="home.php"><input class="btn btn-primary w-100" type='button' value='Voltar'></a>
                    <br><br>
                    <input type="submit"  class="btn btn-primary w-100" name="dados" value="Cadastrar">
                    <br><br>
                   <?php if(isset($_POST['dados'])){ echo $mensagem; }?>
                    </div>
                  </form>
                </div>


              <div class="credits">
                <!-- All the links in the footer should remain intact. -->
                <!-- You can delete the links only if you purchased the pro version. -->
                <!-- Licensing information: https://bootstrapmade.com/license/ -->
                <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/ -->
                  <p class="small mb-0"> Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a> </p>
              </div>
            </div>
          </div>
        </div>

      </section>

    </div>
  </main><!-- End #main -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="static/apexcharts/apexcharts.min.js"></script>
  <script src="static/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="static/chart.js/chart.min.js"></script>
  <script src="static/echarts/echarts.min.js"></script>
  <script src="static/quill/quill.min.js"></script>
  <script src="static/simple-datatables/simple-datatables.js"></script>
  <script src="static/tinymce/tinymce.min.js"></script>
  <script src="static/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="static/main1.js"></script>

</body>

</html>



