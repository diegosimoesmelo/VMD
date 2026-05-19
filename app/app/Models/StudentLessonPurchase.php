<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentLessonPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'user_id',
        'lesson_category',
        'quantity',
        'amount_paid',
        'payment_method',
        'notes',
        'purchased_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'amount_paid' => 'decimal:2',
        'purchased_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
