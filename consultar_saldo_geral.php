<?php
session_start();
include 'basedados.h';

// Verificar se o utilizador está autenticado e é administrador
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Verificar se é administrador (id_tipo = 1)
$user_id = $_SESSION['user_id'];
$sql_check_admin = "SELECT id_tipo FROM utilizador WHERE id_utilizador = '$user_id'";
$result_admin = mysqli_query($conn, $sql_check_admin);

if (!$result_admin || mysqli_num_rows($result_admin) == 0 || mysqli_fetch_assoc($result_admin)['id_tipo'] != 1) {
    echo "<script>alert('Acesso não autorizado.'); window.location.href='login.php';</script>";
    exit();
}

// Inicializar variáveis
$total_faturado = 0;
$total_pago = 0;
$total_pendente = 0;
$clientes = [];
$colaboradores = [];
$pagamentos_recentes = [];
$horas_recentes = [];

// Período para análise (padrão: mês atual)
$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'mes';
$data_inicio = '';
$data_fim = date('Y-m-d');

switch ($periodo) {
    case 'semana':
        $data_inicio = date('Y-m-d', strtotime('-7 days'));
        break;
    case 'mes':
        $data_inicio = date('Y-m-01');
        break;
    case 'trimestre':
        $data_inicio = date('Y-m-d', strtotime('-3 months'));
        break;
    case 'ano':
        $data_inicio = date('Y-01-01');
        break;
    case 'personalizado':
        $data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-01');
        $data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');
        break;
}

// Obter totais de horas trabalhadas
$sql_horas = "SELECT 
                SUM(h.horas * h.valor_hora) as total_faturado,
                SUM(CASE WHEN h.faturado = 1 THEN h.horas * h.valor_hora ELSE 0 END) as total_pago,
                SUM(CASE WHEN h.faturado = 0 THEN h.horas * h.valor_hora ELSE 0 END) as total_pendente
              FROM horas_trabalhadas h
              WHERE h.data_registro BETWEEN '$data_inicio' AND '$data_fim 23:59:59'";

$result_horas = mysqli_query($conn, $sql_horas);

if ($result_horas && mysqli_num_rows($result_horas) > 0) {
    $row = mysqli_fetch_assoc($result_horas);
    $total_faturado = $row['total_faturado'] ?: 0;
    $total_pago = $row['total_pago'] ?: 0;
    $total_pendente = $row['total_pendente'] ?: 0;
}

// Obter top 5 clientes com mais faturação
$sql_clientes = "SELECT 
                    c.id_cliente,
                    c.nome,
                    SUM(h.horas * h.valor_hora) as total_faturado
                 FROM horas_trabalhadas h
                 JOIN casos_juridicos cj ON h.id_caso = cj.id
                 JOIN cliente c ON cj.id_cliente = c.id_cliente
                 WHERE h.data_registro BETWEEN '$data_inicio' AND '$data_fim 23:59:59'
                 GROUP BY c.id_cliente, c.nome
                 ORDER BY total_faturado DESC
                 LIMIT 5";

$result_clientes = mysqli_query($conn, $sql_clientes);

if ($result_clientes && mysqli_num_rows($result_clientes) > 0) {
    while ($row = mysqli_fetch_assoc($result_clientes)) {
        $clientes[] = $row;
    }
}

// Obter top 5 colaboradores com mais horas registadas
$sql_colaboradores = "SELECT 
                        u.id_utilizador,
                        u.nome,
                        SUM(h.horas) as total_horas,
                        SUM(h.horas * h.valor_hora) as total_faturado
                      FROM horas_trabalhadas h
                      JOIN utilizador u ON h.id_colaborador = u.id_utilizador
                      WHERE h.data_registro BETWEEN '$data_inicio' AND '$data_fim 23:59:59'
                      GROUP BY u.id_utilizador, u.nome
                      ORDER BY total_horas DESC
                      LIMIT 5";

$result_colaboradores = mysqli_query($conn, $sql_colaboradores);

if ($result_colaboradores && mysqli_num_rows($result_colaboradores) > 0) {
    while ($row = mysqli_fetch_assoc($result_colaboradores)) {
        $colaboradores[] = $row;
    }
}

// Obter pagamentos recentes
$sql_pagamentos = "SELECT 
                    p.id,
                    p.valor,
                    p.data_pagamento,
                    p.metodo_pagamento,
                    c.titulo as caso_titulo,
                    cl.nome as cliente_nome
                  FROM pagamentos p
                  JOIN casos_juridicos c ON p.id_caso = c.id
                  JOIN cliente cl ON c.id_cliente = cl.id_cliente
                  WHERE p.data_pagamento BETWEEN '$data_inicio' AND '$data_fim 23:59:59'
                  ORDER BY p.data_pagamento DESC
                  LIMIT 10";

