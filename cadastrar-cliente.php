<?php
// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Conecta-se ao banco de dados (substitua com suas informações de conexão)
    $pdo = new PDO("mysql:host=localhost;dbname=projeto_login", "root", "Hlm@1507");
    // Pega os valores do formulário
    $nome = $_POST['nome'];
    $cpf_cnpj = $_POST['cpf_cnpj'];
    $telefone = $_POST['telefone'];
    $rua = $_POST['rua'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $cep = $_POST['cep'];
    $numero = $_POST['numero'];

    // Prepara e executa a inserção do endereço
    $stmt = $pdo->prepare("INSERT INTO endereco (rua, cidade, estado, cep, numero) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$rua, $cidade, $estado, $cep, $numero]);

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
    <meta charset="utf-8">
    <title>Cadastro de Cliente</title>
</head>
<body>
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
        <label for="rua">Rua:</label>
        <input type="text" id="rua" name="rua" required>
        <br>
        <label for="cidade">Cidade:</label>
        <input type="text" id="cidade" name="cidade" required>
        <br>
        <label for="estado">Estado:</label>
        <input type="text" id="estado" name="estado" required>
        <br>
        <label for="cep">CEP:</label>
        <input type="text" id="cep" name="cep" required>
        <br>
        <label for="numero">Número:</label>
        <input type="text" id="numero" name="numero" required>
        <br>
        <input type="submit" value="Cadastrar Cliente">
    </form>
</body>
</html>
