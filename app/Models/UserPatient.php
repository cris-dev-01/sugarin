<?php

declare(strict_types=1);

namespace App\Models;

use Abbasudo\Purity\Traits\Filterable;
use Abbasudo\Purity\Traits\Sortable;
use App\Traits\FormatsDocument;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPatient extends Model
{
    use Filterable, HasFactory, SoftDeletes, Sortable, FormatsDocument;

    protected $appends = ['formatted_document'];

    protected $fillable = [
        'document_type',
        'document',
        'illness_found_at',
        'initial_max_glucose_value',
        'user_id',
        'glucose_range_id',
    ];

    public function formattedDocument(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->formatDocumentWithCalculatedVerificator($this->document)
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function glucoseRange(): BelongsTo
    {
        return $this->belongsTo(GlucoseRange::class);
    }
}
