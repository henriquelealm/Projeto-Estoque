<?php
session_start();

if (isset($_SESSION['id_usuario'])) {
    // Conecte-se ao banco de dados
    require_once 'config.php';

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

    // Consulta para obter o custo total no mês
    $stmt = $pdo->query("SELECT (custo_total_total + custo_total) AS custo_total_combinado
        FROM (
            SELECT SUM(custo_total) AS custo_total_total
            FROM (
                SELECT SUM(preco_compra * quantidade_unidades) AS custo_total 
                FROM comida
                WHERE DATE_FORMAT(data_entrada, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')
                UNION ALL
                SELECT SUM(preco_compra * quantidade_unidades) AS custo_total 
                FROM bebida
                WHERE DATE_FORMAT(data_entrada, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')
            ) AS subquery
        ) AS subquery_total
        CROSS JOIN (
            SELECT SUM(
                CASE
                    WHEN vc.bebida_id IS NOT NULL THEN
                        bb.preco_compra * vc.quantidade
                    WHEN cc.comida_id IS NOT NULL THEN
                        co.preco_compra * cc.quantidade
                    ELSE
                        0
                END
            ) AS custo_total
            FROM venda AS v
            LEFT JOIN venda_comida AS cc ON v.id = cc.venda_id
            LEFT JOIN comida AS co ON cc.comida_id = co.id
            LEFT JOIN venda_bebida AS vc ON v.id = vc.venda_id
            LEFT JOIN bebida AS bb ON vc.bebida_id = bb.id
        ) AS subquery_custo");

    // Verifique se a consulta do custo total no mês foi bem-sucedida
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch();
        $custoTotalNoMes = $row['custo_total_combinado'];
    } else {
        $custoTotalNoMes = 0; // Valor padrão caso não haja resultados
    }

    // Consulta para obter o total de vendas no mês
    $stmt = $pdo->query("SELECT SUM(total_venda) AS total_vendas_mes FROM venda_comida");
    $stmt2 = $pdo->query("SELECT SUM(total_venda) AS total_vendas_mes2 FROM venda_bebida");
    $row = $stmt->fetch();
    $row2 = $stmt2->fetch();
    $totalVendasNoMes = $row['total_vendas_mes'] + $row2['total_vendas_mes2'];

$stmt3 = $pdo->query("SELECT bebida.nome AS nome_bebida, SUM(venda_bebida.quantidade) AS total_vendido
        FROM venda_bebida
        LEFT JOIN bebida ON venda_bebida.bebida_id = bebida.id
        GROUP BY bebida.id
        ORDER BY total_vendido DESC
        LIMIT 1");

    if ($stmt3->rowCount() > 0) {
        $row3 = $stmt3->fetch();
        $bebidaMaisVendida = $row3['nome_bebida'] . ' (' . $row3['total_vendido'] . ' unidades)';
    } else {
        $bebidaMaisVendida = 'Nenhuma bebida vendida.';
    }

    $stmt4 = $pdo->query("SELECT comida.nome AS nome_comida, SUM(venda_comida.quantidade) AS total_vendido
        FROM venda_comida
        LEFT JOIN comida ON venda_comida.comida_id = comida.id
        GROUP BY comida.id
        ORDER BY total_vendido DESC
        LIMIT 1");

    if ($stmt4->rowCount() > 0) {
        $row4 = $stmt4->fetch();
        $comidaMaisVendida = $row4['nome_comida'] . ' (' . $row4['total_vendido'] . ' unidades)';
    } else {
        $comidaMaisVendida = 'Nenhuma comida vendida.';
    }

    $stmt5 = $pdo->query("SELECT
    'Comida' AS produto_tipo,
    AVG((preco_venda - preco_compra) / preco_compra) AS margem_lucro_mediaC
FROM comida

    ");

if ($stmt5->rowCount() > 0) {
    $row5 = $stmt5->fetch();
    $margemLucroMediaC = $row5['margem_lucro_mediaC'] ?? 'Nenhuma margem calculada';
} else {
    $margemLucroMediaC = 'Nenhuma margem calculada';
}

$stmt6 = $pdo->query("SELECT
'Bebida' AS produto_tipo,
AVG((preco_venda - preco_compra) / preco_compra) AS margem_lucro_mediaB
FROM bebida;");

if ($stmt6->rowCount() > 0) {
$row6 = $stmt6->fetch();
$margemLucroMediaB = $row6['margem_lucro_mediaB'] ?? 'Nenhuma margem calculada';
} else {
$margemLucroMediaB = 'Nenhuma margem calculada';
}

$stmt7 = $pdo->query("SELECT
        produto_id,
        nome_produto,
        MAX(lucro_total) AS lucro_maximo
        FROM (
        SELECT
            c.id AS produto_id,
            c.nome AS nome_produto,
            SUM((c.preco_venda * vc.quantidade) - (c.preco_compra * vc.quantidade)) AS lucro_total
        FROM comida c
        JOIN venda_comida vc ON c.id = vc.comida_id
        GROUP BY c.id, c.nome
        UNION ALL
        SELECT
            b.id AS produto_id,
            b.nome AS nome_produto,
            SUM((b.preco_venda * vb.quantidade) - (b.preco_compra * vb.quantidade)) AS lucro_total
        FROM bebida b
        JOIN venda_bebida vb ON b.id = vb.bebida_id
        GROUP BY b.id, b.nome
        ) AS produtos_lucro
        GROUP BY produto_id, nome_produto
        ORDER BY lucro_maximo DESC
        LIMIT 1;");

if ($stmt7->rowCount() > 0) {
    $row7 = $stmt7->fetch();
    $lucroMaximo= $row7['nome_produto'] . '  ( R$ ' . $row7['lucro_maximo'] . ' )' ?? 'Nenhum lucro calculado';
    } else {
    $lucroMaximo = 'Nenhum lucro calculado';
    }
    

$stmt8 = $pdo->query("SELECT
produto_id,
nome_produto,
MIN(lucro_total) AS lucro_minimo
FROM (
SELECT
    c.id AS produto_id,
    c.nome AS nome_produto,
    SUM((c.preco_venda * vc.quantidade) - (c.preco_compra * vc.quantidade)) AS lucro_total
FROM comida c
JOIN venda_comida vc ON c.id = vc.comida_id
GROUP BY c.id, c.nome
UNION ALL
SELECT
    b.id AS produto_id,
    b.nome AS nome_produto,
    SUM((b.preco_venda * vb.quantidade) - (b.preco_compra * vb.quantidade)) AS lucro_total
FROM bebida b
JOIN venda_bebida vb ON b.id = vb.bebida_id
GROUP BY b.id, b.nome
) AS produtos_lucro
GROUP BY produto_id, nome_produto
ORDER BY lucro_minimo ASC
LIMIT 1;
");

if ($stmt8->rowCount() > 0) {
    $row8 = $stmt8->fetch();
    $lucroMinimo= $row8['nome_produto'] . '  ( R$ ' . $row8['lucro_minimo'] . ' )' ?? 'Nenhum lucro calculado';
    } else {
    $lucroMinimo = 'Nenhum lucro calculado';
    }
    
    
} else {
    // Redirecione ou exiba uma mensagem de erro
    header("location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Área Privada</title>
    <link rel="stylesheet" href="style/area-privada.css">
</head>
<body>
<div class="header">
    <div class="user-greeting">
        Olá, <?php echo $nomeDoUsuario; ?>
    </div>
    <div class="date-time" id="date-time">
        Data e Hora
    </div>
</div>
<div class="menu-toggle" onclick="toggleMenu()">☰</div>
<div class="sidebar">
    <nav class="menu">
        <ul>
            <li><a href="visualizar-estoque.php">Visualizar Estoque</a></li>
            <li><a href="cadastrar-cliente.php">Cadastrar Cliente</a></li>
            <li><a href="cadastrar-produtos.php">Cadastrar Produtos</a></li>
            <li><a href="registrar-venda.php">Registrar Venda</a></li>
            <li><a href="historico-vendas.php">Histórico de Vendas</a></li>
            <li><a href="clientes-devendo.php">Clientes Devendo</a></li>
            <li><a href="editar-estoque.php">Editar Estoque</a></li>
            <li><a href="gerar-relatorios.php">Gerar Relatórios</a></li>
            <li><a href="alterar-cliente.php">Alterar Cliente</a></li>
            <li><a href="sair.php">Sair</a></li>
        </ul>
    </nav>
</div>

<!-- Conteúdo da página da área privada -->
<div class="total-gasto">
    <!-- Post-it com o valor Total gasto no mês -->
    <div class="post-it">
        <strong>Total gasto no mês:</strong><br>
        R$ <?php echo number_format($custoTotalNoMes, 2, ',', '.'); ?>
    </div>

    <!-- Post-it com o valor Total de vendas no mês -->
    <div class="post-it2">
        <strong>Total de vendas no mês:</strong><br>
        R$ <?php echo number_format($totalVendasNoMes, 2, ',', '.'); ?>
    </div>

    <!-- Post-it com a bebida mais vendida -->
    <div class="post-it3">
        <strong>Bebida mais vendida:</strong><br>
        <?php echo $bebidaMaisVendida; ?>
    </div>

    <div class="post-it4">
        <strong>Comida mais vendida:</strong><br>
        <?php echo $comidaMaisVendida; ?>
    </div>

    <div class="post-it5">
    <strong>Saldo do mês:</strong><br>
    R$ <?php echo number_format($totalVendasNoMes - $custoTotalNoMes, 2, ',', '.'); ?>
</div>



<div class="post-it6">
    <strong>Margem de lucro média (Comidas):</strong><br>
    <?php echo number_format($margemLucroMediaC *100 , 2, ',', '.'); ?>%
</div>


<div class="post-it7">
    <strong>Margem de lucro média (Bebidas):</strong><br>
    <?php echo number_format($margemLucroMediaB *100 , 2, ',', '.'); ?>%
</div>

<div class="post-it8">
    <strong>Produto mais lucrativo:</strong><br>
    <?php echo ($lucroMaximo); ?>
</div>

<div class="post-it9">
    <strong>Produto menos lucrativo:</strong><br>
    <?php echo ($lucroMinimo); ?>
</div>



</div>

<script>
    function toggleMenu() {
        var sidebar = document.querySelector(".sidebar");
        sidebar.classList.toggle("active");
    }

    function updateDateTime() {
        var dateTimeElement = document.getElementById("date-time");
        var now = new Date();
        var formattedDate = now.toLocaleDateString();
        var formattedTime = now.toLocaleTimeString();
        dateTimeElement.textContent = formattedDate + " " + formattedTime;
    }

    // Atualiza a data e a hora a cada segundo
    setInterval(updateDateTime, 1000);

    // Chama a função para exibir a data e a hora atual
    updateDateTime();
</script>
</body>
</html>
