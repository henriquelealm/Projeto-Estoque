<?php
// Conecte-se ao banco de dados
require_once 'config.php';

// Verifique se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pega os valores do formulário
    $nome = $_POST['nome'];
    $cpf_cnpj = $_POST['cpf_cnpj']; // Campo correto
    $telefone = $_POST['telefone'];
    $rua = $_POST['rua'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $cep = $_POST['cep'];
    $numero = $_POST['numero'];
    $complemento = $_POST['complemento'];

    // Prepara e executa a inserção do endereço
    $stmt = $pdo->prepare("INSERT INTO endereco (rua, cidade, estado, cep, numero, complemento) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$rua, $cidade, $estado, $cep, $numero, $complemento]);

    // Obtém o ID do endereço inserido
    $endereco_id = $pdo->lastInsertId();

    // Prepara e executa a inserção do cliente
    $stmt = $pdo->prepare("INSERT INTO cliente (nome, cpf_ou_cnpj, telefone, endereco_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nome, $cpf_cnpj, $telefone, $endereco_id]);

    // Redireciona para uma página de sucesso ou exibe uma mensagem de sucesso
    header("Location: areaPrivada.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta charset="utf-8">
    <link rel="stylesheet" href="style/cadastrar-cliente.css">
    <title>Cadastro de Cliente</title>
</head>
<body>
    <i class="fas fa-arrow-left voltar-icon" onclick="window.location.href='areaPrivada.php'"></i> 
    <h1>Cadastro de Cliente</h1>
    <form action="cadastrar-cliente.php" method="POST">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>
        <br>
        <label for="cpf_cnpj">CPF ou CNPJ:</label>
        <input type="text" id="cpf_cnpj" name="cpf_cnpj" required>
        <br>
        <label for="telefone">Telefone:</label>
        <input type="text" id="telefone" name="telefone">
        <br>
        <h2>Endereço</h2>
        <br>
        <label for="estado">Estado:</label>
        <input type="text" id="estado" name="estado" required>
        <br>
        <label for="cidade">Cidade:</label>
        <input type="text" id="cidade" name="cidade" required>
        <br>
        <label for="rua">Rua:</label>
        <input type="text" id="rua" name="rua" required>
        <br>
        <label for="cep">CEP:</label>
        <input type="text" id="cep" name="cep" required>
        <br>
        <label for="numero">Número:</label>
        <input type="text" id="numero" name="numero" required>
        <br>
        <label for="complemento">Complemento:</label>
        <input type="text" id="complemento" name="complemento">
        <br>
        <input type="submit" value="Cadastrar Cliente">
    </form>
</body>
</html>
