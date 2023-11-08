<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("location: index.php");
    exit;
}

require_once 'config.php';

$filtro_tipo_pagamento = "";
if (isset($_GET['tipo_pagamento'])) {
    $tipo_pagamento = $_GET['tipo_pagamento'];
    $filtro_tipo_pagamento = " AND venda.tipo_pagamento = '$tipo_pagamento'";
}

$sql = "SELECT venda.id, venda.data_venda, funcionario.nome AS funcionario, venda.tipo_pagamento, cliente.nome AS cliente_nome, cliente.telefone AS cliente_telefone, cliente.endereco_id
        FROM venda
        JOIN funcionario ON venda.funcionario_id = funcionario.id
        LEFT JOIN cliente ON venda.id_cliente = cliente.id
        WHERE 1=1 $filtro_tipo_pagamento
        ORDER BY venda.data_venda DESC";

$stmt = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Histórico de Vendas</title>
    <link rel="stylesheet" href="style/historico-vendas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <i class="fas fa-arrow-left voltar-icon" onclick="window.location.href='areaPrivada.php'"></i>
    <h1>Histórico de Vendas</h1>
    
    <form method="get">
        <label for="tipo_pagamento">Filtrar por Tipo de Pagamento:</label>
        <select name="tipo_pagamento" id="tipo_pagamento">
            <option value="">Todos</option>
            <option value="Cartão">Cartão</option>
            <option value="PIX">PIX</option>
            <option value="Dinheiro">Dinheiro</option>
        </select>
        <button type="submit">Filtrar</button>
    </form>
    
    <table>
        <tr>
            <th>ID da Venda</th>
            <th>Data da Venda</th>
            <th>Funcionário</th>
            <th>Tipo de Pagamento</th>
            <th>Itens Vendidos</th>
            <th>Nome do Cliente</th>
            <th>Telefone do Cliente</th>
            <th>Endereço do Cliente</th>
        </tr>
        <?php
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $venda_id = $row['id'];

            // Consulta para buscar os itens vendidos na venda
            $sql_itens = "SELECT comida.nome AS item_nome, vc.quantidade, vc.total_venda 
                          FROM venda_comida vc
                          JOIN comida ON vc.comida_id = comida.id
                          WHERE vc.venda_id = $venda_id
                          UNION ALL
                          SELECT bebida.nome AS item_nome, vb.quantidade, vb.total_venda 
                          FROM venda_bebida vb
                          JOIN bebida ON vb.bebida_id = bebida.id
                          WHERE vb.venda_id = $venda_id";

            $stmt_itens = $pdo->query($sql_itens);

            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['data_venda'] . "</td>";
            echo "<td>" . $row['funcionario'] . "</td>";
            echo "<td>" . $row['tipo_pagamento'] . "</td>";
            echo "<td>";
            echo "<ul>";
            $total_venda = 0; // Inicialize o total da venda

            while ($row_itens = $stmt_itens->fetch(PDO::FETCH_ASSOC)) {
                echo "<li>{$row_itens['quantidade']}x {$row_itens['item_nome']} (Total: R$ {$row_itens['total_venda']})</li>";
                $total_venda += $row_itens['total_venda']; // Adicione o total do item ao total da venda
            }

            // Adicione um estilo para tornar o texto "Total da Venda" verde
            echo '<li style="color: green;">Total da Venda: R$ ' . number_format($total_venda, 2) . '</li>';
            echo "</ul>";
            echo "</td>";

            // Exibir informações do cliente
            echo "<td>" . $row['cliente_nome'] . "</td>";
            echo "<td>" . $row['cliente_telefone'] . "</td>";

            // Recuperar o endereço do cliente do banco de dados
            $sql_endereco = "SELECT rua, numero, cidade, estado, cep, complemento FROM endereco WHERE id = ?";
            $stmt_endereco = $pdo->prepare($sql_endereco);
            $stmt_endereco->execute([$row['endereco_id']]);
            $endereco = $stmt_endereco->fetch(PDO::FETCH_ASSOC);

            // Exibir o endereço do cliente
            echo "<td>";
            echo "<p><strong>Endereço:</strong> ";
            if (is_array($endereco)) {
                echo "{$endereco['rua']}, {$endereco['numero']}, {$endereco['cidade']}, {$endereco['estado']}, {$endereco['cep']}, {$endereco['complemento']}";
            } else {
                echo "Endereço não disponível";
            }
            echo "</p>";
            echo "</td>";
        }
        ?>
    </table>
</body>
</html>
