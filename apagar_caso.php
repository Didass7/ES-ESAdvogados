<!-- filepath: c:\xampp\htdocs\TP1_LPI\ESAdvogados\apagar_caso.php -->
<?php
include 'basedados.h';

if (isset($_GET['id'])) {
    $id_caso = intval($_GET['id']); // Garante que o ID seja um número inteiro

    // Query para apagar o caso
    $query = "DELETE FROM casos_juridicos WHERE id = $id_caso";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Caso jurídico apagado com sucesso!'); window.location.href='gerir_caso.php';</script>";
    } else {
        echo "<script>alert('Erro ao apagar o caso jurídico: " . mysqli_error($conn) . "'); window.location.href='gerir_caso.php';</script>";
    }
} else {
    echo "<script>alert('ID do caso não fornecido!'); window.location.href='gerir_caso.php';</script>";
}
?>