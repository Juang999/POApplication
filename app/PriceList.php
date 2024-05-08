<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class PriceList extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    public function Area()
    {
        return $this->belongsTo(Area::class);
    }
}
