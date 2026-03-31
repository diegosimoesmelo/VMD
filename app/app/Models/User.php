<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_MANAGER = 'gerente';
    public const ROLE_ADMINISTRATIVE = 'administrativo';
    public const ROLE_TEACHER = 'professor';
    public const ROLE_STUDENT = 'aluno';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'teacher_id',
        'username',
        'role',
        'password',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'must_change_password' => 'boolean',
    ];

    /**
     * @return array<string, string>
     */
    public static function roleOptions(): array
    {
        return [
            self::ROLE_MANAGER => 'Gerente',
            self::ROLE_ADMINISTRATIVE => 'Administrativo',
            self::ROLE_TEACHER => 'Professor',
            self::ROLE_STUDENT => 'Aluno',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function administrativeRoleOptions(): array
    {
        return [
            self::ROLE_MANAGER => 'Gerente',
            self::ROLE_ADMINISTRATIVE => 'Administrativo',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function manageableRoleOptions(): array
    {
        return [
            self::ROLE_MANAGER => 'Gerente',
            self::ROLE_ADMINISTRATIVE => 'Administrativo',
            self::ROLE_TEACHER => 'Professor',
        ];
    }

    public function roleLabel(): string
    {
        return self::roleOptions()[$this->role] ?? $this->role;
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function requiresPasswordChange(): bool
    {
        return $this->must_change_password;
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
