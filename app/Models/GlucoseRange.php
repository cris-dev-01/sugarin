<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GlucoseRange extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'alias',
        'min_fasting_value',
        'max_fasting_value',
        'min_non_fasting_value',
        'max_non_fasting_value'
    ];

    public function authorize(): bool
    {
        return $this->user()->can('create-glucose-ranges', GlucoseRange::class);
    }
}
