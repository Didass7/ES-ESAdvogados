<?php
session_start();
include 'basedados.h';

// Verificar se o utilizador está autenticado
if (!isset($_SESSION['id_utilizador'])) {
    echo "<script>alert('Sessão expirada. Faça login novamente.'); window.location.href = 'login.php';</script>";
    exit;
}

// Inicializar variáveis
$clientes = [];
$mensagem = "";
$cliente_detalhes = null;
$casos = [];
$metodo_pagamento = "Não especificado";
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

        // Buscar casos jurídicos associados ao cliente
        $sql_casos = "SELECT * FROM casos_juridicos WHERE id_cliente = $id_cliente";
        $result_casos = mysqli_query($conn, $sql_casos);

        if ($result_casos && mysqli_num_rows($result_casos) > 0) {
            $casos = mysqli_fetch_all($result_casos, MYSQLI_ASSOC);
        }

        // Buscar o método de pagamento
        if (isset($cliente_detalhes['pagamento'])) {
            $id_pagamento = $cliente_detalhes['pagamento'];
            $sql_pagamento = "SELECT metodo FROM metodopagamento WHERE id_metodo = $id_pagamento";
            $result_pagamento = mysqli_query($conn, $sql_pagamento);

            if ($result_pagamento && mysqli_num_rows($result_pagamento) > 0) {
                $row_pagamento = mysqli_fetch_assoc($result_pagamento);
                $metodo_pagamento = $row_pagamento['metodo'];
            }
        }
    } else {
        $mensagem = "Cliente não encontrado.";
        $modo_visualizacao = "lista"; // Voltar para o modo lista
    }
}

