<?php

require_once __DIR__ . '/../config/conexao.php';

$escapar = function ($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
};

$transportadorEditar = null;
$idEditar = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);

if ($idEditar && $idEditar > 0) {
    $consulta = $pdo->prepare(
        'SELECT * FROM transportadores WHERE id = ?'
    );
    $consulta->execute([$idEditar]);
    $transportadorEditar = $consulta->fetch() ?: null;
}

$consulta = $pdo->query(
    'SELECT * FROM transportadores ORDER BY id DESC'
);
$transportadores = $consulta->fetchAll();

// Rotulo, limite, largura e tipo de cada campo.
$campos = [
    'nome' => ['Nome / razão social', 150, 8, 'text'],
    'documento' => ['CPF / CNPJ', 18, 4, 'text'],
    'telefone' => ['Telefone', 20, 4, 'tel'],
    'email' => ['E-mail', 150, 5, 'email'],
    'placa' => ['Placa do veículo', 8, 3, 'text']
];
?>

<p class="text-secondary">
    Cadastre os transportadores e os dados dos seus veículos.
</p>

<?php if (isset($_GET['sucesso'])): ?>
    <div class="alert alert-success">
        Transportador salvo com sucesso!
    </div>
<?php endif; ?>

<?php if (isset($_GET['erro'])): ?>
    <div class="alert alert-danger">
        Não foi possível salvar. Confira os campos obrigatórios.
        Informe CPF com 11 dígitos ou CNPJ com 14 dígitos.
        Confira também o e-mail e a placa.
    </div>
<?php endif; ?>

<?php if (isset($_GET['editar']) && !$transportadorEditar): ?>
    <div class="alert alert-warning">
        Transportador não encontrado. Selecione um registro na tabela.
    </div>
<?php endif; ?>

<section class="card mt-4">
    <div class="card-body">
        <h2 class="h5 mb-3">
            <?php
                echo $transportadorEditar
                    ? 'Editar transportador'
                    : 'Novo transportador';
            ?>
        </h2>

        <form action="acoes/salvar-transportador.php" method="post">
            <input
                type="hidden"
                name="id"
                value="<?php
                    echo $escapar($transportadorEditar['id'] ?? '');
                ?>"
            >

            <div class="mb-3">
                <label for="id" class="form-label">ID</label>
                <input
                    id="id"
                    class="form-control"
                    value="<?php
                        echo $escapar(
                            $transportadorEditar['id'] ?? 'Gerado ao salvar'
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
                            <?php echo $campo !== 'email' ? '*' : ''; ?>
                        </label>

                        <input
                            type="<?php echo $configuracao[3]; ?>"
                            id="<?php echo $campo; ?>"
                            name="<?php echo $campo; ?>"
                            class="form-control"
                            maxlength="<?php echo $configuracao[1]; ?>"
                            value="<?php
                                echo $escapar(
                                    $transportadorEditar[$campo] ?? ''
                                );
                            ?>"
                            <?php echo $campo !== 'email' ? 'required' : ''; ?>
                        >
                    </div>
                <?php endforeach; ?>
            </div>

            <p class="text-secondary small mt-3">
                * Campos obrigatórios. Exemplos de placa:
                ABC1234 ou ABC1D23.
            </p>

            <button type="submit" class="btn btn-primary">
                <?php
                    echo $transportadorEditar
                        ? 'Salvar alterações'
                        : 'Salvar transportador';
                ?>
            </button>

            <?php if ($transportadorEditar): ?>
                <a
                    href="index.php?pagina=transportadores"
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
        <h2 class="h5">Transportadores cadastrados</h2>

        <p class="text-secondary small">
            Total: <?php echo count($transportadores); ?> transportador(es).
        </p>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nome / razão social</th>
                        <th>CPF / CNPJ</th>
                        <th>Telefone</th>
                        <th>Placa</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transportadores)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-secondary">
                                Nenhum transportador cadastrado.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transportadores as $transportador): ?>
                            <tr>
                                <td>
                                    <?php echo (int) $transportador['id']; ?>
                                </td>

                                <?php
                                foreach (
                                    ['nome', 'documento', 'telefone', 'placa']
                                    as $campo
                                ):
                                ?>
                                    <td>
                                        <?php
                                            echo $escapar(
                                                $transportador[$campo]
                                            );
                                        ?>
                                    </td>
                                <?php endforeach; ?>

                                <td>
                                    <a
                                        href="index.php?pagina=transportadores&editar=<?php
                                            echo (int) $transportador['id'];
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