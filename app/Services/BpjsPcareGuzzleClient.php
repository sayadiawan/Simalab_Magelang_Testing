<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class BpjsPcareGuzzleClient
{
    private Client $client;
    private string $consId;
    private string $secretKey;
    private string $userKey;
    private string $pcareUser;
    private string $pcarePass;
    private string $kdAplikasi;
    private string $baseUrl;
    private string $service;

    public function __construct()
    {
        $cfg = config('bpjs');
        $this->consId     = $cfg['cons_id'];
        $this->secretKey  = $cfg['secret_key'];
        $this->userKey    = $cfg['user_key'];
        $this->pcareUser  = $cfg['pcare_user'];
        $this->pcarePass  = $cfg['pcare_pass'];
        $this->kdAplikasi = $cfg['kd_aplikasi'];
        $this->baseUrl    = rtrim($cfg['base_url'], '/');
        $this->service    = trim($cfg['service'], '/');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 20,
            'verify'   => false,
        ]);
    }

    private function timestampUtc(): string
    {
        return (string) now('UTC')->timestamp;
    }

    private function signature(string $tStamp): string
    {
        $data = $this->consId . '&' . $tStamp;
        return base64_encode(hash_hmac('sha256', $data, $this->secretKey, true));
    }

    private function basicAuthHeader(): string
    {
        $raw = $this->pcareUser . ':' . $this->pcarePass . ':' . $this->kdAplikasi;
        return 'Basic ' . base64_encode($raw);
    }

    public function headers(): array
    {
        $t = $this->timestampUtc();
        return [
            'X-cons-id'   => $this->consId,
            'X-timestamp' => $t,
            'X-signature' => $this->signature($t),
            'X-User-Key'  => $this->userKey,
            'Authorization' => $this->basicAuthHeader(),
            'Accept'      => 'application/json',
            'Content-Type'=> 'application/json',
        ];
    }

    // Contoh method untuk cek referensi poli
    public function getReferensiPoli(): array
    {
        try {
            $url = "/{$this->service}/referensi/poli";
            $res = $this->client->get($url, [
                'headers' => $this->headers(),
            ]);
            return json_decode((string) $res->getBody(), true);
        } catch (RequestException $e) {
            $status = $e->getResponse() ? $e->getResponse()->getStatusCode() : null;
            $body   = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null;

            return [
                'error'   => true,
                'status'  => $status,
                'message' => $e->getMessage(),
                'body'    => $body,
            ];
        }
    }
}