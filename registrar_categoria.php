<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>

<body>
  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" >
    <input type="text" name="categoria_produto" placeholder="digite a categoria">
    <button type="submit">enviar</button>
  </form>
</body>
</html>

<?php

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $servername = "localhost";
    $username = "root";
    $password = "";
    $db_name ="estoque_produtos";

    $categoria=  $_POST['categoria_produto'];

    $sql = "INSERT INTO categorias (categoria) values ('".$categoria."')";
 
    $conn = new mysqli($servername, $username, $password,$db_name);

    if($conn->query($sql) == TRUE){
        echo " {$categoria} adicionada com sucesso";
    }else{
      echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
  }
  ?>
