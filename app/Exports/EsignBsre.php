<?php

namespace App\Exports;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\MultipartStream;
use Illuminate\Support\Facades\Storage;

use GuzzleHttp\Psr7\Utils;

class EsignBsre
{
    protected $baseUrl;
    protected $auth;

    public function __construct($options)
    {
        $this->baseUrl = $options['baseUrl'];

        $this->auth = base64_encode("{$options['username']}:{$options['password']}");
    }

    protected function getBase64($url)
    {
        try {
            $client = new Client();
            $response = $client->get($url, ['response_type' => 'body', 'timeout' => 12]);
            return base64_encode($response->getBody());
        } catch (RequestException $e) {
            return $this->responseHandler($e->getCode(), '', $e->getMessage());
        }
    }

    protected function fileChecker($data)
    {

        try {

            if (filter_var($data, FILTER_VALIDATE_URL)) {
                return $this->getBase64($data);
            } elseif (file_exists($data)) {

                // $formData['file'] = Utils::tryFopen($data, 'r');


                return Utils::tryFopen($data, 'r');
            } else {
                return Utils::tryFopen($data, 'r');
            }
        } catch (\Exception $e) {
            return "File check failed: " . $e->getMessage();
        }
    }

    protected function cleanArr($data)
    {
        return array_filter($data, function($value) {
            return !is_null($value) && $value !== '';
        });
    }

    protected function checkArr($data)
    {
        // Validation logic here, using a library like Respect/Validation or Laravel's Validator
        // This is a placeholder for the validation process.
        // If validation fails, throw an exception or return an error response.

        $cleanedData = $this->cleanArr($data);
        return $this->signDoc($cleanedData);
    }

    function signDoc(array $arr)
    {
        $client = new Client();
        $url = "{$this->baseUrl}/api/sign/pdf";

        try {
            // Prepare the form data
            $formData = [];

            // Append 'file' if provided
            if (isset($arr['file'])) {
                $filePath = $arr['file']; // Assuming the file is stored locally
                if (file_exists($filePath)) {

                        $formData['file'] = Utils::tryFopen($filePath, 'r');




                }
                unset($arr['file']); // Remove file from $arr as it has been processed
            }

            // Append 'imageTTD' if provided
            if (isset($arr['imageTTD'])) {
                $imagePath = storage_path('app/' . $arr['imageTTD']); // Assuming image is stored locally
                if (file_exists($imagePath)) {
                    $formData['imageTTD'] = fopen($imagePath, 'r');
                }
                unset($arr['imageTTD']); // Remove imageTTD from $arr as it has been processed
            }

            // Append remaining data fields
            foreach ($arr as $key => $value) {
                if (is_bool($value)) {
                    # code...
                    if ($value) {
                        # code...
                        $formData[$key] = "true";
                    }else{
                        $formData[$key] = "false";
                    }
                }else{

                    $formData[$key] = (string)$value; // Convert all values to string
                }
            }





            // Guzzle HTTP POST request with form data
            $response = $client->request('POST',$url, [
                'headers' => [
                    'Authorization' => 'Basic ' . $this->auth,
                ],
                'multipart' => array_map(function ($key, $value) {
                    return [
                        'name'     => $key,
                        'contents' => $value
                    ];
                }, array_keys($formData), $formData),
                'timeout' => 7000,
                'verify' => false,
            ]);



            // Handle the response and parse file if needed
            $responseData = $response->getBody()->getContents();

            return [
                'status'      => $response->getStatusCode(),
                'file'        => $responseData,
                'id_dokumen'  => $response->getHeader('id_dokumen')[0] ?? null,
                'message'     => 'Success generating data'
            ];

        } catch (RequestException $e) {
            return [
                'status'  => $e->getResponse() ? $e->getResponse()->getStatusCode() : 500,
                'message' => $e->getMessage()
            ];
        }
    }



    // protected function signDoc($data)
    // {
    //     try {
    //         $client = new Client();
    //         $multipart = [];
    //         $file = file_get_contents($data['file']);

    //         if (isset($data['file'])) {
    //             $multipart[] = [
    //                 'name' => 'file',
    //                 'contents' => $file,
    //                 'filename' =>$data['file'],
    //             ];
    //         }

    //         if (isset($data['imageTTD'])) {
    //             $imageTTD = file_get_contents($data['imageTTD']);

    //             $multipart[] = [
    //                 'name' => 'imageTTD',
    //                 'contents' => $imageTTD,
    //                 'filename' =>$data['imageTTD']
    //             ];
    //         }



    //         foreach ($this->cleanArr($data) as $key => $value) {
    //             if (!in_array($key, ['file', 'imageTTD'])) {
    //                 $multipart[] = [
    //                     'name' => $key,
    //                     'contents' => $value,
    //                 ];
    //             }
    //         }
    //         // dd($multipart);

    //         $response = $client->request('POST',"{$this->baseUrl}/api/sign/pdf", [
    //             'headers' => [
    //                 'Authorization' => "Basic {$this->auth}",
    //             ],
    //             'multipart' => $multipart,
    //             'timeout' => 7000,
    //         ]);

    //         $bodyRaw = (string) $response->getBody();

