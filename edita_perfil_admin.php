<?php
session_start();
include 'basedados.h';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Verificar se o usuário é um administrador (id_tipo = 1)
$sql_check_admin = "SELECT id_tipo FROM utilizador WHERE id_utilizador = '$user_id'";
$result_admin = mysqli_query($conn, $sql_check_admin);

if (!$result_admin || mysqli_num_rows($result_admin) == 0) {
    echo "<script>alert('Erro ao verificar permissões.'); window.location.href='login.php';</script>";
    exit();
}

$user_role = mysqli_fetch_assoc($result_admin)['id_tipo'];
if ($user_role != 1) { // Se não for administrador (id_tipo = 1)
    echo "<script>alert('Acesso não autorizado.'); window.location.href='login.php';</script>";
    exit();
}

// Verificar se foi passado um ID de usuário para edição
$edit_user_id = isset($_GET['id']) ? $_GET['id'] : $user_id;
$is_editing_other = ($edit_user_id != $user_id);

// Se estiver editando outro usuário, verificar se é um colaborador
if ($is_editing_other) {
    $sql_check_user = "SELECT id_tipo FROM utilizador WHERE id_utilizador = '$edit_user_id'";
    $result_user_check = mysqli_query($conn, $sql_check_user);
    
    if (!$result_user_check || mysqli_num_rows($result_user_check) == 0) {
        echo "<script>alert('Usuário não encontrado.'); window.location.href='procura_colaborador.php';</script>";
        exit();
    }
    
    $edit_user_role = mysqli_fetch_assoc($result_user_check)['id_tipo'];
    
    // Se o usuário a ser editado for um administrador (id_tipo = 1) e não for o próprio usuário logado
    if ($edit_user_role == 1 && $edit_user_id != $user_id) {
        echo "<script>alert('Não é permitido editar o perfil de outros administradores.'); window.location.href='procura_colaborador.php';</script>";
        exit();
    }
    
    // Define se está editando um colaborador
    $is_editing_collaborator = ($edit_user_role == 2);
} else {
    $is_editing_collaborator = false;
}

// Verificar se há uma mensagem de sucesso na sessão
$success_message = "";
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    // Limpar a mensagem da sessão para que não apareça novamente ao atualizar
    unset($_SESSION['success_message']);
}

// Buscar informações do usuário a ser editado
$sql_user = "SELECT nomeutilizador, mail FROM utilizador WHERE id_utilizador = '$edit_user_id'";
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
        $sql_update = "UPDATE utilizador SET " . implode(", ", $updates) . " WHERE id_utilizador = '$edit_user_id'";
        if (mysqli_query($conn, $sql_update)) {
            if ($is_editing_collaborator) {
                $_SESSION['success_message'] = "Perfil do colaborador atualizado com sucesso!";
            } else {
                $_SESSION['success_message'] = "Seu perfil foi atualizado com sucesso!";
            }
        } else {
            $_SESSION['success_message'] = "Erro ao atualizar o perfil: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['success_message'] = "Nenhuma alteração foi feita.";
    }
    
    // Redirecionar para a mesma página para evitar reenvio do formulário ao atualizar
    header("Location: edita_perfil_admin.php" . ($is_editing_collaborator ? "?id=$edit_user_id" : ""));
    exit();
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_editing_collaborator ? 'Editar Perfil do Colaborador' : 'Editar Perfil'; ?></title>
    <link rel="stylesheet" href="casos_gerais_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
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

            <a href="logout.php">
              <button class="menu-button">LOGOUT</button>
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

    <div class="main-content">
        <div class="profile-container">
            <div class="profile-icon">
                <i class="fas fa-user-circle"></i>
            </div>
            
            <?php if ($is_editing_collaborator): ?>
                <div class="editing-collaborator">
                    <h3>Editando perfil do colaborador</h3>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo "edita_perfil_admin.php" . ($is_editing_collaborator ? "?id=$edit_user_id" : ""); ?>" method="POST">
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
