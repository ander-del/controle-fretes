<?php

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/relatorios.php';

try {
    $relatorio = consultarRelatorio($pdo, $_GET['data'] ?? '');
} catch (InvalidArgumentException $erro) {
    http_response_code(400);
    exit('Data inválida. Volte ao sistema e selecione uma data.');
} catch (PDOException $erro) {
    error_log($erro->getMessage());
    http_response_code(500);
    exit('Não foi possível gerar o relatório.');
}

// Protege campos de texto ao abrir o CSV em uma planilha.
$textoSeguro = function ($texto) {
    $texto = (string) $texto;

    if (preg_match('/^[\s\x00-\x20]*[=+@-]/u', $texto)) {
        return "'" . $texto;
    }

    return $texto;
};

header('Content-Type: text/csv; charset=UTF-8');
header(
    'Content-Disposition: attachment; filename="fretes-' .
    $relatorio['data'] . '.csv"'
);

$saida = fopen('php://output', 'w');

// Permite reconhecer os acentos ao abrir no Excel.
fwrite($saida, "\xEF\xBB\xBF");

fputcsv(
    $saida,
    ['ID', 'Data', 'Transportador', 'Origem', 'Destino', 'Valor (R$)'],
    ';',
    '"',
    ''
);

foreach ($relatorio['fretes'] as $frete) {
    fputcsv(
        $saida,
        [
            $frete['id'],
            $relatorio['data_formatada'],
            $textoSeguro($frete['transportador']),
            $textoSeguro($frete['origem']),
            $textoSeguro($frete['destino']),
            number_format((float) $frete['valor'], 2, ',', '')
        ],
        ';',
        '"',
        ''
    );
}

fputcsv(
    $saida,
    [
        '',
        '',
        '',
        '',
        'Total do dia',
        number_format($relatorio['total_centavos'] / 100, 2, ',', '')
    ],
    ';',
    '"',
    ''
);

fclose($saida);
exit;