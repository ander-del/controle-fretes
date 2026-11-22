<?php

require_once __DIR__ . '/../config/conexao.php';

// Este arquivo recebe o envio do formulario.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?pagina=produtos');
    exit;
}

$nome = trim((string) ($_POST['nome'] ?? ''));
$descricao = trim((string) ($_POST['descricao'] ?? ''));
$pesoInformado = str_replace(',', '.', (string) ($_POST['peso'] ?? ''));

$peso = filter_var($pesoInformado, FILTER_VALIDATE_FLOAT);

// Confere os dados antes de salvar.
if (
    $nome === '' ||
    mb_strlen($nome, 'UTF-8') > 100 ||
    mb_strlen($descricao, 'UTF-8') > 2000 ||
    $peso === false ||
    !is_finite($peso) ||
    $peso < 0.01 ||
    $peso > 99999999.99
) {
    header('Location: ../index.php?pagina=produtos&erro=1');
    exit;
}

try {
    // Os valores sao enviados separadamente do comando SQL.
    $sql = 'INSERT INTO produtos (nome, descricao, peso)
            VALUES (:nome, :descricao, :peso)';

    $comando = $pdo->prepare($sql);

    $comando->execute([
        ':nome' => $nome,
        ':descricao' => $descricao,
        ':peso' => number_format($peso, 2, '.', '')
    ]);
} catch (PDOException $erro) {
    error_log($erro->getMessage());

    header('Location: ../index.php?pagina=produtos&erro=1');
    exit;
}

// Volta para a listagem depois de cadastrar.
header('Location: ../index.php?pagina=produtos&sucesso=1');
exit;