<?php

require_once __DIR__ . '/../config/conexao.php';

$escapar = function ($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
};
// Quando um frete for selecionado, mostra somente seus detalhes.
if (isset($_GET['detalhe'])) {
    require __DIR__ . '/detalhes-frete.php';
    return;
}

$produtos = $pdo->query(
    'SELECT id, nome, peso FROM produtos ORDER BY nome'
)->fetchAll();

$enderecos = $pdo->query(
    'SELECT id, identificacao, cidade, uf FROM enderecos
     ORDER BY identificacao'
)->fetchAll();

$transportadores = $pdo->query(
    'SELECT id, nome, placa FROM transportadores ORDER BY nome'
)->fetchAll();

$cadastrosProntos =
    count($produtos) > 0 &&
    count($enderecos) >= 2 &&
    count($transportadores) > 0;
?>

<p class="text-secondary">
    Defina o trajeto, o transportador e os produtos desta operação.
</p>

<?php if (isset($_GET['sucesso'])): ?>
    <div class="alert alert-success">
        Frete salvo com sucesso!
    </div>
<?php endif; ?>

<?php if (isset($_GET['erro'])): ?>
    <div class="alert alert-danger">
        Não foi possível salvar o frete. Confira os campos,
        escolha origem e destino diferentes e adicione
        produtos sem repetições, com quantidades inteiras.
    </div>
<?php endif; ?>
<?php if (!$cadastrosProntos): ?>
    <div class="alert alert-warning">
        Para criar um frete, cadastre pelo menos um produto,
        dois endereços e um transportador.
    </div>
<?php else: ?>

<section class="card mt-4">
    <div class="card-body">
        <h2 class="h5 mb-3">Novo frete</h2>

        <form
    id="form-frete"
    action="acoes/salvar-frete.php"
    method="post"
>
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="data" class="form-label">Data *</label>
                    <input
                        type="date"
                        id="data"
                        name="data"
                        class="form-control"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label for="transportador" class="form-label">
                        Transportador *
                    </label>
                    <select
                        id="transportador"
                        name="transportador_id"
                        class="form-select"
                        required
                    >
                        <option value="">Selecione</option>

                        <?php foreach ($transportadores as $transportador): ?>
                            <option value="<?php echo (int) $transportador['id']; ?>">
                                <?php
                                    echo $escapar(
                                        $transportador['nome'] . ' - ' .
                                        $transportador['placa']
                                    );
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="valor" class="form-label">
                        Valor do frete (R$) *
                    </label>
                    <input
                        type="number"
                        id="valor"
                        name="valor"
                        class="form-control"
                        min="0.01"
                        max="99999999.99"
                        step="0.01"
                        required
                    >
                </div>

                <?php
                foreach (
                    ['origem_id' => 'Origem', 'destino_id' => 'Destino']
                    as $campo => $rotulo
                ):
                ?>
                    <div class="col-md-6">
                        <label for="<?php echo $campo; ?>" class="form-label">
                            <?php echo $rotulo; ?> *
                        </label>

                        <select
                            id="<?php echo $campo; ?>"
                            name="<?php echo $campo; ?>"
                            class="form-select"
                            required
                        >
                            <option value="">Selecione</option>

                            <?php foreach ($enderecos as $endereco): ?>
                                <option value="<?php echo (int) $endereco['id']; ?>">
                                    <?php
                                        echo $escapar(
                                            $endereco['identificacao'] . ' - ' .
                                            $endereco['cidade'] . ' / ' .
                                            $endereco['uf']
                                        );
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>

                <div class="col-12">
                    <label for="observacoes" class="form-label">
                        Observações
                    </label>
                    <textarea
                        id="observacoes"
                        name="observacoes"
                        class="form-control"
                        rows="2"
                        maxlength="2000"
                    ></textarea>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
                <h3 class="h5 mb-0">Produtos do frete</h3>

                <button
                    type="button"
                    id="adicionar-produto"
                    class="btn btn-outline-primary btn-sm"
                >
                    Adicionar produto
                </button>
            </div>

            <p class="text-secondary small">
                Um transportador pode levar vários produtos.
                Adicione cada produto apenas uma vez e informe a quantidade.
            </p>

            <div id="itens-frete"></div>

            <p class="fw-bold mt-3">
                Peso total:
                <span id="peso-total">0,00</span> kg
            </p>

            <p class="text-secondary small">
                * Campos obrigatórios. Origem e destino devem ser diferentes.
            </p>

            <button type="submit" class="btn btn-primary">
                Salvar frete
            </button>

            <a
                href="index.php?pagina=fretes"
                class="btn btn-outline-secondary"
            >
                Limpar formulário
            </a>

            <div
                id="resultado-conferencia"
                class="mt-3"
                role="status"
            ></div>
        </form>
    </div>