$result_pagamentos = mysqli_query($conn, $sql_pagamentos);

if ($result_pagamentos && mysqli_num_rows($result_pagamentos) > 0) {
    while ($row = mysqli_fetch_assoc($result_pagamentos)) {
        $pagamentos_recentes[] = $row;
    }
}

// Obter horas trabalhadas recentes
$sql_horas_recentes = "SELECT 
                        h.id,
                        h.horas,
                        h.valor_hora,
                        h.data_registro,
                        h.faturado,
                        c.titulo as caso_titulo,
                        u.nome as colaborador_nome,
                        cl.nome as cliente_nome
                      FROM horas_trabalhadas h
                      JOIN casos_juridicos c ON h.id_caso = c.id
                      JOIN utilizador u ON h.id_colaborador = u.id_utilizador
                      JOIN cliente cl ON c.id_cliente = cl.id_cliente
                      WHERE h.data_registro BETWEEN '$data_inicio' AND '$data_fim 23:59:59'
                      ORDER BY h.data_registro DESC
                      LIMIT 10";

$result_horas_recentes = mysqli_query($conn, $sql_horas_recentes);

if ($result_horas_recentes && mysqli_num_rows($result_horas_recentes) > 0) {
    while ($row = mysqli_fetch_assoc($result_horas_recentes)) {
        $horas_recentes[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faturação Geral</title>
    <link rel="stylesheet" href="menu_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 100px auto 50px;
            padding: 20px;
        }
        
        .card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .card h2 {
            color: #5271ff;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }
        
        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }
        
        .filter-container select,
        .filter-container input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            flex-grow: 1;
        }
        
        .filter-container button {
            background-color: #5271ff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .resumo {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            margin-bottom: 30px;
        }
        
        .resumo-item {
            text-align: center;
            flex: 1;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin: 0 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .resumo-valor {
            font-size: 28px;
            font-weight: bold;
            color: #5271ff;
            margin-top: 10px;
        }
        
        .resumo-titulo {
            font-size: 16px;
            color: #6c757d;
            margin-bottom: 10px;
        }
        
        .grid-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .faturado {
            color: green;
            font-weight: bold;
        }
        
        .nao-faturado {
            color: #ff5252;
        }
        
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        
        .tab-button {
            padding: 10px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            color: #6c757d;
        }
        
        .tab-button.active {
            color: #5271ff;
            border-bottom: 3px solid #5271ff;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
    <script>
        function openTab(evt, tabName) {
            // Esconder todos os conteúdos das abas
            var tabContents = document.getElementsByClassName("tab-content");
            for (var i = 0; i < tabContents.length; i++) {
                tabContents[i].style.display = "none";
            }
            
            // Remover a classe "active" de todos os botões
            var tabButtons = document.getElementsByClassName("tab-button");
            for (var i = 0; i < tabButtons.length; i++) {
                tabButtons[i].className = tabButtons[i].className.replace(" active", "");
            }
            
            // Mostrar o conteúdo da aba atual e adicionar a classe "active" ao botão
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }
    </script>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="menu_admin.php">
                <img src="logo.png" alt="Logotipo" class="logo">
            </a>
        </div>
        <div class="header-container2">
            <a href="muda_password_admin.php">
              <button class="menu-button">MUDAR PASSWORD</button>
            </a>

            <a href="edita_perfil_admin.php">
              <button class="menu-button">EDITAR PERFIL</button>
            </a>

            <a href="logout.php">
              <button class="menu-button">TERMINAR SESSÃO</button>
            </a>

            <button class="menu-button">
              ADMINISTRADOR
              <img src="person.png" alt="Ícone" style="width: 30px; height: 30px; vertical-align: middle;">
            </button>
            
            <a href="menu_admin.php">
              <img src="seta.png" alt="Ícone" style="width: 60px; height: 60px; vertical-align: middle;">
            </a>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2><i class="fas fa-chart-line"></i> Faturação Geral</h2>
            
            <form method="GET" action="" class="filter-container">
                <select name="periodo" onchange="this.form.submit()">
                    <option value="semana" <?php echo $periodo == 'semana' ? 'selected' : ''; ?>>Última Semana</option>
                    <option value="mes" <?php echo $periodo == 'mes' ? 'selected' : ''; ?>>Mês Atual</option>
                    <option value="trimestre" <?php echo $periodo == 'trimestre' ? 'selected' : ''; ?>>Último Trimestre</option>
                    <option value="ano" <?php echo $periodo == 'ano' ? 'selected' : ''; ?>>Ano Atual</option>
                    <option value="personalizado" <?php echo $periodo == 'personalizado' ? 'selected' : ''; ?>>Período Personalizado</option>
                </select>
                
                <?php if ($periodo == 'personalizado'): ?>
                    <input type="date" name="data_inicio" value="<?php echo $data_inicio; ?>" required>
                    <input type="date" name="data_fim" value="<?php echo $data_fim; ?>" required>
                    <button type="submit">Aplicar</button>
                <?php endif; ?>
            </form>
            
            <div class="resumo">
                <div class="resumo-item">
                    <div class="resumo-titulo">Total Faturado</div>
                    <div class="resumo-valor"><?php echo number_format($total_faturado, 2); ?> €</div>
                </div>
                <div class="resumo-item">
                    <div class="resumo-titulo">Total Pago</div>
                    <div class="resumo-valor"><?php echo number_format($total_pago, 2); ?> €</div>
                </div>
                <div class="resumo-item">
                    <div class="resumo-titulo">Total Pendente</div>
                    <div class="resumo-valor"><?php echo number_format($total_pendente, 2); ?> €</div>
                </div>
            </div>
            
            <div class="tabs">
                <button class="tab-button active" onclick="openTab(event, 'clientes')">Top Clientes</button>
                <button class="tab-button" onclick="openTab(event, 'colaboradores')">Top Colaboradores</button>
                <button class="tab-button" onclick="openTab(event, 'pagamentos')">Pagamentos Recentes</button>
                <button class="tab-button" onclick="openTab(event, 'horas')">Horas Trabalhadas Recentes</button>
            </div>
            
            <div id="clientes" class="tab-content" style="display: block;">
                <h3>Top 5 Clientes por Faturação</h3>
                <?php if (!empty($clientes)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Total Faturado</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes as $cliente): ?>
                                <tr>
                                    <td><?php echo $cliente['nome']; ?></td>
                                    <td><?php echo number_format($cliente['total_faturado'], 2); ?> €</td>
                                    <td>
                                        <a href="consultar_saldo_cliente_admin.php?id=<?php echo $cliente['id_cliente']; ?>" class="btn">Ver Detalhes</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Nenhum cliente com faturação no período selecionado.</p>
                <?php endif; ?>
            </div>
            
            <div id="colaboradores" class="tab-content">
                <h3>Top 5 Colaboradores por Horas Trabalhadas</h3>
                <?php if (!empty($colaboradores)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Colaborador</th>
                                <th>Total Horas</th>
                                <th>Total Faturado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($colaboradores as $colaborador): ?>
                                <tr>
                                    <td><?php echo $colaborador['nome']; ?></td>
                                    <td><?php echo number_format($colaborador['total_horas'], 2); ?> h</td>
                                    <td><?php echo number_format($colaborador['total_faturado'], 2); ?> €</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Nenhum colaborador com horas registadas no período selecionado.</p>
                <?php endif; ?>
            </div>
            
            <div id="pagamentos" class="tab-content">
                <h3>Pagamentos Recentes</h3>
                <?php if (!empty($pagamentos_recentes)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Cliente</th>
                                <th>Processo</th>
                                <th>Valor</th>
                                <th>Método de Pagamento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagamentos_recentes as $pagamento): ?>
                                <tr>
                                    <td><?php echo $pagamento['data_pagamento']; ?></td>
                                    <td><?php echo $pagamento['cliente_nome']; ?></td>
                                    <td><?php echo $pagamento['caso_titulo']; ?></td>
                                    <td><?php echo number_format($pagamento['valor'], 2); ?> €</td>
                                    <td><?php echo $pagamento['metodo_pagamento']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Nenhum pagamento registado no período selecionado.</p>
                <?php endif; ?>
            </div>
            
            <div id="horas" class="tab-content">
                <h3>Horas Trabalhadas Recentes</h3>
                <?php if (!empty($horas_recentes)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Colaborador</th>
                                <th>Cliente</th>
                                <th>Horas</th>
                                <th>Valor/Hora</th>
                                <th>Total</th>
                                <th>Faturado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($horas_recentes as $hora): ?>
                                <tr>
                                    <td><?php echo $hora['data_registro']; ?></td>
                                    <td><?php echo $hora['colaborador_nome']; ?></td>
                                    <td><?php echo $hora['cliente_nome']; ?></td>
                                    <td><?php echo number_format($hora['horas'], 2); ?> h</td>
                                    <td><?php echo number_format($hora['valor_hora'], 2); ?> €</td>
                                    <td><?php echo number_format($hora['horas'] * $hora['valor_hora'], 2); ?> €</td>
                                    <td class="<?php echo $hora['faturado'] ? 'faturado' : 'nao-faturado'; ?>">
                                        <?php echo $hora['faturado'] ? 'Sim' : 'Não'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Nenhuma hora registada no período selecionado.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
