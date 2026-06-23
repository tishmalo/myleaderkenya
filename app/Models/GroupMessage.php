<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GroupMessage extends Model {
    protected $fillable = ['group_id', 'username', 'message', 'latitude', 'longitude'];

    public function group() { return $this->belongsTo(Group::class); }
}