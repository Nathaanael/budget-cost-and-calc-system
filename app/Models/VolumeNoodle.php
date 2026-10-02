<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VolumeNoodle extends Model
{
    public const LE_FIELDS = ['le_july', 'le_august', 'le_september', 'le_october', 'le_november', 'le_december'];

    public const MONTH_FIELDS = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

    protected $fillable = [
        'area_noodle_id',
        'noodle_id',
        ...self::LE_FIELDS,
        ...self::MONTH_FIELDS,
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return array_fill_keys([...self::LE_FIELDS, 'total_le', ...self::MONTH_FIELDS, 'total_aop'], 'float');
    }

    protected static function booted(): void
    {
        static::saving(function (self $volume): void {
            $volume->total_le = collect(self::LE_FIELDS)->sum(fn (string $field) => (float) $volume->{$field});
            $volume->total_aop = collect(self::MONTH_FIELDS)->sum(fn (string $field) => (float) $volume->{$field});
        });
    }

    public function areaNoodle(): BelongsTo
    {
        return $this->belongsTo(AreaNoodle::class);
    }

    public function noodle(): BelongsTo
    {
        return $this->belongsTo(Noodle::class);
    }
}
