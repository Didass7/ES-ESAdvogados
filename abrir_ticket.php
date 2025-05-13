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
    <title>Abrir Pedido</title>
    <link rel="stylesheet" href="menu_colaborador.css">
    <style>
        .contact-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .contact-container h1 {
            text-align: center;
            color: #004080;
            margin-bottom: 20px;
        }

        .contact-container p {
            text-align: center;
            color: #555;
            margin-bottom: 30px;
        }

        .contact-container form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .contact-container form input,
        .contact-container form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .contact-container form textarea {
            resize: none;
        }

        .contact-container form button {
            background-color: #004080;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .contact-container form button:hover {
            background-color: #003366;
        }

        .back-button {
            text-align: center;
            margin-top: 20px;
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

    <div class="contact-container">
        <h1>Abrir Pedido</h1>
        <p>Preencha o formulário abaixo para abrir um pedido de suporte:</p>
        <form action="envia_ticket.php" method="POST">
            <label for="assunto">Assunto:</label>
            <input type="text" id="assunto" name="assunto" placeholder="Insira o assunto do pedido" required>

            <label for="descricao">Descrição:</label>
            <textarea id="descricao" name="descricao" rows="5" placeholder="Descreva o problema ou pedido" required></textarea>

            <button type="submit">Enviar Pedido</button>
        </form>
    </div>

</body>

</html>