    //         // dd($response);
    //         // try {
    //         //     $body = json_decode($bodyRaw, true);
    //         //     if (json_last_error() !== JSON_ERROR_NONE) {
    //         //         throw new \Exception('JSON decode error: ' . json_last_error_msg());
    //         //     }
    //         // } catch (\Exception $e) {

    //         //     dd($e->getMessage());
    //         //     // \Log::error('JSON Decode Exception: ' . $e->getMessage());
    //         //     // Handle the error appropriately
    //         // }
    //         return $this->responseHandler($response->getStatusCode(),  $bodyRaw , "Success generate data");

    //     } catch (RequestException $e) {
    //         return $this->responseHandler($e->getCode(), '', $e->getMessage());
    //     }
    // }

    protected function responseHandler($status, $data, $message)
    {
        return [
            'status' => $status,
            'data' => $data,
            'message' => $message,
        ];
    }

    public function signInvisible($data)
    {
        $newArr = [
            'file' => $data['file'],
            'nik' => $data['nik'],
            'passphrase' => $data['passphrase'],
            'tampilan' => 'invisible',
            'reason' => $data['reason'],
            'location' => $data['location'],
            'text' => $data['text'],
        ];
        return $this->checkArr($newArr);
    }

    public function signQr($data)
    {
        $newArr = [
            'file' => $data['file'],
            'nik' => $data['nik'],
            'passphrase' => $data['passphrase'],
            'tampilan' => $data['tampilan'],
            'image' => $data['image'],
            'linkQR' => $data['linkQR'],
            'page' => $data['page'],
            'xAxis' => $data['xAxis'],
            'yAxis' => $data['yAxis'],
            'width' => $data['width'],
            'height' => $data['height'],
            'reason' => $data['reason'],
            'location' => $data['location'],
            'text' => $data['text'],
        ];
        return $this->checkArr($newArr);
    }

    public function signImageTTD($data)
    {
        $newArr = [
            'file' => $data['file'],
            'nik' => $data['nik'],
            'passphrase' => $data['passphrase'],
            'tampilan' => $data['tampilan'],
            'image' => $data['image'],
            'imageTTD' => $data['imageTTD'],
            'linkQR' => $data['linkQR'],
            'page' => $data['page'],
            'xAxis' => $data['xAxis'],
            'yAxis' => $data['yAxis'],
            'width' => $data['width'],
            'height' => $data['height'],
            'tag_koordinat' => $data['tag_koordinat'],
            'reason' => $data['reason'],
            'location' => $data['location'],
            'text' => $data['text'],
        ];
        return $this->checkArr($newArr);
    }

    public function verify($data)
    {
        $newArr = [
            'signed_file' => $data['signed_file'],
        ];

        try {
            $client = new Client();
            $multipart = [];

            if (isset($newArr['signed_file'])) {
                $multipart[] = [
                    'name' => 'signed_file',
                    'contents' => $this->fileChecker($newArr['signed_file']),
                    'filename' => basename($newArr['signed_file']),
                ];
            }


            // $multipart[] = [
            //     'name' => 'signed_file',
            //     'contents' => Storage::get("public/pdf/file_px4nzBR53Q.pdf"),
            //     'filename' => basename($newArr['signed_file']),
            // ];


            $response = $client->post("{$this->baseUrl}/api/sign/verify", [
                'headers' => [
                    'Authorization' => "Basic {$this->auth}",
                ],
                'multipart' => $multipart,
                'timeout' => 30,
                'verify' => false,
            ]);

            $body = json_decode($response->getBody(), true);

            $docValidator = $body['jumlah_signature'] == 0
                ? 'Dokumen belum ditandatangani secara elektronik'
                : $body['summary'];
            return $this->responseHandler($response->getStatusCode(), $body, $docValidator);

        } catch (RequestException $e) {
            return $this->responseHandler($e->getCode(), '', $e->getMessage());
        }
    }

    public function status($data)
    {
        $newArr = [
            'nik' => (int)$data['nik'],
        ];

        try {
            $client = new Client();
            $response = $client->get("{$this->baseUrl}/api/user/status/{$newArr['nik']}", [
                'headers' => [
                    'Authorization' => "Basic {$this->auth}",
                ],
                'timeout' => 7,
            ]);

            return $this->responseHandler($response->getStatusCode(), json_decode($response->getBody(), true), 'Success');

        } catch (RequestException $e) {
            return $this->responseHandler($e->getCode(), '', $e->getMessage());
        }
    }

    public function downloadDoc($data)
    {
        $newArr = [
            'id_doc' => (string)$data['id_doc'],
        ];

        try {

            $client = new Client();
            $response = $client->get("{$this->baseUrl}/api/sign/download/{$newArr['id_doc']}", [
                'headers' => [
                    'Authorization' => "Basic {$this->auth}",
                ],
                'timeout' => 7000,
                'stream' => true,
            ]);

            $body = (string) $response->getBody();

            return $this->responseHandler($response->getStatusCode(), [
                'file' => base64_encode($body),
                'id_dokumen' => $response->getHeader('id_dokumen')[0],
            ], "Success generate data");

        } catch (RequestException $e) {
            return $this->responseHandler($e->getCode(), '', "Dokumen telah didownload sebelumnya");
        }
    }
}
