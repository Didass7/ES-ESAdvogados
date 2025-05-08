<?php
include 'basedados.h';

// Verificar se a tabela atividades_caso existe
$check_table_query = "SHOW TABLES LIKE 'atividades_caso'";
$table_result = mysqli_query($conn, $check_table_query);

// Se a tabela não existir, criá-la
if (mysqli_num_rows($table_result) == 0) {
    $create_table_query = "CREATE TABLE atividades_caso (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_caso INT NOT NULL,
        titulo VARCHAR(255) NOT NULL,
        descricao TEXT,
        data_atividade DATE NOT NULL,
        custo DECIMAL(10,2) DEFAULT NULL,
        FOREIGN KEY (id_caso) REFERENCES casos_juridicos(id) ON DELETE CASCADE
    )";

    if (!mysqli_query($conn, $create_table_query)) {
        die("Erro ao criar tabela atividades_caso: " . mysqli_error($conn));
    }
}

// Verificar se a tabela atividades_caso tem a coluna custo
$check_column_query = "SHOW COLUMNS FROM atividades_caso LIKE 'custo'";
$column_result = mysqli_query($conn, $check_column_query);

// Se a coluna não existir, adicioná-la
if (mysqli_num_rows($column_result) == 0) {
    $add_column_query = "ALTER TABLE atividades_caso ADD COLUMN custo DECIMAL(10,2) DEFAULT NULL";
    mysqli_query($conn, $add_column_query);
}

// Obter os casos que estão em aberto
$query = "SELECT id, titulo FROM casos_juridicos WHERE estado = 'aberto'";
$result = mysqli_query($conn, $query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_caso = $_POST['id_caso'];
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $data_atividade = $_POST['data_atividade'];
    $custo = isset($_POST['custo']) ? floatval($_POST['custo']) : null;

    // Verificar se a coluna custo existe na tabela
    $check_column_query = "SHOW COLUMNS FROM atividades_caso LIKE 'custo'";
    $column_result = mysqli_query($conn, $check_column_query);

    // Usar consulta com ou sem coluna custo dependendo se ela existe
    $query = mysqli_num_rows($column_result) > 0
        ? "INSERT INTO atividades_caso (id_caso, titulo, descricao, data_atividade, custo)
           VALUES ('$id_caso', '$titulo', '$descricao', '$data_atividade', " . ($custo !== null ? $custo : "NULL") . ")"
        : "INSERT INTO atividades_caso (id_caso, titulo, descricao, data_atividade)
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
            <a href="casos_juridicos.php">
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

            <label for="custo">Custo da Atividade (€):</label>
            <input type="number" id="custo" name="custo" step="0.01" min="0" placeholder="Digite o custo da atividade">

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