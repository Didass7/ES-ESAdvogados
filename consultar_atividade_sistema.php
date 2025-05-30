<?php
session_start();
include 'basedados.h';

// Verificar se o utilizador está autenticado e é administrador
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Verificar se é administrador (id_tipo = 1)
$user_id = $_SESSION['user_id'];
$sql_check_admin = "SELECT id_tipo FROM utilizador WHERE id_utilizador = '$user_id'";
$result_admin = mysqli_query($conn, $sql_check_admin);

if (!$result_admin || mysqli_num_rows($result_admin) == 0 || mysqli_fetch_assoc($result_admin)['id_tipo'] != 1) {
    echo "<script>alert('Acesso não autorizado.'); window.location.href='login.php';</script>";
    exit();
}

// Inicializar variáveis
$atividades = [];
$mensagem = "";
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'todos';
$filtro_data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$filtro_data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';
$filtro_colaborador = isset($_GET['colaborador']) ? $_GET['colaborador'] : '';

// Buscar colaboradores para o filtro
$sql_colaboradores = "SELECT id_utilizador, nomeutilizador FROM utilizador WHERE id_tipo = 2";
$result_colaboradores = mysqli_query($conn, $sql_colaboradores);
$colaboradores = [];
if ($result_colaboradores && mysqli_num_rows($result_colaboradores) > 0) {
    while ($row = mysqli_fetch_assoc($result_colaboradores)) {
        $colaboradores[$row['id_utilizador']] = $row['nomeutilizador'];
    }
}

// Verificar se a tabela pagamentos existe
$check_table_query = "SHOW TABLES LIKE 'pagamentos'";
$table_result = mysqli_query($conn, $check_table_query);
$pagamentos_existe = mysqli_num_rows($table_result) > 0;

// Verificar a estrutura da tabela casos_juridicos
$check_columns_query = "DESCRIBE casos_juridicos";
$columns_result = mysqli_query($conn, $check_columns_query);
$columns = [];
while ($row = mysqli_fetch_assoc($columns_result)) {
    $columns[] = $row['Field'];
}

// Verificar se existe coluna para colaborador
$colaborador_field = in_array('id_colaborador', $columns) ? 'c.id_colaborador' : NULL;

