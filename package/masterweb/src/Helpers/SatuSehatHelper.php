<?php

namespace Smt\Masterweb\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Smt\Masterweb\Models\SatuSehat;

class SatuSehatHelper
{
    const TOKEN_CACHE_KEY = 'satusehat.access_token';
    const TOKEN_LOCK_KEY = 'satusehat.access_token.lock';

    protected $client;
    protected $baseUri;
    protected $authUrl;
    protected $clientId;
    protected $clientSecret;
    protected $orgId;
    protected $token;

    public function __construct()
    {
      $this->baseUri = config('services.satu_sehat.base_uri');
      $this->authUrl = config('services.satu_sehat.auth_url');
      $this->clientId = config('services.satu_sehat.client_id');
      $this->clientSecret = config('services.satu_sehat.client_secret');
      $this->orgId = config('services.satu_sehat.org_id');

      $this->client = new Client([
        'base_uri' => $this->baseUri,
      ]);
    }

  /**
   * Apakah integrasi Satu Sehat aktif.
   * Nonaktif di testing agar alur verifikasi tidak bergantung nama/NIK resmi.
   */
  public static function isEnabled(): bool
  {
    return (bool) config('services.satu_sehat.enabled', false);
  }

  /**
   * Response kosong yang aman dipakai pemanggil saat Satu Sehat dimatikan.
   */
  protected static function disabledResponse(): array
  {
    return [
      'status_code' => 0,
      'body' => [
        'resourceType' => 'OperationOutcome',
        'issue' => [[
          'severity' => 'information',
          'code' => 'suppressed',
          'diagnostics' => 'Satu Sehat disabled (SATUSEHAT_ENABLED=false / VERSION_SATUSEHAT != prd)',
        ]],
      ],
      'skipped' => true,
    ];
  }

  /**
   * Ambil token Satu Sehat dari cache/DB, atau minta baru jika perlu.
   * Tidak melempar exception (termasuk 429) agar proses login tetap jalan.
   */
  public static function ensureAccessToken()
  {
    if (!static::isEnabled()) {
      return null;
    }

    $cached = Cache::get(self::TOKEN_CACHE_KEY);
    if (!empty($cached)) {
      return $cached;
    }

    $stored = optional(SatuSehat::first())->token;

    $clientId = config('services.satu_sehat.client_id');
    $clientSecret = config('services.satu_sehat.client_secret');
    $authUrl = config('services.satu_sehat.auth_url');

    if (empty($clientId) || empty($clientSecret) || empty($authUrl)) {
      return $stored;
    }

    if (!Cache::add(self::TOKEN_LOCK_KEY, 1, 20)) {
      usleep(250000);
      $cached = Cache::get(self::TOKEN_CACHE_KEY);
      if (!empty($cached)) {
        return $cached;
      }

      return $stored;
    }

    try {
      $client = new Client(['timeout' => 15]);
      $response = $client->request(
        'POST',
        rtrim($authUrl, '/') . '/accesstoken?grant_type=client_credentials',
        [
          'form_params' => [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
          ],
          'http_errors' => false,
        ]
      );

      $status = $response->getStatusCode();
      if ($status === 200) {
        $body = json_decode((string) $response->getBody(), true);
        $token = $body['access_token'] ?? null;
        $expiresIn = (int) ($body['expires_in'] ?? 3600);
        $ttl = max(60, $expiresIn - 120);

        if (!empty($token)) {
          Cache::put(self::TOKEN_CACHE_KEY, $token, $ttl);
          $row = SatuSehat::first() ?: new SatuSehat();
          $row->token = $token;
          $row->save();

          return $token;
        }
      }

      if ($status === 429) {
        Log::warning('Satu Sehat token request rate-limited (429). Reusing stored token if available.');
      } else {
        Log::warning('Satu Sehat token request failed.', ['status' => $status]);
      }
    } catch (\Throwable $e) {
      Log::warning('Satu Sehat token request skipped: ' . $e->getMessage());
    } finally {
      Cache::forget(self::TOKEN_LOCK_KEY);
    }

    if (!empty($stored)) {
      Cache::put(self::TOKEN_CACHE_KEY, $stored, 300);
    }

    return $stored;
  }

  protected function resolveToken()
  {
    if (empty($this->token)) {
      $this->token = static::ensureAccessToken();
    }

    return $this->token;
  }


  public function get($endpoint, $queryParams = [])
  {
    if (!static::isEnabled()) {
      Log::info('Satu Sehat GET skipped (disabled)', ['endpoint' => $endpoint]);
      return static::disabledResponse();
    }

    try {
      $response = $this->client->request('GET', $endpoint, [
        'headers' => [
          'Authorization' => 'Bearer ' . $this->resolveToken(),
          'Accept' => 'application/json',
          'X-Organization-ID' => $this->orgId,
        ],
        'query' => $queryParams,
      ]);

      return [
        'status_code' => $response->getStatusCode(),
        'body' => json_decode($response->getBody(), true)
      ];
    } catch (RequestException $e) {
      return $this->handleException($e);
    }
  }

  public function post($endpoint, $data = [])
  {
    if (!static::isEnabled()) {
      Log::info('Satu Sehat POST skipped (disabled)', ['endpoint' => $endpoint]);
      return static::disabledResponse();
    }

    try {
      $response = $this->client->post(  config('services.satu_sehat.base_uri').$endpoint, [
        'headers' => [
          'Authorization' => 'Bearer ' . $this->resolveToken(),
          'Accept' => 'application/json',
          'X-Organization-ID' => $this->orgId,
        ],
        'json' => $data,
      ]);

      return [
        'status_code' => $response->getStatusCode(),
        'body' => json_decode($response->getBody(), true)
      ];
    } catch (RequestException $e) {
      return $this->handleException($e);
    }
  }

  public function put($endpoint, $id, $data = [])
  {
    if (!static::isEnabled()) {
      Log::info('Satu Sehat PUT skipped (disabled)', ['endpoint' => $endpoint, 'id' => $id]);
      return static::disabledResponse();
    }

    try {
      $response = $this->client->put(config('services.satu_sehat.base_uri') . $endpoint."/".$id, [
        'headers' => [
          'Authorization' => 'Bearer ' . $this->resolveToken(),
          'Accept' => 'application/json',
          'X-Organization-ID' => $this->orgId,
        ],
        'json' => $data,
      ]);

      return [
        'status_code' => $response->getStatusCode(),
        'body' => json_decode($response->getBody(), true)
      ];
    } catch (RequestException $e) {
      return $this->handleException($e);
    }
  }

  private function handleException(RequestException $e)
  {
    $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 500;
    $body = $e->hasResponse() ? json_decode($e->getResponse()->getBody()->getContents(), true) : ['error' => 'Connection Error: ' . $e->getMessage()];

    return [
      'status_code' => $statusCode,
      'body' => $body
    ];
  }
}
