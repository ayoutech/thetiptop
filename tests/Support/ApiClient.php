<?php
namespace Ttt\Tests;

/** Client de l'API REST qui signe les requêtes comme le fait un vrai consommateur (HMAC-SHA256). */
final class ApiClient
{
    private Http $http;

    public function __construct(private string $base, private string $apiKey, private string $secret = TTT_TEST_SECRET)
    {
        $this->http = new Http();
    }

    /** Requête correctement signée ; chaque en-tête peut être surchargé pour simuler une attaque. */
    public function call(string $pathAndQuery, array $override = [], string $method = 'GET', string $body = ''): array
    {
        $ts = (string)($override['timestamp'] ?? time());
        $signedUri = $override['signed_uri'] ?? $pathAndQuery;
        $sig = $override['signature'] ?? api_sign(api_build_payload($method, $signedUri, $ts, $body), $override['secret'] ?? $this->secret);

        $headers = [
            'X-Api-Key'   => $override['api_key'] ?? $this->apiKey,
            'X-Timestamp' => $ts,
            'X-Signature' => $sig,
        ];
        if (($override['https'] ?? true) === true) {
            $headers['X-Forwarded-Proto'] = 'https';
        }
        foreach (($override['drop'] ?? []) as $h) {
            unset($headers[$h]);
        }
        foreach (($override['headers'] ?? []) as $k => $v) {
            $headers[$k] = $v;
        }
        $r = $this->http->request($method, $this->base . $pathAndQuery, $headers, $body !== '' ? $body : null);
        $r['json'] = json_decode($r['body'], true);
        return $r;
    }
}
