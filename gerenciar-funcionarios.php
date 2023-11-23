<?php
session_start();

require_once 'config.php';

// Verifica se o usuário está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("location: index.php");
    exit;
}

// Função para obter a lista de funcionários
function obterFuncionarios($pdo) {
    $stmt = $pdo->query("SELECT * FROM funcionario");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para criar um novo funcionário
function criarFuncionario($pdo, $nome, $cpf, $cargo) {
    $stmt = $pdo->prepare("INSERT INTO funcionario (nome, cpf, cargo) VALUES (?, ?, ?)");
    $stmt->execute([$nome, $cpf, $cargo]);
}

// Função para atualizar as informações de um funcionário
function atualizarFuncionario($pdo, $id, $nome, $cpf, $cargo) {
    $stmt = $pdo->prepare("UPDATE funcionario SET nome = ?, cpf = ?, cargo = ? WHERE id = ?");
    $stmt->execute([$nome, $cpf, $cargo, $id]);
}

// Função para excluir um funcionário
function excluirFuncionario($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM funcionario WHERE id = ?");
    $stmt->execute([$id]);
}

// Processa o formulário de criação ou atualização de funcionário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['criar'], $_POST['nome'], $_POST['cpf'], $_POST['cargo'])) {
        criarFuncionario($pdo, $_POST['nome'], $_POST['cpf'], $_POST['cargo']);
    } elseif (isset($_POST['atualizar'], $_POST['id'], $_POST['nome'], $_POST['cpf'], $_POST['cargo'])) {
        atualizarFuncionario($pdo, $_POST['id'], $_POST['nome'], $_POST['cpf'], $_POST['cargo']);
    } elseif (isset($_POST['excluir'], $_POST['id'])) {
        excluirFuncionario($pdo, $_POST['id']);
    }
}

// Obtém a lista de funcionários
$funcionarios = obterFuncionarios($pdo);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Gerenciar Funcionários</title>
    <link rel="stylesheet" href="style/gerenciar-funcionarios.css">
    <!-- Adicione seus estilos CSS aqui -->
</head>
<body>
    <div class="container">
        <h1>Gerenciar Funcionários</h1>

        <!-- Lista de funcionários -->
        <div class="text-section">
            <h2>Lista de Funcionários</h2>
            <p>Abaixo estão os funcionários cadastrados:</p>
        </div>

        <ul>
            <?php foreach ($funcionarios as $funcionario): ?>
                <li>
                    <?php echo "{$funcionario['nome']} ({$funcionario['cpf']}) - {$funcionario['cargo']}"; ?>
                    
                    <!-- Formulário para excluir funcionário -->
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="id" value="<?php echo $funcionario['id']; ?>">
                        <button type="submit" name="excluir">Excluir</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Formulário para criar funcionário -->
        <div class="text-section">
            <h2>Adicionar Funcionário</h2>
            <p>Preencha os campos abaixo para adicionar um novo funcionário:</p>
        </div>

        <form method="post">
            <label for="nome">Nome:</label>
            <input type="text" name="nome" required value=""><br>
            <label for="cpf">CPF:</label>
            <input type="text" name="cpf" required value=""><br>
            <label for="cargo">Cargo:</label>
            <input type="text" name="cargo" required value=""><br>

            <button type="submit" name="criar">Criar Funcionário</button>
        </form>
    </div>

    <!-- Adicione outros elementos HTML conforme necessário -->

</body>
</html>
