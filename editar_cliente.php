<?php
session_start();
include 'basedados.h';

// Verificação de autenticação
if (!isset($_SESSION['id_utilizador'])) {
    echo "<script>alert('Sessão expirada. Faça login novamente.'); window.location.href = 'login.php';</script>";
    exit;
}

// Inicialização de variáveis
$user_id = $_SESSION['id_utilizador'];
$mensagem = "";
$cliente_data = null;
$metodos_pagamento = [];
$id_cliente = 0;
$clientes = [];

// Verificar se um ID de cliente foi fornecido
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_cliente = intval($_GET['id']);
    
    // Buscar informações do cliente
    $sql = "SELECT * FROM cliente WHERE id_cliente = $id_cliente";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $cliente_data = mysqli_fetch_assoc($result);
        
        // Buscar métodos de pagamento disponíveis
        $sql_pagamentos = "SELECT * FROM metodopagamento";
        $result_pagamentos = mysqli_query($conn, $sql_pagamentos);
        
        if ($result_pagamentos && mysqli_num_rows($result_pagamentos) > 0) {
            $metodos_pagamento = mysqli_fetch_all($result_pagamentos, MYSQLI_ASSOC);
        }
    } else {
        echo "<script>alert('Cliente não encontrado.'); window.location.href = 'gerir_cliente.php';</script>";
        exit;
    }
} else {
    // Se não foi fornecido um ID, buscar todos os clientes PARA O COLABORADOR para o dropdown
    $id_colaborador = $_SESSION['id_utilizador'];
    $sql_clientes = "SELECT id_cliente, nome, nif FROM cliente WHERE id_colaborador = $id_colaborador ORDER BY nome";
    $result_clientes = mysqli_query($conn, $sql_clientes);
    
    if ($result_clientes && mysqli_num_rows($result_clientes) > 0) {
        while ($row = mysqli_fetch_assoc($result_clientes)) {
            $clientes[] = $row;
        }
    } else {
        $mensagem = "Nenhum cliente encontrado no sistema.";
    }
    
    // Buscar métodos de pagamento disponíveis (para o caso de seleção de cliente)
    $sql_pagamentos = "SELECT * FROM metodopagamento";
    $result_pagamentos = mysqli_query($conn, $sql_pagamentos);
    
    if ($result_pagamentos && mysqli_num_rows($result_pagamentos) > 0) {
        $metodos_pagamento = mysqli_fetch_all($result_pagamentos, MYSQLI_ASSOC);
    }
}

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['selecionar_cliente'])) {
        // Processar a seleção de cliente
        $id_cliente_selecionado = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : 0;
        
        if ($id_cliente_selecionado > 0) {
            // Redirecionar para a mesma página com o ID do cliente selecionado
            header("Location: editar_cliente.php?id=$id_cliente_selecionado");
            exit;
        } else {
            $mensagem = "Por favor, selecione um cliente.";
        }
    } else {
        // Processar a atualização do cliente
        // Obter e sanitizar dados do formulário
        $nome = isset($_POST['nome']) ? mysqli_real_escape_string($conn, $_POST['nome']) : '';
        $dataNasc = isset($_POST['nascimento']) ? $_POST['nascimento'] : '';
        $nif = isset($_POST['nif']) ? mysqli_real_escape_string($conn, $_POST['nif']) : '';
        $contacto1 = isset($_POST['contacto1']) ? mysqli_real_escape_string($conn, $_POST['contacto1']) : '';
        $contacto2 = isset($_POST['contacto2']) ? mysqli_real_escape_string($conn, $_POST['contacto2']) : '';
        $morada = isset($_POST['morada']) ? mysqli_real_escape_string($conn, $_POST['morada']) : '';
        $endereco_faturacao = isset($_POST['endereco_faturacao']) ? mysqli_real_escape_string($conn, $_POST['endereco_faturacao']) : '';
        $pagamento = isset($_POST['pagamento']) ? intval($_POST['pagamento']) : 0;
        
        // Validação de campos obrigatórios
        $campos_obrigatorios = [
            'nome' => $nome,
            'data de nascimento' => $dataNasc,
            'NIF' => $nif,
            'contacto principal' => $contacto1,
            'morada' => $morada,
            'endereço de faturação' => $endereco_faturacao,
            'método de pagamento' => $pagamento
        ];
        
        $campos_vazios = [];
        foreach ($campos_obrigatorios as $campo => $valor) {
            if (empty($valor)) {
                $campos_vazios[] = $campo;
            }
        }
        
        if (!empty($campos_vazios)) {
            $mensagem = "Por favor, preencha os seguintes campos obrigatórios: " . implode(', ', $campos_vazios) . ".";
        } else {
            // Verificar se o NIF já existe em outro cliente
            $sql_check_nif = "SELECT id_cliente FROM cliente WHERE nif = '$nif' AND id_cliente != $id_cliente";
            $result_check_nif = mysqli_query($conn, $sql_check_nif);
            
            if ($result_check_nif && mysqli_num_rows($result_check_nif) > 0) {
                $mensagem = "Erro: O NIF '$nif' já está registado para outro cliente.";
            } else {
                // Atualizar os dados do cliente
                $sql_update = "UPDATE cliente SET 
                              nome = '$nome', 
                              dataNasci = '$dataNasc', 
                              nif = '$nif', 
                              contacto1 = '$contacto1', 
                              contacto2 = '$contacto2', 
                              morada = '$morada', 
                              endereco_faturacao = '$endereco_faturacao', 
                              pagamento = $pagamento 
                              WHERE id_cliente = $id_cliente";
                
                if (mysqli_query($conn, $sql_update)) {
                    echo "<script>alert('Cliente atualizado com sucesso!'); window.location.href = 'gerir_cliente.php?id=$id_cliente';</script>";
                    exit;
                } else {
                    $mensagem = "Erro ao atualizar cliente: " . mysqli_error($conn);
                }
            }
        }
    }
}

