<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
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
            <form>
                <div class="form-group">
                    <div class= "input-icon">
                    <i class="fa fa-envelope-o" aria-hidden="true"></i>
                    <input type="text" id="username" name="username" placeholder="Insira o nome de utilizador" required>

                    </div>
                </div>
                
                <div class="form-group">
                    <i class="fa fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" placeholder= "Insira a password" required>
                                
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

        <a href="https://moodle2425.ipcb.pt/" target="_blank">
          <img src="mail.png" alt="Imagem Mail">
        </a>  
      </div>  
      <p class="copyright">© 2025 Todos os direitos reservados.</p>
    </footer>

</body>