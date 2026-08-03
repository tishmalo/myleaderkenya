<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampaignPriorityCategory extends Model implements AuditableContract
{
    use AuditsChanges;
    public const ICONS = [
        'fas fa-seedling', 'fas fa-briefcase', 'fas fa-graduation-cap', 'fas fa-shield-halved',
        'fas fa-heart-pulse', 'fas fa-road', 'fas fa-droplet', 'fas fa-house',
        'fas fa-wheat-awn', 'fas fa-leaf', 'fas fa-scale-balanced', 'fas fa-people-group',
        'fas fa-landmark', 'fas fa-lightbulb', 'fas fa-chart-line', 'fas fa-bullseye',
    ];

    protected $fillable = ['name', 'slug', 'icon', 'description', 'sort_order', 'is_active', 'created_by', 'updated_by'];
    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];

    public function candidatePriorities(): HasMany
    {
        return $this->hasMany(CandidateCampaignPriority::class);
    }
}
