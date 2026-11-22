<?php

require_once __DIR__ . '/../config/conexao.php';

// Busca os produtos cadastrados no banco.
$consulta = $pdo->query('SELECT * FROM produtos ORDER BY id DESC');
$produtos = $consulta->fetchAll();
?>

<p class="text-secondary">
    Cadastre os produtos que poderão ser vinculados aos fretes.
</p>

<?php if (isset($_GET['sucesso'])): ?>
    <div class="alert alert-success">
        Produto cadastrado com sucesso!
    </div>
<?php endif; ?>

<?php if (isset($_GET['erro'])): ?>
    <div class="alert alert-danger">
        Não foi possível cadastrar. Informe um nome de até
        100 caracteres e um peso válido maior que zero.
    </div>
<?php endif; ?>

<section class="card mt-4">
    <div class="card-body">
        <h2 class="h5 mb-3">Novo produto</h2>

        <form action="acoes/salvar-produto.php" method="post">
            <div class="row g-3">
                <div class="col-md-2">
                    <label for="id" class="form-label">ID</label>
                    <input
                        id="id"
                        class="form-control"
                        value="Gerado ao salvar"
                        disabled
                    >
                </div>

                <div class="col-md-7">
                    <label for="nome" class="form-label">Nome *</label>
                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        class="form-control"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <label for="peso" class="form-label">
                        Peso unitário (kg) *
                    </label>
                    <input
                        type="number"
                        id="peso"
                        name="peso"
                        class="form-control"
                        min="0.01"
                        max="99999999.99"
                        step="0.01"
                        required
                    >
                </div>

                <div class="col-12">
                    <label for="descricao" class="form-label">
                        Descrição
                    </label>
                    <textarea
                        id="descricao"
                        name="descricao"
                        class="form-control"
                        rows="3"
                        maxlength="2000"
                    ></textarea>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    Salvar produto
                </button>
                <button type="reset" class="btn btn-outline-secondary">
                    Limpar
                </button>
            </div>

            <p class="text-secondary small mt-3 mb-0">
                * Campos obrigatórios. O ID é gerado automaticamente.
            </p>
        </form>
    </div>
</section>
<section class="card mt-4">
    <div class="card-body">
        <h2 class="h5 mb-3">Produtos cadastrados</h2>

        <p class="text-secondary small">
            Total: <?php echo count($produtos); ?> produto(s).
        </p>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Peso (kg)</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($produtos)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-4">
                                Nenhum produto cadastrado.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($produtos as $produto): ?>
                            <tr>
                                <td><?php echo (int) $produto['id']; ?></td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $produto['nome'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $produto['descricao'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo number_format(
                                        (float) $produto['peso'],
                                        2,
                                        ',',
                                        '.'
                                    );
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>