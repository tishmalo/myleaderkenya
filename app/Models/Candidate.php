<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'nick_name', 'phone', 'email', 'position_id', 'political_party_id', 'bloc_id',
        'profile_picture', 'about', 'country', 'county', 'constituency', 'ward'
    ];
    // protected $fillable = [
    //     'name', 'nick_name', 'phone', 'email', 'position_id',
    //     'profile_picture', 'about', 'country', 'county',
    //     'constituency', 'ward'
    // ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
// }


// <?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;

// class Candidate extends Model
// {
    

    // public function position()
    // {
    //     return $this->belongsTo(Position::class);
    // }

    public function politicalParty()
    {
        return $this->belongsTo(PoliticalParty::class);
    }

    public function bloc()
    {
        return $this->belongsTo(Bloc::class);
    }
}
