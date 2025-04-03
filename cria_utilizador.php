<?php
include 'basedados.h';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['mail'];
    $password = $_POST['password'];

    // Sanitiza os dados
    $username = str_replace(["'", '"', ";", "--"], "", $username);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $password = str_replace(["'", '"', ";", "--"], "", $password);

    // Verifica se o e-mail é válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Exibe um alerta e redireciona de volta para a página de registo
        echo "<script>alert('E-mail inválido. Por favor, insira um e-mail válido.'); window.location.href='cria_utilizador.php';</script>";
        exit();
    }

    // Verifica se o utilizador já existe
    $checkUser = "SELECT * FROM utilizador WHERE mail = '$email' OR nomeutilizador = '$username'";
    $result = mysqli_query($conn, $checkUser);

    if (mysqli_num_rows($result) > 0) {
        // Exibe um alerta e redireciona de volta para a página de registo
        echo "<script>alert('Utilizador já existe. Tente com outro nome de utilizador ou e-mail.'); window.location.href='cria_utilizador.php';</script>";
        exit();
    }

    // Insere o novo utilizador com o id_tipo predefinido (2 = Colaborador)
    $sql = "INSERT INTO utilizador (nomeutilizador, mail, password, id_tipo) 
            VALUES ('$username', '$email', '$password', 2)";

    if (mysqli_query($conn, $sql)) {
        // Exibe um alerta de sucesso e redireciona para o menu do administrador
        echo "<script>alert('Utilizador criado com sucesso!'); window.location.href='menu_admin.php';</script>";
        exit();
    } else {
        // Exibe um alerta e redireciona de volta para a página de registo
        echo "<script>alert('Erro ao criar o utilizador. Tente novamente.'); window.location.href='cria_utilizador.php';</script>";
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
    <title>Criar Utilizador</title>
    <link rel="stylesheet" href="casos_admin.css">
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

    <div class="login-container">
      <form action="cria_utilizador.php" method="POST">
          <div class="form-group">
              <div class="input-icon">
                  <i class="fa fa-user" aria-hidden="true"></i>
                  <input type="text" id="username" name="username" placeholder="Insira o nome de utilizador" required>
              </div>
          </div>

          <div class="form-group">
              <i class="fa fa-envelope input-icon"></i>
              <input type="email" id="mail" name="mail" placeholder="Insira o e-mail" required>
          </div>

          <div class="form-group">
              <i class="fa fa-lock input-icon"></i>
              <input type="password" id="password" name="password" placeholder="Insira a password" required>
          </div>

          <button type="submit">Registar</button>
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

</html>