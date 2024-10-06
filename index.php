<?php
include 'conexao_db.php'; // Inclui o arquivo de conexão com o banco de dados

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recupera os dados do formulário
    $nome_fornecedor = $_POST['nome_fornecedor'];
    $contato_fornecedor = $_POST['contato_fornecedor'];
    $categoria = $_POST['categoria'];
    $produto = $_POST['produto'];
    $preco_produto = $_POST['preco'];
    $quantidade_estoque = $_POST['quantidade'];

    // Inicia uma transação para garantir a integridade dos dados
    $conn->begin_transaction();

    try {
        // Insere o fornecedor
        $sql_fornecedor = "INSERT INTO fornecedor (nome, contato) VALUES (?, ?)";
        $stmt_fornecedor = $conn->prepare($sql_fornecedor);
        $stmt_fornecedor->bind_param("ss", $nome_fornecedor, $contato_fornecedor);
        $stmt_fornecedor->execute();
        $id_fornecedor = $stmt_fornecedor->insert_id; // Pega o ID do fornecedor inserido

        // Insere o produto na categoria escolhida
        $sql_produto = "INSERT INTO produtos (nome, preco, id_categoria) VALUES (?, ?, ?)";
        $stmt_produto = $conn->prepare($sql_produto);
        $stmt_produto->bind_param("sdi", $produto, $preco_produto, $categoria);
        $stmt_produto->execute();
        $id_produto = $stmt_produto->insert_id; // Pega o ID do produto inserido

        // Vincula o produto ao fornecedor na tabela intermediária
        $sql_fornecedor_produto = "INSERT INTO fornecedor_produtos (id_fornecedor, id_produto) VALUES (?, ?)";
        $stmt_fornecedor_produto = $conn->prepare($sql_fornecedor_produto);
        $stmt_fornecedor_produto->bind_param("ii", $id_fornecedor, $id_produto);
        $stmt_fornecedor_produto->execute();

        // Insere o estoque do produto
        $sql_estoque = "INSERT INTO estoque_produto (id_produto, quantidade) VALUES (?, ?)";
        $stmt_estoque = $conn->prepare($sql_estoque);
        $stmt_estoque->bind_param("ii", $id_produto, $quantidade_estoque);
        $stmt_estoque->execute();

        // Confirma a transação
        $conn->commit();

        echo "Dados inseridos com sucesso!";
    } catch (Exception $e) {
        // Desfaz a transação em caso de erro
        $conn->rollback();
        echo "Erro ao inserir dados: " . $e->getMessage();
    }
}

// Busca as categorias para preencher o select
$sql_categorias = "SELECT id_categorias, categoria FROM categorias";
$result_categorias = $conn->query($sql_categorias);

// Pesquisa de produtos
$sql_produtos = "
SELECT p.id_produtos, p.nome, p.preco, p.id_categoria, e.quantidade 
FROM produtos p
JOIN estoque_produto e ON p.id_produtos = e.id_produto"; // Inclui a quantidade de estoque
$result_produtos = $conn->query($sql_produtos);


?>

<!DOCTYPE html>
<html>
<body>
  <p><a href="registrar_categoria.php">Caso não possua uma categoria:</a></p>
  
  <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input name="nome_fornecedor" placeholder="Digite o nome do fornecedor" required />
    <input name="contato_fornecedor" placeholder="Digite o contato" required />
    
    <div>
       <label>Categoria:</label>
       <select name="categoria" required>
       <?php
          if ($result_categorias->num_rows > 0) {
              while ($row = $result_categorias->fetch_assoc()) {
                  echo "<option value='{$row['id_categorias']}'>{$row['categoria']}</option>";
              }
          } else {
              echo "<option value=''>Nenhuma categoria encontrada</option>";
          }
       ?>
       </select>
    </div>
    
    <input type="text" name="produto" placeholder="Digite o nome do produto" required />
    <input type="number" name="preco" placeholder="Digite o preço do produto" step="0.01" required />
    <input type="number" name="quantidade" placeholder="Digite a quantidade no estoque" required />
    
    <button type="submit">Enviar</button>
  </form>

  <h2>Lista de Produtos</h2>
  <table border="1">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Preço</th>
        <th>ID Categoria</th>
        <th>Quantidade</th> <!-- Nova coluna para a quantidade -->
        <th>Ações</th> <!-- Nova coluna para ações -->
    </tr>
    <?php 
    if ($result_produtos->num_rows > 0) {
        while ($row = $result_produtos->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id_produtos']}</td>
                    <td>{$row['nome']}</td>
                    <td>{$row['preco']}</td>
                    <td>{$row['id_categoria']}</td>
                    <td>{$row['quantidade']}</td> <!-- Exibir a quantidade -->
                    <td>
                        <a href='editar_produto.php?id={$row['id_produtos']}'>Editar</a> | 
                        <a href='excluir_produto.php?id={$row['id_produtos']}' onclick='return confirm(\"Tem certeza que deseja excluir este produto?\");'>Excluir</a>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='6'>Nenhum produto encontrado</td></tr>";
    }
    ?>
</table>

</body>
</html>

<?php 
$conn->close(); // Fecha a conexão com o banco de dados
?>