// Processar a pesquisa no modo lista
if ($modo_visualizacao == "lista") {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesquisar'])) {
        $termo = isset($_POST['termo']) ? mysqli_real_escape_string($conn, $_POST['termo']) : '';
        $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : 'nome';

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
            $mensagem = "Nenhum cliente cadastrado no sistema.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Clientes</title>
    <link rel="stylesheet" href="menu_colaborador.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</head>
<body>
    
    <header>
        <div class="header-container">
            <a href="menu_colaborador.php">
                <img src="logo.png" alt="Logotipo" class="logo">
            </a>
        </div>
        <div class="header-container2">
            <a href="muda_password_colaborador.php">
                <button class="menu-button">MUDAR PASSWORD</button>
            </a>
            <a href="edita_perfil_colaborador.php">
                <button class="menu-button">EDITAR PERFIL</button>
            </a>
            <a href="logout.php">
                <button class="menu-button">LOGOUT</button>
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
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #5271ff;
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', sans-serif;
        }
        
        header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            background-color: #5271ff;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            max-width: 1000px;
            margin: 0 auto;
            padding-top: 120px;
            padding-bottom: 80px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        /* Estilos para a lista de clientes */
        .search-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
            width: 100%;
            max-width: 800px;
            justify-content: center;
        }
        
        /* Novo estilo para o container de detalhes */
        .details-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 20px;
            width: 100%;
            max-width: 800px;
        }
        
        @media (min-width: 768px) {
            .details-container {
                flex-direction: row;
            }
        }
        
        /* Ajuste para as caixas de detalhes ficarem lado a lado */
        .client-details {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            flex: 1;
        }
        
        .client-details h2 {
            color: #5271ff;
            margin-top: 0;
            border-bottom: 2px solid #5271ff;
            padding-bottom: 8px;
            margin-bottom: 15px;
            font-size: 1.3rem;
            text-align: center;
        }
        
        .client-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .info-item {
            margin-bottom: 12px;
        }
        
        .info-item label {
            font-weight: bold;
            display: block;
            margin-bottom: 4px;
            color: #5271ff;
            font-size: 0.95rem;
        }
        
        .info-item p {
            margin: 0;
            padding: 6px;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-size: 0.95rem;
        }
        
        /* Estilos para a tabela de resultados */
        .results-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 20px;
            max-width: 800px;
        }
        
        .results-table th, 
        .results-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .results-table th {
            background-color: #5271ff;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        
        .results-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .action-buttons a {
            display: inline-block;
            padding: 6px 12px;
            background-color: #5271ff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: background-color 0.2s;
            margin-right: 5px;
        }
        
        .action-buttons a:hover {
            background-color: #3a5ae8;
        }
        
        /* Estilo para mensagens */
        .message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            width: 100%;
            max-width: 800px;
        }
        
        .search-container select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.95rem;
            min-width: 120px;
        }
        
        .search-container input[type="text"] {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.95rem;
            flex: 1;
            min-width: 200px;
        }
        
        .search-container button {
            padding: 8px 15px;
            background-color: #5271ff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .search-container button:hover {
            background-color: #3a5ae8;
        }
        
        /* Título da página */
        .page-title {
            color: white;
            text-align: center;
            margin-bottom: 25px;
            font-size: 1.8rem;
        }
        
        /* Estilo para os casos jurídicos */
        .case-item {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .case-item h3 {
            color: #5271ff;
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        footer {
            background-color: white;
            padding: 8px 0;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: 100%;
            height: auto;
            z-index: 999;
        }

        .footer-images {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 5px 0;
        }

        .footer-images img {
            width: 30px;
            height: 30px;
        }

        .copyright {
            font-size: 0.7rem;
            color: #5271ff;
            margin: 2px 0;
            font-weight: bold;
        }

        /* Ajuste para dar mais espaço ao conteúdo principal */
        .main-content {
            padding-bottom: 60px;
        }
    </style>
    <div class="main-content">
        <h1 class="page-title">Consultar Clientes</h1>
        <form method="POST" action="" class="search-container">
            <select name="tipo">
                <option value="nome" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] == 'nome') ? 'selected' : ''; ?>>Nome</option>
                <option value="nif" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] == 'nif') ? 'selected' : ''; ?>>NIF</option>
                <option value="contacto" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] == 'contacto') ? 'selected' : ''; ?>>Contacto</option>
            </select>
            <input type="text" name="termo" placeholder="Digite o termo de pesquisa..." value="<?php echo isset($_POST['termo']) ? $_POST['termo'] : ''; ?>">
            <button type="submit" name="pesquisar">Pesquisar</button>
        </form>

        <?php if (!empty($mensagem)): ?>
            <div class="message"><?php echo $mensagem; ?></div>
        <?php endif; ?>
        
        <?php if ($modo_visualizacao == "lista" && !empty($clientes)): ?>
            <table class="results-table">
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
                            <td class="action-buttons">
                                <a href="consultar_cliente.php?id=<?php echo $cliente['id_cliente']; ?>">Ver Detalhes</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <?php if ($modo_visualizacao == "detalhes" && $cliente_detalhes): ?>
            <div class="details-container">
                <div class="client-details">
                    <h2>Informações do Cliente</h2>
                    <div class="client-info">
                        <div class="info-item">
                            <label>Nome</label>
                            <p><?php echo $cliente_detalhes['nome']; ?></p>
                        </div>
                        <div class="info-item">
                            <label>Data de Nascimento</label>
                            <p><?php echo $cliente_detalhes['dataNasci']; ?></p>
                        </div>
                        <div class="info-item">
                            <label>NIF</label>
                            <p><?php echo $cliente_detalhes['nif']; ?></p>
                        </div>
                        <div class="info-item">
                            <label>Contacto Principal</label>
                            <p><?php echo $cliente_detalhes['contacto1']; ?></p>
                        </div>
                        <div class="info-item">
                            <label>Contacto Secundário</label>
                            <p><?php echo $cliente_detalhes['contacto2'] ? $cliente_detalhes['contacto2'] : 'Não informado'; ?></p>
                        </div>
                        <div class="info-item">
                            <label>Método de Pagamento</label>
                            <p><?php echo $metodo_pagamento; ?></p>
                        </div>
                    </div>
                    
                    <div class="info-item" style="grid-column: span 2;">
                        <label>Morada</label>
                        <p><?php echo $cliente_detalhes['morada']; ?></p>
                    </div>
                    <div class="info-item" style="grid-column: span 2;">
                        <label>Endereço de Faturação</label>
                        <p><?php echo $cliente_detalhes['endereco_faturacao']; ?></p>
                    </div>
                    
                    <div class="action-buttons" style="margin-top: 20px; text-align: center;">
                        <a href="consultar_cliente.php">Voltar à Lista</a>
                    </div>
                </div>
                
                <div class="client-details">
                    <h2>Casos Jurídicos</h2>
                    <?php if (!empty($casos)): ?>
                        <?php foreach ($casos as $caso): ?>
                            <div class="case-item">
                                <h3><?php echo $caso['titulo']; ?></h3>
                                <p><strong>Estado:</strong> <?php echo $caso['estado']; ?></p>
                                <p><strong>Descrição:</strong> <?php echo $caso['descricao']; ?></p>
                                <?php if (!empty($caso['data_fechamento'])): ?>
                                    <p><strong>Data de Fechamento:</strong> <?php echo $caso['data_fechamento']; ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Nenhum caso jurídico registrado para este cliente.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

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
   
