<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class KittyType extends Model implements AuditableContract
{
    use AuditsChanges;

    protected $fillable = ['name', 'slug', 'description', 'sort_order', 'is_active'];
    protected $casts = ['sort_order' => 'integer', 'is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder { return $query->where('is_active', true); }
    public function scopeOrdered(Builder $query): Builder { return $query->orderBy('sort_order')->orderBy('name'); }
    public function purchases(): HasMany { return $this->hasMany(UserTokenPurchase::class); }
}
