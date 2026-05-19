<?php

return [
    'school' => [
        'name' => env('AUTO_SCHOOL_NAME', env('APP_NAME', 'Autoescola')),
        'address' => env('AUTO_SCHOOL_ADDRESS', 'Endereco da autoescola nao configurado'),
        'document' => env('AUTO_SCHOOL_DOCUMENT'),
        'phone' => env('AUTO_SCHOOL_PHONE'),
    ],

    'payment_methods' => [
        'dinheiro' => 'Dinheiro',
        'pix' => 'Pix',
        'cartao_debito' => 'Cartao de debito',
        'cartao_credito' => 'Cartao de credito',
        'boleto' => 'Boleto',
        'transferencia' => 'Transferencia',
        'outro' => 'Outro',
    ],
];
