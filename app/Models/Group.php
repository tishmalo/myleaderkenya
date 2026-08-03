<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use Illuminate\Database\Eloquent\Model;


class Group extends Model implements AuditableContract {
    use AuditsChanges;
    protected $fillable = ['name', 'description', 'created_by', 'invite_code'];

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function members() { return $this->hasMany(GroupMember::class); }
    public function messages() { return $this->hasMany(GroupMessage::class); }
}
