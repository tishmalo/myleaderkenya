<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Location extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'latitude', 'longitude'];

    public function getLatitudeAttribute($value): ?string
    {
        return $this->decryptCoordinate($value);
    }

    public function setLatitudeAttribute($value): void
    {
        $this->attributes['latitude'] = $this->encryptCoordinate($value);
    }

    public function getLongitudeAttribute($value): ?string
    {
        return $this->decryptCoordinate($value);
    }

    public function setLongitudeAttribute($value): void
    {
        $this->attributes['longitude'] = $this->encryptCoordinate($value);
    }

    private function encryptCoordinate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Crypt::encryptString((string) $value);
    }

    private function decryptCoordinate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return (string) $value;
        }
    }
}
