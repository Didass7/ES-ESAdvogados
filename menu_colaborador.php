<?php
session_start();
include 'basedados.h';

if (!isset($_SESSION['id_utilizador'])) {
    echo "<script>alert('Sessão expirada. Faça login novamente.'); window.location.href = 'login.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Colaborador</title>
    <link rel="stylesheet" href="menu_colaborador.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</head>

<body>

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
        </div>
    </header>

    <div class="main-content">
      <a href="cria_cliente.php">
        <button class="menu-button2">CRIAR CLIENTE</button>
      </a>

      <a href="gerir_cliente.php">
        <button class="menu-button2">GERIR CLIENTE</button>
      </a>

      <a href="regista_agenda.php">
        <button class="menu-button2">REGISTAR AGENDA</button>
      </a>

      <a href="casos_juridicos.php">
        <button class="menu-button2">CASOS JURÍDICOS</button>
      </a>

      <a href="area_suporte.php">
        <button class="menu-button2">ÁREA DE SUPORTE</button>
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