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
    <title>Listagem de ponto</title>
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
    <style>
     table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
    text-align: center;
    position: relative;
    margin-left: auto;
    margin-right: auto;
  }
  h1{
    text-align: center;
  }
</style>
</head>
<body>

<?php
require_once("conexao.php");
$email_empresa = $_SESSION['email'];
$_SESSION['ctps'] = $_POST['ctps'];
$ctps=$_SESSION['ctps'];
$sql = " SELECT * FROM refape_web.ponto WHERE ctps='$ctps' and email_empresa='$email_empresa'"; 
$resultado=pg_query($conexao,$sql);
echo "<h1>Pontos obtidos: </h1>";
echo "<br>";
echo "<table class='table table-hover' id='id'>";
echo "<strong>";
echo "<thead><tr><th>Nome</th><th>Email</th><th>CTPS</th><th>Hora de entrada</th><th>Hora de saída</th><th>Tempo de permanência</th><th>ID do ponto</th></tr></thead><tbody>";
while($linha = pg_fetch_assoc($resultado)){
     echo "<tr> <td>".$linha['nome']."</td><td>".$linha['email']."</td><td>".$linha['ctps']."</td><td>".$linha['hora']."</td><td>".$linha['hora_saida']."</td><td>".$linha['tempo_permanencia']."</td><td>".$linha['id']."</td></tr>";
}
echo "</tbody></table>";
pg_close($conexao);
echo "<input  class='btn btn-secondary' type='button' value='Voltar' onClick='javascript:history.back();'/>";
?>

</body>
</html>
