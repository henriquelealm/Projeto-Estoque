<?php
// Conecte-se ao banco de dados
$pdo = new PDO("mysql:host=localhost;dbname=projeto_login", "root", "Hlm@1507");

// Função para calcular o custo total de compra
function calcularCustoTotalCompra($categoria, $data_inicio, $data_fim)
{
    global $pdo;
    $sql = "SELECT SUM(preco_compra * quantidade_unidades) AS custo_total FROM $categoria WHERE data_entrada BETWEEN :data_inicio AND :data_fim";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':data_inicio' => $data_inicio, ':data_fim' => $data_fim]);
    $resultado = $stmt->fetch();
    return $resultado['custo_total'] ?: 0;
}

// Função para calcular o potencial de faturamento
function calcularPotencialFaturamento($categoria, $data_inicio, $data_fim)
{
    global $pdo;
    $sql = "SELECT SUM(preco_venda * quantidade_unidades) AS faturamento_potencial FROM $categoria WHERE data_entrada BETWEEN :data_inicio AND :data_fim";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':data_inicio' => $data_inicio, ':data_fim' => $data_fim]);
    $resultado = $stmt->fetch();
    return $resultado['faturamento_potencial'] ?: 0;
}

// Função para calcular o total de vendas
function calcularTotalVendas($categoria, $data_inicio, $data_fim)
{
    global $pdo;
    $tabelaVendas = "venda_" . $categoria;
    $sql = "SELECT SUM(total_venda) AS total_vendas FROM $tabelaVendas
            JOIN venda v ON $tabelaVendas.venda_id = v.id
            WHERE v.data_venda BETWEEN :data_inicio AND :data_fim";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':data_inicio' => $data_inicio, ':data_fim' => $data_fim]);
    $resultado = $stmt->fetch();
    return $resultado['total_vendas'] ?: 0;
}

// Defina valores padrão para evitar erros
$custoTotalCompraBebida = $faturamentoPotencialBebida = $custoTotalCompraComida = $faturamentoPotencialComida = $totalVendasBebida = $totalVendasComida = 0;

if (isset($_POST['filtrar'])) {
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];

    // Calcular custo total de compra e potencial faturamento para bebidas e comidas
    $custoTotalCompraBebida = calcularCustoTotalCompra('bebida', $data_inicio, $data_fim);
    $faturamentoPotencialBebida = calcularPotencialFaturamento('bebida', $data_inicio, $data_fim);
    $custoTotalCompraComida = calcularCustoTotalCompra('comida', $data_inicio, $data_fim);
    $faturamentoPotencialComida = calcularPotencialFaturamento('comida', $data_inicio, $data_fim);

    // Calcular o total de vendas para bebidas e comidas
    $totalVendasBebida = calcularTotalVendas('bebida', $data_inicio, $data_fim);
    $totalVendasComida = calcularTotalVendas('comida', $data_inicio, $data_fim);
}

// Define valores padrão para os casos em que não foi inserida uma data
if (empty($data_inicio)) {
    $data_inicio = date('Y-m-d', strtotime("-7 days"));  // Data de 7 dias atrás
}

if (empty($data_fim)) {
    $data_fim = date('Y-m-d');  // Data atual
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Relatório de Entradas e Saídas</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>


  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
     body {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
     }
     #container {
        width: 80%;
        max-width: 600px;
        text-align: center;
     }
     canvas {
        max-width: 100%;
        margin: 0 auto;
     }
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
     #btn-gerar-pdf {
        margin-top: 20px;
     }
  </style>
</head>
<body>
<i class="fas fa-arrow-left voltar-icon" onclick="window.location.href='areaPrivada.php'"></i>

  <div id="container">
    <h1>Relatório de Entradas e Saídas</h1>

    <form method="POST" action="">
      <label for="data_inicio">Data de Início:</label>
      <input type="date" name="data_inicio" id="data_inicio" value="<?php echo $data_inicio; ?>">
      <label for= "data_fim">Data de Término:</label>
      <input type="date" name="data_fim" id="data_fim" value="<?php echo $data_fim; ?>">
      <input type="submit" name="filtrar" value="Filtrar">
    </form>

    <canvas id="grafico-combinado" width="600" height="300"></canvas>
    <div>
      <h2>Comidas e Bebidas (Somadas)</h2>
      <canvas id="grafico-comidas-bebidas" width="600" height="300"></canvas>
    </div>

    <button id="btn-gerar-pdf">Gerar PDF</button>
  </div>

  <script>
    var ctx = document.getElementById("grafico-combinado").getContext("2d");
    var ctxComidasBebidas = document.getElementById("grafico-comidas-bebidas").getContext("2d");

    var dados = {
      labels: ["Bebidas", "Comidas"],
      datasets: [{
        label: "Custo Total de Compra",
        data: [<?php echo $custoTotalCompraBebida; ?>, <?php echo $custoTotalCompraComida; ?>],
        backgroundColor: "rgba(75, 192, 192, 0.5)"
      },
      {
        label: "Potencial de Faturamento",
        data: [<?php echo $faturamentoPotencialBebida; ?>, <?php echo $faturamentoPotencialComida; ?>],
        backgroundColor: "rgba(255, 99, 132, 0.5)"
      },
      {
        label: "Total de Vendas",
        data: [<?php echo $totalVendasBebida; ?>, <?php echo $totalVendasComida; ?>],
        backgroundColor: "rgba(54, 162, 25, 0.5)"
      }]
    };

    var dadosComidasBebidas = {
      labels: ["Custo Total de Compra", "Potencial de Faturamento", "Total de Vendas"],
      datasets: [{
        label: "Comidas e Bebidas",
        data: [
          <?php echo $custoTotalCompraBebida + $custoTotalCompraComida; ?>,
          <?php echo $faturamentoPotencialBebida + $faturamentoPotencialComida; ?>,
          <?php echo $totalVendasBebida + $totalVendasComida; ?>
        ],
        backgroundColor: ["rgba(75, 192, 192, 0.5)", "rgba(255, 99, 132, 0.5)", "rgba(54, 162, 235, 0.5)"]
      }]
    };

    var opcoes = {
      responsive: false,
      scales: {
        y: {
          beginAtZero: true
        }
      }
    };

    new Chart(ctx, {
      type: "bar",
      data: dados,
      options: opcoes
    });

    new Chart(ctxComidasBebidas, {
      type: "bar",
      data: dadosComidasBebidas,
      options: opcoes
    });

    // Função para gerar o PDF
    document.getElementById("btn-gerar-pdf").addEventListener("click", function () {
      const dataInicio = "<?php echo $data_inicio; ?>";
      const dataFim = "<?php echo $data_fim; ?>";
      const dataEmissao = new Date().toLocaleString();

      const doc = new jsPDF();

      doc.text("Relatório de Entradas e Saídas", 10, 10);
      doc.text("Período: " + dataInicio + " a " + dataFim, 10, 20);
      doc.text("Data de Emissão: " + dataEmissao, 10, 30);

      // Exporta o gráfico combinado como imagem
      var imgData = document.getElementById("grafico-combinado").toDataURL("image/jpeg", 1.0);
      doc.addImage(imgData, "JPEG", 10, 40, 100, 60);

      // Exporta o gráfico de comidas e bebidas como imagem
      var imgDataComidasBebidas = document.getElementById("grafico-comidas-bebidas").toDataURL("image/jpeg", 1.0);
      doc.addImage(imgDataComidasBebidas, "JPEG", 10, 110, 100, 60);

      doc.save("relatorio.pdf");
    });
  </script>
</body>
</html>
