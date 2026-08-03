<?php
namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Illuminate\Database\Eloquent\Model;

class GroupMessage extends Model implements AuditableContract {
    use AuditsChanges;
    protected $fillable = ['group_id', 'username', 'message', 'message_type', 'aspirant_poll_id', 'latitude', 'longitude'];

    public function group() { return $this->belongsTo(Group::class); }
    public function poll() { return $this->belongsTo(AspirantPoll::class, 'aspirant_poll_id'); }
}
