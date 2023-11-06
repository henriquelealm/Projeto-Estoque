<?php
// Conecte-se ao banco de dados
$pdo = new PDO("mysql:host=localhost;dbname=projeto_login", "root", "Hlm@1507");

// Consulta SQL para selecionar todos os registros de bebida
$sqlBebida = "SELECT * FROM bebida";

// Consulta SQL para selecionar todos os registros de comida
$sqlComida = "SELECT * FROM comida";

// Execute as consultas SQL
$resultadoBebida = $pdo->query($sqlBebida);
$resultadoComida = $pdo->query($sqlComida);

// Função para calcular a diferença entre duas datas em dias
function calcularDiferencaDatas($data1, $data2) {
    $diferenca = strtotime($data1) - strtotime($data2);
    return $diferenca / (60 * 60 * 24); // Converter para dias
}

// Data atual
$hoje = date('Y-m-d');
$umaSemana = strtotime('-7 days', strtotime($hoje));

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Visualizar Estoque</title>
    <link rel="stylesheet" href="style/visualizar-estoque.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Seu CSS personalizado aqui */
        .red-text {
            color: red;
        }
    </style>
</head>
<body>
    <i class="fas fa-arrow-left voltar-icon" onclick="window.location.href='areaPrivada.php'"></i> 
    <div class="content">
        <h1>Estoque de Bebidas e Comidas</h1>
        <div class="filter-options">
            <label for="filter">Filtrar por:</label>
            <select id="filter">
                <option value="data_validade">Data de Validade</option>
                <option value="quantidade_unidades">Quantidade de Unidades</option>
                <option value="preco_venda">Preço de Venda</option>
            </select>
            <button id="apply-filter">Aplicar Filtro</button>
        </div>
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
                <th>Nome do Usuário</th>
            </tr>
            <?php
            // Loop through the results for beverages
            foreach ($resultadoBebida as $row) {
                if ($row['quantidade_unidades'] == 0) {
                    continue; // Pular as bebidas com 0 unidades
                }

                $dataValidade = $row['data_validade'];
                $diferencaDias = calcularDiferencaDatas($dataValidade, $hoje);

                $rowClass = $diferencaDias > 7 ? 'red-text' : '';

                echo "<tr class='$rowClass'>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['categoria'] . "</td>";
                echo "<td>" . $row['nome'] . "</td>";
                echo "<td>" . $row['data_entrada'] . "</td>";
                echo "<td>" . $row['data_validade'] . "</td>";
                echo "<td>" . $row['quantidade_unidades'] . "</td>";
                echo "<td>" . $row['preco_compra'] . "</td>";
                echo "<td>" . $row['preco_venda'] . "</td>";
                echo "<td>" . $row['nome_usuario'] . "</td>";
                echo "</tr>";
            }

            // Loop through the results for food
            foreach ($resultadoComida as $row) {
                if ($row['quantidade_unidades'] == 0) {
                    continue; // Pular as comidas com 0 unidades
                }
                
                $dataValidade = strtotime($row['data_validade']);
                $hoje = strtotime(date('Y-m-d'));
                $seteDiasAtras = strtotime(date('Y-m-d', strtotime("-7 days")));
                
                if ($dataValidade < $hoje) {
                    continue; // Pular comidas vencidas
                }
                
                if ($dataValidade < $seteDiasAtras) {
                    continue; // Pular comidas com mais de 7 dias de validade
                }


                $dataValidade = $row['data_validade'];
                $diferencaDias = calcularDiferencaDatas($dataValidade, $hoje);

                $rowClass = $diferencaDias > 7 ? 'red-text' : '';

                echo "<tr class='$rowClass'>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['tipo'] . "</td>";
                echo "<td>" . $row['nome'] . "</td>";
                echo "<td>" . $row['data_entrada'] . "</td>";
                echo "<td>" . $row['data_validade'] . "</td>";
                echo "<td>" . $row['quantidade_unidades'] . "</td>";
                echo "<td>" . $row['preco_compra'] . "</td>";
                echo "<td>" . $row['preco_venda'] . "</td>";
                echo "<td>" . $row['nome_usuario'] . "</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>

    <script>
    const filterSelect = document.getElementById("filter");
    const table = document.querySelector("table");
    const rows = table.querySelectorAll("tr");

    document.getElementById("apply-filter").addEventListener("click", function () {
        const selectedFilter = filterSelect.value;
        let columnIndex = 0;
        let descending = false;

        if (selectedFilter === "quantidade_unidades" || selectedFilter === "preco_venda") {
            columnIndex = selectedFilter === "quantidade_unidades" ? 5 : 7;
            descending = true; // Ordenação decrescente para quantidade_unidades e preço_venda
        } else {
            columnIndex = 4; // Índice da coluna "Data de Validade"
        }

        const rowsArray = Array.from(rows).slice(1); // Ignora o cabeçalho da tabela.

        rowsArray.sort((a, b) => {
            const aValue = a.cells[columnIndex].textContent;
            const bValue = b.cells[columnIndex].textContent;

            if (selectedFilter === "data_validade") {
                const result = new Date(aValue) - new Date(bValue);
                return descending ? -result : result;
            } else {
                const result = parseFloat(aValue) - parseFloat(bValue);
                return descending ? -result : result;
            }
        });

        // Limpa a tabela
        rowsArray.forEach(row => table.appendChild(row));
    });
    </script>
</body>
</html>
