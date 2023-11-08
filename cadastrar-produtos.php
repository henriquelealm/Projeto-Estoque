<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("location: index.php");
    exit;
}

// Conecte-se ao banco de dados
require_once 'config.php';

// Suponhamos que o nome do usuário esteja disponível em $_SESSION['nome_usuario']
// Consulta para obter o nome do usuário
$idUsuario = $_SESSION['id_usuario'];
$stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id_usuario = :id");
$stmt->bindValue(":id", $idUsuario);
$stmt->execute();

// Verifique se a consulta foi bem-sucedida
if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch();
    $nomeDoUsuario = $row['nome'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture os dados do formulário
    $categoria = $_POST['categoria'];
    $nome = $_POST['nome'];
    $data_validade = $_POST['data_validade'];
    $quantidade_unidades = $_POST['quantidade_unidades'];
    $preco_compra = $_POST['preco_compra'];
    $preco_venda = $_POST['preco_venda'];

    // Obtenha a data de entrada atual no formato apropriado
    $data_entrada = date('Y-m-d H:i:s');

    // SQL para inserir o produto na tabela apropriada (bebida ou comida)
    if ($categoria === 'bebida') {
        $sql = "INSERT INTO bebida (categoria, nome, nome_usuario, data_entrada, data_validade, quantidade_unidades, preco_compra, preco_venda) VALUES (:categoria, :nome, :nome_usuario, :data_entrada, :data_validade, :quantidade_unidades, :preco_compra, :preco_venda)";
    } elseif ($categoria === 'comida') {
        $sql = "INSERT INTO comida (tipo, nome, nome_usuario, data_entrada, data_validade, quantidade_unidades, preco_compra, preco_venda) VALUES (:categoria, :nome, :nome_usuario, :data_entrada, :data_validade, :quantidade_unidades, :preco_compra, :preco_venda)";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":categoria", $categoria);
    $stmt->bindValue(":nome", $nome);
    $stmt->bindValue(":nome_usuario", $nomeDoUsuario); // Usar o nome obtido na consulta
    $stmt->bindValue(":data_entrada", $data_entrada);
    $stmt->bindValue(":data_validade", $data_validade);
    $stmt->bindValue(":quantidade_unidades", $quantidade_unidades);
    $stmt->bindValue(":preco_compra", $preco_compra);
    $stmt->bindValue(":preco_venda", $preco_venda);

    if ($stmt->execute()) {
        // Produto cadastrado com sucesso
        header("location: areaPrivada.php"); // Redirecionar para a página inicial
        exit;
    } else {
        // Ocorreu um erro ao cadastrar o produto
        echo "Erro ao cadastrar o produto.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Cadastrar Produtos</title>
    <link rel="stylesheet" href="style/cadastrar-produtos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
</head>
<style>
       .voltar-icon {
            position: absolute;
            top: 10px;
            left: 10px;
            font-size: 24px;
            cursor: pointer;
        }

        .btn-voltar {
            margin-top: 30px;
        }
    </style>
<body>
    <i class="fas fa-arrow-left voltar-icon" onclick="window.location.href='areaPrivada.php'"></i> 
    <div class="content">
        <h1>Cadastrar Produto</h1>
        <br>
        <form action="cadastrar-produtos.php" method="POST">
            <input type="radio" name="categoria" id="categoria-bebida" value="bebida" required>
            <label for="categoria-bebida">Bebida</label>
            <input type="radio" name="categoria" id="categoria-comida" value="comida" required>
            <label for="categoria-comida">Comida</label>

            <br> <!-- Quebra de linha adicionada aqui -->

            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" required">
            <label for="data_validade">Data de Validade:</label>
            <input type="date" name="data_validade" id="data_validade" required>
            <label for="quantidade_unidades">Quantidade de Unidades:</label>
            <input type="number" name="quantidade_unidades" id="quantidade_unidades" required>
            <label for="preco_compra">Preço de Compra:</label>
            <input type="text" name= "preco_compra" id="preco_compra" required>
            <label for="preco_venda">Preço de Venda:</label>
            <input type="text" name="preco_venda" id="preco_venda" required>
            <input type="submit" value="Cadastrar Produto">
        </form>
    </div>
</body>
</html>
