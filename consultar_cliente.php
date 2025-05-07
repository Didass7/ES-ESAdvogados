<?php
    session_start();
    include 'basedados.h';

    if (!isset($_SESSION['id_utilizador'])) {
        echo "<script>alert('Sessão expirada. Faça login novamente.'); window.location.href = 'login.php';</script>";
        exit;
    }
    
    $clientes = [];
    $mensagem = "";
    $cliente_detalhes = null;
    $casos = [];
    $metodo_pagamento = "Não especificado";
    $modo_visualizacao = "lista"; // Padrão é mostrar a lista de clientes
    
    // Verificar se estamos no modo de visualização detalhada
    if (isset($_GET['id'])) {
        $modo_visualizacao = "detalhes";
        $id_cliente = intval($_GET['id']);
        
        // Buscar informações do cliente
        $sql = "SELECT * FROM cliente WHERE id_cliente = $id_cliente";
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $cliente_detalhes = mysqli_fetch_assoc($result);
            
            // Buscar casos jurídicos associados a este cliente
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
    
    // Processar a pesquisa se estamos no modo lista
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
                        $sql .= "nif = '$termo'";
                        break;
                    case 'contacto':
                        $sql .= "contacto1 LIKE '%$termo%' OR contacto2 LIKE '%$termo%'";
                        break;
                    default:
                        $sql .= "nome LIKE '%$termo%'";
                }
                
                $result = mysqli_query($conn, $sql);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    $clientes = mysqli_fetch_all($result, MYSQLI_ASSOC);
                } else {
                    $mensagem = "Nenhum cliente encontrado com os critérios especificados.";
                }
            } else {
                $mensagem = "Por favor, insira um termo de pesquisa.";
            }
        }
        
        // Listar todos os clientes se não houver pesquisa
        if (empty($clientes) && empty($mensagem)) {
            $sql = "SELECT * FROM cliente LIMIT 20"; // Limitar a 20 para não sobrecarregar
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
    <style>
        .main-content {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
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
            gap: 10px;
            align-items: center;
        }
        
        .search-container select, 
        .search-container input[type="text"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            flex: 1;
        }
        
        .search-container button {
            background-color: #5271ff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .results-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
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
        }
        
        .results-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .action-buttons a {
            display: inline-block;
            margin-right: 5px;
            padding: 5px 10px;
            background-color: #5271ff;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            font-size: 14px;
        }
        
        .message {
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        /* Estilos para os detalhes do cliente */
        .client-details {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .client-details h2 {
            color: #5271ff;
            margin-top: 0;
            border-bottom: 2px solid #5271ff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .client-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .info-item {
            margin-bottom: 15px;
        }
        
        .info-item label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color: #5271ff;
        }
        
        .info-item p {
            margin: 0;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .cases-section h2 {
            color: #5271ff;
            margin-top: 0;
            border-bottom: 2px solid #5271ff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .cases-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .cases-table th, 
        .cases-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .cases-table th {
            background-color: #5271ff;
            color: white;
        }
        
        .cases-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .detail-buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .detail-buttons a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #5271ff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        
        .no-cases {
            text-align: center;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
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

            <a href="menu_colaborador.php">
              <img src="seta.png" alt="Ícone" style="width: 60px; height: 60px; vertical-align: middle;">
            </a>
        </div>
    </header>

    <div class="main-content">
        <?php if ($modo_visualizacao == "lista"): ?>
            <!-- MODO LISTA: Exibir lista de clientes -->
            <h1 style="text-align: center; color: #5271ff; margin-bottom: 20px;">Consultar Clientes</h1>
            
            <form method="POST" action="" class="search-container">
                <select name="tipo">
                    <option value="nome">Nome</option>
                    <option value="nif">NIF</option>
                    <option value="contacto">Contacto</option>
                </select>
                <input type="text" name="termo" placeholder="Digite o termo de pesquisa...">
                <button type="submit" name="pesquisar">Pesquisar</button>
            </form>
            
            <?php if (!empty($mensagem)): ?>
                <div class="message"><?php echo $mensagem; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($clientes)): ?>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>NIF</th>
                            <th>Contacto</th>
                            <th>Morada</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        for ($i = 0; $i < count($clientes); $i++) {
                            echo "<tr>";
                            echo "<td>" . $clientes[$i]['nome'] . "</td>";
                            echo "<td>" . $clientes[$i]['nif'] . "</td>";
                            echo "<td>" . $clientes[$i]['contacto1'] . "</td>";
                            echo "<td>" . $clientes[$i]['morada'] . "</td>";
                            echo "<td class='action-buttons'>";
                            echo "<a href='consultar_cliente.php?id=" . $clientes[$i]['id_cliente'] . "'>Ver</a>";
                            echo "<a href='editar_cliente.php?id=" . $clientes[$i]['id_cliente'] . "'>Editar</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- MODO DETALHES: Exibir detalhes de um cliente específico -->
            <div class="client-details">
                <h2>Detalhes do Cliente</h2>
                
                <div class="client-info">
                    <div class="info-item">
                        <label>Nome:</label>
                        <p><?php echo $cliente_detalhes['nome']; ?></p>
                    </div>
                    
                    <div class="info-item">
                        <label>Data de Nascimento:</label>
                        <p><?php echo date('d/m/Y', strtotime($cliente_detalhes['dataNasci'])); ?></p>
                    </div>
                    
                    <div class="info-item">
                        <label>NIF:</label>
                        <p><?php echo $cliente_detalhes['nif']; ?></p>
                    </div>
                    
                    <div class="info-item">
                        <label>Contacto Principal:</label>
                        <p><?php echo $cliente_detalhes['contacto1']; ?></p>
                    </div>
                    
                    <div class="info-item">
                        <label>Contacto Secundário:</label>
                        <p><?php echo !empty($cliente_detalhes['contacto2']) ? $cliente_detalhes['contacto2'] : 'Não disponível'; ?></p>
                    </div>
                    
                    <div class="info-item">
                        <label>Morada:</label>
                        <p><?php echo $cliente_detalhes['morada']; ?></p>
                    </div>
                    
                    <div class="info-item">
                        <label>Endereço de Faturação:</label>
                        <p><?php echo $cliente_detalhes['endereco_faturacao']; ?></p>
                    </div>
                    
                    <div class="info-item">
                        <label>Método de Pagamento:</label>
                        <p><?php echo $metodo_pagamento; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Seção de casos jurídicos -->
            <div class="client-details cases-section">
                <h2>Casos Jurídicos</h2>
                
                <?php if (!empty($casos)): ?>
                    <table class="cases-table">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Descrição</th>
                                <th>Estado</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            for ($i = 0; $i < count($casos); $i++) {
                                echo "<tr>";
                                echo "<td>" . $casos[$i]['titulo'] . "</td>";
                                echo "<td>" . substr($casos[$i]['descricao'], 0, 100) . (strlen($casos[$i]['descricao']) > 100 ? '...' : '') . "</td>";
                                echo "<td>" . $casos[$i]['estado'] . "</td>";
                                echo "<td class='action-buttons'>";
                                echo "<a href='ver_caso.php?id=" . $casos[$i]['id'] . "'>Ver</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-cases">
                        <p>Este cliente não possui casos jurídicos registrados.</p>
                    </div>
                <?php endif; ?>
                
                <div class="detail-buttons">
                    <a href="criar_caso.php?id_cliente=<?php echo $cliente_detalhes['id_cliente']; ?>">Criar Novo Caso</a>
                    <a href="consultar_cliente.php">Voltar para Lista</a>
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
