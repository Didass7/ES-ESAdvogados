<?php
session_start();
include 'basedados.h';

if (!isset($_SESSION['id_utilizador'])) {
    echo "<script>alert('Sessão expirada. Faça login novamente.'); window.location.href = 'login.php';</script>";
    exit;
}

$mostra_caixa = false;
$nome = $email = $mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = htmlspecialchars($_POST['nome']);
    $email = htmlspecialchars($_POST['email']);
    $mensagem = htmlspecialchars($_POST['mensagem']);
    $mostra_caixa = true;
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto</title>
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

        .info-box {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #e6f7ff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            color: #004080;
        }

        .info-box h2 {
            margin-bottom: 15px;
        }

        .info-box p {
            margin: 8px 0;
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

    <?php if ($mostra_caixa): ?>
        <div class="info-box">
            <h2>Contacto enviado com sucesso!</h2>
            <p><strong>Nome:</strong> <?php echo $nome; ?></p>
            <p><strong>Email:</strong> <?php echo $email; ?></p>
            <p><strong>Mensagem:</strong> <?php echo nl2br($mensagem); ?></p>
        </div>
    <?php else: ?>
        <div class="contact-container">
            <h1>Contacto</h1>
            <p>Entre em contacto connosco preenchendo o formulário abaixo:</p>
            <form action="" method="POST">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" placeholder="Insira o seu nome" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" placeholder="Insira o seu email" required>

                <label for="mensagem">Mensagem:</label>
                <textarea id="mensagem" name="mensagem" rows="5" placeholder="Escreva a sua mensagem" required></textarea>

                <button type="submit">Enviar</button>
            </form>
        </div>
    <?php endif; ?>

</body>
</html>