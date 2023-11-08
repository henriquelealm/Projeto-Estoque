<?php
// Conecte-se ao banco de dados
require_once 'config.php';

// Inicialize variáveis
$clientes = array();

// Consulta para obter todos os clientes
$stmt = $pdo->query("SELECT c.id AS cliente_id, c.nome AS nome_cliente, c.cpf_ou_cnpj, c.telefone, e.rua, e.cidade, e.estado, e.cep, e.numero, e.complemento
    FROM cliente c
    JOIN endereco e ON c.endereco_id = e.id");

if ($stmt->rowCount() > 0) {
    $clientes = $stmt->fetchAll();
}

// Verifique se o formulário de atualização foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {
    // Loop através dos resultados e atualize os registros
    foreach ($clientes as $cliente) {
        $clienteId = $cliente['cliente_id'];
        $nome = $_POST['nome_' . $clienteId];
        $cpf_cnpj = $_POST['cpf_cnpj_' . $clienteId];
        $telefone = $_POST['telefone_' . $clienteId];
        $rua = $_POST['rua_' . $clienteId];
        $cidade = $_POST['cidade_' . $clienteId];
        $estado = $_POST['estado_' . $clienteId];
        $cep = $_POST['cep_' . $clienteId];
        $numero = $_POST['numero_' . $clienteId];
        $complemento = $_POST['complemento_' . $clienteId];

        // Atualize os registros no banco de dados
        $stmt = $pdo->prepare("UPDATE cliente c
            JOIN endereco e ON c.endereco_id = e.id
            SET c.nome = ?, c.cpf_ou_cnpj = ?, c.telefone = ?, e.rua = ?, e.cidade = ?, e.estado = ?, e.cep = ?, e.numero = ?, e.complemento = ?
            WHERE c.id = ?");
        $stmt->execute([$nome, $cpf_cnpj, $telefone, $rua, $cidade, $estado, $cep, $numero, $complemento, $clienteId]);
    }

    // Redirecione para a mesma página para exibir as alterações
    header("Location: alterar-cliente.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style/alterar-cliente.css">
    <meta charset="utf-8">
    <title>Tabela de Clientes</title>
</head>
<body>
<i class="fas fa-arrow-left voltar-icon" onclick="window.location.href='areaPrivada.php'"></i> <!-- Ícone de seta com link para áreaPrivada.php -->
    <h1>Tabela de Clientes</h1>

    <form action="alterar-cliente.php" method="POST">
        <table>
            <tr>
                <th>Nome</th>
                <th>CPF ou CNPJ</th>
                <th>Telefone</th>
                <th>Rua</th>
                <th>Cidade</th>
                <th>Estado</th>
                <th>CEP</th>
                <th>Número</th>
                <th>Complemento</th>
            </tr>
            <?php foreach ($clientes as $cliente): ?>
                <tr>
                    <td><input type="text" name="nome_<?php echo $cliente['cliente_id']; ?>" value="<?php echo $cliente['nome_cliente']; ?>"></td>
                    <td><input type="text" name="cpf_cnpj_<?php echo $cliente['cliente_id']; ?>" value="<?php echo $cliente['cpf_ou_cnpj']; ?>"></td>
                    <td><input type="text" name="telefone_<?php echo $cliente['cliente_id']; ?>" value="<?php echo $cliente['telefone']; ?>"></td>
                    <td><input type="text" name="rua_<?php echo $cliente['cliente_id']; ?>" value="<?php echo $cliente['rua']; ?>"></td>
                    <td><input type="text" name="cidade_<?php echo $cliente['cliente_id']; ?>" value="<?php echo $cliente['cidade']; ?>"></td>
                    <td><input type="text" name="estado_<?php echo $cliente['cliente_id']; ?>" value="<?php echo $cliente['estado']; ?>"></td>
                    <td><input type="text" name="cep_<?php echo $cliente['cliente_id']; ?>" value="<?php echo $cliente['cep']; ?>"></td>
                    <td><input type="text" name="numero_<?php echo $cliente['cliente_id']; ?>" value="<?php echo $cliente['numero']; ?>"></td>
                    <td><input type="text" name="complemento_<?php echo $cliente['cliente_id']; ?>" value="<?php echo $cliente['complemento']; ?>"></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <input type="submit" name="salvar" value="Salvar Alterações">
    </form>
</body>
</html>
