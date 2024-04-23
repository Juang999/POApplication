<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Spatie\Activitylog\Traits\LogsActivity;

class SampleProduct extends Model
{
    use LogsActivity, SoftDeletes;

    protected $guarded = ['id'];

    protected static $logUnguarded = true;

    protected static $logName = 'system';

    public function PhotoSampleProduct()
    {
        return $this->hasMany(SampleProductPhoto::class, 'sample_product_id', 'id');
    }

    public function Thumbnail()
    {
        return $this->hasOne(SampleProductPhoto::class, 'sample_product_id', 'id');
    }

    public function Designer()
    {
        return $this->belongsTo('App\User', 'designer_id', 'attendance_id');
    }

    public function Merchandiser()
    {
        return $this->belongsTo('App\User', 'md_id', 'attendance_id');
    }

    public function LeaderDesigner()
    {
        return $this->belongsTo('App\User', 'leader_designer_id', 'attendance_id');
    }

    public function HistorySampleProduct()
    {
        return $this->hasMany(HistorySampleProduct::class, 'sample_product_id', 'id');
    }

    public function MaterialSample()
    {
        return $this->hasMany(FabricTexture::class, 'sample_product_id', 'id')->where('material_type', '=', 'material');
    }

    public function MaterialAdditional()
    {
        return $this->hasMany(FabricTexture::class, 'sample_product_id', 'id')->where('material_type', '=', 'material_additional');
    }

    public function Accessories()
    {
        return $this->hasMany(FabricTexture::class, 'sample_product_id', 'id')->where('material_type', '=', 'accessories');
    }

    public function AccessoriesProduct()
    {
        return $this->hasMany(FabricTexture::class, 'sample_product_id', 'id')->where('material_type', '=', 'accessories_product');
    }

    public function SampleDesign()
    {
        return $this->hasMany(SampleDesign::class, 'sample_product_id', 'id');
    }

    public function SampleReference()
    {
        return $this->belongsTo('app\Models\SampleProduct', 'reference_sample_id', 'id');
    }
}
