<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'date',
        'location',
        'price',
        'is_active',
    ];

    protected $casts = [
        'date' => 'datetime',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
