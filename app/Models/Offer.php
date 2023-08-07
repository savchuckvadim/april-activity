<?php

namespace App\Models;

use App\Http\Resources\UserRecource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;





    public function advertiser()
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }
    public function followers()
    {
        return $this->belongsToMany(User::class, 'offer_masters', 'offer_id', 'master_id' );
    }
    public function links()
    {
        return $this->hasMany(Link::class, 'offer_id');
    }
    public function transitions()
    {
        $transitions = 0;
        $failTransitions = 0;

        if($this->links){
            foreach ($this->links as $link) {
                $transitions = $transitions + $link->transitions;
            }


            foreach ($this->links as $link) {
                $failTransitions = $failTransitions + $link->fail_transitions;
            }

        }
        return [
            'transitions' => $transitions,
            'failTransitions' => $failTransitions
        ];
    }
}
