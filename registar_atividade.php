<?php
include 'basedados.h';

// Obter os casos que estão em aberto
$query = "SELECT id, titulo FROM casos_juridicos WHERE estado = 'aberto'";
$result = mysqli_query($conn, $query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_caso = $_POST['id_caso'];
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $data_atividade = $_POST['data_atividade'];

    $query = "INSERT INTO atividades_caso (id_caso, titulo, descricao, data_atividade) 
              VALUES ('$id_caso', '$titulo', '$descricao', '$data_atividade')";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Atividade registrada com sucesso!'); window.location.href='registar_atividade.php';</script>";
    } else {
        echo "<script>alert('Erro ao registrar atividade: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Atividade</title>
    <link rel="stylesheet" href="criar_atividade.css">
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
            <a href="logout.php">
                <button class="menu-button">LOGOUT</button>
            </a>
            <button class="menu-button">
                COLABORADOR
                <img src="person.png" alt="Ícone" style="width: 30px; height: 30px; vertical-align: middle;">
            </button>
            <a href="gerir_caso.php">
                <img src="seta.png" alt="Ícone" style="width: 60px; height: 60px; vertical-align: middle;">
            </a>
        </div>
    </header>

    <main>
        <form action="registar_atividade.php" method="POST" class="form-container">
            <label for="id_caso">Selecione o Caso:</label>
            <select id="id_caso" name="id_caso" required>
                <option value="">Selecione um caso</option>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <option value="<?= $row['id'] ?>"><?= $row['titulo'] ?></option>
                <?php endwhile; ?>
            </select>

            <label for="titulo">Título da Atividade:</label>
            <input type="text" id="titulo" name="titulo" placeholder="Digite o título da atividade" required>

            <label for="descricao">Descrição:</label>
            <textarea id="descricao" name="descricao" placeholder="Digite a descrição da atividade" required></textarea>

            <label for="data_atividade">Data da Atividade:</label>
            <input type="date" id="data_atividade" name="data_atividade" required>

            <button type="submit" class="submit-button">Registar Atividade</button>
        </form>
    </main>

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