<?php
// Lista das paginas permitidas no sistema.
$paginas = [
    'inicio' => 'Início',
    'produtos' => 'Produtos',
    'enderecos' => 'Endereços',
    'transportadores' => 'Transportadores',
    'fretes' => 'Fretes',
    'relatorios' => 'Relatórios'
];

// Identifica qual pagina foi escolhida no menu.
$pagina = $_GET['pagina'] ?? 'inicio';

// Se a pagina nao existir, mostra o inicio.
if (!is_string($pagina) || !isset($paginas[$pagina])) {
    $pagina = 'inicio';
}

$titulo = $paginas[$pagina];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $titulo; ?> | Controle de Fretes</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-yEPZ7jP9S0QKiUQ0m3jjsO4ProIW7wvR9gXkI7JOGa2XVsw53Yke2bsdkBbjmmiX"
        crossorigin="anonymous"
    >
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <aside class="sidebar">
        <small class="text-primary fw-bold">CONTROLE DE FRETES</small>
        <h5 class="mt-2 mb-4">Sistema de<br>Controle de Fretes</h5>

        <nav class="nav flex-column" aria-label="Menu principal">
            <?php foreach ($paginas as $chave => $nome): ?>
                <a
                    class="nav-link <?php echo $pagina === $chave ? 'active' : ''; ?>"
                    href="index.php?pagina=<?php echo $chave; ?>"
                    <?php if ($pagina === $chave) echo 'aria-current="page"'; ?>
                >
                    <?php echo $nome; ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <div class="conteudo">
        <header class="cabecalho">
            <strong>Sistema de Controle de Fretes</strong>
        </header>

        <main class="area-principal">
            <p class="text-secondary small">
                Sistema / <?php echo $titulo; ?>
            </p>

            <h1 class="h3"><?php echo $titulo; ?></h1>

            <?php if ($pagina === 'inicio'): ?>
                <p class="text-secondary">
                    Organize seus cadastros e gerencie os fretes em um só lugar.
                </p>

                <section class="boas-vindas my-4">
                    <h2 class="h5 text-primary">
                        Bem-vindo ao Sistema de Controle de Fretes
                    </h2>
                    <p>
                        Comece cadastrando produtos, endereços e transportadores.
                        Depois, registre o frete e seus produtos.
                    </p>

                    <a href="index.php?pagina=fretes" class="btn btn-primary">
                        Consultar fretes
                    </a>
                </section>

                <h2 class="h5 mb-3">Cadastros e operações</h2>

                <div class="row g-3">
                    <?php
                    $cadastros = [
                        'produtos' => 'Cadastre nome, descrição e peso dos produtos.',
                        'enderecos' => 'Organize os locais de origem e destino.',
                        'transportadores' => 'Mantenha os contatos e dados dos veículos.'
                    ];
                    ?>

                    <?php foreach ($cadastros as $chave => $descricao): ?>
                        <div class="col-md-4">
                            <section class="card h-100">
                                <div class="card-body">
                                    <h3 class="h6">
                                        <?php echo $paginas[$chave]; ?>
                                    </h3>
                                    <p class="text-secondary small">
                                        <?php echo $descricao; ?>
                                    </p>
                                    <a
                                        href="index.php?pagina=<?php echo $chave; ?>"
                                        class="btn btn-outline-primary btn-sm"
                                    >
                                        Abrir cadastro
                                    </a>
                                </div>
                            </section>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4">
                    <a href="index.php?pagina=relatorios" class="btn btn-outline-primary">
                        Abrir relatórios
                    </a>
                </div>
            <?php else: ?>
                <div class="alert alert-info mt-4">
                    Esta seção será implementada nas próximas etapas.
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>