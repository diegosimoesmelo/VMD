<?php

namespace App\Support;

use App\Models\Student;

class SchoolProfile
{
    /**
     * @return array{name:string, address:string, document:mixed, phone:string}
     */
    public static function forStudent(?Student $student): array
    {
        if ($student?->treinamento_para_habilitados) {
            return self::trainingSchool();
        }

        return self::defaultSchool();
    }

    /**
     * @return array{name:string, address:string, document:mixed, phone:mixed}
     */
    public static function defaultSchool(): array
    {
        return config('receipt.school');
    }

    /**
     * @return array{name:string, address:string, document:null, phone:string}
     */
    private static function trainingSchool(): array
    {
        return [
            'name' => 'Domine o Medo de Dirigir',
            'address' => 'Av. João de Barros, 1187 - Espinheiro',
            'document' => null,
            'phone' => '(81) 98627-9871',
        ];
    }
}
