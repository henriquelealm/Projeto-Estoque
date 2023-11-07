<?php
session_start();

if(isset($_SESSION['id_usuario'])) {
    // Conecte-se ao banco de dados
    require_once 'config.php';

    // Consulta para obter o nome do usuário
    $idUsuario = $_SESSION['id_usuario'];
    $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id_usuario = :id");
    $stmt->bindValue(":id", $idUsuario);
    $stmt->execute();

    // Verifique se a consulta foi bem-sucedida
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch();
        $nomeDoUsuario = $row['nome'];
    }

    // Consulta para obter a data e hora atuais do banco de dados
    $stmt = $pdo->query("SELECT NOW() AS data_hora");
    $row = $stmt->fetch();
    $dataHora = $row['data_hora'];
} else {
    // Redirecione ou exiba uma mensagem de erro
    header("location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Área Privada</title>
    <link rel="stylesheet" href="style/area-privada.css">
</head>
<body>
<div class="header">
    <div class="user-greeting">
        Olá, <?php echo $nomeDoUsuario; ?>
    </div>
    <div class="date-time" id="date-time">
        Data e Hora
    </div>
</div>
<div class="menu-toggle" onclick="toggleMenu()">☰</div>
<div class="sidebar">
    <nav class="menu">
        <ul>
            <li><a href="visualizar-estoque.php">Visualizar Estoque</a></li>
            <li><a href="cadastrar-cliente.php">Cadastrar Cliente</a></li>
            <li><a href="cadastrar-produtos.php">Cadastrar Produtos</a></li>
            <li><a href="editar-estoque.php">Editar Estoque</a></li>
            <li><a href="registrar-venda.php">Registrar Venda</a></li>
            <li><a href="historico-vendas.php">Histórico de Vendas</a></li>
            <li><a href="gerar-relatorios.php">Gerar Relatórios</a></li>
            
            <li><a href="sair.php">Sair</a></li>
        </ul>
    </nav>
</div>

<!-- Conteúdo da página da área privada -->

<script>
    function toggleMenu() {
        var sidebar = document.querySelector(".sidebar");
        sidebar.classList.toggle("active");
    }

    function updateDateTime() {
        var dateTimeElement = document.getElementById("date-time");
        var now = new Date();
        var formattedDate = now.toLocaleDateString();
        var formattedTime = now.toLocaleTimeString();
        dateTimeElement.textContent = formattedDate + " " + formattedTime;
    }

    // Atualiza a data e a hora a cada segundo
    setInterval(updateDateTime, 1000);

    // Chama a função para exibir a data e a hora atual
    updateDateTime();
</script>
</body>
</html>

