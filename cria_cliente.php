<?php
session_start();
include 'basedados.h';

// Buscar métodos de pagamento
$metodos_pagamento = [];
$metodos_query = "SELECT id_metodo, metodo FROM metodopagamento";
$result_metodos = mysqli_query($conn, $metodos_query);

if ($result_metodos && mysqli_num_rows($result_metodos) > 0) {
    while ($row = mysqli_fetch_assoc($result_metodos)) {
        $metodos_pagamento[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['id_utilizador'])) {
        echo "<script>alert('Sessão expirada. Faça login novamente.'); window.location.href = 'login.php';</script>";
        exit;
    }

    $id_colaborador = $_SESSION['id_utilizador'];

    // Captura e sanitiza os dados
    $nome = $_POST['nome'];
    $dataNasc =$_POST['nascimento'];
    $nif =$_POST['nif'];
    $contacto1 =$_POST['contacto1'];
    $contacto2 =$_POST['contacto2'];
    $morada =$_POST['morada'];
    $endereco_faturacao =$_POST['endereco_faturacao'];
    $pagamento =$_POST['pagamento'];

    $sql = "INSERT INTO cliente (nome, dataNasci, nif, contacto1, contacto2, morada, endereco_faturacao, pagamento, id_colaborador) 
            VALUES ('$nome', '$dataNasc', '$nif', '$contacto1', '$contacto2', '$morada', '$endereco_faturacao', '$pagamento', '$id_colaborador')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Novo cliente registado com sucesso'); window.location.href = 'menu_colaborador.php';</script>";
    } else {
        echo "<script>alert('Erro ao registar cliente: " . $conn->error . "'); window.location.href = 'cria_cliente.php';</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Cliente</title>
    <link rel="stylesheet" href="cria_cliente.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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

            <a href="menu_colaborador.php">
              <img src="seta.png" alt="Ícone" style="width: 60px; height: 60px; vertical-align: middle;">
            </a>
        </div>
    </header>

  <main style="margin-top: 100px; margin-bottom: 120px;">
    <form action="cria_cliente.php" method="POST" style="background-color: white; padding: 30px; border-radius: 25px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; max-width: 900px; margin: auto; box-shadow: 0 5px 15px rgba(0,0,0,0.15); text-align: center;">

      <div>
        <label style="font-weight: bold; color: #5271ff;">NOME</label><br>
        <input type="text" name="nome" placeholder="INSERIR NOME" required>
      </div>

      <div>
        <label style="font-weight: bold; color: #5271ff;">NASCIMENTO</label><br>
        <input type="date" name="nascimento" required>
      </div>

      <div>
        <label style="font-weight: bold; color: #5271ff;">NIF</label><br>
        <input type="text" name="nif" placeholder="INSERIR NIF" maxlength="9" required>
      </div>

      <div>
        <label style="font-weight: bold; color: #5271ff;">CONTACTO</label><br>
        <input type="text" name="contacto1" placeholder="INSERIR CONTACTO PRINCIPAL" maxlength="9" required>
      </div>

      <div>
        <label style="font-weight: bold; color: #5271ff;">2° CONTACTO</label><br>
        <input type="text" name="contacto2" placeholder="INSERIR CONTACTO SECUNDÁRIO" maxlength="9">
      </div>

      <div>
        <label style="font-weight: bold; color: #5271ff;">MORADA</label><br>
        <input type="text" name="morada" placeholder="INSERIR MORADA" required>
      </div>

      <div>
        <label style="font-weight: bold; color: #5271ff;">END. FAT.</label><br>
        <input type="text" name="endereco_faturacao" placeholder="INSERIR ENDEREÇO DE FATURAÇÃO" required>
      </div>

      <div>
        <label style="font-weight: bold; color: #5271ff;">PAGAMENTO</label><br>
        <select name="pagamento" required>
          <option value="" disabled selected style = "font-weight: bold; color: white;">SELECIONE UM MÉTODO</option>
          <?php foreach ($metodos_pagamento as $metodo): ?>
            <option value="<?= $metodo['id_metodo'] ?>"><?= htmlspecialchars($metodo['metodo']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>
    <form action="cria_cliente.php" method="POST">
      <div style="grid-column: span 2; text-align: center; margin-top: 20px;">
          <button type="submit" style="background-color: white; color: #5271ff; border: 2px solid #5271ff; padding: 12px 40px; border-radius: 30px; font-weight: bold; font-size: 1rem; cursor: pointer;">
            SUBMETER
          </button>
      </div>
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