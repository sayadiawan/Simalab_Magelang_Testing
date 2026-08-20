<?php

namespace Smt\Masterweb\Validators;

use GuzzleHttp\Client;

class ReCaptcha
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        $client = new Client;
        $response = $client->post('https://www.google.com/recaptcha/api/siteverify',
            [
                'form_params' =>
                    [
                        'secret' => config('app.recaptcha_secret_key'),
                        'response' => $value
                    ]
            ]
        );

        $body = json_decode((string)$response->getBody());
        return $body->success;
    }
}