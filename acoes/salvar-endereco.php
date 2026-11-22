<?php

require_once __DIR__ . '/../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?pagina=enderecos');
    exit;
}

// Redireciona quando algum dado estiver incorreto.
$falhar = function () {
    header('Location: ../index.php?pagina=enderecos&erro=1');
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

// Campos aceitos e seus limites.
$limites = [
    'identificacao' => 100,
    'cep' => 9,
    'logradouro' => 150,
    'numero' => 20,
    'complemento' => 100,
    'bairro' => 100,
    'cidade' => 100,
    'uf' => 2
];

$dados = [];

foreach ($limites as $campo => $limite) {
    $valor = $_POST[$campo] ?? '';

    if (!is_string($valor)) {
        $falhar();
    }

    $valor = trim($valor);

    if (
        ($campo !== 'complemento' && $valor === '') ||
        mb_strlen($valor, 'UTF-8') > $limite
    ) {
        $falhar();
    }

    $dados[$campo] = $valor;
}

$dados['uf'] = strtoupper($dados['uf']);

$ufs = [
    'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO',
    'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI',
    'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
];

// Confere apenas o formato do CEP, sem consultar sua existencia.
if (
    !preg_match('/^[0-9]{5}-?[0-9]{3}$/', $dados['cep']) ||
    !in_array($dados['uf'], $ufs, true)
) {
    $falhar();
}

// Padroniza o CEP com hifen.
$cep = str_replace('-', '', $dados['cep']);
$dados['cep'] = substr($cep, 0, 5) . '-' . substr($cep, 5);

try {
    if ($id !== null) {
        $consulta = $pdo->prepare('SELECT id FROM enderecos WHERE id = ?');
        $consulta->execute([$id]);

        if (!$consulta->fetch()) {
            $falhar();
        }

        $sql = 'UPDATE enderecos SET
                    identificacao = :identificacao,
                    cep = :cep,
                    logradouro = :logradouro,
                    numero = :numero,
                    complemento = :complemento,
                    bairro = :bairro,
                    cidade = :cidade,
                    uf = :uf
                WHERE id = :id';

        $dados['id'] = $id;
    } else {
        $sql = 'INSERT INTO enderecos
                    (identificacao, cep, logradouro, numero,
                     complemento, bairro, cidade, uf)
                VALUES
                    (:identificacao, :cep, :logradouro, :numero,
                     :complemento, :bairro, :cidade, :uf)';
    }

    $comando = $pdo->prepare($sql);
    $comando->execute($dados);
} catch (PDOException $erro) {
    error_log($erro->getMessage());
    $falhar();
}

header('Location: ../index.php?pagina=enderecos&sucesso=1');
exit;