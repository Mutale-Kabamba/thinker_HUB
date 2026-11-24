<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'country_code',
        'country_name',
        'region_label',
        'timezone',
        'currency',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Tenant $tenant): void {
            if (empty($tenant->slug) && ! empty($tenant->name)) {
                $base = Str::slug($tenant->name);
                $slug = $base;
                $i = 1;
                while (static::query()->where('slug', $slug)->where('id', '!=', $tenant->id)->exists()) {
                    $slug = "{$base}-{$i}";
                    $i++;
                }
                $tenant->slug = $slug;
            }

            if (! empty($tenant->country_code)) {
                $tenant->country_code = strtoupper(trim($tenant->country_code));
            }
            if (! empty($tenant->currency)) {
                $tenant->currency = strtoupper(trim($tenant->currency));
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function programAdmins(): HasMany
    {
        return $this->hasMany(User::class)->where('role', User::ROLE_PROGRAM_ADMIN);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function getDisplayLabelAttribute(): string
    {
        $parts = array_filter([$this->name, $this->country_name ?: $this->country_code]);

        return implode(' — ', $parts);
    }
}
