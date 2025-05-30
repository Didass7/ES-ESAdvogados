<?php
include 'basedados.h';

if (isset($_GET['nome']) && isset($_GET['mail'])) {
    $nome = urldecode($_GET['nome']);
    $mail = urldecode($_GET['mail']);

    // Protege contra injeção SQL
    $nome = mysqli_real_escape_string($conn, $nome);
    $mail = mysqli_real_escape_string($conn, $mail);

    // Vai buscar os dados completos do colaborador
    $sql = "SELECT * FROM utilizador WHERE nomeUtilizador = '$nome' AND mail = '$mail' AND id_tipo = 2";
    $result = mysqli_query($conn, $sql);
    $utilizador = mysqli_fetch_assoc($result);
    mysqli_close($conn);
} else {
    $utilizador = null;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Colaborador</title>
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

            <a href="procura_colaborador.php">
              <img src="seta.png" alt="Ícone" style="width: 60px; height: 60px; vertical-align: middle;">
            </a>
        </div>
    </header>

    <div class="container mt-5">
        <div class="info-box">
            <h2 class="text-center">Informações do Colaborador</h2>
            <div class="info-content">
                <p><strong>ID Utilizador:</strong> <?php echo $utilizador['id_utilizador']; ?></p>
                <p><strong>Nome de Utilizador:</strong> <?php echo $utilizador['nomeUtilizador']; ?></p>
                <p><strong>Email:</strong> <?php echo $utilizador['mail']; ?></p>
            </div>
        </div>
    </div>

    <style>
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
            background-color: #f4f4f9;
        }

        .info-box {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .info-box h2 {
            font-size: 1.8rem;
            color: #004080;
            margin-bottom: 20px;
        }

        .info-content p {
            font-size: 1.2rem;
            color: #333;
            margin: 10px 0;
        }

        .info-content p strong {
            color: #004080;
        }
    </style>

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
