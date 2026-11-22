<?php

require_once __DIR__ . '/../config/conexao.php';

$id = filter_input(
    INPUT_GET,
    'detalhe',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$frete = null;

if ($id) {
    $consulta = $pdo->prepare(
        'SELECT
            f.*,
            t.nome AS transportador,
            t.documento,
            t.telefone,
            t.placa
         FROM fretes f
         JOIN transportadores t ON t.id = f.transportador_id
         WHERE f.id = ?'
    );

    $consulta->execute([$id]);
    $frete = $consulta->fetch();
}

if (!$frete):
?>
    <div class="alert alert-warning">Frete não encontrado.</div>
    <a href="index.php?pagina=fretes" class="btn btn-outline-primary">
        Voltar para fretes
    </a>
<?php
    return;
endif;

// Busca os enderecos completos.
$buscarEndereco = $pdo->prepare(
    'SELECT * FROM enderecos WHERE id = ?'
);

$locais = [];

foreach (['origem' => 'Origem', 'destino' => 'Destino'] as $campo => $rotulo) {
    $buscarEndereco->execute([$frete[$campo . '_id']]);
    $locais[$rotulo] = $buscarEndereco->fetch();
}

// Usa o peso que foi registrado no momento do frete.
$consulta = $pdo->prepare(
    'SELECT
        p.nome,
        fp.produto_id,
        fp.quantidade,
        fp.peso_unitario
     FROM frete_produtos fp
     JOIN produtos p ON p.id = fp.produto_id
     WHERE fp.frete_id = ?
     ORDER BY fp.id'
);

$consulta->execute([$id]);
$itens = $consulta->fetchAll();

$pesoTotal = 0;
$totalUnidades = 0;

foreach ($itens as $item) {
    $pesoTotal += (float) $item['peso_unitario'] * (int) $item['quantidade'];
    $totalUnidades += (int) $item['quantidade'];
}
?>

<div class="d-flex justify-content-between align-items-center my-3">
    <h2 class="h4 mb-0">
        Detalhes do frete #<?php echo (int) $frete['id']; ?>
    </h2>

    <a href="index.php?pagina=fretes" class="btn btn-outline-primary">
        Voltar para fretes
    </a>
</div>

<section class="card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <strong>Data</strong>
                <p>
                    <?php echo date('d/m/Y', strtotime($frete['data'])); ?>
                </p>
            </div>

            <div class="col-md-4">
                <strong>Transportador</strong>
                <p><?php echo $escapar($frete['transportador']); ?></p>
            </div>

            <div class="col-md-4">
                <strong>Valor do frete</strong>
                <p>
                    R$ <?php
                        echo number_format((float) $frete['valor'], 2, ',', '.');
                    ?>
                </p>
            </div>

            <div class="col-md-4">
                <strong>CPF / CNPJ</strong>
                <p><?php echo $escapar($frete['documento']); ?></p>
            </div>

            <div class="col-md-4">
                <strong>Telefone</strong>
                <p><?php echo $escapar($frete['telefone']); ?></p>
            </div>

            <div class="col-md-4">
                <strong>Placa</strong>
                <p><?php echo $escapar($frete['placa']); ?></p>
            </div>
        </div>
    </div>
</section>

<div class="row g-3 mt-1">
    <?php foreach ($locais as $rotulo => $local): ?>
        <div class="col-md-6">
            <section class="card h-100">
                <div class="card-body">
                    <h3 class="h6 text-primary"><?php echo $rotulo; ?></h3>

                    <strong><?php echo $escapar($local['identificacao']); ?></strong>

                    <p class="mt-2 mb-0">
                        <?php
                            echo $escapar(
                                $local['logradouro'] . ', ' . $local['numero']
                            );
                        ?><br>

                        <?php if (!empty($local['complemento'])): ?>
                            <?php echo $escapar($local['complemento']); ?><br>
                        <?php endif; ?>

                        <?php
                            echo $escapar(
                                $local['bairro'] . ' - ' .
                                $local['cidade'] . ' / ' . $local['uf']
                            );
                        ?><br>

                        CEP: <?php echo $escapar($local['cep']); ?>
                    </p>
                </div>
            </section>
        </div>
    <?php endforeach; ?>
</div>

<section class="card mt-4">
    <div class="card-body">
        <h3 class="h5">Produtos transportados</h3>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID do produto</th>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Peso unitário</th>
                        <th>Peso total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itens as $item): ?>
                        <tr>
                            <td><?php echo (int) $item['produto_id']; ?></td>
                            <td><?php echo $escapar($item['nome']); ?></td>
                            <td><?php echo (int) $item['quantidade']; ?></td>
                            <td>
                                <?php
                                    echo number_format(
                                        (float) $item['peso_unitario'],
                                        2,
                                        ',',
                                        '.'
                                    );
                                ?> kg
                            </td>
                            <td>
                                <?php
                                    echo number_format(
                                        (float) $item['peso_unitario'] *
                                        (int) $item['quantidade'],
                                        2,
                                        ',',
                                        '.'
                                    );
                                ?> kg
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p>
            <?php echo count($itens); ?> produto(s) |
            <?php echo $totalUnidades; ?> unidade(s)
        </p>

        <p class="fw-bold">
            Peso total:
            <?php echo number_format($pesoTotal, 2, ',', '.'); ?> kg
        </p>

        <h3 class="h6 mt-4">Observações</h3>
        <p>
            <?php
                echo trim($frete['observacoes'] ?? '') !== ''
                    ? nl2br($escapar($frete['observacoes']))
                    : 'Nenhuma observação.';
            ?>
        </p>
    </div>
</section>