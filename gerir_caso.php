<?php
    
    include 'basedados.h';

    // Verificar se a conexão foi estabelecida
    if (!$conn) {
        die("Erro ao conectar à base de dados: " . mysqli_connect_error());
    }

    // Obter a lista de casos associados aos clientes
    $query = "SELECT cj.id, cj.titulo, cj.descricao, cj.estado, cj.data_fechamento, c.nome AS cliente_nome 
              FROM casos_juridicos cj
              JOIN cliente c ON cj.id_cliente = c.id_cliente";
    $result = mysqli_query($conn, $query);

    // Verificar se a consulta foi bem-sucedida
    if (!$result) {
        die("Erro ao obter a lista de casos: " . mysqli_error($conn));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_estado'])) {
        $id_caso = $_POST['id_caso'];
        $novo_estado = $_POST['novo_estado'];

        // Atualizar o estado do caso
        $query = "UPDATE casos_juridicos SET estado = '$novo_estado' WHERE id = $id_caso";
        mysqli_query($conn, $query);

        // Atualizar a data de fecho apenas se o estado for "terminado"
        if ($novo_estado === 'terminado') {
            $data_fechamento = date('Y-m-d');
            $query = "UPDATE casos_juridicos SET data_fechamento = '$data_fechamento' WHERE id = $id_caso";
            mysqli_query($conn, $query);
        }

        // Redirecionar para a mesma página para carregar os dados atualizados
        header("Location: gerir_caso.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerir Casos Jurídicos</title>
    <link rel="stylesheet" href="gerir_caso.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        <h1 style="text-align: center; color: #5271ff;">Gerir Casos Jurídicos</h1>
        <div class="table-container">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Descrição</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th>Data Término</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $row['titulo'] ?></td>
                                <td><?= $row['descricao'] ?></td>
                                <td><?= $row['cliente_nome'] ?></td>
                                <td><?= $row['estado'] ?></td>
                                <td><?= $row['data_fechamento'] ?? 'N/A' ?></td>
                                <td>
                                    <?php if ($row['estado'] !== 'terminado'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="id_caso" value="<?= $row['id'] ?>">
                                            <select name="novo_estado" required>
                                                <option value="aberto" <?= $row['estado'] === 'aberto' ? 'selected' : '' ?>>Aberto</option>
                                                <option value="fechado" <?= $row['estado'] === 'fechado' ? 'selected' : '' ?>>Fechado</option>
                                                <option value="terminado" <?= $row['estado'] === 'terminado' ? 'selected' : '' ?>>Terminado</option>
                                            </select>
                                            <button type="submit" name="alterar_estado">Alterar</button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="apagar_caso.php?id=<?= $row['id'] ?>" class="delete-button" onclick="return confirm('Tem a certeza que deseja apagar este caso?')">Apagar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Nenhum caso encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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