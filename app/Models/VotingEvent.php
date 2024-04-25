<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Facades\DB;

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

    public function Highest()
    {
        return $this->hasOne(VotingScore::class, 'voting_event_id', 'id')->orderByDesc('voting_scores.score');
    }

    public function Lowest()
    {
        return $this->hasOne(VotingScore::class, 'voting_event_id', 'id')->orderBy('voting_scores.score', 'ASC');
    }
}
