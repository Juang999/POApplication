<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class VotingEvent extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    protected static $logUnguarded = true;

    protected static $logName = 'system';

    public function Member()
    {
        return $this->hasMany(VotingMember::class, 'voting_event_id', 'id');
    }

    public function Sample()
    {
        return $this->hasMany(VotingSample::class, 'voting_event_id', 'id');
    }

    public function ScoreVoting()
    {
        return $this->hasMany(VotingScore::class, 'voting_event_id', 'id');
    }
}
