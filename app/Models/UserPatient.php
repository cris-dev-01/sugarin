<?php

declare(strict_types=1);

namespace App\Models;

use Abbasudo\Purity\Traits\Filterable;
use Abbasudo\Purity\Traits\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPatient extends Model
{
    use Filterable, HasFactory, SoftDeletes, Sortable;

    protected $fillable = [
        'document_type',
        'document',
        'illness_found_at',
        'initial_max_glucose_value',
    ];
}
