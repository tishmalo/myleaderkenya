<?php
namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model implements AuditableContract {
    use AuditsChanges;
    protected $fillable = ['group_id', 'user_id'];
    public $timestamps = true;

    public function group() { return $this->belongsTo(Group::class); }
    public function user()  { return $this->belongsTo(User::class); }
}
