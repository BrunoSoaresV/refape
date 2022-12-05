<?php
  include("protecao.php");
  if (!isset($_SESSION)) {
    session_start();
  }
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Desativados</title>
  <link href="static/imagem1.png" rel="icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" />
	  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" />
	  <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
	  <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
  <script>
	$(document).ready(function() {
    $('#id').DataTable( {
        dom: 'Bfrtip',
        buttons: [
          'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    } )
} )
	</script>
  <!-- Template Main CSS File -->
  <link href="static/style.css" rel="stylesheet">
  <style>
    table,
    th,
    td {
      border: 1px solid black;
      border-collapse: collapse;
      text-align: center;
      position: relative;
      margin-left: auto;
      margin-right: auto;
    }

    h1 {
      text-align: center;
    }
  </style>
</head>
<body>
  <?php
  require_once("conexao.php");
  if (isset($_SESSION['cnpj']) || isset($_SESSION['nome'])) {
    $email_empresa = $_SESSION['email'];
    $sql = " SELECT * FROM refape_web.funcionario  WHERE email_empresa='$email_empresa' AND status='false'";
    $resultado = pg_query($conexao, $sql);
    echo "<h1>Dados dos funcionários desativados:</h1>";
    echo "<br>";
    echo "<table class='table table-hover' id='id'>";
    echo "<strong>";
    echo "<thead><tr><th>Nome</th><th>Email</th><th>CTPS</th><th>ID do funcionário</th><th>Email da empresa</th><th>Editar</th></tr></thead><tbody>";
    while ($linha = pg_fetch_assoc($resultado)) {
      echo "<tr> <td>" . $linha['nome'] . "</td><td>" . $linha['email'] . "</td><td>" . $linha['ctps'] . "</td><td>" . $linha['id'] . "</td><td>" . $linha['email_empresa'] . "</td>
     <td><a href=editar?id=" . $linha['id'] . "><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-pencil' viewBox='0 0 16 16'>
     <path d='M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z'/>
   </svg></a></td></tr>";
    }
    echo "</tbody></table>";
    pg_close($conexao);
    echo "<a class='btn btn-secondary'href='listagem'><input class='btn btn-secondary' type='button' value='Voltar'></a>";
  }
  ?>

</body>

</html>