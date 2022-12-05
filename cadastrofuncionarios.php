<?php
include("protecao.php");
if(!isset($_SESSION)){
    session_start();
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

                  <form class="row g-3 needs-validation" novalidate method="POST" action="c2.php" name="cadastro" enctype='multipart/form-data'>
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
                      <button class="btn btn-primary w-100" onclick="document.frmc2.submit();">Cadastrar</button>
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



