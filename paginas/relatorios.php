<?php

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/relatorios.php';

$escapar = function ($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
};

$hoje = new DateTimeImmutable(
    'now',
    new DateTimeZone('America/Sao_Paulo')
);

$data = $_GET['data'] ?? $hoje->format('Y-m-d');
$relatorio = null;
$mensagemErro = '';

try {
    $relatorio = consultarRelatorio($pdo, $data);
} catch (InvalidArgumentException $erro) {
    $mensagemErro = 'Selecione uma data válida.';
} catch (PDOException $erro) {
    error_log($erro->getMessage());
    $mensagemErro = 'Não foi possível consultar o relatório.';
}
?>

<p class="text-secondary">
    Consulte os fretes por dia e baixe o relatório.
</p>

<section class="card mt-4">
    <div class="card-body">
        <h2 class="h5 mb-3">Consultar fretes do dia</h2>

        <form action="index.php" method="get">
            <input type="hidden" name="pagina" value="relatorios">

            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="data" class="form-label">
                        Selecione o dia *
                    </label>

                    <input
                        type="date"
                        id="data"
                        name="data"
                        class="form-control"
                        value="<?php
                            echo $escapar($relatorio['data'] ?? '');
                        ?>"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        Consultar
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

<?php if ($mensagemErro !== ''): ?>
    <div class="alert alert-danger mt-4">
        <?php echo $escapar($mensagemErro); ?>
    </div>
<?php endif; ?>

<?php if ($relatorio !== null): ?>

<div class="row g-3 mt-1">
    <div class="col-md-4">
        <section class="card h-100">
            <div class="card-body">
                <p class="text-secondary small">Data consultada</p>
                <strong>
                    <?php echo $escapar($relatorio['data_formatada']); ?>
                </strong>
            </div>
        </section>
    </div>

    <div class="col-md-4">
        <section class="card h-100">
            <div class="card-body">
                <p class="text-secondary small">Fretes encontrados</p>
                <strong>
                    <?php echo count($relatorio['fretes']); ?>
                </strong>
            </div>
        </section>
    </div>

    <div class="col-md-4">
        <section class="card h-100">
            <div class="card-body">
                <p class="text-secondary small">Valor total</p>
                <strong class="text-primary">
                    R$ <?php
                        echo number_format(
                            $relatorio['total_centavos'] / 100,
                            2,
                            ',',
                            '.'
                        );
                    ?>
                </strong>
            </div>
        </section>
    </div>
</div>

<section class="card mt-4">
    <div class="card-body">
        <h2 class="h5">
            Fretes de <?php echo $escapar($relatorio['data_formatada']); ?>
        </h2>

        <?php if (empty($relatorio['fretes'])): ?>
            <div class="alert alert-info mt-3">
                Nenhum frete encontrado para a data selecionada.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Transportador</th>
                            <th>Origem</th>
                            <th>Destino</th>
                            <th>Valor</th>
                            <th>Ação</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($relatorio['fretes'] as $frete): ?>
                            <tr>
                                <td><?php echo (int) $frete['id']; ?></td>

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
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <a
            href="acoes/baixar-relatorio.php?data=<?php
                echo $escapar($relatorio['data']);
            ?>"
            class="btn btn-primary mt-3"
        >
            Baixar relatório CSV
        </a>
    </div>
</section>

<?php endif; ?>