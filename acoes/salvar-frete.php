<?php

require_once __DIR__ . '/../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?pagina=fretes');
    exit;
}

$falhar = function () {
    header('Location: ../index.php?pagina=fretes&erro=1');
    exit;
};

// Le um campo de texto sem aceitar arrays.
$lerTexto = function ($campo) use ($falhar) {
    $valor = $_POST[$campo] ?? '';

    if (!is_string($valor)) {
        $falhar();
    }

    return trim($valor);
};

// Valida identificadores de registros.
$lerId = function ($valor) use ($falhar) {
    if (!is_string($valor)) {
        $falhar();
    }

    $id = filter_var(
        $valor,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($id === false) {
        $falhar();
    }

    return $id;
};

$data = $lerTexto('data');
$observacoes = $lerTexto('observacoes');

$transportadorId = $lerId($_POST['transportador_id'] ?? '');
$origemId = $lerId($_POST['origem_id'] ?? '');
$destinoId = $lerId($_POST['destino_id'] ?? '');

// Confere se a data existe e esta no formato esperado.
$dataConvertida = DateTimeImmutable::createFromFormat('!Y-m-d', $data);

if (
    !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $data) ||
    !$dataConvertida ||
    $dataConvertida->format('Y-m-d') !== $data ||
    (int) substr($data, 0, 4) < 1000 ||
    $origemId === $destinoId ||
    mb_strlen($observacoes, 'UTF-8') > 2000
) {
    $falhar();
}

// Prepara o valor monetario sem usar calculos com float.
$valorInformado = str_replace(',', '.', $lerTexto('valor'));

if (!preg_match('/^[0-9]{1,8}(\.[0-9]{1,2})?$/', $valorInformado)) {
    $falhar();
}

$partes = explode('.', $valorInformado);
$reais = (int) $partes[0];
$centavos = str_pad($partes[1] ?? '', 2, '0');

if ($reais === 0 && (int) $centavos === 0) {
    $falhar();
}

$valor = $reais . '.' . $centavos;

// Confere as listas de produtos e quantidades.
$produtos = $_POST['produtos'] ?? [];
$quantidades = $_POST['quantidades'] ?? [];

if (
    !is_array($produtos) ||
    !is_array($quantidades) ||
    !array_is_list($produtos) ||
    !array_is_list($quantidades) ||
    count($produtos) < 1 ||
    count($produtos) > 100 ||
    count($produtos) !== count($quantidades)
) {
    $falhar();
}

$itens = [];

foreach ($produtos as $indice => $produtoInformado) {
    $produtoId = $lerId($produtoInformado);

    if (!is_string($quantidades[$indice])) {
        $falhar();
    }

    $quantidade = filter_var(
        $quantidades[$indice],
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 1000000]]
    );

    if ($quantidade === false || isset($itens[$produtoId])) {
        $falhar();
    }

    $itens[$produtoId] = $quantidade;
}

try {
    // Salva o frete e seus produtos como uma unica operacao.
    $pdo->beginTransaction();

    $consulta = $pdo->prepare(
        'SELECT id FROM transportadores WHERE id = ?'
    );
    $consulta->execute([$transportadorId]);

    if (!$consulta->fetch()) {
        throw new RuntimeException('Transportador inexistente.');
    }

    $consulta = $pdo->prepare(
        'SELECT id FROM enderecos WHERE id IN (?, ?)'
    );
    $consulta->execute([$origemId, $destinoId]);

    if (count($consulta->fetchAll()) !== 2) {
        throw new RuntimeException('Endereco inexistente.');
    }

    // Busca os pesos no banco, sem confiar no valor exibido no navegador.
    $buscarProduto = $pdo->prepare(
        'SELECT peso FROM produtos WHERE id = ? FOR UPDATE'
    );

    $pesos = [];

    foreach ($itens as $produtoId => $quantidade) {
        $buscarProduto->execute([$produtoId]);
        $produto = $buscarProduto->fetch();

        if (!$produto) {
            throw new RuntimeException('Produto inexistente.');
        }

        $pesos[$produtoId] = $produto['peso'];
    }

    $comando = $pdo->prepare(
        'INSERT INTO fretes
            (data, transportador_id, origem_id, destino_id, valor, observacoes)
         VALUES (?, ?, ?, ?, ?, ?)'
    );

    $comando->execute([
        $data,
        $transportadorId,
        $origemId,
        $destinoId,
        $valor,
        $observacoes
    ]);

    $freteId = $pdo->lastInsertId();

    $salvarItem = $pdo->prepare(
        'INSERT INTO frete_produtos
            (frete_id, produto_id, quantidade, peso_unitario)
         VALUES (?, ?, ?, ?)'
    );

    foreach ($itens as $produtoId => $quantidade) {
        $salvarItem->execute([
            $freteId,
            $produtoId,
            $quantidade,
            $pesos[$produtoId]
        ]);
    }

    $pdo->commit();
} catch (Throwable $erro) {
    // Se algo falhar, desfaz toda a operacao.
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($erro->getMessage());
    $falhar();
}

header('Location: ../index.php?pagina=fretes&sucesso=1');
exit;