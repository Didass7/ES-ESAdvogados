<?php

session_start();

include 'basedados.h';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Evita SQL Injection manualmente
    $username = str_replace(["'", '"', ";", "--"], "", $username);
    $password = str_replace(["'", '"', ";", "--"], "", $password);

    $sql = "SELECT * FROM utilizador WHERE nomeutilizador = '$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Valida a password manualmente
        if ($password === $row['password']) {
            $_SESSION['user'] = $username;
            $_SESSION['user_id'] = $row['id_utilizador']; // Adiciona o ID do utilizador na sessão

            // Redireciona com base no tipo de utilizador
            if (isset($row['id_tipo']) && intval($row['id_tipo']) === 1) {
                header("Location: menu_admin.php");
                exit;
            } else {
                header("Location: menu_colaborador.php");
                exit;
            }
        } else {
            // Credenciais inválidas: redireciona para a página de login
            header("Location: login.php?error=invalid_credentials");
            exit;
        }
    } else {
        // Utilizador não encontrado: redireciona para a página de login
        header("Location: login.php?error=user_not_found");
        exit;
    }
}

mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
    </header>

    <div class="login-container">
        <form action="login.php" method="POST">
            <div class="email">
                <div class="input-container">
                    <i class="fa fa-user" aria-hidden="true"></i>
                    <input type="text" id="username" name="username" placeholder="Insira o nome de utilizador" required>
                </div>
            </div>

            <div class="password">
                <div class="input-container">
                    <i class="fa fa-lock" aria-hidden="true"></i>
                    <input type="password" id="password" name="password" placeholder="Insira a password" required>
                </div>
            </div>

            <button type="submit">Iniciar Sessão</button>
        </form>
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