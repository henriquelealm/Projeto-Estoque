<?php
// Conecte-se ao banco de dados
$pdo = new PDO("mysql:host=localhost;dbname=projeto_login", "root", "Hlm@1507");

// Verifique se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['pesquisar'])) {
        // Se o formulário de pesquisa foi submetido, faça a pesquisa
        $termo_pesquisa = $_POST['termo_pesquisa'];

        $sql = "SELECT id, nome, quantidade_unidades, categoria, data_validade, preco_venda FROM (
            SELECT id, nome, quantidade_unidades, 'bebida' as categoria, data_validade, preco_venda FROM bebida
            UNION
            SELECT id, nome, quantidade_unidades, 'comida' as categoria, data_validade, preco_venda FROM comida
        ) AS produtos
        WHERE nome LIKE :termo_pesquisa";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':termo_pesquisa' => '%' . $termo_pesquisa . '%']);

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif (isset($_POST['venda'])) {
        // Se o formulário de venda foi submetido, registre a venda
        $tipo_pagamento = $_POST['tipo_pagamento'];
        $funcionario_id = 2; // Substitua pelo ID do funcionário

        $pdo->beginTransaction();

        // Crie uma nova entrada na tabela venda comum
        $stmt = $pdo->prepare("INSERT INTO venda (quantidade_unidades, data_venda, funcionario_id, tipo_pagamento) VALUES (?, NOW(), ?, ?)");
        $stmt->execute([0, $funcionario_id, $tipo_pagamento]);
        $venda_id = $pdo->lastInsertId();

        // Agora percorra os itens vendidos (pode ser mais de um)
        if (isset($_POST['itens_vendidos'])) {
            foreach ($_POST['itens_vendidos'] as $item) {
                $item_id = $item['item_id'];
                $categoria = $item['categoria'];
                $quantidade_vendida = intval($item['quantidade_vendida']);

                if ($quantidade_vendida > 0) { // Verifique se a quantidade é maior que zero
                    // Verifique se há unidades suficientes no estoque
                    $stmt = $pdo->prepare("SELECT quantidade_unidades, preco_venda FROM $categoria WHERE id = ?");
                    $stmt->execute([$item_id]);
                    $estoque_info = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($estoque_info && $estoque_info['quantidade_unidades'] >= $quantidade_vendida) {
                        $preco_venda = $estoque_info['preco_venda'];
                        $total_venda = $preco_venda * $quantidade_vendida;

                        // Insira o item vendido na tabela correspondente (bebida ou comida)
                        if ($categoria === 'bebida') {
                            $stmt = $pdo->prepare("INSERT INTO venda_bebida (venda_id, bebida_id, quantidade, total_venda) VALUES (?, ?, ?, ?)");
                        } elseif ($categoria === 'comida') {
                            $stmt = $pdo->prepare("INSERT INTO venda_comida (venda_id, comida_id, quantidade, total_venda) VALUES (?, ?, ?, ?)");
                        }
                        $stmt->execute([$venda_id, $item_id, $quantidade_vendida, $total_venda]);

                        // Atualize o estoque subtraindo a quantidade vendida
                        $stmt = $pdo->prepare("UPDATE $categoria SET quantidade_unidades = quantidade_unidades - ? WHERE id = ?");
                        $stmt->execute([$quantidade_vendida, $item_id]);
                    } else {
                        echo '<script>';
                        echo 'alert("Quantidade insuficiente no estoque para o item com ID: ' . $item_id . '. A venda não foi registrada.");';
                        echo '</script>';
                    }
                }
            }
        }

        $pdo->commit();
        echo "Venda de comida registrada com sucesso!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Registrar Venda</title>
    <link rel="stylesheet" href="style/registrar-venda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .voltar-icon {
            position: absolute;
            top: 10px;
            left: 10px;
            font-size: 24px;
            cursor: pointer;
        }

        .container {
            margin-top: 20px;
        }

        .form-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .form-label {
            flex: 1;
        }

        .form-input {
            flex: 2;
            width: 70px;
        }

        .select-payment {
            width: 70px;
            margin-left: 10px;
        }

        .btn-submit {
            margin-left: 10px;
        }

        .btn-branco {
            background-color: #fff;
            color: #333;
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .btn-branco:hover {
            background-color: #eee;
        }
    </style>
</head>
<body>
    <i class="fas fa-arrow-left voltar-icon" onclick="window.location.href='areaPrivada.php'"></i>
    <div class="container">
        <h1>Registrar Venda</h1>

        <form method="POST" action="registrar-venda.php">
            <div class="form-row">
                <label for="termo_pesquisa" class="form-label">Pesquisar por Nome:</label>
                <input type="text" name="termo_pesquisa" class="form-input">
                <input type="submit" name="pesquisar" value="Pesquisar" class="btn-branco">
            </div>
        </form>

        <?php
        if (isset($resultados)) {
            echo '<h2>Resultados da Pesquisa:</h2>';
            if (count($resultados) > 0) {
                echo '<form method="POST" action="registrar-venda.php">';
                echo '<table>';
                echo '<tr>';
                echo '<th>ID</th>';
                echo '<th>Nome</th>';
                echo '<th>Categoria</th>';
                echo '<th>Quantidade Disponível</th>';
                echo '<th>Data de validade</th>';
                echo '<th>Preço de Venda</th>';
                echo '<th>Selecionar</th>';
                echo '</tr>';

                $index = 0;

                foreach ($resultados as $row) {
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td>' . $row['nome'] . '</td>';
                    echo '<td>' . $row['categoria'] . '</td>';
                    echo '<td>' . $row['quantidade_unidades'] . '</td>';
                    echo '<td>' . $row['data_validade'] . '</td>';
                    echo '<td>' . $row['preco_venda'] . '</td>';
                    echo '<td>';
                    echo '<input type="hidden" name="itens_vendidos[' . $index . '][item_id]" value="' . $row['id'] . '">';
                    echo '<input type="hidden" name="itens_vendidos[' . $index . '][categoria]" value="' . $row['categoria'] . '">';
                    echo '<label for="quantidade_vendida" class="form-label">Quantidade Vendida:</label>';
                    echo '<input type="number" name="itens_vendidos[' . $index . '][quantidade_vendida]" min="1" class="form-input">';
                    echo '<select name="tipo_pagamento" class="select-payment">';
                    echo '<option value="Cartão">Cartão</option>';
                    echo '<option value="PIX">PIX</option>';
                    echo '<option value="Dinheiro">Dinheiro</option>';
                    echo '</select>';
                    echo '</td>';
                    echo '</tr>';
                    $index++;
                }

                echo '<tr>';
                echo '<td colspan="6"></td>';
                echo '<td><input type="submit" name="venda" value="Registrar Venda" class="btn-submit"></td>';
                echo '</tr>';
                echo '</table>';
                echo '</form>';
            } else {
                echo 'Nenhum resultado encontrado.';
            }
        }
        ?>
    </div>
</body>
</html>
