<?php

class Usuarios {
    private $pdo;
    public $msgERRO = "";

    public function conectar() {
        $host = "localhost"; // Altere para o seu host, se necessário
        $nome = "projeto_login"; // Altere para o nome do seu banco de dados
        $usuario = "root"; // Altere para o nome de usuário do seu banco de dados
        $senha = "Hlm@1507"; // Altere para a senha do seu banco de dados

        try {
            $this->pdo = new PDO("mysql:dbname=" . $nome . ";host=" . $host, $usuario, $senha);
        } catch (PDOException $e) {
            $this->msgERRO = $e->getMessage();
        }
    }

    public function cadastrar($nome, $telefone, $email, $senha) {
        $sql = $this->pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = :e");
        $sql->bindValue(":e", $email);
        $sql->execute();

        if ($sql->rowCount() > 0) {
            return false; // Email já cadastrado
        } else {
            $sql = $this->pdo->prepare("INSERT INTO usuarios (nome, telefone, email, senha) VALUES (:n, :t, :e, :s)");
            $sql->bindValue(":n", $nome);
            $sql->bindValue(":t", $telefone);
            $sql->bindValue(":e", $email);
            $sql->bindValue(":s", md5($senha));
            $sql->execute();
            return true; // Cadastro realizado com sucesso
        }
    }

    public function logar($email, $senha) {
        $sql = $this->pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = :e AND senha = :s");
        $sql->bindValue(":e", $email);
        $sql->bindValue(":s", md5($senha));
        $sql->execute();

        if ($sql->rowCount() > 0) {
            $dado = $sql->fetch();
            session_start();
            $_SESSION['id_usuario'] = $dado['id_usuario'];
            return true; // Logado com sucesso
        } else {
            return false; // Login falhou
        }
    }
}

// Exemplo de uso
$usuarios = new Usuarios();
$usuarios->conectar();

if ($usuarios->cadastrar("Nome", "Telefone", "email@example.com", "senha123")) {
    echo "Cadastro realizado com sucesso.";
} 


?>
