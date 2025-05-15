<?php
include 'basedados.h';

// Função para verificar se uma tabela existe
function table_exists($conn, $table) {
    $check_query = "SHOW TABLES LIKE '$table'";
    $result = mysqli_query($conn, $check_query);
    return mysqli_num_rows($result) > 0;
}

// Função para verificar se uma coluna existe em uma tabela
function column_exists($conn, $table, $column) {
    $check_query = "SHOW COLUMNS FROM $table LIKE '$column'";
    $result = mysqli_query($conn, $check_query);
    return mysqli_num_rows($result) > 0;
}

// Verificar e corrigir a tabela casos_juridicos
if (table_exists($conn, 'casos_juridicos')) {
    echo "<h3>Verificando tabela casos_juridicos...</h3>";
    
    // Verificar coluna id_colaborador
    if (!column_exists($conn, 'casos_juridicos', 'id_colaborador')) {
        // Verificar se existe id_advogado
        if (column_exists($conn, 'casos_juridicos', 'id_advogado')) {
            echo "<p>A coluna 'id_advogado' existe. Renomeando para 'id_colaborador'...</p>";
            $query = "ALTER TABLE casos_juridicos CHANGE id_advogado id_colaborador INT";
            if (mysqli_query($conn, $query)) {
                echo "<p style='color:green'>Coluna renomeada com sucesso!</p>";
            } else {
                echo "<p style='color:red'>Erro ao renomear coluna: " . mysqli_error($conn) . "</p>";
            }
        } else {
            echo "<p>Adicionando coluna 'id_colaborador'...</p>";
            $query = "ALTER TABLE casos_juridicos ADD COLUMN id_colaborador INT";
            if (mysqli_query($conn, $query)) {
                echo "<p style='color:green'>Coluna adicionada com sucesso!</p>";
            } else {
                echo "<p style='color:red'>Erro ao adicionar coluna: " . mysqli_error($conn) . "</p>";
            }
        }
    } else {
        echo "<p style='color:green'>A coluna 'id_colaborador' já existe.</p>";
    }
    
    // Verificar coluna data_criacao
    if (!column_exists($conn, 'casos_juridicos', 'data_criacao')) {
        echo "<p>Adicionando coluna 'data_criacao'...</p>";
        $query = "ALTER TABLE casos_juridicos ADD COLUMN data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP";
        if (mysqli_query($conn, $query)) {
            echo "<p style='color:green'>Coluna adicionada com sucesso!</p>";
            
            // Atualizar registros existentes
            $update_query = "UPDATE casos_juridicos SET data_criacao = NOW() WHERE data_criacao IS NULL";
            mysqli_query($conn, $update_query);
            echo "<p style='color:green'>Registros existentes atualizados com a data atual.</p>";
        } else {
            echo "<p style='color:red'>Erro ao adicionar coluna: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color:green'>A coluna 'data_criacao' já existe.</p>";
    }
} else {
    echo "<p style='color:red'>A tabela 'casos_juridicos' não existe!</p>";
}

// Verificar e corrigir a tabela pagamentos
if (!table_exists($conn, 'pagamentos')) {
    echo "<h3>A tabela 'pagamentos' não existe. Criando...</h3>";
    
    $create_table_query = "CREATE TABLE pagamentos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_caso INT NOT NULL,
        valor DECIMAL(10,2) NOT NULL,
        descricao TEXT,
        data_pagamento DATETIME DEFAULT CURRENT_TIMESTAMP,
        id_registrador INT,
        FOREIGN KEY (id_caso) REFERENCES casos_juridicos(id) ON DELETE CASCADE,
        FOREIGN KEY (id_registrador) REFERENCES utilizador(id_utilizador)
    )";
    
    if (mysqli_query($conn, $create_table_query)) {
        echo "<p style='color:green'>Tabela 'pagamentos' criada com sucesso!</p>";
    } else {
        echo "<p style='color:red'>Erro ao criar tabela 'pagamentos': " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<h3 style='color:green'>A tabela 'pagamentos' já existe.</h3>";
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Estrutura das Tabelas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        h1, h2, h3 {
            color: #5271ff;
        }
        
        .btn {
            display: inline-block;
            background-color: #5271ff;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .btn:hover {
            background-color: #3a5ae8;
        }
        
        .btn-container {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verificação da Estrutura da Base de Dados</h1>
        
        <div class="btn-container">
            <a href="consultar_atividade_sistema.php" class="btn">
                Voltar para Consultar Atividade do Sistema
            </a>
            
            <a href="menu_admin.php" class="btn">
                Voltar para o Menu Principal
            </a>
        </div>
    </div>
</body>
</html>