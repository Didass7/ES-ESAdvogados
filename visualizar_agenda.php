<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'basedados.h';

// Buscar compromissos do colaborador autenticado
$colaborador = $_SESSION['user_id'];
$agenda = [];
$sql = "SELECT mes, dia, acao FROM compromisso WHERE colaborador = ? ORDER BY FIELD(mes, 'JANEIRO','FEVEREIRO','MARÇO','ABRIL','MAIO','JUNHO','JULHO','AGOSTO','SETEMBRO','OUTUBRO','NOVEMBRO','DEZEMBRO'), dia";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $colaborador);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $agenda[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Visualizar Agenda</title>
    <link rel="stylesheet" href="regista_agenda.css"/>
    <style>
        .agenda-visualizacao {
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            max-width: 700px;
        }
        .agenda-table {
            width: 100%;
            border-collapse: collapse;
        }
        .agenda-table th, .agenda-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .agenda-table th {
            background: #5271ff;
            color: #fff;
        }
        .agenda-table tr:nth-child(even) {
            background: #f4f4f9;
        }
        .agenda-table tr:hover {
            background: #e6f7ff;
        }
        h2 {
            text-align: center;
            color: #5271ff;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="menu_colaborador.php">
                <img src="logo.png" alt="Logotipo" class="logo"/>
            </a>
        </div>
        <div class="header-container2">
            <a href="logout.php">
                <button class="menu-button">LOGOUT</button>
            </a>
            <button class="menu-button">
                COLABORADOR
                <img src="person.png" alt="Ícone" class="icon"/>
            </button>
            <a href="menu_colaborador.php">
                <img src="seta.png" alt="Ícone" class="icon-large"/>
            </a>
        </div>
    </header>

    <main>
        <div class="agenda-visualizacao">
            <h2>Minha Agenda</h2>
            <?php if (count($agenda) > 0): ?>
                <table class="agenda-table">
                    <thead>
                        <tr>
                            <th>Mês</th>
                            <th>Dia</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agenda as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['mes']); ?></td>
                                <td><?php echo htmlspecialchars($item['dia']); ?></td>
                                <td><?php echo htmlspecialchars($item['acao']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center;">Sem compromissos registados.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>