<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TimeBlock;
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
        'max_non_fasting_value',
    ];

    public function authorize(): bool
    {
        return $this->user()->can('create-glucose-ranges', GlucoseRange::class);
    }

    /**
     * @return array{min: int, max: int}
     */
    public function thresholdsFor(TimeBlock $timeBlock): array
    {
        return $timeBlock === TimeBlock::FASTING
            ? ['min' => $this->min_fasting_value, 'max' => $this->max_fasting_value]
            : ['min' => $this->min_non_fasting_value, 'max' => $this->max_non_fasting_value];
    }
}
