<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("location: index.php");
    exit;
}

require_once 'config.php';

// Processar as atualizações quando o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {
    $vendasPagas = $_POST['pagamento_realizado'];

    if (!empty($vendasPagas)) {
        // Converter as vendas pagas em uma string segura para a consulta
        $vendasPagasStr = implode(",", array_map('intval', $vendasPagas));
        
        // Atualizar as vendas para definir esta_devendo como 0
        $sql = "UPDATE venda SET esta_devendo = '0' WHERE id IN ($vendasPagasStr)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }
}

// Consulta para obter todas as vendas e clientes que estão devendo
$sql = "SELECT v.id AS venda_id, v.data_venda, c.nome AS cliente_nome, c.telefone, v.tipo_pagamento
        FROM venda v
        INNER JOIN cliente c ON v.id_cliente = c.id
        WHERE v.esta_devendo = '1'";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Clientes Devendo</title>
    <link rel="stylesheet" href="style/clientes-devendo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <i class="fas fa-arrow-left voltar-icon" onclick="window.location.href='areaPrivada.php'"></i>
    <div class="container">
        <h1>Clientes Devendo</h1>
        <form method="post" action="clientes-devendo.php">
            <table>
                <tr>
                    <th>ID da Venda</th>
                    <th>Data da Venda</th>
                    <th>Nome do Cliente</th>
                    <th>Telefone</th>
                    <th>Método de Pagamento</th>
                    <th>Pagamento Realizado</th>
                </tr>
                <?php foreach ($resultados as $row): ?>
                    <tr>
                        <td><?= $row['venda_id'] ?></td>
                        <td><?= $row['data_venda'] ?></td>
                        <td><?= $row['cliente_nome'] ?></td>
                        <td><?= $row['telefone'] ?></td>
                        <td><?= $row['tipo_pagamento'] ?></td>
                        <td>
                            <input type="checkbox" name="pagamento_realizado[]" value="<?= $row['venda_id'] ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <input type="submit" name="salvar" value="Salvar Alterações">
        </form>
    </div>
</body>
</html>
