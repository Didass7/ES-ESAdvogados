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
        // Verificar se o formulário foi enviado
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
                        // Alterado para usar LIKE para maior flexibilidade
                        $sql .= "nif LIKE '%$termo%'";
                        break;
                    case 'contacto':
                        // Alterado para usar LIKE para maior flexibilidade
                        $sql .= "contacto1 LIKE '%$termo%' OR contacto2 LIKE '%$termo%'";
                        break;
                    default:
                        $sql .= "nome LIKE '%$termo%'";
                }
                
                // Debug - exibir a consulta SQL
                // echo "<script>console.log('SQL: $sql');</script>";
                
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
    <style>
        .main-content {
            padding: 20px;
            max-width: 1100px;
            margin: 0 auto;
            padding-top: 120px;
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
        }
        
        /* Novo estilo para o container de detalhes */
        .details-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        /* Ajuste para as caixas de detalhes ficarem lado a lado */
        .client-details {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            flex: 1;
            max-width: 48%;
        }
        
        .client-details h2 {
            color: #5271ff;
            margin-top: 0;
            border-bottom: 2px solid #5271ff;
            padding-bottom: 8px;
            margin-bottom: 15px;
            font-size: 1.3rem;
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
            color: #5271ff;
            text-align: center;
            margin-bottom: 25px;
            font-size: 1.8rem;
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
    </div>
