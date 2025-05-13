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
    <style>
        .support-container {
            background-color: white;
            border-radius: 20px;
            padding: 40px;
            width: 90%;
            max-width: 800px;
            margin: 100px auto 80px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .support-container h1 {
            color: #5271ff;
            font-size: 2.2rem;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .support-container p {
            color: #555;
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        .support-options {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
            margin-top: 30px;
        }

        .support-option {
            flex: 1;
            min-width: 200px;
            max-width: 250px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .support-option:hover {
            transform: translateY(-5px);
        }

        .support-icon {
            font-size: 3rem;
            color: #5271ff;
            margin-bottom: 15px;
        }

        .menu-button2 {
            width: 100%;
            padding: 15px 20px;
            font-size: 1.3rem;
            transition: all 0.3s ease;
        }

        .menu-button2:hover {
            background-color: #5271ff;
            color: white;
        }

        @media (max-width: 768px) {
            .support-container {
                padding: 30px 20px;
                margin-top: 80px;
            }

            .support-options {
                flex-direction: column;
                align-items: center;
            }

            .support-option {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
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

    <div class="support-container">
        <h1>Bem-vindo à Área de Suporte</h1>
        <p>Escolha uma das opções abaixo para obter ajuda:</p>

        <div class="support-options">
            <div class="support-option">
                <div class="support-icon">
                    <i class="fas fa-question-circle"></i>
                </div>
                <a href="faq.php">
                    <button class="menu-button2">FAQ</button>
                </a>
            </div>
            
            <div class="support-option">
                <div class="support-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <a href="contato_suporte.php">
                    <button class="menu-button2">Contacto</button>
                </a>
            </div>
            
            <div class="support-option">
                <div class="support-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <a href="abrir_ticket.php">
                    <button class="menu-button2">Abrir Pedido</button>
                </a>
            </div>
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
