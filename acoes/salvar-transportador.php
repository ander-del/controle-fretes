<?php

require_once __DIR__ . '/../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?pagina=transportadores');
    exit;
}

$falhar = function () {
    header('Location: ../index.php?pagina=transportadores&erro=1');
    exit;
};

$idInformado = $_POST['id'] ?? '';

if (!is_string($idInformado)) {
    $falhar();
}

$id = null;

if (trim($idInformado) !== '') {
    $id = filter_var(
        $idInformado,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($id === false) {
        $falhar();
    }
}

$limites = [
    'nome' => 150,
    'documento' => 18,
    'telefone' => 20,
    'email' => 150,
    'placa' => 8
];

$dados = [];

foreach ($limites as $campo => $limite) {
    $valor = $_POST[$campo] ?? '';

    if (!is_string($valor)) {
        $falhar();
    }

    $valor = trim($valor);

    if (
        ($campo !== 'email' && $valor === '') ||
        mb_strlen($valor, 'UTF-8') > $limite
    ) {
        $falhar();
    }

    $dados[$campo] = $valor;
}

// Confere o formato do documento, sem validar sua autenticidade.
if (!preg_match('/^[0-9.\/-]+$/', $dados['documento'])) {
    $falhar();
}

$documento = preg_replace('/[^0-9]/', '', $dados['documento']);

if (!in_array(strlen($documento), [11, 14], true)) {
    $falhar();
}

// Confere o e-mail somente quando preenchido.
if (
    $dados['email'] !== '' &&
    !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)
) {
    $falhar();
}

// Aceita placas antigas e do padrao Mercosul.
$dados['placa'] = strtoupper(
    str_replace('-', '', $dados['placa'])
);

if (!preg_match('/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/', $dados['placa'])) {
    $falhar();
}

try {
    if ($id !== null) {
        $consulta = $pdo->prepare(
            'SELECT id FROM transportadores WHERE id = ?'
        );
        $consulta->execute([$id]);

        if (!$consulta->fetch()) {
            $falhar();
        }

        $sql = 'UPDATE transportadores SET
                    nome = :nome,
                    documento = :documento,
                    telefone = :telefone,
                    email = :email,
                    placa = :placa
                WHERE id = :id';

        $dados['id'] = $id;
    } else {
        $sql = 'INSERT INTO transportadores
                    (nome, documento, telefone, email, placa)
                VALUES
                    (:nome, :documento, :telefone, :email, :placa)';
    }

    $comando = $pdo->prepare($sql);
    $comando->execute($dados);
} catch (PDOException $erro) {
    error_log($erro->getMessage());
    $falhar();
}

header('Location: ../index.php?pagina=transportadores&sucesso=1');
exit;