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
    <title>FAQ - Perguntas Frequentes</title>
    <link rel="stylesheet" href="menu_colaborador.css">
    <style>
        .faq-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .faq-container h1 {
            text-align: center;
            color: #004080;
            margin-bottom: 20px;
        }

        .faq-container p {
            text-align: center;
            color: #555;
            margin-bottom: 30px;
        }

        .faq-item {
            margin-bottom: 20px;
        }

        .faq-item h3 {
            color: #004080;
            margin-bottom: 10px;
        }

        .faq-item p {
            color: #333;
            line-height: 1.6;
        }

        .back-button {
            display: block;
            margin: 20px auto;
            text-align: center;
        }

        .back-button .menu-button {
            background-color: #004080;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .back-button .menu-button:hover {
            background-color: #003366;
        }
    </style>
</head>

<body>

    <header>
        <div class="header-container">
            <a href="area_suporte.php">
                <button class="menu-button">VOLTAR</button>
            </a>
        </div>
    </header>

    <div class="faq-container">
        <h1>FAQ - Perguntas Frequentes</h1>
        <p>Encontre respostas para as perguntas mais comuns abaixo:</p>

        <div class="faq-item">
            <h3>Como faço para recuperar a minha palavra-passe?</h3>
            <p>Para recuperar a sua palavra-passe, aceda à página de recuperação de palavra-passe e siga as instruções fornecidas.</p>
        </div>

        <div class="faq-item">
            <h3>Como posso entrar em contacto com o suporte?</h3>
            <p>Pode entrar em contacto com o suporte através da página de contacto ou abrindo um pedido na área de suporte.</p>
        </div>

        <div class="faq-item">
            <h3>Onde posso consultar os meus pedidos de suporte?</h3>
            <p>Os seus pedidos de suporte podem ser consultados na área de histórico de pedidos, disponível no menu principal.</p>
        </div>

        <div class="back-button">
            <a href="area_suporte.php">
                <button class="menu-button">Voltar à Área de Suporte</button>
            </a>
        </div>
    </div>

</body>

</html>