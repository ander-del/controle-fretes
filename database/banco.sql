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
CREATE TABLE IF NOT EXISTS transportadores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    documento VARCHAR(18) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(150),
    placa VARCHAR(8) NOT NULL
);
CREATE TABLE IF NOT EXISTS fretes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    data DATE NOT NULL,
    transportador_id INT UNSIGNED NOT NULL,
    origem_id INT UNSIGNED NOT NULL,
    destino_id INT UNSIGNED NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    observacoes TEXT,

    FOREIGN KEY (transportador_id)
        REFERENCES transportadores(id),

    FOREIGN KEY (origem_id)
        REFERENCES enderecos(id),

    FOREIGN KEY (destino_id)
        REFERENCES enderecos(id)
);

CREATE TABLE IF NOT EXISTS frete_produtos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    frete_id INT UNSIGNED NOT NULL,
    produto_id INT UNSIGNED NOT NULL,
    quantidade INT UNSIGNED NOT NULL,
    peso_unitario DECIMAL(10, 2) NOT NULL,

    FOREIGN KEY (frete_id)
        REFERENCES fretes(id),

    FOREIGN KEY (produto_id)
        REFERENCES produtos(id),

    UNIQUE KEY produto_por_frete (frete_id, produto_id)
);