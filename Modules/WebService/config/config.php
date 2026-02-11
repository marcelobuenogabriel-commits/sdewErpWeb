<?php

return [
    'name' => 'WebService',
    'senior' => [
        'cad_usuario_wsdl' => env('SENIOR_CAD_USUARIO_WSDL', 'http://knbrglassfish01:8080/g5-senior-services/'),
        'user' => env('SENIOR_USER', 'sapienspa'),
        'password' => env('SENIOR_PASSWORD', 'S4p13nsp4'),
        'timeout' => env('SENIOR_TIMEOUT', 5),
        'auth_type' => env('SENIOR_AUTH_TYPE', 'http'), // http | wsse | none
        'encrypt' => env('SENIOR_ENCRYPT', 0),
    ],
];
