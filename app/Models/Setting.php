<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    protected function castedValue(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->type) {
                'integer' => (int) $this->value,
                'float' => (float) $this->value,
                default => $this->value,
            },
        );
    }
}
