<?php
session_start();
include 'basedados.h';

if (!isset($_SESSION['id_utilizador'])) {
    echo "<script>alert('Sessão expirada. Faça login novamente.'); window.location.href = 'login.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área de Suporte</title>
    <link rel="stylesheet" href="menu_colaborador.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>

    <header>
        <div class="header-container">
            <a href="menu_colaborador.php">
                <img src="logo.png" alt="Logótipo" class="logo">
            </a>
        </div>
        <div class="header-container2">
            <a href="menu_colaborador.php">
                <button class="menu-button">VOLTAR</button>
            </a>
        </div>
    </header>

    <div class="main-content">
        <h1>Bem-vindo à Área de Suporte</h1>
        <p>Escolha uma das opções abaixo para obter ajuda:</p>

        <div class="support-options">
            <a href="faq.php">
                <button class="menu-button2">FAQ</button>
            </a>
            <a href="contato_suporte.php">
                <button class="menu-button2">Contacto</button>
            </a>
            <a href="abrir_ticket.php">
                <button class="menu-button2">Abrir Pedido</button>
            </a>
        </div>
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