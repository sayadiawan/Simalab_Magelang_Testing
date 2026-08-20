<?php

namespace App\Services;

use DateTime;
use DateTimeZone;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class BpjsPcareClient
{
    private string $consId;
    private string $secretKey;
    private string $userKey;
    private string $pcareUser;
    private string $pcarePass;
    private string $kdAplikasi;
    private string $baseUrl;
    private string $service;
    private array $hn; // header names

    public function __construct()
    {
        $cfg = config('bpjs');
        $this->consId     = (string) $cfg['cons_id'];
        $this->secretKey  = (string) $cfg['secret_key'];
        $this->userKey    = (string) $cfg['user_key'];
        $this->pcareUser  = (string) $cfg['pcare_user'];
        $this->pcarePass  = (string) $cfg['pcare_pass'];
        $this->kdAplikasi = (string) $cfg['kd_aplikasi'];
        $this->baseUrl    = rtrim((string) $cfg['base_url'], '/');
        $this->service    = trim((string) $cfg['service'], '/');
        $this->hn         = $cfg['headers'];
    }

    private function utcEpoch(): string
    {
        return (string) (new DateTime('now', new DateTimeZone('UTC')))->getTimestamp();
    }

    private function signature(string $t): string
    {
        $data = $this->consId . '&' . $t;
        return base64_encode(hash_hmac('sha256', $data, $this->secretKey, true));
    }

    private function basicAuth(): string
    {
        $raw = $this->pcareUser . ':' . $this->pcarePass . ':' . $this->kdAplikasi;
        return 'Basic ' . base64_encode($raw);
    }

    private function headers(): array
    {
        $t = $this->utcEpoch();
        return [
            $this->hn['cons_id']       => $this->consId,
            $this->hn['timestamp']     => $t,
            $this->hn['signature']     => $this->signature($t),
            $this->hn['user_key']      => $this->userKey,
            $this->hn['authorization'] => $this->basicAuth(),
            'Accept'                   => 'application/json',
            'Content-Type'             => 'application/json',
        ];
    }

    private function url(string $path): string
    {
        return "{$this->baseUrl}/{$this->service}/" . ltrim($path, '/');
    }

    /**
     * Tambah Pendaftaran/Kunjungan
     * @param array $payload payload sesuai dokumen WS (lihat FormRequest di bawah)
     * @return array decoded JSON (response sudah didecode bila tidak terenkripsi)
     * @throws RequestException
     */
    public function addPendaftaran(array $payload): array
    {
        $client = new Client([
            'timeout' => 25,
            'headers' => $this->headers(),
        ]);

        try {
            $response = $client->post($this->url('pendaftaran'), [
                'json' => $payload
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return is_array($data) ? $data : ['raw' => $response->getBody()->getContents()];
            
        } catch (RequestException $e) {
            // Rethrow untuk kompatibilitas dengan controller
            throw $e;
        }
    }
}