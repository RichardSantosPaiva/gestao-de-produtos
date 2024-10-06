<?php
// Inclui a conexão com o banco de dados
include 'conexao_db.php';
if (isset($_GET['id'])) {
    $id_produto = $_GET['id'];

    // Inicie a transação
    $conn->begin_transaction();

    try {
        // Primeiro, remova as associações do fornecedor
        $sql_fornecedor_produtos = "DELETE FROM fornecedor_produtos WHERE id_produto = ?";
        $stmt_fornecedor_produtos = $conn->prepare($sql_fornecedor_produtos);
        $stmt_fornecedor_produtos->bind_param("i", $id_produto);
        $stmt_fornecedor_produtos->execute();

        // Em seguida, remova o estoque
        $sql_estoque = "DELETE FROM estoque_produto WHERE id_produto = ?";
        $stmt_estoque = $conn->prepare($sql_estoque);
        $stmt_estoque->bind_param("i", $id_produto);
        $stmt_estoque->execute();

        // Por fim, exclua o produto
        $sql_delete = "DELETE FROM produtos WHERE id_produtos = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $id_produto);
        $stmt_delete->execute();

        // Confirme a transação
        $conn->commit();

        echo "Produto excluído com sucesso!";
    } catch (Exception $e) {
        // Desfaça a transação em caso de erro
        $conn->rollback();
        echo "Erro ao excluir produto: " . $e->getMessage();
    }
}

?>
