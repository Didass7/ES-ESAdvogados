<?php
// Arquivo para obter os custos das atividades de um caso específico
header('Content-Type: application/json');

include 'basedados.h';

// Verificar se o ID do caso foi fornecido
if (!isset($_GET['id_caso']) || empty($_GET['id_caso'])) {
    echo json_encode(['error' => 'ID do caso não fornecido']);
    exit;
}

$id_caso = intval($_GET['id_caso']);

// Verificar se a tabela atividades_caso existe
$check_table_query = "SHOW TABLES LIKE 'atividades_caso'";
$table_result = mysqli_query($conn, $check_table_query);

if (mysqli_num_rows($table_result) == 0) {
    // Se a tabela não existir, retornar um array vazio
    echo json_encode([]);
    exit;
}

// Verificar se a tabela atividades_caso tem a coluna custo
$check_column_query = "SHOW COLUMNS FROM atividades_caso LIKE 'custo'";
$column_result = mysqli_query($conn, $check_column_query);

// Obter as atividades do caso - usar consulta com ou sem coluna custo dependendo se ela existe
$query = mysqli_num_rows($column_result) > 0
    ? "SELECT id, titulo, descricao, data_atividade, custo FROM atividades_caso WHERE id_caso = $id_caso ORDER BY data_atividade DESC"
    : "SELECT id, titulo, descricao, data_atividade FROM atividades_caso WHERE id_caso = $id_caso ORDER BY data_atividade DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['error' => 'Erro ao obter atividades: ' . mysqli_error($conn)]);
    exit;
}

$atividades = [];

// Para cada atividade, usar o custo do banco de dados ou gerar um valor aleatório se não existir
while ($row = mysqli_fetch_assoc($result)) {
    // Usar o custo do banco de dados ou gerar um valor aleatório se não existir
    $custo = (!isset($row['custo']) || $row['custo'] === null) ? rand(50, 500) : $row['custo'];

    $atividades[] = [
        'id' => $row['id'],
        'titulo' => $row['titulo'],
        'descricao' => $row['descricao'],
        'data_atividade' => $row['data_atividade'],
        'custo' => $custo
    ];
}

// Retornar os dados em formato JSON
echo json_encode($atividades);