// Se não encontrar nenhuma coluna de colaborador, mostrar mensagem e link para corrigir
if (!$colaborador_field) {
    $mensagem = "Erro: Não foi possível identificar a coluna de colaborador na tabela casos_juridicos. <a href='adicionar_colaborador.php' style='color: blue; text-decoration: underline;'>Clique aqui para corrigir</a>";
    $atividades = [];
} else {
    // Continuar com a lógica normal
    $data_caso_field = in_array('data_fechamento', $columns) ? "c.data_fechamento" : "NOW()";

    // Construir a consulta SQL com base nos filtros
    $sql_atividades = "
    SELECT * FROM (
        SELECT 
            'caso' AS tipo,
            c.id AS id_item,
            c.titulo AS titulo,
            c.descricao AS descricao,
            $data_caso_field AS data,
            u.nomeutilizador AS utilizador,
            cl.nome AS cliente
        FROM casos_juridicos c
        JOIN utilizador u ON c.id_colaborador = u.id_utilizador
        JOIN cliente cl ON c.id_cliente = cl.id_cliente
        
        UNION ALL
        
        SELECT 
            'atividade' AS tipo,
            a.id AS id_item,
            a.titulo AS titulo,
            a.descricao AS descricao,
            a.data_atividade AS data,
            u.nomeutilizador AS utilizador,
            cl.nome AS cliente
        FROM atividades_caso a
        JOIN casos_juridicos c ON a.id_caso = c.id
        JOIN utilizador u ON c.id_colaborador = u.id_utilizador
        JOIN cliente cl ON c.id_cliente = cl.id_cliente
        
        UNION ALL
        
        SELECT 
            'horas' AS tipo,
            h.id AS id_item,
            c.titulo AS titulo,
            h.descricao AS descricao,
            h.data_registro AS data,
            u.nomeutilizador AS utilizador,
            cl.nome AS cliente
        FROM horas_trabalhadas h
        JOIN casos_juridicos c ON h.id_caso = c.id
        JOIN utilizador u ON h.id_colaborador = u.id_utilizador
        JOIN cliente cl ON c.id_cliente = cl.id_cliente";

    // Adicionar a consulta de pagamentos apenas se a tabela existir
    if ($pagamentos_existe) {
        $sql_atividades .= "
        UNION ALL
        
        SELECT 
            'pagamento' AS tipo,
            p.id AS id_item,
            c.titulo AS titulo,
            p.descricao AS descricao,
            p.data_pagamento AS data,
            u.nomeutilizador AS utilizador,
            cl.nome AS cliente
        FROM pagamentos p
        JOIN casos_juridicos c ON p.id_caso = c.id
        JOIN utilizador u ON p.id_registrador = u.id_utilizador
        JOIN cliente cl ON c.id_cliente = cl.id_cliente";
    }

    $sql_atividades .= "
    ) AS atividades
    WHERE 1=1";

    // Adicionar filtros à consulta
    if ($filtro_tipo != 'todos') {
        $sql_atividades .= " AND tipo = '$filtro_tipo'";
    }

    if (!empty($filtro_data_inicio)) {
        $sql_atividades .= " AND data >= '$filtro_data_inicio'";
    }

    if (!empty($filtro_data_fim)) {
        $sql_atividades .= " AND data <= '$filtro_data_fim 23:59:59'";
    }

    if (!empty($filtro_colaborador)) {
        $sql_atividades .= " AND utilizador = '$filtro_colaborador'";
    }

    $sql_atividades .= " ORDER BY data DESC LIMIT 200";

    $result_atividades = mysqli_query($conn, $sql_atividades);

    if ($result_atividades) {
        if (mysqli_num_rows($result_atividades) > 0) {
            $atividades = mysqli_fetch_all($result_atividades, MYSQLI_ASSOC);
        } else {
            $mensagem = "Nenhuma atividade encontrada com os filtros especificados.";
        }
    } else {
        $mensagem = "Erro na consulta: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atividade do Sistema</title>
    <link rel="stylesheet" href="menu_admin.css">
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
        
        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }
        
        .filter-container select,
        .filter-container input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            flex-grow: 1;
        }
        
        .filter-container button {
            background-color: #5271ff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
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
        
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-caso {
            background-color: #e3f2fd;
            color: #0d47a1;
        }
        
        .badge-atividade {
            background-color: #e8f5e9;
            color: #1b5e20;
        }
        
        .badge-horas {
            background-color: #fff3e0;
            color: #e65100;
        }
        
        .badge-pagamento {
            background-color: #f3e5f5;
            color: #4a148c;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #dee2e6;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="menu_admin.php">
                <img src="logo.png" alt="Logotipo" class="logo">
            </a>
        </div>
        <div class="header-container2">
            <a href="muda_password_admin.php">
              <button class="menu-button">MUDAR PASSWORD</button>
            </a>

            <a href="edita_perfil_admin.php">
              <button class="menu-button">EDITAR PERFIL</button>
            </a>

            <a href="logout.php">
              <button class="menu-button">LOGOUT</button>
            </a>

            <button class="menu-button">
              ADMINISTRADOR
              <img src="person.png" alt="Ícone" style="width: 30px; height: 30px; vertical-align: middle;">
            </button>
            
            <a href="menu_admin.php">
              <img src="seta.png" alt="Ícone" style="width: 60px; height: 60px; vertical-align: middle;">
            </a>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2><i class="fas fa-history"></i> Atividade do Sistema</h2>
            
            <form method="GET" action="" class="filter-container">
                <select name="tipo">
                    <option value="todos" <?php echo $filtro_tipo == 'todos' ? 'selected' : ''; ?>>Todos os Tipos</option>
                    <option value="caso" <?php echo $filtro_tipo == 'caso' ? 'selected' : ''; ?>>Casos</option>
                    <option value="atividade" <?php echo $filtro_tipo == 'atividade' ? 'selected' : ''; ?>>Atividades</option>
                    <option value="horas" <?php echo $filtro_tipo == 'horas' ? 'selected' : ''; ?>>Horas Trabalhadas</option>
                    <?php if ($pagamentos_existe): ?>
                    <option value="pagamento" <?php echo $filtro_tipo == 'pagamento' ? 'selected' : ''; ?>>Pagamentos</option>
                    <?php endif; ?>
                </select>
                
                <select name="colaborador">
                    <option value="">Todos os Colaboradores</option>
                    <?php foreach ($colaboradores as $id => $nome): ?>
                        <option value="<?php echo $id; ?>" <?php echo $filtro_colaborador == $id ? 'selected' : ''; ?>><?php echo $nome; ?></option>
                    <?php endforeach; ?>
                </select>
                
                <input type="date" name="data_inicio" placeholder="Data Início" value="<?php echo $filtro_data_inicio; ?>">
                <input type="date" name="data_fim" placeholder="Data Fim" value="<?php echo $filtro_data_fim; ?>">
                
                <button type="submit">Filtrar</button>
            </form>
            
            <?php if (!empty($mensagem)): ?>
                <div class="alert"><?php echo $mensagem; ?></div>
            <?php endif; ?>
            
            <?php if (empty($atividades) && empty($mensagem)): ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <p>Nenhuma atividade encontrada. Tente ajustar os filtros.</p>
                </div>
            <?php elseif (!empty($atividades)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Título</th>
                            <th>Descrição</th>
                            <th>Data</th>
                            <th>Colaborador</th>
                            <th>Cliente</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($atividades as $atividade): ?>
                            <tr>
                                <td>
                                    <?php if ($atividade['tipo'] == 'caso'): ?>
                                        <span class="badge badge-caso">Caso</span>
                                    <?php elseif ($atividade['tipo'] == 'atividade'): ?>
                                        <span class="badge badge-atividade">Atividade</span>
                                    <?php elseif ($atividade['tipo'] == 'horas'): ?>
                                        <span class="badge badge-horas">Horas</span>
                                    <?php elseif ($atividade['tipo'] == 'pagamento'): ?>
                                        <span class="badge badge-pagamento">Pagamento</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($atividade['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($atividade['descricao'] ?? 'N/A'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($atividade['data'])); ?></td>
                                <td><?php echo htmlspecialchars($atividade['utilizador']); ?></td>
                                <td><?php echo htmlspecialchars($atividade['cliente']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

<footer>
  <div class="footer-content">
    <div class="footer-text">CONSULTAR ATIVIDADE DO SISTEMA</div>
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



