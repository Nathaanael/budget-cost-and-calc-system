<?php

namespace App\Models\Concerns;

use RuntimeException;

trait HasRandomFiveDigitId
{
    public static function bootHasRandomFiveDigitId(): void
    {
        static::creating(function ($model) {
            if ($model->getKey() !== null) {
                return;
            }

            for ($attempt = 0; $attempt < 100; $attempt++) {
                $id = random_int(10000, 99999);

                if (! static::withoutGlobalScopes()->whereKey($id)->exists()) {
                    $model->setAttribute($model->getKeyName(), $id);

                    return;
                }
            }

            throw new RuntimeException('Tidak dapat menghasilkan ID master unik 5 digit.');
        });
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'int';
    }
}
