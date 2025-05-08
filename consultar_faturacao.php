<?php

    include 'basedados.h';

    // Obter a lista de casos ativos
    $query = "SELECT cj.id, cj.titulo, cj.descricao, c.nome AS cliente_nome
              FROM casos_juridicos cj
              JOIN cliente c ON cj.id_cliente = c.id_cliente
              WHERE cj.estado = 'aberto'";
    $result = mysqli_query($conn, $query);

    // Verificar se a consulta foi bem-sucedida
    if (!$result) {
        die("Erro ao obter a lista de casos: " . mysqli_error($conn));
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Faturação</title>
    <link rel="stylesheet" href="casos_colaborador.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <style>
        .table-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            margin-top: 50px;
        }

        .styled-table {
            width: 90%;
            max-width: 1200px;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 1rem;
            text-align: left;
            background-color: #f9f9f9;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .styled-table thead {
            background-color: white;
            color: #5271ff;
        }

        .styled-table th, .styled-table td {
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }

        .styled-table tbody tr:hover {
            background-color: #f1f1f1;
        }

        .consultar-btn {
            background-color: #5271ff;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 12px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .consultar-btn:hover {
            background-color: #4056d6;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 800px;
            color: #333;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #333;
        }

        .costs-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .costs-table th, .costs-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .costs-table th {
            background-color: #f2f2f2;
        }

        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .page-header {
            text-align: center;
            padding: 20px;
            margin-top: 100px;
            background-color: white;
            border-radius: 10px;
            width: 90%;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .page-header h1 {
            text-align: center;
            color: #5271ff;
            margin-bottom: 15px;
            font-size: 2rem;
        }

        .header-description {
            color: #666;
            font-size: 1.1rem;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.5;
        }
    </style>
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

    <div class="page-header">
        <h1>Consultar Faturação</h1>
        <div class="header-description">
            <p>Abaixo estão listados todos os casos ativos. Clique em "Consultar" para ver os custos detalhados de cada atividade.</p>
        </div>
    </div>

    <div class="table-container">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Descrição</th>
                    <th>Cliente</th>
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
                            <td>
                                <button class="consultar-btn" onclick="consultarFaturacao(<?= $row['id'] ?>, '<?= addslashes($row['titulo']) ?>')">Consultar</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">Nenhum caso ativo encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal para exibir os custos -->
    <div id="custos-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharModal()">&times;</span>
            <h2 id="modal-title">Custos do Caso</h2>
            <div id="custos-container">
                <!-- Conteúdo será preenchido via JavaScript -->
            </div>
        </div>
    </div>

    <script>
        function consultarFaturacao(idCaso, tituloCaso) {
            // Fazer uma requisição AJAX para obter os custos das atividades
            fetch('obter_custos.php?id_caso=' + idCaso)
                .then(response => response.json())
                .then(data => {
                    // Preencher o modal com os dados
                    document.getElementById('modal-title').textContent = 'Custos do Caso: ' + tituloCaso;

                    let html = '<table class="costs-table">';
                    html += '<thead><tr><th>Atividade</th><th>Data</th><th>Custo (€)</th></tr></thead>';
                    html += '<tbody>';

                    let totalCusto = 0;

                    if (data.length > 0) {
                        data.forEach(item => {
                            html += `<tr>
                                <td>${item.titulo}</td>
                                <td>${item.data_atividade}</td>
                                <td>${item.custo ? item.custo + ' €' : 'N/A'}</td>
                            </tr>`;

                            if (item.custo) {
                                totalCusto += parseFloat(item.custo);
                            }
                        });
                    } else {
                        html += '<tr><td colspan="3" style="text-align: center;">Nenhuma atividade registrada para este caso.</td></tr>';
                    }

                    // Adicionar linha de total
                    html += `<tr class="total-row">
                        <td colspan="2" style="text-align: right;"><strong>Total:</strong></td>
                        <td><strong>${totalCusto.toFixed(2)} €</strong></td>
                    </tr>`;

                    html += '</tbody></table>';

                    document.getElementById('custos-container').innerHTML = html;

                    // Exibir o modal
                    document.getElementById('custos-modal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Erro ao obter os custos:', error);
                    alert('Erro ao obter os custos das atividades.');
                });
        }

        function fecharModal() {
            document.getElementById('custos-modal').style.display = 'none';
        }

        // Fechar o modal quando o usuário clicar fora dele
        window.onclick = function(event) {
            const modal = document.getElementById('custos-modal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>

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