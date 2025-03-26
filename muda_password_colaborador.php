<?php

include 'basedados.h';

// Verifica se o utilizador está logado
if (!isset($_SESSION['user_id'])) {
    // Se o utilizador não estiver logado, redireciona para o login
    header("Location: login.php");
    exit();
}

// ID do utilizador logado
$user_id = $_SESSION['user_id'];

// Processa o formulário de mudança de password
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Sanitiza as entradas
    $current_password = str_replace(["'", '"', ";", "--"], "", $current_password);
    $new_password = str_replace(["'", '"', ";", "--"], "", $new_password);
    $confirm_password = str_replace(["'", '"', ";", "--"], "", $confirm_password);

    // Verifica se a nova password corresponde à confirmação
    if ($new_password !== $confirm_password) {
        // Redireciona com mensagem de erro
        header("Location: muda_password_colaborador.php?error=As+passwords+não+coincidem.");
        exit();
    } else {
        // Verifica se a senha atual está correta
        $sql = "SELECT password, id_tipo FROM utilizador WHERE id = '$user_id'";
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $stored_password = $row['password'];
            $user_type = $row['id_tipo']; // Obtém o tipo de utilizador (1 = Admin, 2 = Colaborador)
            
            // Verifica se a senha atual inserida corresponde à senha armazenada
            if ($current_password === $stored_password) {
                // Atualiza a senha no banco de dados
                $sql_update = "UPDATE utilizador SET password = '$new_password' WHERE id = '$user_id'";
                
              if (mysqli_query($conn, $sql_update)) {
                  // Se a senha for alterada com sucesso, redireciona para o menu do colaborador
                  header("Location: menu_colaborador.php?success=Senha+alterada+com+sucesso.");
                  exit();
              } else {
                  // Redireciona com erro de atualização
                  header("Location: muda_password_colaborador.php?error=Erro+ao+alterar+a+senha.");
                  exit();
              }
          } else {
              // Se a senha atual não for correta, redireciona com erro
              header("Location: muda_password_colaborador.php?error=A+senha+atual+está+incorreta.");
              exit();
          }
      } else {
          // Se houver erro na consulta ao banco, redireciona com erro
          header("Location: muda_password_colaborador.php?error=Erro+ao+verificar+a+senha+atual.");
          exit();
      }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mudar Password</title>
    <link rel="stylesheet" href="casos_gerais_colaborador.css">
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

            <a href="edita_perfil_colaborador.php">
              <button class="menu-button">EDITAR PERFIL</button>
            </a>

            <a href="login.php">
              <button class="menu-button">LOGOUT</button>
            </a>

            <button class="menu-button">
              COLABORADOR
              <img src="person.png" alt="Ícone" style="width: 30px; height: 30px; vertical-align: middle;">
            </button>
        </div>
    </header>

    <div class="main-content">
        <div class="login-container">
            <form action="muda_password_colaborador.php" method="POST">
                <div class="form-group">
                    <i class="fa fa-lock input-icon"></i>
                    <input type="password" id="current_password" name="current_password" placeholder="Senha Atual" required>
                </div>

                <div class="form-group">
                    <i class="fa fa-lock input-icon"></i>
                    <input type="password" id="new_password" name="new_password" placeholder="Nova Senha" required>
                </div>

                <div class="form-group">
                    <i class="fa fa-lock input-icon"></i>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmar Nova Senha" required>
                </div>

                <button type="submit">Alterar Senha</button>
            </form>
        </div>
    </div>

    <div class = "main-content2">
      <a href="menu_colaborador.php">
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