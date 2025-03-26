<?php
    
    include 'basedados.h';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $username = $_POST['nome_utilizador'];
      $email = $_POST['mail'];
      
      // Sanitiza os dados
      $username = str_replace(["'", '"', ";", "--"], "", $username);
      $email = filter_var($email, FILTER_SANITIZE_EMAIL);
      
      // Insere o novo utilizador com a senha vazia e tipo "colaborador"
      $sql = "INSERT INTO utilizador (nome_utilizador, mail, password, id_tipo) 
              VALUES ('$username', '$email', '', 'Colaborador')";
      
      if (mysqli_query($conn, $sql)) {
          // Cria o link para definir a senha (ajuste a URL conforme o seu domínio)
          $setPasswordLink = "http://seudominio.com/set_password.php?user=" . urlencode($username) .
                             "&email=" . urlencode($email) .
                             "&ts=" . $ts .
                             "&hash=" . $hash;
          $subject = "Defina sua senha";
          $message = "Olá, $username,\n\nPor favor, defina sua senha clicando no link abaixo (válido por 1 hora):\n$setPasswordLink";
          $headers = "From: no-reply@seudominio.com";
          
          if (mail($email, $subject, $message, $headers)) {
              echo "Um e-mail foi enviado para $email com as instruções para definir sua senha.";
          } else {
              echo "Erro ao enviar o e-mail.";
          }
      } else {
          echo "Erro ao criar o utilizador: " . mysqli_error($conn);
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
            <a href="pagina-inicial.php">
                <img src="logo.png" alt="Logotipo" class="logo">
            </a>
        </div>
        <div class="header-container2">
            <a href="login.php">
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

    <footer>
      <div class="footer-images">
        <a href="https://maps.app.goo.gl/UQYLoEsTwdgCKoft9" target="_blank">
          <img src="location.png" alt="Imagem Localização">
        </a>

        <a href="https://moodle2425.ipcb.pt/" target="_blank">
          <img src="phone.png" alt="Imagem Telefone">
        </a>

        <a href="https://moodle2425.ipcb.pt/" target="_blank">
          <img src="mail.png" alt="Imagem Mail">
        </a>  
      </div> 
      <p class="copyright">© 2025 Todos os direitos reservados.</p> 
    </footer>

</body>

</html>