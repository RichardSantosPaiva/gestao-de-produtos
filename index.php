<?php
// Inclui a conexão com o banco de dados
include 'conexao_db.php'; 

// Consulta para buscar os produtos
$sql_produtos = "
   SELECT p.id_produtos, p.nome AS nome_produto, p.preco, p.id_categoria, e.quantidade, p.foto, f.nome AS nome_fornecedor, f.contato AS contato_fornecedor 
   FROM produtos p
   JOIN estoque_produto e ON p.id_produtos = e.id_produto
   JOIN fornecedor_produtos fp ON p.id_produtos = fp.id_produto
   JOIN fornecedor f ON fp.id_fornecedor = f.id_fornecedor"; // Inclui o nome do fornecedor e contato
$result_produtos = $conn->query($sql_produtos);

if (!$result_produtos) {
    die("Erro ao buscar produtos: " . $conn->error);
}

// Consulta para buscar as categorias para o dropdown
$sql_categorias = "SELECT id_categorias, categoria FROM categorias";
$result_categorias = $conn->query($sql_categorias);

if (!$result_categorias) {
    die("Erro ao buscar categorias: " . $conn->error);
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recupera os dados do formulário
    $nome_fornecedor = $_POST['nome_fornecedor'];
    $contato_fornecedor = $_POST['contato_fornecedor'];
    $categoria = $_POST['categoria'];
    $produto = $_POST['produto'];
    $preco_produto = $_POST['preco'];
    $quantidade_estoque = $_POST['quantidade'];
    
    // Verifica se o arquivo foi enviado corretamente
    if (isset($_FILES['foto_produto']) && $_FILES['foto_produto']['error'] === UPLOAD_ERR_OK) {
        $foto_tmp = $_FILES['foto_produto']['tmp_name'];
        $foto_nome = $_FILES['foto_produto']['name'];
        $foto_extensao = pathinfo($foto_nome, PATHINFO_EXTENSION);
        $novo_nome_foto = uniqid() . '.' . $foto_extensao; // Cria um nome único para a foto
        
        // Define o diretório onde a imagem será salva
        $diretorio_fotos = 'uploads/'; // Crie uma pasta "uploads" no seu projeto
        $caminho_foto = $diretorio_fotos . $novo_nome_foto;
        
        // Move o arquivo para o diretório de uploads
        move_uploaded_file($foto_tmp, $caminho_foto);
    } else {
        echo "Erro ao enviar a foto.";
        exit;
    }

    // Inicia a transação
    $conn->begin_transaction();

    try {
        // Insere o fornecedor
        $sql_fornecedor = "INSERT INTO fornecedor (nome, contato) VALUES (?, ?)";
        $stmt_fornecedor = $conn->prepare($sql_fornecedor);
        $stmt_fornecedor->bind_param("ss", $nome_fornecedor, $contato_fornecedor);
        $stmt_fornecedor->execute();
        $id_fornecedor = $stmt_fornecedor->insert_id;

        // Insere o produto, incluindo o caminho da foto
        $sql_produto = "INSERT INTO produtos (nome, preco, id_categoria, foto) VALUES (?, ?, ?, ?)";
        $stmt_produto = $conn->prepare($sql_produto);
        $stmt_produto->bind_param("sdis", $produto, $preco_produto, $categoria, $caminho_foto);
        $stmt_produto->execute();
        $id_produto = $stmt_produto->insert_id;

        // Vincula o produto ao fornecedor
        $sql_fornecedor_produto = "INSERT INTO fornecedor_produtos (id_fornecedor, id_produto) VALUES (?, ?)";
        $stmt_fornecedor_produto = $conn->prepare($sql_fornecedor_produto);
        $stmt_fornecedor_produto->bind_param("ii", $id_fornecedor, $id_produto);
        $stmt_fornecedor_produto->execute();

        // Insere o estoque
        $sql_estoque = "INSERT INTO estoque_produto (id_produto, quantidade) VALUES (?, ?)";
        $stmt_estoque = $conn->prepare($sql_estoque);
        $stmt_estoque->bind_param("ii", $id_produto, $quantidade_estoque);
        $stmt_estoque->execute();

        // Confirma a transação
        $conn->commit();

        echo "Dados e foto inseridos com sucesso!";
    } catch (Exception $e) {
        // Desfaz a transação em caso de erro
        $conn->rollback();
        echo "Erro ao inserir dados: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html>
<body>
    <p><a href="registrar_categoria.php">Caso não possua uma categoria:</a></p>
    
    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
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
        
        <!-- Campo de upload da foto -->
        <input type="file" name="foto_produto" accept="image/*" required />

        <button type="submit">Enviar</button>
    </form>

    <h2>Lista de Produtos</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nome do Produto</th>
            <th>Preço</th>
            <th>ID Categoria</th>
            <th>Quantidade</th>
            <th>Foto</th> <!-- Nova coluna para a foto -->
            <th>Nome do Fornecedor</th> <!-- Coluna para o nome do fornecedor -->
            <th>Contato do Fornecedor</th> <!-- Coluna para o contato do fornecedor -->
            <th>Ações</th>
        </tr>
        <?php 
        if ($result_produtos->num_rows > 0) {
            while ($row = $result_produtos->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id_produtos']}</td>
                        <td>{$row['nome_produto']}</td>
                        <td>{$row['preco']}</td>
                        <td>{$row['id_categoria']}</td>
                        <td>{$row['quantidade']}</td>
                        <td><img src='{$row['foto']}' width='100' /></td> <!-- Exibe a foto -->
                        <td>{$row['nome_fornecedor']}</td> <!-- Exibe o nome do fornecedor -->
                        <td>{$row['contato_fornecedor']}</td> <!-- Exibe o contato do fornecedor -->
                        <td>
                            <a href='editar_produto.php?id={$row['id_produtos']}'>Editar</a>
                            <a href='excluir_produto.php?id={$row['id_produtos']}'>Deletar</a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='9'>Nenhum produto encontrado</td></tr>";
        }
        ?>
    </table>
</body>
</html>
