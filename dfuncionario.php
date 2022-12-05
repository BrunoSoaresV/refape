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
  <title>Listagem</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="static/imagem1.png" rel="icon">
  <link href="static/apple-touch-icon.png" rel="apple-touch-icon">

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
                <a href="verificarpontos" class="logo d-flex align-items-center w-auto">
                  <span class="d-none d-lg-block">Listagem de pontos</span>
                </a>
              </div><!-- End Logo -->

              <div class="card mb-3">

                <div class="card-body">

                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">Listar pontos</h5>
                    <p class="text-center small">Para listar os pontos registrados insira a data desejada</p>
                  </div>

                  <form class="row g-3 needs-validation" novalidate method="POST" action="ldponto.php" name="listagem" enctype='multipart/form-data'>
			                <div class="col-12">
                      <label for="data" class="form-label">Data</label>
                      <div class="input-group has-validation">
                        <input type="date" name="data"  class="form-control" id="data" required>
                        <div class="invalid-feedback">Por favor, coloque uma data</div>
                      </div>
                    </div>
                    <div class="col-12">
                      <button class="btn btn-primary w-100" onclick="document.frmldponto.submit();">Buscar</button>
                      <br> <br>
                      <a href="verificarpontos"><input class="btn btn-primary w-100" type='button' value='Voltar'></a>
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

</body>

</html>



