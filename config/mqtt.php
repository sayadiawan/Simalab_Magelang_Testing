<?php

return [

    'host' => env('MQTT_HOST', 'mqtt.nexussolusiteknologi.co.id'),

    'port' => (int) env('MQTT_PORT', 8883),

    'username' => env('MQTT_SERVER_USERNAME'),

    'password' => env('MQTT_SERVER_PASSWORD'),

    'tls' => filter_var(env('MQTT_TLS', true), FILTER_VALIDATE_BOOLEAN),

    'qos' => (int) env('MQTT_QOS', 1),

    'retain' => filter_var(env('MQTT_RETAIN', false), FILTER_VALIDATE_BOOLEAN),

    'client_id_prefix' => env('MQTT_CLIENT_ID_PREFIX', 'simalab-server'),

    'connect_timeout' => (int) env('MQTT_CONNECT_TIMEOUT', 10),

    'keep_alive' => (int) env('MQTT_KEEP_ALIVE', 60),

    'listen_timeout' => (int) env('MQTT_LISTEN_TIMEOUT', 6),

    'topics' => [
        'tms' => [
            'order' => env('MQTT_TOPIC_TMS_ORDER', 'simalab/magelang/tms/order'),
            'result' => env('MQTT_TOPIC_TMS_RESULT', 'simalab/magelang/tms/result'),
            'status' => env('MQTT_TOPIC_TMS_STATUS', 'simalab/magelang/tms/status'),
        ],
    ],

];
