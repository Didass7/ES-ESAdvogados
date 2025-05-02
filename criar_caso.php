<?php
    
    include 'basedados.h';

    // Obter a lista de clientes
    $query = "SELECT id_cliente, nome FROM cliente";
    $result = mysqli_query($conn, $query);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $titulo = $_POST['titulo'];
        $descricao = $_POST['descricao'];
        $estado = 'aberto'; // Estado padrão
        $id_cliente = $_POST['id_cliente'];

        // Inserir os dados na tabela casos_juridicos
        $query = "INSERT INTO casos_juridicos (titulo, descricao, estado, id_cliente) 
                  VALUES ('$titulo', '$descricao', '$estado', $id_cliente)";

        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Caso jurídico criado com sucesso!');</script>";
        } else {
            echo "<script>alert('Erro ao criar caso jurídico: " . mysqli_error($conn) . "');</script>";
        }
    }
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Caso Jurídico</title>
    <link rel="stylesheet" href="casos_colaborador.css">
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
        <h1>Criar Caso Jurídico</h1>
        <form action="criar_caso.php" method="POST">
            <label for="titulo">Título do Caso:</label>
            <input type="text" id="titulo" name="titulo" placeholder="Digite o título do caso" required>

            <label for="descricao">Descrição do Caso:</label>
            <textarea id="descricao" name="descricao" placeholder="Digite a descrição do caso" required></textarea>

            <label for="id_cliente">Selecionar Cliente:</label>
            <select id="id_cliente" name="id_cliente" required>
                <option value="">Selecione um cliente</option>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <option value="<?= $row['id_cliente'] ?>"><?= $row['nome'] ?></option>
                <?php endwhile; ?>
            </select>

            <button type="submit">Criar Caso</button>
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