<?php
session_start();

if (isset($_SESSION['id_usuario'])) {
    // Conecte-se ao banco de dados
    require_once 'config.php';

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

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch();
        $custoTotalNoMes = $row['custo_total_combinado'];
    } else {
        $custoTotalNoMes = 0; // Valor padrão caso não haja resultados
    }

    echo "Custo Total no Mês: " . $custoTotalNoMes . "<br>";

    // Consulta para obter o total de vendas no mês
    $stmt = $pdo->query("SELECT SUM(total_venda) AS total_vendas_mes FROM venda_comida");
    $stmt2 = $pdo->query("SELECT SUM(total_venda) AS total_vendas_mes2 FROM venda_bebida");
    $row = $stmt->fetch();
    $row2 = $stmt2->fetch();
    $totalVendasNoMes = $row['total_vendas_mes'] + $row2['total_vendas_mes2'];

    echo "Total de Vendas no Mês: " . $totalVendasNoMes . "<br>";



    // Consulta para obter a margem de lucro média para comida
    $stmt5 = $pdo->query("SELECT
        'Comida' AS produto_tipo,
        AVG((preco_venda - preco_compra) / preco_compra) AS margem_lucro_mediaC
    FROM comida");

    if ($stmt5->rowCount() > 0) {
        $row5 = $stmt5->fetch();
        $margemLucroMediaC = $row5['margem_lucro_mediaC'] ?? 'Nenhuma margem calculada';
    } else {
        $margemLucroMediaC = 'Nenhuma margem calculada';
    }

    echo "Margem de Lucro Média para Comida: " . $margemLucroMediaC . "<br>";

    // Consulta para obter a margem de lucro média para bebida
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

    echo "Margem de Lucro Média para Bebida: " . $margemLucroMediaB . "<br>";

} else {
    echo "Você não está autenticado.";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Relatório</title>
    <!-- Inclua as bibliotecas do Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>Relatório de Vendas e Lucro</h1>
    
    <div>
        <h2>Custo Total no Mês</h2>
        <canvas id="custoTotalChart" width="400" height="200"></canvas>
    </div>

    <div>
        <h2>Total de Vendas no Mês</h2>
        <canvas id = "totalVendasChart" width="400" height="200"></canvas>
    </div>

    <div>
        <h2>Margem de Lucro Média para Comida</h2>
        <canvas id="margemLucroMediaCChart" width="400" height="200"></canvas>
    </div>

    <div>
        <h2>Margem de Lucro Média para Bebida</h2>
        <canvas id="margemLucroMediaBChart" width="400" height="200"></canvas>
    </div>



    <script>
        // PHP Variáveis para passar dados para JavaScript
        var custoTotalNoMes = <?php echo $custoTotalNoMes; ?>;
        var totalVendasNoMes = <?php echo $totalVendasNoMes; ?>;
        var margemLucroMediaC = <?php echo $margemLucroMediaC; ?>;
        var margemLucroMediaB = <?php echo $margemLucroMediaB; ?>;
        var lucroMaximo = '<?php echo $lucroMaximo; ?>';
        var lucroMinimo = '<?php echo $lucroMinimo; ?>';

        // Crie os gráficos usando o Chart.js
        var ctxCustoTotal = document.getElementById('custoTotalChart').getContext('2d');
        var custoTotalChart = new Chart(ctxCustoTotal, {
            type: 'bar',
            data: {
                labels: ['Custo Total no Mês'],
                datasets: [{
                    label: 'Custo Total no Mês',
                    data: [custoTotalNoMes],
                    backgroundColor: 'blue'
                }]
            }
        });

        var ctxTotalVendas = document.getElementById('totalVendasChart').getContext('2d');
        var totalVendasChart = new Chart(ctxTotalVendas, {
            type: 'bar',
            data: {
                labels: ['Total de Vendas no Mês'],
                datasets: [{
                    label: 'Total de Vendas no Mês',
                    data: [totalVendasNoMes],
                    backgroundColor: 'green'
                }]
            }
        });




        var ctxMargemLucroMediaC = document.getElementById('margemLucroMediaCChart').getContext('2d');
        var margemLucroMediaCChart = new Chart(ctxMargemLucroMediaC, {
            type: 'bar',
            data: {
                labels: ['Margem de Lucro Média para Comida'],
                datasets: [{
                    label: 'Margem de Lucro Média para Comida',
                    data: [margemLucroMediaC],
                    backgroundColor: 'red'
                }]
            }
        });

        var ctxMargemLucroMediaB = document.getElementById('margemLucroMediaBChart').getContext('2d');
        var margemLucroMediaBChart = new Chart(ctxMargemLucroMediaB, {
            type: 'bar',
            data: {
                labels: ['Margem de Lucro Média para Bebida'],
                datasets: [{
                    label: 'Margem de Lucro Média para Bebida',
                    data: [margemLucroMediaB],
                    backgroundColor: 'brown'
                }]
            }
        });


    </script>
</body>
</html>
