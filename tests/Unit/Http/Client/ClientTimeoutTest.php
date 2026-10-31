<?php

use Nfse\Enums\TipoAmbiente;
use Nfse\Http\Client\AdnClient;
use Nfse\Http\Client\CncClient;
use Nfse\Http\Client\SefinClient;
use Nfse\Http\NfseContext;

function opcoesCurlDoClientParaTimeout(object $client): array
{
    $property = new ReflectionProperty($client, 'httpClient');
    $property->setAccessible(true);

    return $property->getValue($client)->getConfig('curl');
}

it('uses a 10s connect timeout and keeps the 60s total timeout', function (string $clientClass) {
    $context = new NfseContext(TipoAmbiente::Producao, 'fake/path.pfx', 'password');
    $curl = opcoesCurlDoClientParaTimeout(new $clientClass($context));

    // Com o Retry (3 tentativas), o pior caso de um host inacessível fica em 3x10s + 3s.
    expect($curl[CURLOPT_CONNECTTIMEOUT])->toBe(10)
        ->and($curl[CURLOPT_TIMEOUT])->toBe(60);
})->with([
    'SEFIN' => [SefinClient::class],
    'ADN' => [AdnClient::class],
    'CNC' => [CncClient::class],
]);
