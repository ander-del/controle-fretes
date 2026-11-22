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