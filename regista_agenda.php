<?php
  session_start();
  if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
  }

  include 'basedados.h'; 

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $dias = $_POST['dia'] ?? [];
      $acoes = $_POST['acao'] ?? [];

      foreach ($dias as $mes => $valoresDias) {
          foreach ($valoresDias as $index => $dia) {
              $acao = $acoes[$mes][$index];

              // Ignora se estiver vazio
              if (!empty($dia) && !empty($acao)) {
                  $stmt = $conn->prepare("INSERT INTO agenda (mes, dia, acao) VALUES (?, ?, ?)");
                  $stmt->bind_param("sis", $mes, $dia, $acao);
                  $stmt->execute();
              }
          }
      }

      // Redireciona ou mostra confirmação
      header("Location: menu_colaborador.php?sucesso=1");
      exit();
  }
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Registar Agenda</title>
  <link rel="stylesheet" href="regista_agenda.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"/>
</head>

<body>
  <header>
    <div class="header-container">
      <a href="menu_colaborador.php">
        <img src="logo.png" alt="Logotipo" class="logo"/>
      </a>
    </div>
    <div class="header-container2">
      <a href="logout.php">
        <button class="menu-button">LOGOUT</button>
      </a>
      <button class="menu-button">
        COLABORADOR
        <img src="person.png" alt="Ícone" class="icon"/>
      </button>
      <a href="menu_colaborador.php">
        <img src="seta.png" alt="Ícone" class="icon-large"/>
      </a>
    </div>
  </header>

  <main class="main-content">
    <div>
      <h2>2025</h2>
      <form method="POST" action="">
        <div class="grid-meses">
          <?php
          $meses = [
              "JANEIRO" => 31,
              "FEVEREIRO" => 28,
              "MARÇO" => 31,
              "ABRIL" => 30,
              "MAIO" => 31,
              "JUNHO" => 30,
              "JULHO" => 31,
              "AGOSTO" => 31,
              "SETEMBRO" => 30,
              "OUTUBRO" => 31,
              "NOVEMBRO" => 30,
              "DEZEMBRO" => 31
          ];
          
          foreach ($meses as $mes => $dias) {
              echo '
              <div class="mes-card" data-mes="'.$mes.'" data-max-dias="'.$dias.'">
                <h3>'.$mes.'</h3>
                <div class="campos-wrapper">
                  <div class="campos">
                    <input type="number" class="input-field" name="dia['.$mes.'][]" placeholder="DIA" min="1" max="'.$dias.'">
                    <input type="text" class="input-field" name="acao['.$mes.'][]" placeholder="AÇÃO">
                  </div>
                </div>
                <button type="button" class="menu-button adicionar-btn">ADICIONAR</button>
              </div>';
          }
          ?>
        </div>
        <div class="submit-wrapper">
          <button type="submit" class="menu-button">SUBMETER</button>
        </div>
      </form>
    </div>
  </main>

  <footer> 
      <div class="footer-images">
        <div class="footer-content">
          <span class="footer-text">REGISTAR AGENDA</span> 
        </div>
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
      <p class="copyright">© 2025 Todos os direitos reservados.</p>
    </div>
  </footer>

  <script>
    document.querySelectorAll('.adicionar-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const mesCard = btn.closest('.mes-card');
        const camposWrapper = mesCard.querySelector('.campos-wrapper');
        const maxDias = mesCard.dataset.maxDias;
        const mes = mesCard.dataset.mes;

        // Cria novos campos
        const novoCampo = document.createElement('div');
        novoCampo.classList.add('campos');
        novoCampo.innerHTML = `
          <input type="number" class="input-field" name="dia[${mes}][]" placeholder="DIA" min="1" max="${maxDias}">
          <input type="text" class="input-field" name="acao[${mes}][]" placeholder="AÇÃO">
        `;

        camposWrapper.appendChild(novoCampo);
      });
    });
  </script>
</body>
</html>
