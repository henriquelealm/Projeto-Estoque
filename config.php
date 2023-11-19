<?php
$host = "kentao-bd.cpysdt0xcid9.us-east-2.rds.amazonaws.com";
$port = 3306;
$username = "admin";
$password = "kentao2023";
$database = "kentaoBD";



try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
