<?php

namespace App\Http\Middleware;

use Fideloper\Proxy\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Di production (Cloudkilat / reverse proxy), IP asli user ada di
     * X-Forwarded-For. Tanpa trust proxy, throttle melihat IP proxy saja
     * sehingga banyak user berbagi limit yang sama → error 429 sporadis.
     *
     * @var array|string
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_ALL;
}
