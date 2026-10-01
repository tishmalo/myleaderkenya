<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class County extends Model implements AuditableContract
{
    use AuditsChanges;
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'bloc_id',
        'area',
        'population',
        'capital',
        'registered_voters',
        'postal_abbreviation',
        'image',
    ];

    public function bloc()
    {
        return $this->belongsTo(Bloc::class);
    }

    public function blocs()
    {
        return $this->belongsToMany(Bloc::class, 'bloc_county')->withTimestamps();
    }

    protected static function booted(): void
    {
        // The public county page is addressed by slug, so every county needs
        // one whether it arrives through the admin form or an import.
        static::saving(function (self $county): void {
            if (blank($county->slug)) {
                $county->slug = static::uniqueSlug($county->name);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    private static function uniqueSlug(?string $name): string
    {
        $base = Str::slug((string) $name) ?: 'county';
        $slug = $base;
        $suffix = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public function constituencies()
    {
        return $this->hasMany(Constituency::class);
    }

    public function pollingStations()
    {
        return $this->hasMany(PollingStation::class, 'county', 'name');   // 'county' column in polling_stations = 'name' in counties
    }

    //     public function pollingStations()
    // {
    //     return $this->hasMany(\App\Models\PollingStation::class);
    // }
}
