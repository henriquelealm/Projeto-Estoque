	CREATE DATABASE projeto_login;
	
	USE projeto_login;
	
	CREATE TABLE usuarios (
	    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
	    nome VARCHAR(30),
	    telefone VARCHAR(30),
	    email VARCHAR(40),
	    senha VARCHAR(32)
	);
	
	CREATE TABLE funcionario (
	    id INT AUTO_INCREMENT PRIMARY KEY,
	    nome VARCHAR(100),
	    cpf CHAR(11),
	    cargo VARCHAR(50),
	    FOREIGN KEY (id) REFERENCES usuarios (id_usuario)
	);
	
	CREATE TABLE administrador (
	    id INT AUTO_INCREMENT PRIMARY KEY,
	    nome VARCHAR(100),
	    cpf CHAR(11),
	    FOREIGN KEY (id) REFERENCES usuarios (id_usuario)
	);
	
	CREATE TABLE cliente (
	    id INT AUTO_INCREMENT PRIMARY KEY,
	    nome VARCHAR(100),
	    cpf_ou_cnpj CHAR(14),
        telefone varchar(20)
	);
	
	CREATE TABLE bebida (
	    id INT AUTO_INCREMENT PRIMARY KEY,
	    categoria VARCHAR(50),
	    nome_primeiro VARCHAR(50),
	    nome_ultimo VARCHAR(50),
	    data_entrada DATE,
	    data_validade DATE,
	    quantidade_unidades INT,
	    preco_compra DECIMAL(10, 2),
	    preco_venda DECIMAL(10, 2)
	);
	
	CREATE TABLE comida (
	    id INT AUTO_INCREMENT PRIMARY KEY,
	    tipo VARCHAR(50),
	    nome VARCHAR(100),
	    data_entrada DATE,
	    data_validade DATE,
	    quantidade_unidades INT,
	    preco_compra DECIMAL(10, 2),
	    preco_venda DECIMAL(10, 2)
	);
	
	CREATE TABLE entrada (
	    id INT AUTO_INCREMENT PRIMARY KEY,
	    quantidade_unidades INT,
	    data_entrada DATE,
	    bebida_id INT,
	    comida_id INT,
	    FOREIGN KEY (bebida_id) REFERENCES bebida (id),
	    FOREIGN KEY (comida_id) REFERENCES comida (id)
	);
	
	CREATE TABLE venda (
	    id INT AUTO_INCREMENT PRIMARY KEY,
	    quantidade_unidades INT,
	    data_venda DATE,
	    funcionario_id INT,
	    FOREIGN KEY (funcionario_id) REFERENCES funcionario (id)
	);
	
	CREATE TABLE venda_bebida (
	    venda_id INT,
	    bebida_id INT,
	    PRIMARY KEY (venda_id, bebida_id),
	    FOREIGN KEY (venda_id) REFERENCES venda (id),
	    FOREIGN KEY (bebida_id) REFERENCES bebida (id)
	);
	
	CREATE TABLE venda_comida (
	    venda_id INT,
	    comida_id INT,
	    PRIMARY KEY (venda_id, comida_id),
	    FOREIGN KEY (venda_id) REFERENCES venda (id),
	    FOREIGN KEY (comida_id) REFERENCES comida (id)
	);
	
	
	ALTER TABLE bebida
	DROP COLUMN nome_ultimo;
	
	-- Adicione a coluna 'nome_usuario' à tabela 'comida'
	ALTER TABLE comida
	ADD nome_usuario VARCHAR(100);
	
	-- Adicione a coluna 'nome_usuario' à tabela 'bebida'
	ALTER TABLE bebida
	ADD nome_usuario VARCHAR(100);
	
	INSERT INTO funcionario (nome, cpf, cargo, id)
	VALUES ('Henrique Leal', '07521772490', 'Vendedor', 2);
	
	ALTER TABLE bebida
	CHANGE COLUMN nome_primeiro nome VARCHAR(255);
	
	ALTER TABLE venda_comida
	ADD total_venda DECIMAL(10, 2);
	
	ALTER TABLE venda_bebida
	ADD total_venda DECIMAL(10, 2);
	
	ALTER TABLE venda
	ADD tipo_pagamento ENUM('Cartão', 'PIX', 'Dinheiro');
	
	ALTER TABLE venda_bebida
	ADD quantidade INT;
	
	ALTER TABLE venda_comida
	ADD quantidade INT;
	
	CREATE TABLE endereco (
	    id INT AUTO_INCREMENT PRIMARY KEY,
	    rua VARCHAR(255),
	    cidade VARCHAR(100),
	    estado VARCHAR(100),
	    cep VARCHAR(20),
	    numero VARCHAR(10)
	);
	
	ALTER TABLE cliente
	ADD endereco_id INT,
	ADD FOREIGN KEY (endereco_id) REFERENCES endereco(id);
	
	ALTER TABLE cliente
	ADD telefone varchar(20);
	
	
