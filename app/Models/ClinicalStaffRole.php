<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicalStaffRole extends Model
{
    use HasFactory;

    protected $table = 'clinical_staff_roles';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'role_value',
        'description',
        'permissions',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'permissions' => 'array',
        ];
    }

    public function doctors(): HasMany
    {
        return $this->hasMany(ClinicalStaff::class, 'clinical_staff_role_id');
    }
}
