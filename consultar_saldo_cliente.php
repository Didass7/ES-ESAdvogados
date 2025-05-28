<?php
session_start();
include 'basedados.h';

// Verificar se o utilizador está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Inicializar variáveis
$clientes = [];
$mensagem = "";
$cliente_detalhes = null;
$pagamentos = [];
$horas_trabalhadas = [];
$saldo_total = 0;
$total_pago = 0;
$total_pendente = 0;
$modo_visualizacao = "lista"; // Padrão: mostrar a lista de clientes

// Verificar se estamos no modo de visualização detalhada
if (isset($_GET['id'])) {
    $modo_visualizacao = "detalhes";
    $id_cliente = intval($_GET['id']);

    // Buscar informações do cliente
    $sql = "SELECT * FROM cliente WHERE id_cliente = $id_cliente";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $cliente_detalhes = mysqli_fetch_assoc($result);
        
        // Buscar casos jurídicos do cliente
        $sql_casos = "SELECT id, titulo FROM casos_juridicos WHERE id_cliente = $id_cliente";
        $result_casos = mysqli_query($conn, $sql_casos);
        
        $casos_ids = [];
        $casos = [];
        if ($result_casos && mysqli_num_rows($result_casos) > 0) {
            while ($caso = mysqli_fetch_assoc($result_casos)) {
                $casos_ids[] = $caso['id'];
                $casos[$caso['id']] = $caso['titulo'];
            }
        }
        
        // Se o cliente tem casos, buscar horas trabalhadas
        if (!empty($casos_ids)) {
            $casos_ids_str = implode(',', $casos_ids);
            
            // Buscar horas trabalhadas
            $sql_horas = "SELECT h.*, c.titulo as caso_titulo, u.nome as colaborador_nome
                         FROM horas_trabalhadas h 
                         JOIN casos_juridicos c ON h.id_caso = c.id 
                         JOIN utilizador u ON h.id_colaborador = u.id
                         WHERE h.id_caso IN ($casos_ids_str)
                         ORDER BY h.data_registro DESC";
            
            $result_horas = mysqli_query($conn, $sql_horas);
            
            if ($result_horas && mysqli_num_rows($result_horas) > 0) {
                while ($hora = mysqli_fetch_assoc($result_horas)) {
                    $horas_trabalhadas[] = $hora;
                    $valor = $hora['horas'] * $hora['valor_hora'];
                    $saldo_total += $valor;
                    
                    if ($hora['faturado']) {
                        $total_pago += $valor;
                    } else {
                        $total_pendente += $valor;
                    }
                }
            }
            
            // Buscar pagamentos registados
            $sql_pagamentos = "SELECT p.*, c.titulo as caso_titulo 
                              FROM pagamentos p 
                              JOIN casos_juridicos c ON p.id_caso = c.id 
                              WHERE p.id_caso IN ($casos_ids_str)
                              ORDER BY p.data_pagamento DESC";
            
            $result_pagamentos = mysqli_query($conn, $sql_pagamentos);
            
            if ($result_pagamentos && mysqli_num_rows($result_pagamentos) > 0) {
                $pagamentos = mysqli_fetch_all($result_pagamentos, MYSQLI_ASSOC);
            }
        }
    } else {
        $mensagem = "Cliente não encontrado.";
        $modo_visualizacao = "lista"; // Voltar para o modo lista
    }
}

