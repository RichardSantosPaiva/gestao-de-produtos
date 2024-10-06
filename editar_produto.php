<?php
include 'conexao_db.php';

if (isset($_GET['id'])) {
    $id_produto = $_GET['id'];

    // Busca os dados do produto para preencher o formulário de edição
    $sql_produto = "
    SELECT p.nome, p.preco, p.id_categoria, e.quantidade 
    FROM produtos p
    JOIN estoque_produto e ON p.id_produtos = e.id_produto
    WHERE p.id_produtos = ?";
    
    $stmt_produto = $conn->prepare($sql_produto);
    $stmt_produto->bind_param("i", $id_produto);
    $stmt_produto->execute();
    $result_produto = $stmt_produto->get_result();
    $produto = $result_produto->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Recebe os novos valores do formulário de edição
        $nome_produto = $_POST['nome'];
        $preco_produto = $_POST['preco'];
        $categoria = $_POST['categoria'];
        $quantidade_estoque = $_POST['quantidade'];

        // Inicia uma transação
        $conn->begin_transaction();

        try {
            // Atualiza os dados do produto
            $sql_update_produto = "UPDATE produtos SET nome = ?, preco = ?, id_categoria = ? WHERE id_produtos = ?";
            $stmt_update_produto = $conn->prepare($sql_update_produto);
            $stmt_update_produto->bind_param("sdii", $nome_produto, $preco_produto, $categoria, $id_produto);
            $stmt_update_produto->execute();

            // Atualiza a quantidade de estoque
            $sql_update_estoque = "UPDATE estoque_produto SET quantidade = ? WHERE id_produto = ?";
            $stmt_update_estoque = $conn->prepare($sql_update_estoque);
            $stmt_update_estoque->bind_param("ii", $quantidade_estoque, $id_produto);
            $stmt_update_estoque->execute();

            // Confirma a transação
            $conn->commit();

            echo "Produto atualizado com sucesso!";
            header("Location: index.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback(); // Reverte a transação em caso de erro
            echo "Erro ao atualizar o produto: " . $e->getMessage();
        }
    }
}
?>

<!-- Formulário de edição de produto -->
<form method="POST" action="">
    <input type="text" name="nome" value="<?php echo $produto['nome']; ?>" required />
    <input type="number" name="preco" value="<?php echo $produto['preco']; ?>" step="0.01" required />
    <select name="categoria" required>
        <?php
        // Preenche o select de categorias
        $sql_categorias = "SELECT id_categorias, categoria FROM categorias";
        $result_categorias = $conn->query($sql_categorias);
        while ($row = $result_categorias->fetch_assoc()) {
            $selected = ($row['id_categorias'] == $produto['id_categoria']) ? 'selected' : '';
            echo "<option value='{$row['id_categorias']}' $selected>{$row['categoria']}</option>";
        }
        ?>
    </select>
    <input type="number" name="quantidade" value="<?php echo $produto['quantidade']; ?>" required />
    <button type="submit">Salvar Alterações</button>
</form>
