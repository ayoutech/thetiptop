<?php
namespace Ttt\Tests;

/** Client HTTP minimal (curl) : ne suit jamais les redirections, garde un cookie jar par instance. */
final class Http
{
    private string $jar;

    public function __construct()
    {
        $this->jar = tempnam(sys_get_temp_dir(), 'ttt_jar_');
    }

    /** Chemin du cookie jar (pour lancer des requêtes parallèles avec la même session). */
    public function jarPath(): string
    {
        return $this->jar;
    }

    public function __destruct()
    {
        @unlink($this->jar);
    }

    /** @return array{status:int, headers:array<string,string>, body:string} */
    public function request(string $method, string $url, array $headers = [], $body = null): array
    {
        $ch = curl_init($url);
        $resp = [];
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_COOKIEJAR      => $this->jar,
            CURLOPT_COOKIEFILE     => $this->jar,
            CURLOPT_HTTPHEADER     => array_map(fn($k, $v) => "$k: $v", array_keys($headers), $headers),
            CURLOPT_HEADERFUNCTION => function ($c, $line) use (&$resp) {
                if (strpos($line, ':') !== false) {
                    [$k, $v] = explode(':', $line, 2);
                    $resp[strtolower(trim($k))] = trim($v);
                }
                return strlen($line);
            },
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($body) ? http_build_query($body) : $body);
        }
        $out = curl_exec($ch);
        if ($out === false) {
            throw new \RuntimeException('Requête HTTP en échec : ' . curl_error($ch));
        }
        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        return ['status' => $status, 'headers' => $resp, 'body' => (string)$out];
    }

    /** Récupère le jeton CSRF d'une page contenant un formulaire (comme le ferait un navigateur). */
    public function csrfToken(string $pageUrl): string
    {
        $page = $this->get($pageUrl);
        if (!preg_match('/name="csrf_token" value="([a-f0-9]{64})"/', $page['body'], $m)) {
            throw new \RuntimeException("Aucun jeton CSRF sur $pageUrl (HTTP {$page['status']})");
        }
        return $m[1];
    }

    /** Simule un vrai utilisateur : ouvre la page du formulaire, lit le jeton, envoie le formulaire. */
    public function postForm(string $pageUrl, array $fields, ?string $actionUrl = null): array
    {
        $fields['csrf_token'] = $this->csrfToken($pageUrl);
        return $this->post($actionUrl ?? $pageUrl, $fields);
    }

    public function get(string $url, array $headers = []): array
    {
        return $this->request('GET', $url, $headers);
    }

    public function post(string $url, array $fields, array $headers = []): array
    {
        return $this->request('POST', $url, $headers, $fields);
    }
}
