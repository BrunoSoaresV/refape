<?php
include("protecao.php");

if(isset($_POST['dados'])){ 
  $mensagem = "";
  require_once("conexao.php");

  if (isset($_SESSION['cnpj']) || isset($_SESSION['nome'])) {
    $email_empresa = $_SESSION['email'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $ctps = $_POST['ctps'];
    $foto = $_FILES['foto'] ?? null;
    $foto1 = $_FILES['foto1'] ?? null;

    if (!$foto || !$foto1) {
        $mensagem = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Erro no upload das fotos.
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    } else {
        $sql0 = "SELECT * FROM refape_web.funcionario WHERE email_empresa='$email_empresa' and ctps='$ctps';";
        $resultado0 = pg_query($conexao, $sql0);

        if ($resultado0 && pg_num_rows($resultado0) > 0) {
            $mensagem = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Funcionário já cadastrado.
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
        } else {
            if ($foto['size'] > 20971520 || $foto1['size'] > 20971520) {
                $mensagem = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>O tamanho máximo da foto é 20 MB.
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
            } else {
                $dir_pasta = './pasta';
                $dir_empresa = "$dir_pasta/$email_empresa";
                $dir_ctps = "$dir_empresa/$ctps";

                if (!is_dir($dir_pasta)) {
                    mkdir($dir_pasta);
                }
                if (!is_dir($dir_empresa)) {
                    mkdir($dir_empresa);
                }
                if (!is_dir($dir_ctps)) {
                    mkdir($dir_ctps);
                }

                $pasta = "pasta/$email_empresa/$ctps/";
                $nomearquivo = $foto['name'];
                $extensao = strtolower(pathinfo($nomearquivo, PATHINFO_EXTENSION));
                $nomearquivo3 = $foto1['name'];
                $extensao1 = strtolower(pathinfo($nomearquivo3, PATHINFO_EXTENSION));

                if ($extensao != "png" || $extensao1 != "png") {
                    $mensagem = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Apenas o formato png é aceito.
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
                } else {
                    $o = move_uploaded_file($foto["tmp_name"], "./$pasta" . '1' . "." . $extensao);
                    $g = move_uploaded_file($foto1["tmp_name"], "./$pasta" . '2' . "." . $extensao1);

                    if ($o && $g) {
                        $sql = "INSERT INTO refape_web.funcionario (nome, email, ctps, email_empresa) VALUES ('$nome', '$email', '$ctps', '$email_empresa');";
                        $resultado = pg_query($conexao, $sql);

                        if (!$resultado) {
                            $mensagem = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Ocorreu um erro no cadastro, tente novamente.
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                            </div>";
                        } else {
                            $mensagem = "<div class='alert alert-success alert-dismissible fade show' role='alert'>Cadastro realizado com sucesso!
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                            </div>";

                            $sql3 = "SELECT * FROM refape_web.funcionario WHERE ctps='$ctps' and email_empresa='$email_empresa';";
                            $resultado3 = pg_query($conexao, $sql3);
                            $linha3 = pg_fetch_assoc($resultado3);
                            if ($linha3) {
                                $id1 = $linha3['id'];
                                $id = str_pad($id1, 16, '0', STR_PAD_LEFT);
                                $sql2 = "UPDATE refape_web.funcionario SET foto='$id/$email_empresa/1.png', foto1='$id/$email_empresa/2.png' WHERE id='$id1'";
                                pg_query($conexao, $sql2);

                                //$executar = exec("python3 app.py $id $ctps $email_empresa");
                                 //No windows
                                 $executar = exec("C:/Users/bruno/AppData/Local/Programs/Python/Python310/python.exe app.py $id $ctps $email_empresa");

                                function deletar($apagar) {
                                    $iterator = new RecursiveDirectoryIterator($apagar, FilesystemIterator::SKIP_DOTS);
                                    $rec_iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
                                    foreach ($rec_iterator as $file) {
                                        $file->isFile() ? unlink($file->getPathname()) : rmdir($file->getPathname());
                                    }
                                    rmdir($apagar);
                                }
                                deletar("pasta/$email_empresa");
                            }
                        }
                    } else {
                        $mensagem = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Erro ao mover os arquivos.
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        </div>";
                    }
                }
            }
        }
    }

    pg_close($conexao);
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
                    <h5 class="card-title text-center pb-0 fs-4">Cadastro de funcionários</h5>
                  </div>

                  <?php if(!empty($mensagem)) { echo $mensagem; } ?>

                  <form class="row g-3 needs-validation" action="cadastrofuncionarios.php" method="POST" novalidate enctype="multipart/form-data">

                    <div class="col-12">
                      <label for="nome" class="form-label">Nome</label>
                      <input type="text" name="nome" class="form-control" id="nome" required>
                      <div class="invalid-feedback">Por favor, digite seu nome!</div>
                    </div>

                    <div class="col-12">
                      <label for="email" class="form-label">Email</label>
                      <input type="email" name="email" class="form-control" id="email" required>
                      <div class="invalid-feedback">Por favor, digite um e-mail válido!</div>
                    </div>

                    <div class="col-12">
                      <label for="ctps" class="form-label">CTPS</label>
                      <input type="text" name="ctps" class="form-control" id="ctps" required>
                      <div class="invalid-feedback">Por favor, digite sua CTPS!</div>
                    </div>

                    <div class="col-12">
                      <label for="foto" class="form-label">Foto</label>
                      <input type="file" name="foto" class="form-control" id="foto" required>
                      <div class="invalid-feedback">Por favor, faça o upload da sua foto!</div>
                    </div>

                    <div class="col-12">
                      <label for="foto1" class="form-label">Foto 2</label>
                      <input type="file" name="foto1" class="form-control" id="foto1" required>
                      <div class="invalid-feedback">Por favor, faça o upload da sua segunda foto!</div>
                    </div>

                    <div class="col-12">
                      <button class="btn btn-primary w-100" type="submit" name="dados">Cadastrar</button>
                    </div>
                  </form>

                </div>
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
  <script src="static/chart.js/chart.umd.js"></script>
  <script src="static/echarts/echarts.min.js"></script>
  <script src="static/quill/quill.min.js"></script>
  <script src="static/simple-datatables/simple-datatables.js"></script>
  <script src="static/tinymce/tinymce.min.js"></script>
  <script src="static/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="static/main.js"></script>

</body>

</html>
