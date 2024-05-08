<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;


class PriceList extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];
}
