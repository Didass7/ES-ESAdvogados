<?php
session_start();
include 'basedados.h';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Obter o ID do colaborador logado
$colaborador_id = $_SESSION['user_id'];

// Verificar se a tabela horas_trabalhadas existe
$check_table_query = "SHOW TABLES LIKE 'horas_trabalhadas'";
$table_result = mysqli_query($conn, $check_table_query);

// Se a tabela não existir, criá-la sem chaves estrangeiras
if (mysqli_num_rows($table_result) == 0) {
    $create_table_query = "CREATE TABLE horas_trabalhadas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_caso INT NOT NULL,
        id_colaborador INT NOT NULL,
        data_registro DATE NOT NULL,
        horas DECIMAL(5,2) NOT NULL,
        descricao TEXT NOT NULL,
        valor_hora DECIMAL(10,2) NOT NULL,
        faturado BOOLEAN DEFAULT FALSE
    )";
    
    if (!mysqli_query($conn, $create_table_query)) {
        die("Erro ao criar tabela horas_trabalhadas: " . mysqli_error($conn));
    }
}

// Obter a lista de casos jurídicos ativos associados aos clientes do colaborador
$query_casos = "SELECT cj.id, cj.titulo 
                FROM casos_juridicos cj
                JOIN cliente cl ON cj.id_cliente = cl.id_cliente
                WHERE cj.estado = 'aberto' AND cl.id_colaborador = ?";

$stmt_casos = $conn->prepare($query_casos);
$stmt_casos->bind_param("i", $colaborador_id);
$stmt_casos->execute();
$result_casos = $stmt_casos->get_result();

// Processar o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['faturar_id'])) {
        $faturar_id = $_POST['faturar_id'];
        
        // Obter o valor total das horas a faturar
        $query_valor = "SELECT (h.horas * h.valor_hora) as valor_total, cj.id_cliente 
                        FROM horas_trabalhadas h
                        JOIN casos_juridicos cj ON h.id_caso = cj.id
                        WHERE h.id = ?";
        $stmt_valor = $conn->prepare($query_valor);
        $stmt_valor->bind_param("i", $faturar_id);
        $stmt_valor->execute();
        $result_valor = $stmt_valor->get_result();
        
        if ($result_valor && $result_valor->num_rows > 0) {
            $row_valor = $result_valor->fetch_assoc();
            $valor_total = $row_valor['valor_total'];
            $id_cliente = $row_valor['id_cliente'];
            
            // Obter o saldo do cliente
            $query_saldo = "SELECT saldo FROM cliente WHERE id_cliente = ?";
            $stmt_saldo = $conn->prepare($query_saldo);
            $stmt_saldo->bind_param("i", $id_cliente);
            $stmt_saldo->execute();
            $result_saldo = $stmt_saldo->get_result();
            
            if ($result_saldo && $result_saldo->num_rows > 0) {
                $row_saldo = $result_saldo->fetch_assoc();
                $saldo = $row_saldo['saldo'];
                
                // Verificar se o cliente tem saldo suficiente
                if ($saldo >= $valor_total) {
                    // Atualizar o status para faturado
                    $query_update = "UPDATE horas_trabalhadas SET faturado = TRUE WHERE id = ?";
                    $stmt_update = $conn->prepare($query_update);
                    $stmt_update->bind_param("i", $faturar_id);
                    
                    if ($stmt_update->execute()) {
                        // Subtrair o valor do saldo do cliente
                        $novo_saldo = $saldo - $valor_total;
                        $query_update_saldo = "UPDATE cliente SET saldo = ? WHERE id_cliente = ?";
                        $stmt_update_saldo = $conn->prepare($query_update_saldo);
                        $stmt_update_saldo->bind_param("di", $novo_saldo, $id_cliente);
                        $stmt_update_saldo->execute();
                        $stmt_update_saldo->close();
                        
                        echo "<script>alert('Horas faturadas com sucesso e saldo do cliente atualizado!'); window.location.href='horas_trabalhadas.php';</script>";
                    } else {
                        echo "<script>alert('Erro ao faturar horas: " . $stmt_update->error . "');</script>";
                    }
                    
                    $stmt_update->close();
                } else {
                    echo "<script>alert('O cliente não tem saldo suficiente para faturar estas horas. Redirecionando para adicionar saldo.'); window.location.href='colaborador_saldo.php';</script>";
                }
            } else {
                echo "<script>alert('Erro ao obter o saldo do cliente.');</script>";
            }
            
            $stmt_saldo->close();
        } else {
            echo "<script>alert('Erro ao obter o valor total das horas.');</script>";
        }
        
        $stmt_valor->close();
    } else {
        $id_caso = $_POST['id_caso'];
        $data_registro = $_POST['data_registro'];
        $horas = $_POST['horas'];
        $descricao = $_POST['descricao'];
        $valor_hora = $_POST['valor_hora'];

        // Validar se a data é igual ou posterior à data atual
        $hoje = date("Y-m-d");
        if ($data_registro < $hoje) {
            echo "<script>alert('A data de registro deve ser igual ou posterior à data atual.');</script>";
        } else {
            // Inserir os dados na tabela
            $query = "INSERT INTO horas_trabalhadas (id_caso, id_colaborador, data_registro, horas, descricao, valor_hora) 
                      VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iisdsd", $id_caso, $colaborador_id, $data_registro, $horas, $descricao, $valor_hora);
            
            if ($stmt->execute()) {
                echo "<script>alert('Horas trabalhadas registradas com sucesso!');</script>";
            } else {
                echo "<script>alert('Erro ao registrar horas trabalhadas: " . $stmt->error . "');</script>";
            }
            
            $stmt->close();
        }
    }
}

