<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait AliasesLegacyStaffId
{
    protected function doctorId(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['clinical_staff_id'] ?? null,
            set: fn ($value) => ['clinical_staff_id' => $value],
        );
    }
}
