<?php

session_start();
include 'basedados.h';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Por favor, faça login primeiro.'); window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Sanitiza as entradas
    $current_password = str_replace(["'", '"', ";", "--"], "", $current_password);
    $new_password = str_replace(["'", '"', ";", "--"], "", $new_password);
    $confirm_password = str_replace(["'", '"', ";", "--"], "", $confirm_password);

    if ($new_password !== $confirm_password) {
        echo "<script>alert('As passwords não coincidem.'); window.location.href='muda_password_admin.php';</script>";
        exit();
    } else {
        $current_password_hashed = md5($current_password);

        $sql = "SELECT password FROM utilizador WHERE id_utilizador = '$user_id'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $stored_password = $row['password'];

            if ($current_password_hashed === $stored_password) {
                $new_password_hashed = md5($new_password);

                $sql_update = "UPDATE utilizador SET password = '$new_password_hashed' WHERE id_utilizador = '$user_id'";

                if (mysqli_query($conn, $sql_update)) {
                    echo "<script>alert('Senha alterada com sucesso.'); window.location.href='menu_admin.php';</script>";
                    exit();
                } else {
                    echo "<script>alert('Erro ao alterar a senha.'); window.location.href='muda_password_admin.php';</script>";
                    exit();
                }
            } else {
                echo "<script>alert('A senha atual está incorreta.'); window.location.href='muda_password_admin.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Erro ao verificar a senha atual.'); window.location.href='muda_password_admin.php';</script>";
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

            <a href="edita_perfil_admin.php">
              <button class="menu-button">EDITAR PERFIL</button>
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
        <div class="login-container">
            <form action="muda_password_admin.php" method="POST" class="form-box">
                <h2>Mudar Password</h2>
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

                <button type="submit" class="btn-submit">Alterar Senha</button>
            </form>
        </div>
    </div>

<footer>
  <div class="footer-content">
    <div class="footer-text">MUDAR PASSWORD</div>
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

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #5271ff; /* Fundo azul */
            margin: 0;
            padding: 0;
        }

        .main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
        }

        .form-box h2 {
            font-size: 1.8rem;
            color: #004080;
            margin-bottom: 20px;
        }

        .form-group {
            position: relative;
            margin-bottom: 20px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 10px 10px 40px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-group input:focus {
            border-color: #004080;
            outline: none;
        }

        .form-group .input-icon {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            font-size: 1.2rem;
            color: #004080;
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