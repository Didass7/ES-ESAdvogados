<?php
include 'basedados.h';

// Verificar se o ID do caso foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID do caso não fornecido.'); window.location.href='gerir_caso.php';</script>";
    exit;
}

$id_caso = intval($_GET['id']);

// Obter informações do caso
$query_caso = "SELECT cj.id, cj.titulo, cj.descricao, cj.estado, cj.data_fechamento, c.nome AS cliente_nome
              FROM casos_juridicos cj
              JOIN cliente c ON cj.id_cliente = c.id_cliente
              WHERE cj.id = $id_caso";
$result_caso = mysqli_query($conn, $query_caso);

if (!$result_caso || mysqli_num_rows($result_caso) == 0) {
    echo "<script>alert('Caso não encontrado.'); window.location.href='gerir_caso.php';</script>";
    exit;
}

$caso = mysqli_fetch_assoc($result_caso);

// Verificar se a tabela atividades_caso existe
$check_table_query = "SHOW TABLES LIKE 'atividades_caso'";
$table_result = mysqli_query($conn, $check_table_query);

// Se a tabela não existir, criá-la
if (mysqli_num_rows($table_result) == 0) {
    $create_table_query = "CREATE TABLE atividades_caso (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_caso INT NOT NULL,
        titulo VARCHAR(255) NOT NULL,
        descricao TEXT,
        data_atividade DATE NOT NULL,
        custo DECIMAL(10,2) DEFAULT NULL,
        FOREIGN KEY (id_caso) REFERENCES casos_juridicos(id) ON DELETE CASCADE
    )";

    if (!mysqli_query($conn, $create_table_query)) {
        die("Erro ao criar tabela atividades_caso: " . mysqli_error($conn));
    }
}

// Obter as atividades do caso
$query_atividades = "SELECT id, titulo, descricao, data_atividade, custo FROM atividades_caso WHERE id_caso = $id_caso ORDER BY data_atividade DESC";
$result_atividades = mysqli_query($conn, $query_atividades);

// Calcular o custo total
$custo_total = 0;
$atividades = [];

if ($result_atividades && mysqli_num_rows($result_atividades) > 0) {
    while ($row = mysqli_fetch_assoc($result_atividades)) {
        $atividades[] = $row;
        if (isset($row['custo']) && $row['custo'] !== null) {
            $custo_total += floatval($row['custo']);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atividades do Caso</title>
    <link rel="stylesheet" href="gerir_caso.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <style>
        main {
            padding: 120px 20px 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            padding: 25px;
            margin-bottom: 30px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .page-header h1 {
            color: #5271ff;
            margin-bottom: 25px;
            font-size: 2.2rem;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .case-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-top: 3px solid #5271ff;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 15px;
        }

        .case-info-item {
            flex: 1 1 200px;
            min-width: 200px;
        }

        .case-info-label {
            color: #5271ff;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }

        .case-info-value {
            color: #333;
            font-size: 1.05rem;
            line-height: 1.4;
        }

        .case-description {
            flex: 1 1 100%;
            margin-top: 10px;
        }

        .activities-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .activities-header {
            background-color: #5271ff;
            color: white;
            padding: 15px 25px;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .activities-list {
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .activity-item {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            transition: background-color 0.2s;
        }

        .activity-item:hover {
            background-color: #f8f9fa;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 5px;
        }

        .activity-description {
            color: #666;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .activity-meta {
            display: flex;
            font-size: 0.9rem;
            color: #888;
        }

        .activity-date {
            margin-right: 15px;
        }

        .activity-cost {
            min-width: 120px;
            text-align: right;
            font-weight: 600;
            font-size: 1.2rem;
            color: #28a745;
        }

        .total-container {
            display: flex;
            justify-content: flex-end;
            padding: 20px 25px;
            background-color: #f8f9fa;
            border-top: 2px solid #eee;
        }

        .total-label {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin-right: 20px;
        }

        .total-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #5271ff;
            min-width: 120px;
            text-align: right;
        }

        .no-activities {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            font-style: italic;
            font-size: 1.1rem;
        }

        .actions-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .back-button {
            display: inline-block;
            background-color: #5271ff;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(82, 113, 255, 0.2);
        }

        .back-button:hover {
            background-color: #4056d6;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(82, 113, 255, 0.3);
        }

        .back-button:active {
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .activity-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .activity-cost {
                margin-top: 10px;
                align-self: flex-end;
            }

            .case-info {
                flex-direction: column;
            }

            .case-info-item {
                flex: 1 1 100%;
                min-width: 100%;
            }

            .page-header h1 {
                font-size: 1.8rem;
            }
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

            <a href="casos_juridicos.php">
              <img src="seta.png" alt="Ícone" style="width: 60px; height: 60px; vertical-align: middle;">
            </a>
        </div>
    </header>

    <main>
        <div class="page-header">
            <h1>Atividades do Caso</h1>
            <div class="case-info">
                <div class="case-info-item">
                    <div class="case-info-label">Título</div>
                    <div class="case-info-value"><?= $caso['titulo'] ?></div>
                </div>

                <div class="case-info-item">
                    <div class="case-info-label">Cliente</div>
                    <div class="case-info-value"><?= $caso['cliente_nome'] ?></div>
                </div>

                <div class="case-info-item">
                    <div class="case-info-label">Estado</div>
                    <div class="case-info-value"><?= $caso['estado'] ?></div>
                </div>

                <?php if (!empty($caso['data_fechamento'])): ?>
                <div class="case-info-item">
                    <div class="case-info-label">Data de Término</div>
                    <div class="case-info-value"><?= $caso['data_fechamento'] ?></div>
                </div>
                <?php endif; ?>

                <div class="case-description">
                    <div class="case-info-label">Descrição</div>
                    <div class="case-info-value"><?= $caso['descricao'] ?></div>
                </div>
            </div>
        </div>

        <div class="activities-container">
            <div class="activities-header">
                Lista de Atividades
            </div>

            <?php if (!empty($atividades)): ?>
                <ul class="activities-list">
                    <?php foreach ($atividades as $atividade): ?>
                        <li class="activity-item">
                            <div class="activity-content">
                                <div class="activity-title"><?= $atividade['titulo'] ?></div>
                                <div class="activity-description"><?= $atividade['descricao'] ?></div>
                                <div class="activity-meta">
                                    <div class="activity-date">
                                        <i class="far fa-calendar-alt"></i> <?= $atividade['data_atividade'] ?>
                                    </div>
                                </div>
                            </div>
                            <div class="activity-cost">
                                <?= isset($atividade['custo']) && $atividade['custo'] !== null ? number_format($atividade['custo'], 2) . ' €' : 'N/A' ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="total-container">
                    <div class="total-label">Total:</div>
                    <div class="total-value"><?= number_format($custo_total, 2) ?> €</div>
                </div>
            <?php else: ?>
                <div class="no-activities">
                    <i class="fas fa-info-circle"></i> Nenhuma atividade registrada para este caso.
                </div>
            <?php endif; ?>
        </div>

        <div class="actions-container">
            <a href="gerir_caso.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </main>

    <footer>
      <div class="footer-images">
        <a href="https://maps.app.goo.gl/UQYLoEsTwdgCKoft9" target="_blank">
          <img src="location.png" alt="Imagem Localização">
        </a>

        <a href="https://moodle2425.ipcb.pt/" target="_blank">
          <img src="phone.png" alt="Imagem Telefone">
        </a>

        <a href="https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox" target="_blank">
          <img src="mail.png" alt="Imagem Mail">
        </a>
      </div>
      <p class="copyright">© 2025 Todos os direitos reservados.</p>
    </footer>
</body>
</html>
