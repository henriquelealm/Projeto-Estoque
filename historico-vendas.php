<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Histórico de Vendas</title>
    <link rel="stylesheet" href="style/historico-vendas.css">
</head>
<body>
    <h1>Histórico de Vendas</h1>
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
        // Conexão com o banco de dados
        $pdo = new PDO("mysql:host=localhost;dbname=projeto_login", "root", "Hlm@1507");

        $sql = "SELECT venda.id, venda.data_venda, funcionario.nome AS funcionario, venda.tipo_pagamento, cliente.nome AS cliente_nome, cliente.telefone AS cliente_telefone
        FROM venda
        JOIN funcionario ON venda.funcionario_id = funcionario.id
        LEFT JOIN cliente ON venda.id_cliente = cliente.id";

        $stmt = $pdo->query($sql);

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
            
            // Adicione placeholders para o endereço do cliente
            echo "<td>";
            echo "<p><strong>Endereço:</strong> [Rua, Número, Cidade, Estado, CEP, Complemento]</p>";
            echo "</td>";

            echo "</tr>";
        }
        ?>
    </table>
</body>
</html>
