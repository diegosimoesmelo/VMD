<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;

    public const SHIFT_MORNING = 'manha';
    public const SHIFT_AFTERNOON = 'tarde';
    public const SHIFT_NIGHT = 'noite';

    protected $fillable = [
        'nome',
        'cpf',
        'telefone',
        'categorias_ensino',
        'turnos_disponiveis',
    ];

    protected $casts = [
        'categorias_ensino' => 'array',
        'turnos_disponiveis' => 'array',
    ];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * @return list<string>
     */
    public static function categoryOptions(): array
    {
        return ['A', 'B', 'C', 'D', 'E'];
    }

    /**
     * @return array<string, string>
     */
    public static function shiftOptions(): array
    {
        return [
            self::SHIFT_MORNING => 'Manha',
            self::SHIFT_AFTERNOON => 'Tarde',
            self::SHIFT_NIGHT => 'Noite',
        ];
    }

    public function teachesCategory(?string $category): bool
    {
        return $category !== null && in_array($category, $this->categorias_ensino ?? [], true);
    }

    public function supportsTimeSlot(string $time): bool
    {
        $turnos = $this->turnos_disponiveis ?? [];

        if (in_array(self::SHIFT_MORNING, $turnos, true) && in_array($time, $this->morningTimeSlots(), true)) {
            return true;
        }

        if (in_array(self::SHIFT_AFTERNOON, $turnos, true) && in_array($time, $this->afternoonTimeSlots(), true)) {
            return true;
        }

        return false;
    }

    /**
     * @return list<string>
     */
    public function schedulableCategories(): array
    {
        return array_values(array_intersect($this->categorias_ensino ?? [], ['A', 'B']));
    }

    /**
     * @return list<string>
     */
    private function morningTimeSlots(): array
    {
        return ['07:00', '07:50', '08:40', '09:30', '10:20', '11:10'];
    }

    /**
     * @return list<string>
     */
    private function afternoonTimeSlots(): array
    {
        return ['14:00', '14:50', '15:40', '16:30', '17:20'];
    }
}
