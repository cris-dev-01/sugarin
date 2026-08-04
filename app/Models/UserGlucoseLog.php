<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserGlucoseLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'value',
        'time_block',
        'user_patient_id',
        'status_id',
    ];

    public function userPatient(): BelongsTo
    {
        return $this->belongsTo(UserPatient::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }
}