// Processar pesquisa de clientes
if ($modo_visualizacao == "lista") {
    if (isset($_POST['pesquisar'])) {
        $termo = mysqli_real_escape_string($conn, $_POST['termo']);
        $tipo = mysqli_real_escape_string($conn, $_POST['tipo']);
        
        if (!empty($termo)) {
            // Construir a consulta SQL baseada no tipo de pesquisa
            $sql = "SELECT * FROM cliente WHERE ";
            switch ($tipo) {
                case 'nome':
                    $sql .= "nome LIKE '%$termo%'";
                    break;
                case 'nif':
                    $sql .= "nif LIKE '%$termo%'";
                    break;
                case 'contacto':
                    $sql .= "contacto1 LIKE '%$termo%' OR contacto2 LIKE '%$termo%'";
                    break;
                default:
                    $sql .= "nome LIKE '%$termo%'";
            }
            
            $result = mysqli_query($conn, $sql);
            
            if ($result) {
                if (mysqli_num_rows($result) > 0) {
                    $clientes = mysqli_fetch_all($result, MYSQLI_ASSOC);
                } else {
                    $mensagem = "Nenhum cliente encontrado com os critérios especificados.";
                }
            } else {
                $mensagem = "Erro na consulta: " . mysqli_error($conn);
            }
        } else {
            $mensagem = "Por favor, insira um termo de pesquisa.";
        }
    } else {
        // Listar todos os clientes se não houver pesquisa
        $sql = "SELECT * FROM cliente LIMIT 20";
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $clientes = mysqli_fetch_all($result, MYSQLI_ASSOC);
        } else {
            $mensagem = "Nenhum cliente registado no sistema.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Saldo de Clientes</title>
    <link rel="stylesheet" href="casos_colaborador.css">
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
        
        .search-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .search-container select,
        .search-container input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .search-container button {
            background-color: #5271ff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
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
        
        .btn {
            display: inline-block;
            padding: 8px 15px;
            background-color: #5271ff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            background-color: #3a5ae8;
        }
        
        .btn-secondary {
            background-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .resumo {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        .resumo-item {
            text-align: center;
            flex: 1;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin: 0 10px;
        }
        
        .resumo-valor {
            font-size: 24px;
            font-weight: bold;
            color: #5271ff;
            margin-top: 10px;
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
        
        .faturado {
            color: green;
            font-weight: bold;
        }
        
        .nao-faturado {
            color: #ff5252;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
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
            <a href="menu_colaborador.php">
                <img src="logo.png" alt="Logotipo" class="logo">
            </a>
        </div>
        <div class="header-container2">
            <a href="logout.php">
                <button class="menu-button">TERMINAR SESSÃO</button>
            </a>
            <button class="menu-button">
                COLABORADOR
                <img src="person.png" alt="Ícone" style="width: 30px; height: 30px; vertical-align: middle;">
            </button>
            <a href="gerir_cliente.php">
                <img src="seta.png" alt="Ícone" style="width: 60px; height: 60px; vertical-align: middle;">
            </a>
        </div>
    </header>

    <div class="container">
        <?php if (!empty($mensagem)): ?>
            <div class="alert <?php echo strpos($mensagem, 'sucesso') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($modo_visualizacao == "lista"): ?>
            <div class="card">
                <h2><i class="fas fa-users"></i> Consultar Saldo de Clientes</h2>
                
                <form method="POST" action="" class="search-container">
                    <select name="tipo" class="form-control">
                        <option value="nome" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] == 'nome') ? 'selected' : ''; ?>>Nome</option>
                        <option value="nif" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] == 'nif') ? 'selected' : ''; ?>>NIF</option>
                        <option value="contacto" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] == 'contacto') ? 'selected' : ''; ?>>Contacto</option>
                    </select>
                    <input type="text" name="termo" class="form-control" placeholder="Digite o termo de pesquisa..." value="<?php echo isset($_POST['termo']) ? $_POST['termo'] : ''; ?>">
                    <button type="submit" name="pesquisar" class="btn">Pesquisar</button>
                </form>
                
                <?php if (!empty($clientes)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>NIF</th>
                                <th>Contacto</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes as $cliente): ?>
                                <tr>
                                    <td><?php echo $cliente['nome']; ?></td>
                                    <td><?php echo $cliente['nif']; ?></td>
                                    <td><?php echo $cliente['contacto1']; ?></td>
                                    <td>
                                        <a href="consultar_saldo_cliente.php?id=<?php echo $cliente['id_cliente']; ?>" class="btn">Ver Saldo</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Nenhum cliente encontrado.</p>
                <?php endif; ?>
            </div>
            
        <?php elseif ($modo_visualizacao == "detalhes" && $cliente_detalhes): ?>
            <div class="card">
                <h2><i class="fas fa-user"></i> Detalhes do Cliente: <?php echo $cliente_detalhes['nome']; ?></h2>
                
                <div class="resumo">
                    <div class="resumo-item">
                        <div>Total Faturado</div>
                        <div class="resumo-valor"><?php echo number_format($saldo_total, 2); ?> €</div>
                    </div>
                    <div class="resumo-item">
                        <div>Total Pago</div>
                        <div class="resumo-valor"><?php echo number_format($total_pago, 2); ?> €</div>
                    </div>
                    <div class="resumo-item">
                        <div>Total Pendente</div>
                        <div class="resumo-valor"><?php echo number_format($total_pendente, 2); ?> €</div>
                    </div>
                </div>
                
                <div class="tabs">
                    <button class="tab-button active" onclick="openTab(event, 'horas')">Horas Trabalhadas</button>
                    <button class="tab-button" onclick="openTab(event, 'pagamentos')">Pagamentos</button>
                    <button class="tab-button" onclick="openTab(event, 'casos')">Processos Jurídicos</button>
                </div>
                
                <div id="horas" class="tab-content" style="display: block;">
                    <h3>Horas Trabalhadas</h3>
                    <?php if (!empty($horas_trabalhadas)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Processo</th>
                                    <th>Data</th>
                                    <th>Colaborador</th>
                                    <th>Horas</th>
                                    <th>Valor/Hora</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($horas_trabalhadas as $hora): ?>
                                    <tr>
                                        <td><?php echo $hora['caso_titulo']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($hora['data_registro'])); ?></td>
                                        <td><?php echo $hora['colaborador_nome']; ?></td>
                                        <td><?php echo number_format($hora['horas'], 2); ?></td>
                                        <td><?php echo number_format($hora['valor_hora'], 2); ?> €</td>
                                        <td><?php echo number_format($hora['horas'] * $hora['valor_hora'], 2); ?> €</td>
                                        <td>
                                            <?php if ($hora['faturado']): ?>
                                                <span class="faturado"><i class="fas fa-check-circle"></i> Pago</span>
                                            <?php else: ?>
                                                <span class="nao-faturado"><i class="fas fa-clock"></i> Pendente</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Nenhuma hora trabalhada registada para este cliente.</p>
                    <?php endif; ?>
                </div>
                
                <div id="pagamentos" class="tab-content">
                    <h3>Histórico de Pagamentos</h3>
                    <?php if (!empty($pagamentos)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Processo</th>
                                    <th>Data</th>
                                    <th>Valor</th>
                                    <th>Método</th>
                                    <th>Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagamentos as $pagamento): ?>
                                    <tr>
                                        <td><?php echo $pagamento['caso_titulo']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($pagamento['data_pagamento'])); ?></td>
                                        <td><?php echo number_format($pagamento['valor'], 2); ?> €</td>
                                        <td><?php echo $pagamento['metodo_pagamento']; ?></td>
                                        <td><?php echo $pagamento['descricao']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Nenhum pagamento registado para este cliente.</p>
                    <?php endif; ?>
                </div>
                
                <div id="casos" class="tab-content">
                    <h3>Processos Jurídicos</h3>
                    <?php if (!empty($casos)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($casos as $id => $titulo): ?>
                                    <tr>
                                        <td><?php echo $id; ?></td>
                                        <td><?php echo $titulo; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Nenhum processo jurídico registado para este cliente.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>


