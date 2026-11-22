CREATE DATABASE IF NOT EXISTS controle_fretes
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE controle_fretes;

CREATE TABLE IF NOT EXISTS produtos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    peso DECIMAL(10, 2) NOT NULL
);
CREATE TABLE IF NOT EXISTS enderecos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identificacao VARCHAR(100) NOT NULL,
    cep VARCHAR(9) NOT NULL,
    logradouro VARCHAR(150) NOT NULL,
    numero VARCHAR(20) NOT NULL,
    complemento VARCHAR(100),
    bairro VARCHAR(100) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    uf CHAR(2) NOT NULL
);