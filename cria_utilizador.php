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
      <form action="cria_utilizador.php" method="POST" class="form-box">
          <h2>Criar Utilizador</h2>
          <div class="form-group">
              <label for="username"><i class="fa fa-user"></i> Nome de Utilizador</label>
              <input type="text" id="username" name="username" placeholder="Insira o nome de utilizador" required>
          </div>

          <div class="form-group">
              <label for="mail"><i class="fa fa-envelope"></i> E-mail</label>
              <input type="email" id="mail" name="mail" placeholder="Insira o e-mail" required>
          </div>

          <div class="form-group">
              <label for="password"><i class="fa fa-lock"></i> Password</label>
              <input type="password" id="password" name="password" placeholder="Insira a password" required>
          </div>

          <button type="submit" class="btn-submit">Registar</button>
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

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #5271ff; /* Fundo azul */
            margin: 0;
            padding: 0;
        }

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
        }

        .form-box {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .form-box h2 {
            font-size: 1.8rem;
            color: #004080;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            font-size: 1rem;
            color: #333;
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-group input:focus {
            border-color: #004080;
            outline: none;
        }

        .btn-submit {
            background-color: #004080;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #003366;
        }
    </style>

</body>

</html>