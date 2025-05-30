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
    $custo = isset($_POST['custo']) ? floatval($_POST['custo']) : 0; // Ensure custo is a number, default to 0

    // Validate date on the server-side
    $hoje = date("Y-m-d");
    if ($data_atividade < $hoje) {
        echo "<script>alert('A data da atividade deve ser igual ou posterior à data atual.');</script>";
    } else {
        // Get the client ID associated with the case
        $sql_cliente = "SELECT id_cliente FROM casos_juridicos WHERE id = '$id_caso'";
        $result_cliente = mysqli_query($conn, $sql_cliente);

        if ($result_cliente && mysqli_num_rows($result_cliente) > 0) {
            $row_cliente = mysqli_fetch_assoc($result_cliente);
            $id_cliente = $row_cliente['id_cliente'];

            // Get the client's current balance
            $sql_saldo = "SELECT saldo FROM cliente WHERE id_cliente = '$id_cliente'";
            $result_saldo = mysqli_query($conn, $sql_saldo);

            if ($result_saldo && mysqli_num_rows($result_saldo) > 0) {
                $row_saldo = mysqli_fetch_assoc($result_saldo);
                $saldo = floatval($row_saldo['saldo']);

                // Check if the client has sufficient funds
                if ($saldo >= $custo) {
                    // Deduct the cost from the client's balance
                    $novo_saldo = $saldo - $custo;
                    $sql_update = "UPDATE cliente SET saldo = '$novo_saldo' WHERE id_cliente = '$id_cliente'";

                    if (mysqli_query($conn, $sql_update)) {
                        // Insert the activity into the database
                        $insert_query = "INSERT INTO atividades_caso (id_caso, titulo, descricao, data_atividade, custo)
                                         VALUES ('$id_caso', '$titulo', '$descricao', '$data_atividade', '$custo')";

                        if (mysqli_query($conn, $insert_query)) {
                            echo "<script>alert('Atividade registrada com sucesso e saldo do cliente atualizado!'); window.location.href='registar_atividade.php';</script>";
                        } else {
                            echo "<script>alert('Erro ao registrar atividade: " . mysqli_error($conn) . "');</script>";
                        }
                    } else {
                        echo "<script>alert('Erro ao atualizar o saldo do cliente: " . mysqli_error($conn) . "');</script>";
                    }
                } else {
                    echo "<script>alert('O cliente não tem saldo suficiente para esta atividade. Por favor, adicione saldo à conta do cliente.'); window.location.href='colaborador_saldo.php';</script>";
                }
            } else {
                echo "<script>alert('Erro ao obter o saldo do cliente.');</script>";
            }
        } else {
            echo "<script>alert('Erro ao obter o ID do cliente associado ao caso.');</script>";
        }
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dataAtividadeInput = document.getElementById('data_atividade');
            var hoje = new Date().toISOString().split('T')[0]; // Get current date in YYYY-MM-DD format
            dataAtividadeInput.setAttribute('min', hoje); // Set the min attribute to today's date
        });
    </script>
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
  <div class="footer-content">
    <div class="footer-text">REGISTAR ATIVIDADE</div>
    <div class="footer-images">
      <a href="https://maps.app.goo.gl/UQYLoEsTwdgCKoft9" target="_blank">
          <img src="location.png" alt="Imagem Localização"/>
        </a>
        <a href="https://moodle2425.ipcb.pt/" target="_blank">
          <img src="phone.png" alt="Imagem Telefone"/>
        </a>
        <a href="https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox" target="_blank">
          <img src="mail.png" alt="Imagem Email"/>
        </a>
    </div>
  </div>
  <div class="copyright-wrapper">
    <span class="copyright">© 2025 Todos os direitos reservados.</span>
  </div>
</footer>
</body>

</html>