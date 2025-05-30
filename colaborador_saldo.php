<?php
session_start();
include 'basedados.h';

// Verificar se o utilizador está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Buscar clientes associados ao colaborador
$sql_clientes = "SELECT id_cliente, nome, saldo FROM cliente WHERE id_colaborador = '$user_id'";
$result_clientes = mysqli_query($conn, $sql_clientes);

$clientes = [];
if ($result_clientes && mysqli_num_rows($result_clientes) > 0) {
    while ($row = mysqli_fetch_assoc($result_clientes)) {
        $clientes[] = $row;
    }
}

// Processar o formulário de adicionar/retirar saldo
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_cliente = $_POST['id_cliente'];
    $valor = floatval($_POST['valor']); // Valor a adicionar ou retirar
    $tipo = $_POST['tipo']; // 'adicionar' ou 'retirar'

    // Obter o saldo atual do cliente
    $sql_saldo = "SELECT saldo FROM cliente WHERE id_cliente = '$id_cliente'";
    $result_saldo = mysqli_query($conn, $sql_saldo);

    if ($result_saldo && mysqli_num_rows($result_saldo) > 0) {
        $row_saldo = mysqli_fetch_assoc($result_saldo);
        $saldo_atual = floatval($row_saldo['saldo']);

        // Calcular o novo saldo
        if ($tipo == 'adicionar') {
            if ($valor > 0) {
                $novo_saldo = $saldo_atual + $valor;
            } else {
                echo "<script>alert('Por favor, insira um valor positivo para adicionar.'); window.location.href='colaborador_saldo.php';</script>";
                exit();
            }
        } elseif ($tipo == 'retirar') {
            if ($valor > 0) {
                $novo_saldo = $saldo_atual - $valor;
                if ($novo_saldo < 0) {
                    echo "<script>alert('O valor a retirar é superior ao saldo disponível.'); window.location.href='colaborador_saldo.php';</script>";
                    exit();
                }
            } else {
                echo "<script>alert('Por favor, insira um valor positivo para retirar.'); window.location.href='colaborador_saldo.php';</script>";
                exit();
            }
        }

        // Atualizar o saldo do cliente na base de dados
        $sql_update = "UPDATE cliente SET saldo = '$novo_saldo' WHERE id_cliente = '$id_cliente'";

        if (mysqli_query($conn, $sql_update)) {
            echo "<script>alert('Saldo do cliente atualizado com sucesso!'); window.location.href='colaborador_saldo.php';</script>";
        } else {
            echo "<script>alert('Erro ao atualizar o saldo do cliente: " . mysqli_error($conn) . "');</script>";
        }
    } else {
        echo "<script>alert('Erro ao obter o saldo do cliente.');</script>";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saldo dos Clientes</title>
    <link rel="stylesheet" href="menu_colaborador.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .clientes-container {
            max-width: 800px;
            margin: 100px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            color: blue;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="menu_colaborador.php">
                <img src="logo.png" alt="Logotipo" class="logo">
            </a>
        </div>
        <div class="header-container2">
            <a href="logout.php">
                <button class="menu-button">LOGOUT</button>
            </a>
            <button class="menu-button">
                COLABORADOR
                <img src="person.png" alt="Ícone" style="width: 30px; height: 30px; vertical-align: middle;">
            </button>
            <a href="menu_colaborador.php">
                <img src="seta.png" alt="Ícone" style="width: 60px; height: 60px; vertical-align: middle;">
            </a>
        </div>
    </header>

    <div class="clientes-container">
        <h2 style="color: #5271ff; text-align: center; margin-bottom: 10px;">Clientes Associados</h2>
        <table>
            <thead>
                <tr>
                    <th style="color: #5271ff;">ID</th>
                    <th style="color: #5271ff;">Nome</th>
                    <th style="color: #5271ff;">Saldo</th>
                    <th style="color: #5271ff;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td style="color: #5271ff; font-weight: bold;"><?php echo $cliente['id_cliente']; ?></td>
                        <td style="color: #5271ff; font-weight: bold;"><?php echo $cliente['nome']; ?></td>
                        <td style="color: #5271ff; font-weight: bold;"><?php echo $cliente['saldo']; ?></td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="id_cliente" value="<?php echo $cliente['id_cliente']; ?>">
                                <input type="number" name="valor" step="0.01" placeholder="Valor" required>
                                <button type="submit" name="tipo" value="adicionar">Adicionar Saldo</button>
                                <button type="submit" name="tipo" value="retirar">Retirar Saldo</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>