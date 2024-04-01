<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class SubStyle extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected static $logName = 'system';

    protected static $logUnguarded = true;
}
