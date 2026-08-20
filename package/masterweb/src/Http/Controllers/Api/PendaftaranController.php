<?php

namespace Smt\Masterweb\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BpjsPcareClient;
use Illuminate\Http\JsonResponse;
use GuzzleHttp\Exception\RequestException;
use Smt\Masterweb\Http\Controllers\Requests\Pcare\AddPendaftaranRequest;

use AamDsam\Bpjs\PCare;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Log;

class PendaftaranController extends Controller
{
    public function store(AddPendaftaranRequest $request, BpjsPcareClient $pcare): JsonResponse
    {
        $payload = $request->validated();

        try {
            $result = $pcare->addPendaftaran($payload);
            return response()->json([
                'ok'   => true,
                'data' => $result,
            ]);
        } catch (RequestException $e) {
            $status = $e->getCode() ?: 500;
            $message = $e->getMessage();
            $body = null;
            
            // Try to get response body if available
            if ($e->hasResponse()) {
                try {
                    $body = json_decode($e->getResponse()->getBody()->getContents(), true);
                } catch (\Exception $jsonException) {
                    $body = $e->getResponse()->getBody()->getContents();
                }
            }
            
            return response()->json([
                'ok'      => false,
                'status'  => $status,
                'message' => $message,
                'body'    => $body,
            ], $status);
        } catch (\Throwable $e) {
            return response()->json([
                'ok'      => false,
                'status'  => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    function pcare_conf(){
        $config = [
                'cons_id'      => config('bpjs.consid'),
                'secret_key'   => config('bpjs.screet_key'),
                'username'     => config('bpjs.username'),
                'password'     => config('bpjs.password'),
                'app_code'     => config('bpjs.app_code'),
                'base_url'     => config('bpjs.base_url'),
                'service_name' => config('bpjs.service_name'),
        ];
        return $config;
    }

    // public function index(){
    //     // dd();
    //     // $bpjs = new PCare\Peserta($this->pcare_conf());
    //     // return $bpjs->keyword('0002259319037')->show();

    //     $bpjs = new PCare\Peserta($this->pcare_conf());
    //     return $bpjs->jenisKartu('NOKA')->keyword('0002259319037')->show();
    // }


        
    public function index()
    {
        // --- Ambil kredensial dari config
        $consId    = config('bpjs.vclaim.cons_id');
        $secretKey = config('bpjs.vclaim.secret_key');
        $userKey   = config('bpjs.vclaim.user_key');
        $baseUrl   = rtrim(config('bpjs.vclaim.base_url'), '/') . '/'; // contoh: https://new-api.bpjs-kesehatan.go.id/vclaim-rest/

        // 1) Timestamp UTC (detik)
        $tStamp = (string) time();

        // 2) Signature: Base64(HMAC_SHA256(consId&timestamp, secretKey))
        $signature = base64_encode(hash_hmac('sha256', $consId . '&' . $tStamp, $secretKey, true));

        // 3) Siapkan Guzzle client “aman” (TLS1.2, HTTP/1.1, IPv4, Connection: close, matikan Expect)
       
        $client = new \GuzzleHttp\Client([
            'base_uri'    => rtrim(config('bpjs.vclaim.base_url'), '/') . '/',
            'timeout'     => 30,
            'http_errors' => true,
            'version'     => 1.1,     // paksa HTTP/1.1 (hindari HTTP/2)
            'verify'      => true,    // pastikan CA bundle ada
            'headers'     => [
              'User-Agent'      => 'Silaboy/1.0 (silaboy.id))',
              'Accept'          => 'application/json',
              'Accept-Language' => 'id-ID',
              'Connection'      => 'close',
              'Expect'          => '',             // matikan 100-continue
              'X-cons-id'       => $consId,
              'X-timestamp'     => $tStamp,
              'X-signature'     => $signature,
              'user_key'        => $userKey,
            ],
            'curl' => [
              CURLOPT_SSLVERSION    => CURL_SSLVERSION_TLSv1_2, // TLS 1.2 saja
              CURLOPT_IPRESOLVE     => CURL_IPRESOLVE_V4,       // paksa IPv4
              CURLOPT_TCP_KEEPALIVE => 1,
              // Jika masih reset, coba aktifkan baris berikut:
              // CURLOPT_SSL_CIPHER_LIST => 'HIGH:!aNULL:!MD5',
            ],
          ]);

        // 4) Endpoint (format tanggal HARUS YYYY-MM-DD)
        $noKartu = '0001020303999';
        $tglSep  = '2025-09-08';
        $path    = "Peserta/nokartu/{$noKartu}/tglSEP/{$tglSep}";

        try {
            $resp = $client->get($path);
            $raw  = (string) $resp->getBody();
            $json = json_decode($raw, true);

            // 5) Jika environment mengirim payload terenkripsi di field 'response', lakukan decrypt+decompress
            if (isset($json['response']) && is_string($json['response'])) {
                $decrypted = $this->bpjsDecryptAndMaybeDecompress($json['response'], $consId, $secretKey, $tStamp);
                $json['response'] = json_decode($decrypted, true);
            }

            // Kembalikan sebagai JSON Laravel
            return response()->json($json, $resp->getStatusCode());

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Log detail curl handler context untuk diagnosa (termasuk curl_errno, ssl info, ip, http_version)
            Log::error('BPJS VClaim error', [
                'uri'     => optional($e->getRequest())->getUri() . '',
                'message' => $e->getMessage(),
                'context' => method_exists($e, 'getHandlerContext') ? $e->getHandlerContext() : null,
            ]);
            // Tampilkan pesan ringkas ke client
            return response()->json([
                'metaData' => ['code' => 500, 'message' => 'Gagal koneksi ke gateway VClaim'],
                'error'    => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dekripsi AES-256-CBC dan decompress (jika diperlukan).
     * Beberapa environment VClaim/PCare mengirim 'response' terenkripsi + LZString.
     * Anda bisa sesuaikan bagian decompress tergantung format gateway Anda.
     */
    private function bpjsDecryptAndMaybeDecompress(string $cipherTextB64, string $consId, string $secretKey, string $tStamp): string
    {
        $cipher = base64_decode($cipherTextB64);

        // Kunci: sha256(consId + secretKey + timestamp) -> 32 bytes
        $key = hash('sha256', $consId . $secretKey . $tStamp, true);

        // IV: 16 byte pertama dari key (konvensi yang umum di implementasi komunitas)
        $iv = substr($key, 0, 16);

        $decrypted = openssl_decrypt($cipher, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        // Jika gateway Anda mengemas dengan LZString, aktifkan decompress (butuh composer pkg lz-string)
        // composer require nullpunkt/lz-string-php
        // return \LZCompressor\LZString::decompressFromEncodedURIComponent($decrypted);

        // Kalau tidak terkompres, langsung kembalikan plaintext JSON
        return $decrypted;
    }
}