</section>

<!-- Modelo usado pelo JavaScript para criar cada linha de produto. -->
<template id="modelo-produto">
    <div class="item-frete border rounded p-3 mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label w-100">
                    Produto *
                    <select
                        name="produtos[]"
                        class="form-select produto-select mt-2"
                        required
                    >
                        <option value="">Selecione</option>

                        <?php foreach ($produtos as $produto): ?>
                            <option
                                value="<?php echo (int) $produto['id']; ?>"
                                data-peso="<?php echo $escapar($produto['peso']); ?>"
                            >
                                <?php echo $escapar($produto['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <div class="col-md-2">
                <label class="form-label w-100">
                    Quantidade *
                    <input
                        type="number"
                        name="quantidades[]"
                        class="form-control quantidade mt-2"
                        min="1"
                        max="1000000"
                        step="1"
                        value="1"
                        required
                    >
                </label>
            </div>

            <div class="col-md-2">
                <label class="form-label w-100">
                    Peso unitário (kg)
                    <input
                        class="form-control peso-unitario mt-2"
                        value="0,00"
                        readonly
                    >
                </label>
            </div>

            <div class="col-md-2 pb-2">
                <button
                    type="button"
                    class="btn btn-outline-danger btn-sm remover-produto"
                >
                    Remover
                </button>
            </div>
        </div>
    </div>
</template>

<script src="assets/fretes.js"></script>

<?php endif; ?>
<?php
$consulta = $pdo->query(
    'SELECT
        f.id,
        f.data,
        f.valor,
        t.nome AS transportador,
        origem.identificacao AS origem,
        destino.identificacao AS destino
     FROM fretes f
     JOIN transportadores t ON t.id = f.transportador_id
     JOIN enderecos origem ON origem.id = f.origem_id
     JOIN enderecos destino ON destino.id = f.destino_id
     ORDER BY f.id DESC'
);

$fretes = $consulta->fetchAll();
?>

<section class="card mt-4">
    <div class="card-body">
        <h2 class="h5">Fretes cadastrados</h2>

        <p class="text-secondary small">
            Total: <?php echo count($fretes); ?> frete(s).
        </p>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Data</th>
                        <th>Transportador</th>
                        <th>Origem</th>
                        <th>Destino</th>
                        <th>Valor</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($fretes)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-secondary">
                                Nenhum frete cadastrado.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($fretes as $frete): ?>
                            <tr>
                                <td><?php echo (int) $frete['id']; ?></td>
                                <td>
                                    <?php
                                        echo date(
                                            'd/m/Y',
                                            strtotime($frete['data'])
                                        );
                                    ?>
                                </td>
                                <td>
                                    <?php echo $escapar($frete['transportador']); ?>
                                </td>
                                <td><?php echo $escapar($frete['origem']); ?></td>
                                <td><?php echo $escapar($frete['destino']); ?></td>
                                <td class="text-nowrap">
                                    R$ <?php
                                        echo number_format(
                                            (float) $frete['valor'],
                                            2,
                                            ',',
                                            '.'
                                        );
                                    ?>
                                </td>
                                <td>
                                    <a
                                        href="index.php?pagina=fretes&detalhe=<?php
                                            echo (int) $frete['id'];
                                        ?>"
                                        class="btn btn-outline-primary btn-sm"
                                    >
                                        Ver detalhes
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>