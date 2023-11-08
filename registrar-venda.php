<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("location: index.php");
    exit;
}

require_once 'config.php';

$quantidadeVendidaValida = false;
$clienteNome = '';
$clienteTelefone = '';
$clienteEndereco = '';
$clienteNaoEncontrado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['pesquisar'])) {
        // Se o formulário de pesquisa de produtos foi submetido, faça a pesquisa
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
    } elseif (isset($_POST['pesquisar_cliente'])) {
        $cliente_cpf_cnpj = $_POST['cliente_cpf_cnpj'];

        $stmt = $pdo->prepare("SELECT cliente.id AS id, cliente.nome AS nome, cliente.telefone AS telefone, endereco.rua AS rua, endereco.cidade AS cidade, endereco.estado AS estado, endereco.cep AS cep, endereco.numero AS numero, endereco.complemento AS complemento FROM cliente 
        INNER JOIN endereco ON cliente.endereco_id = endereco.id 
        WHERE cliente.cpf_ou_cnpj = ?");
        $stmt->execute([$cliente_cpf_cnpj]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $clienteNome = $result['nome'];
            $clienteTelefone = $result['telefone'];
            $clienteEndereco = "{$result['rua']}, {$result['numero']}, {$result['cidade']}, {$result['estado']}, {$result['cep']}, {$result['complemento']}";

            $_SESSION['clienteNome'] = $clienteNome;
            $_SESSION['clienteTelefone'] = $clienteTelefone;
            $_SESSION['clienteEndereco'] = $clienteEndereco;

            // Armazene o ID do cliente na sessão
            $_SESSION['cliente_id'] = $result['id'];
        } else {
            $clienteNaoEncontrado = 'Cliente não encontrado.';
        }
    } elseif (isset($_POST['venda'])) {
        $funcionario_id = $_SESSION['id_usuario'];
        $tipo_pagamento = $_POST['tipo_pagamento'];

        if (isset($_SESSION['clienteNome'])) {
            $clienteNome = $_SESSION['clienteNome'];
            $clienteTelefone = $_SESSION['clienteTelefone'];
            $clienteEndereco = $_SESSION['clienteEndereco'];

            // Consultar o ID do cliente com base no nome
            $stmt = $pdo->prepare("SELECT id FROM cliente WHERE nome = ?");
            $stmt->execute([$clienteNome]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $cliente_id = $result['id'];
            }
        }

        $pdo->beginTransaction();

// Crie uma nova entrada na tabela de venda
$stmt = $pdo->prepare("INSERT INTO venda (data_venda, funcionario_id, tipo_pagamento, id_cliente, esta_devendo) VALUES (NOW(), ?, ?, ?, ?)");
$estaDevendo = isset($_POST['pagar_depois']) ? 1 : 0;
$stmt->execute([$funcionario_id, $tipo_pagamento, $cliente_id, $estaDevendo]);
$venda_id = $pdo->lastInsertId();

// Agora percorra os itens vendidos (pode ser mais de um)
if (isset($_POST['itens_vendidos'])) {
    foreach ($_POST['itens_vendidos'] as $item) {
        $item_id = $item['item_id'];
        $categoria = $item['categoria'];
        $quantidade_vendida = intval($item['quantidade_vendida']);

        if ($quantidade_vendida > 0) {
            $quantidadeVendidaValida = true;

            $stmt = $pdo->prepare("SELECT quantidade_unidades, preco_venda FROM $categoria WHERE id = ?");
            $stmt->execute([$item_id]);
            $estoque_info = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($estoque_info && $estoque_info['quantidade_unidades'] >= $quantidade_vendida) {
                $preco_venda = $estoque_info['preco_venda'];
                $total_venda = $preco_venda * $quantidade_vendida;

                if ($categoria === 'bebida') {
                    $stmt = $pdo->prepare("INSERT INTO venda_bebida (venda_id, bebida_id, quantidade, total_venda) VALUES (?, ?, ?, ?)");
                } elseif ($categoria === 'comida') {
                    $stmt = $pdo->prepare("INSERT INTO venda_comida (venda_id, comida_id, quantidade, total_venda) VALUES (?, ?, ?, ?)");
                }
                $stmt->execute([$venda_id, $item_id, $quantidade_vendida, $total_venda]);

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

if ($quantidadeVendidaValida) {
    $pdo->commit();
    echo "Venda registrada com sucesso!";
} else {
    echo '<script>';
    echo 'alert("Nenhum produto com quantidade válida foi selecionado. A venda não foi registrada.");';
    echo '</script>';
}
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
                    echo '</td>';
                    echo '</tr>';
                    $index++;
                }

                echo '</table>';
                echo '<div class="form-row">';
                echo '<label for="tipo_pagamento" class="form-label">Selecione o Método de Pagamento:</label>';
                echo '<select name="tipo_pagamento" class="select-payment">';
                echo '<option value="Cartão">Cartão</option>';
                echo '<option value="PIX">PIX</option>';
                echo '<option value="Dinheiro">Dinheiro</option>';
                echo '</select>';
                echo '</div>';
                
                // Adicione a nova opção "Pagar depois"
                echo '<div class="form-row">';
                echo '<label for="pagar_depois" class="form-label" style="color: black;">Pagar depois:</label>';
                echo '<input type="checkbox" name="pagar_depois" value="1" class="form-input">';
                echo '</div>';
                
                echo '<input type="submit" name="venda" value="Registrar Venda" class="btn-submit">';
                echo '</form>';
                
            } else {
                echo 'Nenhum resultado encontrado.';
            }
        }
        ?>

        <?php
        if (isset($clienteNome)) {
            // Exibir informações do cliente se encontrado
            if (empty($clienteNaoEncontrado)) {
                echo '<div class="cliente-info-popup">';
                echo '<span class="close-popup" onclick="fecharPopup()">&times;</span>';
                echo '<h2>Informações do Cliente:</h2>';
                echo '<p><strong>Nome do Cliente:</strong> ' . $_SESSION['clienteNome'] . '</p>';
                echo '<p><strong>Telefone:</strong> ' . $_SESSION['clienteTelefone'] . '</p>';
                echo '<p><strong>Endereço:</strong> ' . $_SESSION['clienteEndereco'] . '</p>';
                echo '</div>';
                echo '<script>
                    function fecharPopup() {
                        document.querySelector(".cliente-info-popup").style.display = "none";
                    }
                </script>';
            } else {
                echo '<p>' . $clienteNaoEncontrado . '</p>';
            }
        }
        ?>

        <h2>Inserir Cliente</h2>
        <form method="POST" action="registrar-venda.php">
            <div class="form-row" id="cliente-search-bar">
                <label for="cliente_cpf_cnpj" class="form-label">CPF ou CNPJ do Cliente:</label>
                <input type="text" name="cliente_cpf_cnpj" class="form-input">
                <input type="submit" name="pesquisar_cliente" value="Pesquisar Cliente" class="btn-branco">
            </div>
        </form>
        
        <?php
        if (isset($clienteNome)) {
            // Verifique se as chaves existem e têm valores antes de exibir o popup
            if (!empty($_SESSION['clienteNome']) && !empty($_SESSION['clienteTelefone']) && !empty($_SESSION['clienteEndereco'])) {
                echo '<div class="cliente-info-popup">';
                echo '<span class="close-popup" onclick="fecharPopup()">&times;</span>';
                echo '<h2>Informações do Cliente:</h2>';
                echo '<p><strong>Nome do Cliente:</strong> ' . $_SESSION['clienteNome'] . '</p>';
                echo '<p><strong>Telefone:</strong> ' . $_SESSION['clienteTelefone'] . '</p>';
                echo '<p><strong>Endereço:</strong> ' . $_SESSION['clienteEndereco'] . '</p>';
                echo '</div>';
                echo '<script>
                    function fecharPopup() {
                        document.querySelector(".cliente-info-popup").style.display = "none";
                    }
                    // Mostra o popup apenas se as informações do cliente estiverem definidas
                    document.addEventListener("DOMContentLoaded", function() {
                        if(document.querySelector(".cliente-info-popup")) {
                            document.querySelector(".cliente-info-popup").style.display = "block";
                        }
                    });
                </script>';
            }
        }
        ?>
    </div>
    <script>
    // Ocultar a barra de pesquisa de clientes após a pesquisa de produtos
    if (<?php echo isset($resultados) ? 'true' : 'false'; ?>) {
        document.querySelector("body > div > form:nth-child(8)").style.display = 'none';
        document.querySelector("body > div > h2:nth-child(7)").style.display = 'none';
    }
    </script>
</body>
</html>
