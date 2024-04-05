<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class CategoryPartnumber extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    protected static $logUnguarded = true;

    protected static $logName = 'system';
}
