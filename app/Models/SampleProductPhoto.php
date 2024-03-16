<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class SampleProductPhoto extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    protected static $logUnguarded = true;

    protected static $logName = 'system';

    public function SampleProduct()
    {
        return $this->belongsTo(SampleProduct::class, 'sample_product_id', 'id');
    }
}
