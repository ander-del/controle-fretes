<?php

require_once __DIR__ . '/../config/conexao.php';

// Protege os textos exibidos no HTML.
$escapar = function ($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
};

$produtoEditar = null;

// Busca o produto selecionado para edicao.
$idEditar = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);

if ($idEditar && $idEditar > 0) {
    $consulta = $pdo->prepare('SELECT * FROM produtos WHERE id = ?');
    $consulta->execute([$idEditar]);
    $produtoEditar = $consulta->fetch() ?: null;
}

// Busca a lista de produtos.
$consulta = $pdo->query('SELECT * FROM produtos ORDER BY id DESC');
$produtos = $consulta->fetchAll();
?>

<p class="text-secondary">
    Cadastre os produtos que poderão ser vinculados aos fretes.
</p>

<?php if (isset($_GET['sucesso'])): ?>
    <div class="alert alert-success">
        Produto salvo com sucesso!
    </div>
<?php endif; ?>

<?php if (isset($_GET['erro'])): ?>
    <div class="alert alert-danger">
        Não foi possível salvar. Confira os dados informados.
    </div>
<?php endif; ?>

<?php if (isset($_GET['editar']) && !$produtoEditar): ?>
    <div class="alert alert-warning">
        Produto não encontrado. Selecione um produto na tabela.
    </div>
<?php endif; ?>

<section class="card mt-4">
    <div class="card-body">
        <h2 class="h5 mb-3">
            <?php echo $produtoEditar ? 'Editar produto' : 'Novo produto'; ?>
        </h2>

        <form action="acoes/salvar-produto.php" method="post">
            <input
                type="hidden"
                name="id"
                value="<?php echo $escapar($produtoEditar['id'] ?? ''); ?>"
            >

            <div class="row g-3">
                <div class="col-md-2">
                    <label for="id" class="form-label">ID</label>
                    <input
                        id="id"
                        class="form-control"
                        value="<?php
                            echo $escapar(
                                $produtoEditar['id'] ?? 'Gerado ao salvar'
                            );
                        ?>"
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
                        value="<?php
                            echo $escapar($produtoEditar['nome'] ?? '');
                        ?>"
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
                        value="<?php
                            echo $escapar($produtoEditar['peso'] ?? '');
                        ?>"
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
                    ><?php
                        echo $escapar($produtoEditar['descricao'] ?? '');
                    ?></textarea>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <?php
                        echo $produtoEditar
                            ? 'Salvar alterações'
                            : 'Salvar produto';
                    ?>
                </button>

                <?php if ($produtoEditar): ?>
                    <a
                        href="index.php?pagina=produtos"
                        class="btn btn-outline-secondary"
                    >
                        Cancelar
                    </a>
                <?php else: ?>
                    <button
                        type="reset"
                        class="btn btn-outline-secondary"
                    >
                        Limpar
                    </button>
                <?php endif; ?>
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
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($produtos)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary">
                                Nenhum produto cadastrado.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($produtos as $produto): ?>
                            <tr>
                                <td><?php echo (int) $produto['id']; ?></td>
                                <td><?php echo $escapar($produto['nome']); ?></td>
                                <td><?php echo $escapar($produto['descricao']); ?></td>
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
                                <td>
                                    <a
                                        href="index.php?pagina=produtos&editar=<?php
                                            echo (int) $produto['id'];
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