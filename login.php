<?php
if (!isset($_SESSION)){
  session_start();
}
if(isset($_POST['dados'])){ 
  $mensagem = "";
  require_once("conexao.php");
if(isset($_POST['email'])||isset($_POST['senha'])) {
$email=$_POST['email'];
$senha=$_POST['senha'];
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
        $_SESSION['cnpj']=$linha['cnpj'];
        $_SESSION['nome']=$linha['nome'];
        $_SESSION['email'] = $linha['email'];
        header("Location: home.php");
     }else{
        $mensagem= "<div class='alert alert-danger alert-dismissible fade show' role='alert'>E-mail ou senha errados. Tente novamente.
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
     }
}else{
    $mensagem=" <div class='alert alert-danger alert-dismissible fade show' role='alert'>E-mail ou senha errados. Por favor, tente novamente.
    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
}
}
}    
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Login</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="static/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
  <link href="static/imagem1.png" rel="icon">
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
                <a href="index.html" class="logo d-flex align-items-center w-auto">
                  <span class="d-none d-lg-block">Refape</span>
                </a>
              </div><!-- End Logo -->

              <div class="card mb-3">

                <div class="card-body">

                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">Logar com sua conta</h5>
                    <p class="text-center small">Entre com seu e-mail e senha para login</p>
                  </div>

                  <form class="row g-3 needs-validation" novalidate method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>">

                    <div class="col-12">
                      <label for="email" class="form-label">E-mail</label>
                      <div class="input-group has-validation">
                        <input type="email" name="email" placeholder="Informe seu e-mail"  class="form-control" id="email" required>
                        <div class="invalid-feedback">Por favor, entre com o seu e-mail.</div>
                      </div>
                    </div>

                    <div class="col-12">
                      <label for="senha" class="form-label">Senha</label>
                      <input type="password" name="senha" class="form-control" placeholder="Senha" id="senha" required>
                      <div class="invalid-feedback">Por favor, entre com sua senha.</div>
                    </div>

                    <div class="col-12">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" onclick="mostrarsenha()" name="msenha"  id="msenha">
                        <label class="form-check-label" for="msenha">Mostrar senha</label>
                      </div>
                    </div>
                    <div class="col-12">
                    <input type="submit"  class="btn btn-primary w-100" name="dados" value="Login">
                    <br><br>
                   <?php if(isset($_POST['dados'])){ echo $mensagem; }?>
                  </div>
                    <div class="col-12">
                      <p class="small mb-0">Não tem uma conta? <a href="cadastro.php">Criar uma conta</a></p>
                    </div>
                  </form>

                </div>
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

<script>
  function mostrarsenha(){
  var senha=document.getElementById("senha")
  if(senha.type=="password"){
    senha.type="text"
}else{
  senha.type="password"
  }
}
</script>

</body>
</html>
