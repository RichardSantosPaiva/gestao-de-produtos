<?php
include 'conexao_db.php'; // Inclui o arquivo de conexão com o banco de dados

if (isset($_GET['id'])) {
    $id_produto = $_GET['id'];

    // Inicia uma transação
    $conn->begin_transaction();

    try {
        // Exclui o produto da tabela de estoque
        $sql_estoque = "DELETE FROM estoque_produto WHERE id_produto = ?";
        $stmt_estoque = $conn->prepare($sql_estoque);
        $stmt_estoque->bind_param("i", $id_produto);
        $stmt_estoque->execute();

        // Exclui o relacionamento do produto com o fornecedor
        $sql_fornecedor_produto = "DELETE FROM fornecedor_produtos WHERE id_produto = ?";
        $stmt_fornecedor_produto = $conn->prepare($sql_fornecedor_produto);
        $stmt_fornecedor_produto->bind_param("i", $id_produto);
        $stmt_fornecedor_produto->execute();

        // Exclui o produto da tabela de produtos
        $sql_produto = "DELETE FROM produtos WHERE id_produtos = ?";
        $stmt_produto = $conn->prepare($sql_produto);
        $stmt_produto->bind_param("i", $id_produto);
        $stmt_produto->execute();

        // Confirma a transação
        $conn->commit();

        echo "Produto excluído com sucesso!";
        header("Location: index.php"); // Redireciona de volta para a página principal
        exit();
    } catch (Exception $e) {
        $conn->rollback(); // Reverte a transação em caso de erro
        echo "Erro ao excluir o produto: " . $e->getMessage();
    }
}

$conn->close();
?>
