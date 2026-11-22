<?php

require_once __DIR__ . '/../config/conexao.php';

$escapar = function ($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
};

$enderecoEditar = null;
$idEditar = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);

if ($idEditar && $idEditar > 0) {
    $consulta = $pdo->prepare('SELECT * FROM enderecos WHERE id = ?');
    $consulta->execute([$idEditar]);
    $enderecoEditar = $consulta->fetch() ?: null;
}

$consulta = $pdo->query('SELECT * FROM enderecos ORDER BY id DESC');
$enderecos = $consulta->fetchAll();

// Nome do campo, rotulo, limite e largura no formulario.
$campos = [
    'identificacao' => ['Identificação do local', 100, 8],
    'cep' => ['CEP', 9, 4],
    'logradouro' => ['Logradouro', 150, 6],
    'numero' => ['Número', 20, 2],
    'complemento' => ['Complemento', 100, 4],
    'bairro' => ['Bairro', 100, 5],
    'cidade' => ['Cidade', 100, 5]
];

$ufs = [
    'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO',
    'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI',
    'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
];
?>

<p class="text-secondary">
    Cadastre os locais que serão usados como origem ou destino.
</p>

<?php if (isset($_GET['sucesso'])): ?>
    <div class="alert alert-success">
        Endereço salvo com sucesso!
    </div>
<?php endif; ?>

<?php if (isset($_GET['erro'])): ?>
    <div class="alert alert-danger">
        Não foi possível salvar. Confira os campos obrigatórios,
        o CEP e a UF.
    </div>
<?php endif; ?>

<?php if (isset($_GET['editar']) && !$enderecoEditar): ?>
    <div class="alert alert-warning">
        Endereço não encontrado. Selecione um endereço na tabela.
    </div>
<?php endif; ?>

<section class="card mt-4">
    <div class="card-body">
        <h2 class="h5 mb-3">
            <?php echo $enderecoEditar ? 'Editar endereço' : 'Novo endereço'; ?>
        </h2>

        <form action="acoes/salvar-endereco.php" method="post">
            <input
                type="hidden"
                name="id"
                value="<?php echo $escapar($enderecoEditar['id'] ?? ''); ?>"
            >

            <div class="mb-3">
                <label for="id" class="form-label">ID</label>
                <input
                    id="id"
                    class="form-control"
                    value="<?php
                        echo $escapar(
                            $enderecoEditar['id'] ?? 'Gerado ao salvar'
                        );
                    ?>"
                    disabled
                >
            </div>

            <div class="row g-3">
                <?php foreach ($campos as $campo => $configuracao): ?>
                    <div class="col-md-<?php echo $configuracao[2]; ?>">
                        <label for="<?php echo $campo; ?>" class="form-label">
                            <?php echo $configuracao[0]; ?>
                            <?php echo $campo !== 'complemento' ? '*' : ''; ?>
                        </label>

                        <input
                            type="text"
                            id="<?php echo $campo; ?>"
                            name="<?php echo $campo; ?>"
                            class="form-control"
                            maxlength="<?php echo $configuracao[1]; ?>"
                            value="<?php
                                echo $escapar($enderecoEditar[$campo] ?? '');
                            ?>"
                            <?php
                                echo $campo !== 'complemento' ? 'required' : '';
                            ?>
                        >
                    </div>
                <?php endforeach; ?>

                <div class="col-md-2">
                    <label for="uf" class="form-label">UF *</label>
                    <select id="uf" name="uf" class="form-select" required>
                        <option value="">Selecione</option>

                        <?php foreach ($ufs as $uf): ?>
                            <option
                                value="<?php echo $uf; ?>"
                                <?php
                                    echo ($enderecoEditar['uf'] ?? '') === $uf
                                        ? 'selected'
                                        : '';
                                ?>
                            >
                                <?php echo $uf; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <p class="text-secondary small mt-3">
                * Campos obrigatórios. CEP no formato 00000-000.
            </p>

            <button type="submit" class="btn btn-primary">
                <?php
                    echo $enderecoEditar
                        ? 'Salvar alterações'
                        : 'Salvar endereço';
                ?>
            </button>

            <?php if ($enderecoEditar): ?>
                <a
                    href="index.php?pagina=enderecos"
                    class="btn btn-outline-secondary"
                >
                    Cancelar
                </a>
            <?php else: ?>
                <button type="reset" class="btn btn-outline-secondary">
                    Limpar
                </button>
            <?php endif; ?>
        </form>
    </div>
</section>

<section class="card mt-4">
    <div class="card-body">
        <h2 class="h5">Endereços cadastrados</h2>

        <p class="text-secondary small">
            Total: <?php echo count($enderecos); ?> endereço(s).
        </p>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Identificação</th>
                        <th>Endereço</th>
                        <th>Cidade / UF</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($enderecos)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary">
                                Nenhum endereço cadastrado.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($enderecos as $endereco): ?>
                            <tr>
                                <td><?php echo (int) $endereco['id']; ?></td>
                                <td>
                                    <?php echo $escapar($endereco['identificacao']); ?>
                                </td>
                                <td>
                                    <?php
                                        echo $escapar(
                                            $endereco['logradouro'] . ', ' .
                                            $endereco['numero'] . ' - ' .
                                            $endereco['bairro']
                                        );
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        echo $escapar(
                                            $endereco['cidade'] . ' / ' .
                                            $endereco['uf']
                                        );
                                    ?>
                                </td>
                                <td>
                                    <a
                                        href="index.php?pagina=enderecos&editar=<?php
                                            echo (int) $endereco['id'];
                                        ?>"
                                        class="btn btn-outline-primary btn-sm"
                                    >
                                        Editar
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