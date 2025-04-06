<?php

    session_abort();
    include 'basedados.h';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = $_POST['nomeUtilizador'];
        $nome = mysqli_real_escape_string($conn, $nome);

        $sql = "SELECT * FROM utilizador WHERE nomeUtilizador = '$nome' AND id_tipo = 2";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $utilizador = mysqli_fetch_assoc($result);
            $nome = urlencode($utilizador['nomeUtilizador']);
            $mail = urlencode($utilizador['mail']);
            header("Location: consulta_colaborador.php?nome=$nome&mail=$mail");
            exit();
        } else {
            echo "<script>alert('Utilizador não encontrado ou não é um colaborador.');</script>";
        }

        mysqli_close($conn);
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Colaborador</title>
    <link rel="stylesheet" href="procura_colaborador.css">
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

    
    <form method="POST" action="">
        <div class="search-container">
            <div class="input-wrapper">
                <span class="icon">🔍</span>
                <input type="text" name="nomeUtilizador" placeholder="Insira o nome do colaborador" required>
            </div>
            <button class="search-button" type="submit">PROCURAR</button>
        </div>
    </form>

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