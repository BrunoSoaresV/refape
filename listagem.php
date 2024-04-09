<?php
include("protecao.php");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("conexao.php");
if (isset($_SESSION['cnpj']) || isset($_SESSION['nome'])) {
    $email_empresa = $_SESSION['email'];
    $sql = "SELECT * FROM refape_web.funcionario WHERE email_empresa='$email_empresa' AND status='true'";
    $resultado = pg_query($conexao, $sql);
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listagem</title>
    <link href="static/imagem1.png" rel="icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" />
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
        }

        .btn-container {
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }

        .btn-container a {
            margin-bottom: 15px; 
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if (isset($_SESSION['cnpj']) || isset($_SESSION['nome'])) : ?>
            <h1>Dados dos funcionários:</h1>
            <table id="funcionarios" class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>CTPS</th>
                        <th>ID do funcionário</th>
                        <th>Email da empresa</th>
                        <th>Editar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($linha = pg_fetch_assoc($resultado)) : ?>
                        <tr>
                            <td><?= $linha['nome'] ?></td>
                            <td><?= $linha['email'] ?></td>
                            <td><?= $linha['ctps'] ?></td>
                            <td><?= $linha['id'] ?></td>
                            <td><?= $linha['email_empresa'] ?></td>
                            <td>
                                <a href="editar.php?id=<?= $linha['id'] ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                                        <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div class="btn-container">
                <a href="desativado.php" class="btn btn-secondary">Ver funcionários desativados</a>
                <a href="home.php" class="btn btn-secondary">Voltar</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#funcionarios').DataTable({
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
            });
        });
    </script>
</body>

</html>
<?php
pg_close($conexao);
?>
