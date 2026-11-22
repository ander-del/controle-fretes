<?php

function consultarRelatorio(PDO $pdo, $data): array
{
    if (!is_string($data)) {
        throw new InvalidArgumentException('Data inválida.');
    }

    $dataConvertida = DateTimeImmutable::createFromFormat('!Y-m-d', $data);

    if (
        !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $data) ||
        !$dataConvertida ||
        $dataConvertida->format('Y-m-d') !== $data ||
        (int) substr($data, 0, 4) < 1000
    ) {
        throw new InvalidArgumentException('Data inválida.');
    }

    $consulta = $pdo->prepare(
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
         WHERE f.data = ?
         ORDER BY f.id'
    );

    $consulta->execute([$data]);
    $fretes = $consulta->fetchAll();

    // Soma em centavos para evitar imprecisao monetaria.
    $totalCentavos = 0;

    foreach ($fretes as $frete) {
        $totalCentavos += (int) str_replace('.', '', $frete['valor']);
    }

    return [
        'data' => $data,
        'data_formatada' => $dataConvertida->format('d/m/Y'),
        'fretes' => $fretes,
        'total_centavos' => $totalCentavos
    ];
}