// Obter as horas trabalhadas pelo colaborador
$query_horas = "SELECT h.id, c.titulo as caso, h.data_registro, h.horas, h.descricao, h.valor_hora, 
                (h.horas * h.valor_hora) as valor_total, h.faturado
                FROM horas_trabalhadas h
                JOIN casos_juridicos c ON h.id_caso = c.id
                WHERE h.id_colaborador = ?
                ORDER BY h.data_registro DESC";

$stmt_horas = $conn->prepare($query_horas);
$stmt_horas->bind_param("i", $colaborador_id);
$stmt_horas->execute();
$result_horas = $stmt_horas->get_result();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Horas Trabalhadas</title>
    <link rel="stylesheet" href="casos_colaborador.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 100px auto 50px;
            padding: 20px;
        }
        
        .card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .card h2 {
            color: #5271ff;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .btn-primary {
            background-color: #5271ff;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #3a5ae8;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .faturado {
            color: green;
            font-weight: bold;
        }
        
        .nao-faturado {
            color: #ff5252;
        }
        
        .resumo {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        .resumo-item {
            text-align: center;
            flex: 1;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin: 0 10px;
        }
        
        .resumo-valor {
            font-size: 24px;
            font-weight: bold;
            color: #5271ff;
            margin-top: 10px;
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

    <div class="container">
        <div class="card">
            <h2><i class="fas fa-clock"></i> Registrar Horas Trabalhadas</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="id_caso">Caso Jurídico:</label>
                    <select id="id_caso" name="id_caso" class="form-control" required>
                        <option value="">Selecione um caso</option>
                        <?php while ($caso = mysqli_fetch_assoc($result_casos)): ?>
                            <option value="<?= $caso['id'] ?>"><?= $caso['titulo'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="data_registro">Data:</label>
                    <input type="date" id="data_registro" name="data_registro" class="form-control" required value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>">
                </div>
                
                <div class="form-group">
                    <label for="horas">Horas Trabalhadas:</label>
                    <input type="number" id="horas" name="horas" step="0.25" min="0.25" class="form-control" required placeholder="Ex: 2.5">
                </div>
                
                <div class="form-group">
                    <label for="valor_hora">Valor por Hora (€):</label>
                    <input type="number" id="valor_hora" name="valor_hora" step="0.01" min="0" class="form-control" required placeholder="Ex: 75.00">
                </div>
                
                <div class="form-group">
                    <label for="descricao">Descrição do Trabalho:</label>
                    <textarea id="descricao" name="descricao" class="form-control" rows="3" required placeholder="Descreva o trabalho realizado"></textarea>
                </div>
                
                <button type="submit" class="btn-primary">Registrar Horas</button>
            </form>
        </div>
        
        <div class="card">
            <h2><i class="fas fa-history"></i> Histórico de Horas Trabalhadas</h2>
            
            <?php
            // Calcular totais
            $total_horas = 0;
            $total_valor = 0;
            $total_faturado = 0;
            $total_pendente = 0;
            
            if ($result_horas->num_rows > 0) {
                while ($row = $result_horas->fetch_assoc()) {
                    $total_horas += $row['horas'];
                    $valor_total = $row['horas'] * $row['valor_hora'];
                    $total_valor += $valor_total;
                    
                    if ($row['faturado']) {
                        $total_faturado += $valor_total;
                    } else {
                        $total_pendente += $valor_total;
                    }
                }
                
                // Resetar o ponteiro do resultado
                mysqli_data_seek($result_horas, 0);
            }
            ?>
            
            <div class="resumo">
                <div class="resumo-item">
                    <div>Total de Horas</div>
                    <div class="resumo-valor"><?= number_format($total_horas, 2) ?></div>
                </div>
                <div class="resumo-item">
                    <div>Valor Total</div>
                    <div class="resumo-valor"><?= number_format($total_valor, 2) ?> €</div>
                </div>
                <div class="resumo-item">
                    <div>Faturado</div>
                    <div class="resumo-valor"><?= number_format($total_faturado, 2) ?> €</div>
                </div>
                <div class="resumo-item">
                    <div>Pendente</div>
                    <div class="resumo-valor"><?= number_format($total_pendente, 2) ?> €</div>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Caso</th>
                        <th>Data</th>
                        <th>Horas</th>
                        <th>Descrição</th>
                        <th>Valor/Hora</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_horas->num_rows > 0): ?>
                        <?php while ($row = $result_horas->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['caso'] ?></td>
                                <td><?= date('d/m/Y', strtotime($row['data_registro'])) ?></td>
                                <td><?= number_format($row['horas'], 2) ?></td>
                                <td><?= $row['descricao'] ?></td>
                                <td><?= number_format($row['valor_hora'], 2) ?> €</td>
                                <td><?= number_format($row['valor_total'], 2) ?> €</td>
                                <td>
                                    <?php if ($row['faturado']): ?>
                                        <span class="faturado"><i class="fas fa-check-circle"></i> Faturado</span>
                                    <?php else: ?>
                                        <span class="nao-faturado"><i class="fas fa-clock"></i> Pendente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$row['faturado']): ?>
                                        <form method="POST" action="">
                                            <input type="hidden" name="faturar_id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn-primary btn-sm">Faturar</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">Nenhum registro de horas encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<footer>
  <div class="footer-content">
    <div class="footer-text">HORAS TRABALHADAS</div>
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


