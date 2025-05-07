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

// Verificar se um ID de cliente foi fornecido
if (isset($_GET['id'])) {
    $id_cliente = intval($_GET['id']);
    
    // Buscar informações do cliente
    $sql = "SELECT * FROM cliente WHERE id_cliente = $id_cliente";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $cliente_data = mysqli_fetch_assoc($result);
    } else {
        echo "<script>alert('Cliente não encontrado.'); window.location.href = 'consultar_cliente.php';</script>";
        exit;
    }
    
    // Buscar métodos de pagamento disponíveis
    $sql_pagamentos = "SELECT * FROM metodopagamento";
    $result_pagamentos = mysqli_query($conn, $sql_pagamentos);
    
    if ($result_pagamentos && mysqli_num_rows($result_pagamentos) > 0) {
        $metodos_pagamento = mysqli_fetch_all($result_pagamentos, MYSQLI_ASSOC);
    }
} else {
    echo "<script>alert('ID do cliente não fornecido.'); window.location.href = 'consultar_cliente.php';</script>";
    exit;
}

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            echo "<script>alert('Cliente atualizado com sucesso!'); window.location.href = 'consultar_cliente.php?id=$id_cliente';</script>";
            exit;
        } else {
            $mensagem = "Erro ao atualizar cliente: " . mysqli_error($conn);
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
            <a href="menu_colaborador.php">
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
            
            <!-- Formulário de edição -->
            <form action="editar_cliente.php?id=<?php echo $id_cliente; ?>" method="POST">
                <div class="form-grid">
                    <!-- Informações pessoais -->
                    <div class="form-group">
                        <label for="nome">Nome *</label>
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($cliente_data['nome']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nascimento">Data de Nascimento *</label>
                        <input type="date" id="nascimento" name="nascimento" value="<?php echo htmlspecialchars($cliente_data['dataNasci']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nif">NIF *</label>
                        <input type="text" id="nif" name="nif" value="<?php echo htmlspecialchars($cliente_data['nif']); ?>" maxlength="9" required>
                    </div>
                    
                    <!-- Informações de contato -->
                    <div class="form-group">
                        <label for="contacto1">Contacto Principal *</label>
                        <input type="text" id="contacto1" name="contacto1" value="<?php echo htmlspecialchars($cliente_data['contacto1']); ?>" maxlength="9" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="contacto2">Contacto Secundário</label>
                        <input type="text" id="contacto2" name="contacto2" value="<?php echo htmlspecialchars($cliente_data['contacto2']); ?>" maxlength="9">
                    </div>
                    
                    <!-- Método de pagamento -->
                    <div class="form-group">
                        <label for="pagamento">Método de Pagamento *</label>
                        <select id="pagamento" name="pagamento" required>
                            <option value="">Selecione um método</option>
                            <?php 
                            for ($i = 0; $i < count($metodos_pagamento); $i++) {
                                $selected = ($cliente_data['pagamento'] == $metodos_pagamento[$i]['id_metodo']) ? 'selected' : '';
                                echo "<option value='" . $metodos_pagamento[$i]['id_metodo'] . "' $selected>" . 
                                     htmlspecialchars($metodos_pagamento[$i]['metodo']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
                <!-- Endereços -->
                <div class="form-group">
                    <label for="morada">Morada *</label>
                    <input type="text" id="morada" name="morada" value="<?php echo htmlspecialchars($cliente_data['morada']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="endereco_faturacao">Endereço de Faturação *</label>
                    <input type="text" id="endereco_faturacao" name="endereco_faturacao" 
                           value="<?php echo htmlspecialchars($cliente_data['endereco_faturacao']); ?>" required>
                </div>
                
                <!-- Botões de ação -->
                <div class="form-buttons">
                    <button type="submit" class="submit-button">Salvar Alterações</button>
                    <a href="consultar_cliente.php?id=<?php echo $id_cliente; ?>" class="cancel-button">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Rodapé -->
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

