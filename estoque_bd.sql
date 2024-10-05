drop database estoque_produtos;
CREATE DATABASE estoque_produtos;

-- Usar o banco de dados recém-criado
USE estoque_produtos;

-- Criação da tabela fornecedor
CREATE TABLE fornecedor (
    id_fornecedor INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    contato VARCHAR(20)
);

-- Criação da tabela categorias
CREATE TABLE categorias (
    id_categorias INT AUTO_INCREMENT PRIMARY KEY,
    categoria VARCHAR(100)
);

-- Criação da tabela produtos
CREATE TABLE produtos (
    id_produtos INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    preco DECIMAL(10,2),
    id_categoria INT,
    CONSTRAINT fk_id_categoria FOREIGN KEY (id_categoria) 
        REFERENCES categorias(id_categorias)
);

-- Criação da tabela fornecedor_produtos (tabela intermediária entre fornecedor e produtos)
CREATE TABLE fornecedor_produtos (
    id_fornecedor_produtos INT AUTO_INCREMENT PRIMARY KEY,
    id_fornecedor INT,
    id_produto INT,
    CONSTRAINT fk_id_fornecedor FOREIGN KEY (id_fornecedor) 
        REFERENCES fornecedor(id_fornecedor),
    CONSTRAINT fk_id_produto FOREIGN KEY (id_produto) 
        REFERENCES produtos(id_produtos)
);

-- Criação da tabela estoque_produto
CREATE TABLE estoque_produto (
    id_estoque INT AUTO_INCREMENT PRIMARY KEY,
    id_produto INT,
    quantidade INT,
    CONSTRAINT fk_id_produto_estoque FOREIGN KEY (id_produto) 
        REFERENCES produtos(id_produtos)
);

/*-- ---------------------------------------------------------------- -*/ 
SELECT * FROM fornecedor;
SELECT * FROM categorias;
SELECT * FROM produtos;

/*
4. Consulta com Junção (JOIN)
Se você deseja combinar informações de diferentes tabelas, como listar os produtos com seus respectivos fornecedores, você pode usar JOIN:
*/

SELECT 
    f.nome AS nome_fornecedor, 
    p.nome AS nome_produto, 
    c.categoria AS categoria 
FROM 
    fornecedor f
JOIN 
    fornecedor_produtos fp ON f.id_fornecedor = fp.id_fornecedor
JOIN 
    produtos p ON fp.id_produto = p.id_produtos
JOIN 
    categorias c ON p.id_categoria = c.id_categorias;


/*
5. Obter Estoque de Produtos
Para obter a quantidade em estoque de cada produto:
*/

SELECT 
    p.nome AS nome_produto, 
    e.quantidade AS quantidade_estoque 
FROM 
    estoque_produto e
JOIN 
    produtos p ON e.id_produto = p.id_produtos;

/*
6. Filtrar Resultados
Se você quiser filtrar resultados, como buscar produtos de uma categoria específica, use o WHERE:
*/

SELECT 
    p.nome AS nome_produto, 
    p.preco AS preco 
FROM 
    produtos p
JOIN 
    categorias c ON p.id_categoria = c.id_categorias
WHERE 
    c.categoria = 'produtos de higiene';  -- Substitua pelo nome da categoria desejada



/*
7. Ordenar Resultados
Para ordenar os resultados, você pode usar ORDER BY. Por exemplo, para listar todos os produtos em ordem crescente de preço:
*/

SELECT 
    nome, 
    preco 
FROM 
    produtos 
ORDER BY 
    preco ASC;
    
