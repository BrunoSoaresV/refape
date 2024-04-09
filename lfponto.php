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
    <title>Listagem de ponto</title>
    <link href="static/imagem1.png" rel="icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
</head>

<body>
    <div class="container mt-5">
        <?php
        require_once("conexao.php");
        if (isset($_SESSION['cnpj']) || isset($_SESSION['nome'])) {
            $email_empresa = $_SESSION['email'];
            $ctps = $_POST['ctps']; 
            $sql = "SELECT * FROM refape_web.ponto WHERE ctps='$ctps' AND email_empresa='$email_empresa'";
            $resultado = pg_query($conexao, $sql);

            echo "<h1 class='text-center mb-4'>Pontos obtidos:</h1>";
            echo "<table class='table table-striped table-bordered' id='pontoTable'>";
            echo "<thead class='table-dark'>
                    <tr>
                        <th scope='col'>Nome</th>
                        <th scope='col'>Email</th>
                        <th scope='col'>CTPS</th>
                        <th scope='col'>Hora de entrada</th>
                        <th scope='col'>Hora de saída</th>
                        <th scope='col'>Tempo de permanência</th>
                        <th scope='col'>ID do ponto</th>
                    </tr>
                </thead>
                <tbody>";
            while ($linha = pg_fetch_assoc($resultado)) {
                echo "<tr>
                        <td>{$linha['nome']}</td>
                        <td>{$linha['email']}</td>
                        <td>{$linha['ctps']}</td>
                        <td>{$linha['hora']}</td>
                        <td>{$linha['hora_saida']}</td>
                        <td>{$linha['tempo_permanencia']}</td>
                        <td>{$linha['id']}</td>
                    </tr>";
            }
            echo "</tbody></table>";
            echo "<div class='text-center mt-4'>
                    <button class='btn btn-secondary' onclick='javascript:history.back();'>Voltar</button>
                </div>";
        } else {
            echo "<div class='alert alert-danger text-center'>Acesso não autorizado.</div>";
        }

        pg_close($conexao);
        ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#pontoTable').DataTable({
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/Portuguese-Brasil.json"
                }
            });
        });
    </script>
</body>

</html>
