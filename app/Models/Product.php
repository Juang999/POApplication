<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = ['id'];

    public function Photo()
    {
        return $this->hasMany(Photo::class, 'product_id', 'id');
    }

    public function Thumbnail()
    {
        return $this->hasOne(Photo::class, 'product_id', 'id');
    }

    public function BufferProduct()
    {
        return $this->hasOne(BufferProduct::class, 'clothes_id', 'id');
    }

    public function DetailSession()
    {
        return $this->hasMany(DetailSession::class, 'product_id', 'id');
    }

    public function DetailOrder()
    {
        return $this->hasMany(Order::class, 'product_id', 'id');
    }

    public function PriceList()
    {
        return $this->hasMany(PriceList::class, 'clothes_id', 'id');
    }

    public function DataMaterial()
    {
        return $this->hasMany(FabricTextureProduct::class, 'product_id', 'id')->where('material_type', '=', 'material');
    }

    public function MaterialAdditional()
    {
        return $this->hasMany(FabricTextureProduct::class, 'product_id', 'id')->where('material_type', '=', 'material_additional');
    }

    public function Accessories()
    {
        return $this->hasMany(FabricTextureProduct::class, 'product_id', 'id')->where('material_type', '=', 'accessories');
    }

    public function AccessoriesProduct()
    {
        return $this->hasMany(FabricTextureProduct::class, 'product_id', 'id')->where('material_type', '=', 'accessories_product');
    }
}
