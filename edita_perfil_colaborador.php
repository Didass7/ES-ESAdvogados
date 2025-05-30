<?php
session_start();
include 'basedados.h';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id']) && isset($_SESSION['id_utilizador'])) {
    // Se user_id não existe mas id_utilizador existe, use id_utilizador
    $_SESSION['user_id'] = $_SESSION['id_utilizador'];
}

// Verificar novamente após a correção
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Verificar se o usuário é um colaborador (id_tipo = 2)
$sql_check_role = "SELECT id_tipo FROM utilizador WHERE id_utilizador = '$user_id'";
$result_role = mysqli_query($conn, $sql_check_role);

if (!$result_role || mysqli_num_rows($result_role) == 0) {
    echo "<script>alert('Erro ao verificar permissões.'); window.location.href='login.php';</script>";
    exit();
}

$user_role = mysqli_fetch_assoc($result_role)['id_tipo'];
if ($user_role != 2) { // Se não for colaborador (id_tipo = 2)
    echo "<script>alert('Acesso não autorizado.'); window.location.href='login.php';</script>";
    exit();
}

// Verificar se está tentando editar outro usuário (o que não é permitido para colaboradores)
if (isset($_GET['id']) && $_GET['id'] != $user_id) {
    echo "<script>alert('Colaboradores só podem editar seu próprio perfil.'); window.location.href='menu_colaborador.php';</script>";
    exit();
}

// Verificar se há uma mensagem de sucesso na sessão
$success_message = "";
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    // Limpar a mensagem da sessão para que não apareça novamente ao atualizar
    unset($_SESSION['success_message']);
}

// Buscar informações atuais do usuário
$sql_user = "SELECT nomeutilizador, mail FROM utilizador WHERE id_utilizador = '$user_id'";
$result_user = mysqli_query($conn, $sql_user);

if (!$result_user) {
    echo "<script>alert('Erro ao buscar informações do usuário: " . mysqli_error($conn) . "');</script>";
} else {
    $user_data = mysqli_fetch_assoc($result_user);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = trim($_POST['new_username']);
    $new_email = trim($_POST['new_email']);

    // Remove caracteres perigosos (proteção básica)
    $new_username = str_replace(["'", '"', ";", "--"], "", $new_username);
    $new_email = str_replace(["'", '"', ";", "--"], "", $new_email);

    // Prepara a query de atualização
    $updates = [];
    if (!empty($new_username)) {
        $updates[] = "nomeutilizador = '$new_username'";
    }
    if (!empty($new_email)) {
        $updates[] = "mail = '$new_email'";
    }
    
    if (!empty($updates)) {
        $sql_update = "UPDATE utilizador SET " . implode(", ", $updates) . " WHERE id_utilizador = '$user_id'";
        
        if (mysqli_query($conn, $sql_update)) {
            // Verifica se alguma linha foi afetada
            if (mysqli_affected_rows($conn) > 0) {
                $_SESSION['success_message'] = "Perfil atualizado com sucesso!";
            } else {
                // Se nenhuma linha foi afetada, pode ser que os dados sejam idênticos
                $_SESSION['success_message'] = "Nenhuma alteração foi necessária (dados idênticos).";
            }
        } else {
            $_SESSION['success_message'] = "Erro ao atualizar o perfil: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['success_message'] = "Nenhuma alteração foi feita. Preencha pelo menos um campo.";
    }
    
    // Redirecionar para a mesma página para evitar reenvio do formulário ao atualizar
    header("Location: edita_perfil_colaborador.php");
    exit();
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="casos_gerais_colaborador.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #5271ff;
            margin: 0;
            padding: 0;
        }
        
        header {
            background-color: #5271ff;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-container {
            display: flex;
            align-items: center;
        }
        
        .header-container2 {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .header-container2 a,
        .header-container a {
            text-decoration: none;
        }

        .menu-button {
            background-color: white;
            color: #5271ff;
            border: none;
            border-radius: 50px;
            padding: 8px 15px;
            font-size: 0.9rem;
            cursor: pointer;
            font-weight: bold;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }
        
        .main-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
            width: 100%;
            padding: 20px;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        
        .profile-container {
            background-color: white;
            border-radius: 20px;
            padding: 30px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .profile-icon {
            font-size: 3rem;
            color: #5271ff;
            margin-bottom: 20px;
        }
        
        .form-title {
            color: #5271ff;
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .profile-input-container {
            background-color: #5271ff;
            border-radius: 20px;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .profile-input {
            width: 90%;
            padding: 8px;
            border: none;
            background: transparent;
            color: white;
            text-align: center;
        }
        
        .profile-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .submit-button {
            background-color: #5271ff;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        
        footer {
            background-color: white;
            padding: 15px 0;
            text-align: center;
            margin-top: auto;
        }
        
        .footer-images {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 10px;
        }
        
        .footer-images img {
            width: 30px;
            height: 30px;
        }
        
        .copyright {
            color: #5271ff;
            font-size: 0.8rem;
            margin: 0;
        }
    </style>
</head>

<body>
    <header>
        <div class="header-container">
            <a href="pagina-inicial.php">
                <img src="logo.png" alt="Logotipo" class="logo" style="width: 40px; height: 40px;">
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
              <img src="person.png" alt="Ícone" style="width: 20px; height: 20px; vertical-align: middle;">
            </button>
            
            <a href="menu_colaborador.php">
              <img src="seta.png" alt="Ícone" style="width: 30px; height: 30px; vertical-align: middle;">
            </a>
        </div>
    </header>

    <div class="main-content">
        <div class="profile-container">
            <div class="profile-icon">
                <i class="fas fa-user-circle"></i>
            </div>
            
            <form action="edita_perfil_colaborador.php" method="POST">
                <div class="form-title">NOME DE UTILIZADOR <i class="fas fa-info-circle"></i></div>
                <div class="profile-input-container">
                    <input type="text" name="new_username" class="profile-input" placeholder="INSIRA O NOVO NOME DE UTILIZADOR" value="<?php echo isset($user_data['nomeutilizador']) ? $user_data['nomeutilizador'] : ''; ?>">
                </div>
                
                <div class="form-title">E-MAIL <i class="fas fa-info-circle"></i></div>
                <div class="profile-input-container">
                    <input type="email" name="new_email" class="profile-input" placeholder="INSIRA O NOVO E-MAIL" value="<?php echo isset($user_data['mail']) ? $user_data['mail'] : ''; ?>">
                </div>
                
                <button type="submit" class="submit-button">SUBMETER</button>
            </form>
        </div>
    </div>

<footer>
  <div class="footer-content">
    <div class="footer-text">EDITAR PERFIL</div>
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

    <?php if (!empty($success_message)): ?>
    <script>
        // Exibe o alerta após o carregamento da página
        window.onload = function() {
            alert("<?php echo $success_message; ?>");
        }
    </script>
    <?php endif; ?>
</body>
</html>
