<?php
session_start();
include 'basedados.h';

// ID do utilizador logado
$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = trim($_POST['new_username']);
    $password = trim($_POST['password']);

    // Remove caracteres perigosos (proteção básica)
    $new_username = str_replace(["'", '"', ";", "--"], "", $new_username);
    $password = str_replace(["'", '"', ";", "--"], "", $password);

    // Verifica se os campos foram preenchidos
    if (empty($new_username) || empty($password)) {
        header("Location: edita_perfil_admin.php?error=Preencha+todos+os+campos.");
        exit();
    }

    // Verifica se a password está correta
    $sql_check = "SELECT password FROM utilizador WHERE id_utilizador = '$user_id'";
    $result = mysqli_query($conn, $sql_check);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        if ($password === $row['password']) {
            // Atualiza o nome de utilizador no banco de dados
            $sql_update = "UPDATE utilizador SET nomeutilizador = '$new_username' WHERE id_utilizador = '$user_id'";
            if (mysqli_query($conn, $sql_update)) {
                header("Location: menu_admin.php?success=Nome+alterado+com+sucesso.");
                exit();
            } else {
                header("Location: edita_perfil_admin.php?error=Erro+ao+alterar+o+nome.");
                exit();
            }
        } else {
            header("Location: edita_perfil_admin.php?error=Password+incorreta.");
            exit();
        }
    } else {
        header("Location: edita_perfil_admin.php?error=Erro+ao+verificar+a+password.");
        exit();
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="casos_gerais_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</head>

<body>

    <header>
        <div class="header-container">
            <a href="pagina-inicial.php">
                <img src="logo.png" alt="Logotipo" class="logo">
            </a>
        </div>
        <div class="header-container2">
            <a href="muda_password_admin.php">
              <button class="menu-button">MUDAR PASSWORD</button>
            </a>

            <a href="login.php">
              <button class="menu-button">LOGOUT</button>
            </a>

            <button class="menu-button">
              ADMINISTRADOR
              <img src="person.png" alt="Ícone" style="width: 30px; height: 30px; vertical-align: middle;">
            </button>
        </div>
    </header>

    <div class="main-content">
      <div class="login-container">
        <form action="edita_perfil_admin.php" method="POST">
            <!-- Campo para o novo nome de utilizador -->
            <div class="form-group">
                <i class="fa fa-user input-icon"></i>
                <input type="text" id="new_username" name="new_username" placeholder="Novo Nome de Utilizador" required>
            </div>

            <!-- Campo para confirmação da password -->
            <div class="form-group">
                <i class="fa fa-lock input-icon"></i>
                <input type="password" id="password" name="password" placeholder="Digite sua Password" required>
            </div>

            <!-- Botão para submeter o formulário -->
            <button type="submit">Alterar Nome de Utilizador</button>
        </form>
      </div>
    </div>

    <div class = "main-content2">
      <a href="menu_admin.php">
        <img src="seta.png" alt="Ícone" style="width: 60px; height: 60px; vertical-align: middle;">
      </a>
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