// Fechar conexão com o banco de dados
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
    <link rel="stylesheet" href="menu_colaborador.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <style>
        /* Estilos para o formulário */
        .main-content {
            padding: 20px;
            max-width: 900px;
            margin: 0 auto;
            padding-top: 120px;
            padding-bottom: 80px;
        }
        
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .form-title {
            color: #5271ff;
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            font-weight: bold;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #5271ff;
        }
        
        .form-group input, 
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .form-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .form-buttons button, 
        .form-buttons a {
            padding: 12px 25px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }
        
        .submit-button {
            background-color: #5271ff;
            color: white;
            border: none;
        }
        
        .cancel-button {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        /* Estilo para o formulário de seleção de cliente */
        .select-client-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }
        
        .select-client-form select {
            width: 100%;
            max-width: 500px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        /* Estilo para quando não há clientes */
        .no-clients-message {
            text-align: center;
            margin: 20px 0;
            color: #5271ff;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Cabeçalho -->
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

    <!-- Conteúdo principal -->
    <div class="main-content">
        <div class="form-container">
            <div class="form-title">Editar Cliente</div>
            
            <!-- Mensagem de erro (se houver) -->
            <?php if (!empty($mensagem)): ?>
                <div class="error-message"><?php echo $mensagem; ?></div>
            <?php endif; ?>
            
            <?php if ($cliente_data === null): ?>
                <!-- Formulário de seleção de cliente -->
                <?php if (!empty($clientes)): ?>
                    <form action="editar_cliente.php" method="POST" class="select-client-form">
                        <div class="form-group" style="width: 100%;">
                            <label for="id_cliente">Selecione um Cliente para Editar</label>
                            <select id="id_cliente" name="id_cliente" required>
                                <option value="">-- Selecione um cliente --</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo $cliente['id_cliente']; ?>">
                                        <?php echo $cliente['nome'] . ' (NIF: ' . $cliente['nif'] . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-buttons">
                            <button type="submit" name="selecionar_cliente" class="submit-button">Selecionar</button>
                            <a href="gerir_cliente.php" class="cancel-button">Cancelar</a>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="no-clients-message">
                        <p>Não há clientes cadastrados no sistema.</p>
                        <div class="form-buttons">
                            <a href="cria_cliente.php" class="submit-button">Criar Cliente</a>
                            <a href="gerir_cliente.php" class="cancel-button">Voltar</a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Formulário de edição -->
                <form action="editar_cliente.php?id=<?php echo $id_cliente; ?>" method="POST">
                    <div class="form-grid">
                        <!-- Informações pessoais -->
                        <div class="form-group">
                            <label for="nome">Nome *</label>
                            <input type="text" id="nome" name="nome" value="<?php echo $cliente_data['nome']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="nascimento">Data de Nascimento *</label>
                            <input type="date" id="nascimento" name="nascimento" value="<?php echo $cliente_data['dataNasci']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="nif">NIF *</label>
                            <input type="text" id="nif" name="nif" value="<?php echo $cliente_data['nif']; ?>" maxlength="9" required>
                        </div>
                        
                        <!-- Informações de contato -->
                        <div class="form-group">
                            <label for="contacto1">Contacto Principal *</label>
                            <input type="text" id="contacto1" name="contacto1" value="<?php echo $cliente_data['contacto1']; ?>" maxlength="9" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="contacto2">Contacto Secundário</label>
                            <input type="text" id="contacto2" name="contacto2" value="<?php echo $cliente_data['contacto2']; ?>" maxlength="9">
                        </div>
                        
                        <!-- Método de pagamento -->
                        <div class="form-group">
                            <label for="pagamento">Método de Pagamento *</label>
                            <select id="pagamento" name="pagamento" required>
                                <option value="">Selecione um método</option>
                                <?php foreach ($metodos_pagamento as $metodo): ?>
                                    <option value="<?php echo $metodo['id_metodo']; ?>" <?php echo ($cliente_data['pagamento'] == $metodo['id_metodo']) ? 'selected' : ''; ?>>
                                        <?php echo $metodo['metodo']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Endereços -->
                    <div class="form-group">
                        <label for="morada">Morada *</label>
                        <input type="text" id="morada" name="morada" value="<?php echo $cliente_data['morada']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="endereco_faturacao">Endereço de Faturação *</label>
                        <input type="text" id="endereco_faturacao" name="endereco_faturacao" 
                               value="<?php echo $cliente_data['endereco_faturacao']; ?>" required>
                    </div>
                    
                    <!-- Botões de ação -->
                    <div class="form-buttons">
                        <button type="submit" class="submit-button">Salvar Alterações</button>
                        <a href="gerir_cliente.php?id=<?php echo $id_cliente; ?>" class="cancel-button">Cancelar</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

<footer>
  <div class="footer-content">
    <div class="footer-text">EDITAR CLIENTE</div>
    <div class="footer-images">
      <a href="https://maps.app.goo.gl/UQYLoEsTwdgCKoft9" target="_blank">
          <img src="location.png" alt="Imagem Localização"/>
        </a>
        <a href="https://moodle2425.ipcb.pt/" target="_blank">
          <img src="phone.png" alt="Imagem Telefone"/>
        </a>
        <a href="https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox" target="_blank">
          <img src="mail.png" alt="Imagem Email"/>
        </a>
    </div>
  </div>
  <div class="copyright-wrapper">
    <span class="copyright">© 2025 Todos os direitos reservados.</span>
  </div>
</footer>
</body>
</html>