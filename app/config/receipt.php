<?php

return [
    'school' => [
        'name' => env('AUTO_SCHOOL_NAME', env('APP_NAME', 'Autoescola')),
        'address' => env('AUTO_SCHOOL_ADDRESS', 'Endereço da autoescola não configurado'),
        'document' => env('AUTO_SCHOOL_DOCUMENT'),
        'phone' => env('AUTO_SCHOOL_PHONE'),
    ],

    'payment_methods' => [
        'dinheiro' => 'Dinheiro',
        'pix' => 'Pix',
        'cartao_debito' => 'Cartão de débito',
        'cartao_credito' => 'Cartão de crédito',
        'boleto' => 'Boleto',
        'transferencia' => 'Transferência',
        'outro' => 'Outro',
    ],
];
