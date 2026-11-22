<?php

require_once __DIR__ . '/../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?pagina=produtos');
    exit;
}

$idInformado = trim((string) ($_POST['id'] ?? ''));
$id = null;

if ($idInformado !== '') {
    $id = filter_var(
        $idInformado,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($id === false) {
        header('Location: ../index.php?pagina=produtos&erro=1');
        exit;
    }
}

$nome = trim((string) ($_POST['nome'] ?? ''));
$descricao = trim((string) ($_POST['descricao'] ?? ''));
$pesoInformado = str_replace(',', '.', (string) ($_POST['peso'] ?? ''));
$peso = filter_var($pesoInformado, FILTER_VALIDATE_FLOAT);

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

$dados = [
    ':nome' => $nome,
    ':descricao' => $descricao,
    ':peso' => number_format($peso, 2, '.', '')
];

try {
    if ($id !== null) {
        // Confere se o produto ainda existe.
        $consulta = $pdo->prepare('SELECT id FROM produtos WHERE id = ?');
        $consulta->execute([$id]);

        if (!$consulta->fetch()) {
            header('Location: ../index.php?pagina=produtos&erro=1');
            exit;
        }

        // Atualiza o produto selecionado.
        $sql = 'UPDATE produtos
                SET nome = :nome, descricao = :descricao, peso = :peso
                WHERE id = :id';

        $dados[':id'] = $id;
    } else {
        // Cadastra um novo produto.
        $sql = 'INSERT INTO produtos (nome, descricao, peso)
                VALUES (:nome, :descricao, :peso)';
    }

    $comando = $pdo->prepare($sql);
    $comando->execute($dados);
} catch (PDOException $erro) {
    error_log($erro->getMessage());

    header('Location: ../index.php?pagina=produtos&erro=1');
    exit;
}

header('Location: ../index.php?pagina=produtos&sucesso=1');
exit;