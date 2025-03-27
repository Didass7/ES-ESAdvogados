<?php
    
    session_start();
    include 'basedados.h';
    // ID do utilizador logado
    $user_id = $_SESSION['user_id'];

    // Processa o formulário de edição de perfil
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $new_username = $_POST['new_username'];
        $new_email = $_POST['new_email'];

        // Sanitiza as entradas
        $new_username = str_replace(["'", '"', ";", "--"], "", $new_username);
        $new_email = str_replace(["'", '"', ";", "--"], "", $new_email);

        // Verifica se o nome de utilizador e o e-mail são diferentes do atual antes de atualizar
        $sql = "SELECT nomeutilizador, email FROM utilizador WHERE id_utilizador = '$user_id'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $current_username = $row['nomeutilizador'];
            $current_email = $row['email'];

            // Verifica se o nome de utilizador ou e-mail foram alterados
            if ($new_username !== $current_username || $new_email !== $current_email) {
                // Atualiza o nome de utilizador e o e-mail no banco de dados
                $sql_update = "UPDATE utilizador SET nomeutilizador = '$new_username', email = '$new_email' WHERE id_utilizador = '$user_id'";

                if (mysqli_query($conn, $sql_update)) {
                    // Se a atualização for bem-sucedida, redireciona para o menu do colaborador
                    header("Location: menu_colaborador.php?success=Perfil+alterado+com+sucesso.");
                    exit();
                } else {
                    // Redireciona com erro de atualização
                    header("Location: edita_perfil_colaborador.php?error=Erro+ao+alterar+perfil.");
                    exit();
                }
            } else {
                // Caso o nome de utilizador e o e-mail sejam os mesmos, redireciona sem alteração
                header("Location: edita_perfil_colaborador.php?error=Nenhuma+alteração+feita.");
                exit();
            }
        } else {
            // Se não encontrar o utilizador
            header("Location: edita_perfil_colaborador.php?error=Erro+ao+recuperar+informações+do+perfil.");
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

            <a href="muda_password_colaborador.php">
              <button class="menu-button">MUDAR PASSWORD</button>
            </a>

            <a href="logout.php">
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
          <form action="edita_perfil_colaborador.php" method="POST">
              <!-- Campo para o novo nome de utilizador -->
              <div class="form-group">
                  <i class="fa fa-user input-icon"></i>
                  <input type="text" id="new_username" name="new_username" placeholder="Novo Nome de Utilizador" required>
              </div>

              <!-- Campo para o novo e-mail -->
              <div class="form-group">
                  <i class="fa fa-envelope input-icon"></i>
                  <input type="email" id="new_email" name="new_email" placeholder="Novo E-mail" required>
              </div>

              <!-- Botão para submeter o formulário -->
              <button type="submit">Alterar Perfil</button>
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