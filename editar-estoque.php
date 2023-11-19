<?php
// Conecte-se ao banco de dados
require_once 'config.php';

// Verifique se o formulário foi enviado para salvar as alterações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        $categoria = $_POST['categoria'];
        $nome = $_POST['nome'];
        $data_entrada = $_POST['data_entrada'];
        $data_validade = $_POST['data_validade'];
        $quantidade_unidades = $_POST['quantidade_unidades'];
        $preco_compra = $_POST['preco_compra'];
        $preco_venda = $_POST['preco_venda'];
        $nome_usuario = $_POST['nome_usuario']; // Novo campo para o nome do usuário

        // Verifique se a categoria é válida ('bebida' ou 'comida')
        if ($categoria === 'bebida') {
            // Atualize os dados no banco de dados
            $stmt = $pdo->prepare("UPDATE " . $categoria . " SET nome = ?, data_entrada = ?, data_validade = ?, quantidade_unidades = ?, preco_compra = ?, preco_venda = ?, nome_usuario = ? WHERE id = ?");
            $stmt->execute([$nome, $data_entrada, $data_validade, $quantidade_unidades, $preco_compra, $preco_venda, $nome_usuario, $id]);

            // Redirecione de volta para a página de estoque correta após a atualização
            if ($categoria === 'bebida') {
                header("location: editar-estoque.php");
            } else if ($categoria === 'comida') {
                header("location: editar-estoque.php");
            }
            exit;
        }
        if ($categoria === 'comida') {
            // Atualize os dados no banco de dados
            $stmt = $pdo->prepare("UPDATE " . $categoria . " SET nome = ?, data_entrada = ?, data_validade = ?, quantidade_unidades = ?, preco_compra = ?, preco_venda = ?, nome_usuario = ? WHERE id = ?");
            $stmt->execute([$nome, $data_entrada, $data_validade, $quantidade_unidades, $preco_compra, $preco_venda, $nome_usuario, $id]);

            // Redirecione de volta para a página de estoque correta após a atualização
            if ($categoria === 'bebida') {
                header("location: editar-estoque.php");
            } else if ($categoria === 'comida') {
                header("location: editar-estoque.php");
            }
            exit;
        } else {
            echo "Categoria inválida. Escolha 'bebida' ou 'comida'.";
        }
    }
}

// Consulta SQL para selecionar todos os registros de bebida
$sqlBebida = "SELECT * FROM bebida";

// Consulta SQL para selecionar todos os registros de comida
$sqlComida = "SELECT * FROM comida";

// Execute as consultas SQL
$resultadoBebida = $pdo->query($sqlBebida);
$resultadoComida = $pdo->query($sqlComida);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta charset="utf-8">
    <title>Editar Estoque</title>
    <link rel="stylesheet" href="style/editar-estoque.css">
</head>

<body>
    <i class="fas fa-arrow-left voltar-icon" onclick="window.location.href='areaPrivada.php'"></i> <!-- Ícone de seta com link para áreaPrivada.php -->


    <div class="content">
        <h1>Editar Estoque</h1>
        <br>
        <h2>Bebidas</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Categoria</th>
                <th>Nome</th>
                <th>Data de Entrada</th>
                <th>Data de Validade</th>
                <th>Quantidade de Unidades</th>
                <th>Preço de Compra</th>
                <th>Preço de Venda</th>
                <th>Usuário Cadastrou</th> <!-- Novo campo para exibir o usuário que cadastrou -->
                <th>Ação</th>
            </tr>
            <?php
            foreach ($resultadoBebida as $row) {
                echo "<tr>";
                echo "<form method='POST' action='editar-estoque.php'>";
                echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
                echo "<input type='hidden' name='categoria' value='bebida'>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>Bebida</td>";
                echo "<td><input type='text' name='nome' value='" . $row['nome'] . "'></td>";
                echo "<td><input type='text' name='data_entrada' value='" . $row['data_entrada'] . "'></td>";
                echo "<td><input type='text' name='data_validade' value='" . $row['data_validade'] . "'></td>";
                echo "<td><input type='text' name='quantidade_unidades' value='" . $row['quantidade_unidades'] . "'></td>";
                echo "<td><input type='text' name='preco_compra' value='" . $row['preco_compra'] . "'></td>";
                echo "<td><input type='text' name='preco_venda' value='" . $row['preco_venda'] . "'></td>";
                echo "<td><input type='text' name='nome_usuario' value='" . $row['nome_usuario'] . "'></td>"; // Campo para exibir o usuário
                echo "<td><input type='submit' value='Salvar'></td>";
                echo "</form>";
                echo "</tr>";
            }
            ?>
        </table>

        <h2>Comidas</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Tipo</th>
                <th>Nome</th>
                <th>Data de Entrada</th>
                <th>Data de Validade</th>
                <th>Quantidade de Unidades</th>
                <th>Preço de Compra</th>
                <th>Preço de Venda</th>
                <th>Usuário Cadastrou</th> <!-- Novo campo para exibir o usuário que cadastrou -->
                <th>Ação</th>
            </tr>
            <?php
            foreach ($resultadoComida as $row) {
                echo "<tr>";
                echo "<form method='POST' action='editar-estoque.php'>";
                echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
                echo "<input type='hidden' name='categoria' value='comida'>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>Comida</td>";
                echo "<td><input type='text' name='nome' value='" . $row['nome'] . "'></td>";
                echo "<td><input type='text' name='data_entrada' value='" . $row['data_entrada'] . "'></td>";
                echo "<td><input type='text' name='data_validade' value='" . $row['data_validade'] . "'></td>";
                echo "<td><input type='text' name='quantidade_unidades' value='" . $row['quantidade_unidades'] . "'></td>";
                echo "<td><input type='text' name='preco_compra' value='" . $row['preco_compra'] . "'></td>";
                echo "<td><input type='text' name='preco_venda' value='" . $row['preco_venda'] . "'></td>";
                echo "<td><input type='text' name='nome_usuario' value='" . $row['nome_usuario'] . "'></td>"; // Campo para exibir o usuário
                echo "<td><input type='submit' value='Salvar'></td>";
                echo "</form>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
</body>

</html>